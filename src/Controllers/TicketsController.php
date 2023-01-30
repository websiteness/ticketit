<?php

namespace Kordy\Ticketit\Controllers;

use App\Http\Controllers\Controller;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Kordy\Ticketit\Models;
use Kordy\Ticketit\Models\Agent;
use Kordy\Ticketit\Models\Category;
use Kordy\Ticketit\Models\TSetting;
use Kordy\Ticketit\Models\Ticket;
use Kordy\Ticketit\Models\Status;
use Sentinel;
use DB;
use Kordy\Ticketit\Repositories\CategoriesRepository;
use Kordy\Ticketit\Repositories\CommentsRepository;
use Kordy\Ticketit\Repositories\SettingsRepository;
use Kordy\Ticketit\Services\Integrations\AsanaService;
use App\User;
use App\Models\Account;
use AppendIterator;
use Kordy\Ticketit\Services\Integrations\ClickupService;
use Kordy\Ticketit\Services\Integrations\InfinityService;
use Kordy\Ticketit\Services\Integrations\SlackService;
use App\Models\TicketsDeveloperStatus;
use Kordy\Ticketit\Models\SupportNote;
use App\Jobs\ProcessTicketsToChannels;
use Kordy\Ticketit\Models\Scripts;
use Kordy\Ticketit\Models\TicketTags;
use Kordy\Ticketit\Models\Tags;
use Kordy\Ticketit\Models\Priority;
use Illuminate\Support\Facades\Validator;
use Kordy\Ticketit\Services\TicketsService;

class TicketsController extends Controller
{
    protected $tickets;
    protected $agent;
    protected $user;

    public function __construct(Ticket $tickets, Agent $agent, User $user)
    {
        Cache::flush();
        // $this->middleware('Kordy\Ticketit\Middleware\ResAccessMiddleware', ['only' => ['show']]);
        $this->middleware('Kordy\Ticketit\Middleware\IsAgentMiddleware', ['only' => ['edit']]);
        $this->middleware('Kordy\Ticketit\Middleware\IsAdminMiddleware', ['only' => ['destroy']]);

        $this->tickets = $tickets;
        $this->agent = $agent;
        $this->user = $user;
    }

    public function data(Request $request, $complete = false)
    {

        $user = $this->agent->find(Sentinel::getUser()->id);
        $ticket_service = new TicketsService();
        $ticket_service->saveFiltersInSession($request);

        $tickets_query = $ticket_service->buildDatatableQuery($user, $complete, $request);

        $table = $ticket_service->renderDatatable($tickets_query, $user, $complete, $this->tickets);

        $tags = [];
        $available_tags = Tags::orderBy('name', 'ASC')->get();

        foreach($available_tags as $item) {
            if($item) {
                array_push($tags, $item->name);
            }
        }

        $table = json_decode(json_encode($table), true);
        $table = $table['original'];
        $table['tags'] = $tags;

        return  $table;

    }
                                  
    /**
     * Display a listing of active tickets related to user.
     *
     * @return Response
     */
    public function index(CategoriesRepository $cr)
    {

  
        $users = Agent::all();
        $statuses = Status::all();
        $sub_categories = $cr->getSubCategories();
        $tags = Tags::all();
        $complete = false;
        $ss = new \Kordy\Ticketit\Services\StatsService();
        $statuses_count = $ss->getStatusesAssoc();
        $categories_count = $ss->getCategoriesAssoc();

        $datatable_visible_column_arr = [];
		if(is_array(session('active_ticket_datatable_columns')) && count(session('active_ticket_datatable_columns'))) {
            $datatable_visible_column_arr = session('active_ticket_datatable_columns');
        }

        return view('ticketit::index', compact('complete', 'users', 'statuses', 'sub_categories', 'tags', 'statuses_count', 'categories_count', 'datatable_visible_column_arr'));
    }

    /**
     * Display a listing of completed tickets related to user.
     *
     * @return Response
     */
    public function indexComplete(CategoriesRepository $cr)
    {
        $users = Agent::all();
        $statuses = Status::all();
        $sub_categories = $cr->getSubCategories();

        $complete = true;

        $ss = new \Kordy\Ticketit\Services\StatsService();
        $statuses_count = $ss->getStatusesAssoc();
        $categories_count = $ss->getCategoriesAssoc();
        $datatable_visible_column_arr = [];
        if(is_array(session('completed_ticket_datatable_columns')) && count(session('completed_ticket_datatable_columns'))) {
            $datatable_visible_column_arr = session('completed_ticket_datatable_columns');
        }

        return view('ticketit::index', compact('complete', 'users', 'statuses', 'sub_categories', 'statuses_count', 'categories_count', 'datatable_visible_column_arr'));
    }

    /**
     * Returns priorities, categories and statuses lists in this order
     * Decouple it with list().
     *
     * @return array
     */
    protected function PCS()
    {
        $priorities = Cache::remember('ticketit::priorities', 60, function () {
            return Models\Priority::all();
        });

        $categories = Cache::remember('ticketit::categories', 60, function () {
            return Models\Category::whereNull('parent')->get();
        });

        $subcategories = [];
        foreach ($categories as $category) {
            if($category->children->count()){
                $subcategories[$category->id] = $category->children;
            }else{
                $subcategories[$category->id] = null;
            }
        }

        $statuses = Cache::remember('ticketit::statuses', 60, function () {
            return Models\Status::all();
        });

        if (LaravelVersion::min('5.3.0')) {
            return [$priorities->pluck('name', 'id'), $categories->pluck('label', 'id'), $statuses->pluck('name', 'id'), $subcategories];
        } else {
            return [$priorities->lists('name', 'id'), $categories->lists('label', 'id'), $statuses->lists('name', 'id'), $subcategories];
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $users = Agent::all();
        $user = Sentinel::getUser();

        list($priorities, $categories, $statuses, $subcategories) = $this->PCS();
        return view('ticketit::tickets.create', compact('priorities', 'categories', 'subcategories', 'users', 'user'));
    }

    /**
     * Store a newly created ticket and auto assign an agent for it.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, AsanaService $asana_service)
    {
        //dd($request->all());
        $this->validate($request, [
            'subject'     => 'required|min:3',
            'content'     => 'required|min:6',
            'priority_id' => 'required|exists:ticketit_priorities,id',
            'category_id' => 'required|exists:ticketit_categories,id',
            // 'zone_id' => 'required'
        ]);

        $ticket = new Ticket();
        $ticket->subject = $request->subject;
        $ticket->html = $request->html;
        $content = $this->imagesToLink($request->get('content'));
        $content_text = $content;

        // check if heat map urls is added
        if(isset($request->heat_map_url[0]) && $request->heat_map_url[0]) {
            $content .= $this->heatMapURLToTag($request->heat_map_url);
        }

        $ticket->setPurifiedContent($content);
        $category = Models\Category::find($request->category_id);

        if($category->children->count())
        {
            $ticket->category_id = $request->subcategory_id;
        }else{
            $ticket->category_id = $request->category_id;
        }

        $ticket->priority_id = $request->priority_id;
        $ticket->status_id = TSetting::grab('default_status_id');
        $ticket->zone_id = $request->zone_id; 

        if($request->user_id) {
            $ticket->user_id = $request->user_id;
        } else {
            $ticket->user_id = Sentinel::getUser()->id;
        }

        /* if($request->has('ticket_for')){
            $ticket->autoSelectAgent('superadmin');
        }else{
            $ticket->autoSelectAgent();
        } */
                  
        $ticket->autoSelectAgent();
        $ticket->save();

        ProcessTicketsToChannels::dispatch($ticket,$content, $content_text);

        // push ticket to asana
        // try {
        //     $asana_service->push_ticket($ticket->id);
        // } catch(\Exception $e) {
        //     \Log::error('Tickets Error: failed to push tickets to Asana');
        //     \Log::error($e->getMessage());
        // }

        session()->flash('status', trans('ticketit::lang.the-ticket-has-been-created'));

        return redirect()->action('\Kordy\Ticketit\Controllers\TicketsController@index');
    }
                                               
    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $ticket = $this->tickets->findOrFail($id);   
        $user = Sentinel::getUser();
 
        if($ticket->user_id == $user->id || Sentinel::getUser()->ticketit_agent || Sentinel::getUser()->ticketit_admin){
            list($priority_lists, $category_lists, $status_lists, $subcategories) = $this->PCS();

            $close_perm = $this->permToClose($id);
            $reopen_perm = $this->permToReopen($id);
            
            if(Sentinel::inRole('client')){
                $first_admin = Sentinel::getUser()->admin_user;
            }elseif (Sentinel::inRole('admin')) {
                $first_admin = Sentinel::getUser();
            }elseif(Sentinel::inRole('agent')){
                $first_admin = Sentinel::getUser()->admin_user;
            }elseif(Sentinel::inRole('super-admin')){
                $first_admin = Sentinel::findRoleBySlug('super-admin')->users()->first();
            }
    
            $cat_agents = Agent::agentsLists();
            // dd($cat_agents);
            if (is_array($cat_agents)) {
                $agent_lists = ['auto' => 'Auto Select'] + $cat_agents;
            } else {
                $agent_lists = ['auto' => 'Auto Select'];
            }
    
            $selected_category = ($ticket->category->parent_category) ? $ticket->category->parent_category->id : $ticket->category->id;
            $selected_subcategory = ($ticket->category->parent_category) ? $ticket->category->id : null;
    
            $comments = $ticket->comments()->paginate(TSetting::grab('paginate_items'));
            $plan_names = '';
            try {
               $plan_names = !empty($ticket->user->account->get_plan_names()) ? implode(', ', $ticket->user->account->get_plan_names()) : '';
            } catch (\Exception $e) {
                $plan_names = '';
                \Log::info($e->getMessage());
            }
            
            $dev_statuses = TicketsDeveloperStatus::all()->pluck('name', 'id')->toArray();
            $scripts = Scripts::all();

            $ticketService = new TicketsService();

            $ticket_first_response_time_average = $ticketService->getFirstResponseTimeAverage(['user_id'=>$ticket->user_id]);

            $average_no_of_interactions = $ticketService->getAverageNoOfInteractions(['user_id'=>$ticket->user_id]);

            $average_resolution_time = $ticketService->getAverageResolutionTime(['user_id'=>$ticket->user_id]);

            $total_tickets = $ticketService->getTotalTickets(['user_id'=>$ticket->user_id]);

            $ticket_response_time_average = $ticketService->getResponseTimeAverage(['user_id'=>$ticket->user_id]);

            return view('ticketit::tickets.show', compact('ticket', 'status_lists', 'priority_lists', 'category_lists', 'subcategories', 'selected_category', 'selected_subcategory', 'agent_lists', 'comments',
                    'close_perm', 'reopen_perm', 'plan_names', 'dev_statuses', 'scripts',
                'ticket_first_response_time_average', 'ticket_response_time_average', 'average_no_of_interactions', 'average_resolution_time', 'total_tickets'));
        } else {
            return redirect()->route(TSetting::grab('main_route').'.index');
        }

                                          
    }
                 
    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function update(Request $request, $id, AsanaService $asana_service)
    {



        $user = Sentinel::getUser();

        if($user->ticketit_admin || $user->ticketit_agent) {
            $this->validate($request, [
                // 'subject'     => 'required|min:3',
                // 'content'     => 'required|min:6',
                'priority_id' => 'required|exists:ticketit_priorities,id',
                // 'category_id' => 'required|exists:ticketit_categories,id',
                'status_id'   => 'required|exists:ticketit_statuses,id',
                'agent_id'    => 'required',
            ]);
        } else {
            $this->validate($request, [
                'priority_id' => 'required|exists:ticketit_priorities,id',
            ]);
        }

        $ticket = $this->tickets->findOrFail($id);

        if($request->subject) {
            $ticket->subject = $request->subject;
        }
        $content = '';
        if($request->content) {
            $content = $this->imagesToLink($request->get('content'));
            $ticket->setPurifiedContent($content);
        }

        if($request->status_id) {
            $ticket->status_id = $request->status_id;

            // complete asana task
            /* if($request->status_id == 4) {
                try {
                    $asana_service->complete_task($ticket->id);
                } catch(\Exception $e) {
                    \Log::error('Tickets Error: failed to mark ticket as complete on Asana');
                    \Log::error($e->getMessage());
                }
            } */
        }

        if($request->category) {
            $category = Models\Category::find($request->category_id);
            if($category->children->count())
            {
                $ticket->category_id = $request->subcategory_id;
            }else{
                $ticket->category_id = $request->category_id;
            }
        }

        $ticket->priority_id = $request->priority_id;

        if (!isset($request->agent_id) || $request->input('agent_id') == 'auto') {
            $ticket->autoSelectAgent();
        } else {
            $ticket->agent_id = $request->input('agent_id');
        }

        // if ticket is closed, set it to complete
        if($request->status_id == 4) {
            $ticket->completed_at = Carbon::now();
        }

        $ticket->completion_date = $request->completion_date;
        $ticket->dev_hours = $request->dev_hours;
        $ticket->dev_status_id = $request->dev_status_id;
        $ticket->dev_notes = $request->dev_notes;
        $ticket->slack_conversation_link = $request->slack_conversation_link;
        $changes = $ticket->getDirty();
        $ticket->save();

        if($request->status_id) {

            // try {
            //     $infinity_service = new InfinityService();
            //     $infinity_service->updateTicket($ticket, $content);
            // } catch(\Exception $e) {
            //     \Log::error('Tickets Error: failed to update ticket on Infinity');
            //     \Log::error($e->getMessage());
            // }

            try {
                $clickup_service = new ClickupService();
                $clickup_service->save('update', $ticket, $content, null, $changes);
            } catch(\Exception $e) {
                \Log::error('Tickets Error: failed to update ticket on ClickUp');
                \Log::error($e->getMessage());
            }
            /* $asana_service->update_task_status_tag($ticket);

            // complete asana task
            if($request->status_id == 4) {
                try {
                    $asana_service->complete_task($ticket->id);
                } catch(\Exception $e) {
                    \Log::error('Tickets Error: failed to mark ticket as complete on Asana');
                    \Log::error($e->getMessage());
                }
            } */
        }

        if(!isset($request->tags)) {
            $curr_ticket = Ticket::where('id', $id)->first();
            $curr_ticket->tags()->detach();
        }else if(isset($request->tags)) {
            //Insert tags 
            $data = array();

            foreach($request->tags as $value) {
                array_push($data,[
                    'ticket_id' => $id,
                    'ticketit_tags_id' => $value
                ]);
            }
            
            TicketTags::insert($data);
        }
  
        session()->flash('status', trans('ticketit::lang.the-ticket-has-been-modified'));

        return redirect()->route(TSetting::grab('main_route').'.show', $id);
    }
                                          
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $ticket = $this->tickets->findOrFail($id);
        $subject = $ticket->subject;
        $ticket->delete();

        session()->flash('status', trans('ticketit::lang.the-ticket-has-been-deleted', ['name' => $subject]));

        return redirect()->route(TSetting::grab('main_route').'.index');
    }
                                                                   
    /**
     * Mark ticket as complete.
     *
     * @param int $id
     *
     * @return Response
     */
    public function complete($id, AsanaService $asana_service, InfinityService $infinity_service, ClickupService $clickup_service)
    {
        if ($this->permToClose($id) == 'yes') {
            $ticket = $this->tickets->findOrFail($id);
            $ticket->completed_at = Carbon::now();

            if (TSetting::grab('default_close_status_id')) {
                $ticket->status_id = TSetting::grab('default_close_status_id');
            }

            $subject = $ticket->subject;
          $ticket->save();

     

            // complete asana task
            // try {
            //     $infinity_service->close_ticket($ticket);
            //     $asana_service->complete_task($id);
            // } catch(\Exception $e) {
            //     \Log::error('Tickets Error: failed to mark ticket as complete on Asana');
            //     \Log::error($e->getMessage());
            // }

            try {
                $clickup_service->updateTicketStatus($ticket, 'close');
            } catch(\Exception $e) {
                \Log::error('Tickets Error: failed to mark ticket as complete on ClickUp');
                \Log::error($e->getMessage());
            }

            session()->flash('status', trans('ticketit::lang.the-ticket-has-been-completed', ['name' => $subject]));

            return redirect()->route(TSetting::grab('main_route').'.index');
        }

        return redirect()->route(TSetting::grab('main_route').'.index')
            ->with('warning', trans('ticketit::lang.you-are-not-permitted-to-do-this'));
    }

    /**
     * Reopen ticket from complete status.
     *
     * @param int $id
     *
     * @return Response
     */
    public function reopen($id, ClickupService $clickup_service)
    {
        if ($this->permToReopen($id) == 'yes') {
            $ticket = $this->tickets->findOrFail($id);
            $ticket->completed_at = null;

            if (TSetting::grab('default_reopen_status_id')) {
                $ticket->status_id = TSetting::grab('default_reopen_status_id');
            }

            $subject = $ticket->subject;
            $ticket->save();

            $clickup_service->updateTicketStatus($ticket, 'open');

            session()->flash('status', trans('ticketit::lang.the-ticket-has-been-reopened', ['name' => $subject]));

            return redirect()->route(TSetting::grab('main_route').'.index');
        }

        return redirect()->route(TSetting::grab('main_route').'.index')
            ->with('warning', trans('ticketit::lang.you-are-not-permitted-to-do-this'));
    }

    public function agentSelectList($category_id, $ticket_id)
    {
        if(Sentinel::inRole('client')){
            $first_admin = Sentinel::getUser()->admin_user;
        }elseif (Sentinel::inRole('admin') || Sentinel::inRole('super-admin')) {
            $first_admin = Sentinel::getUser();
        }
        // dd($first_admin);
        $cat_agents = Models\Category::find($category_id)->agents()->agentsLists();
        if (is_array($cat_agents)) {
            $agents = ['auto' => 'Auto Select'] + $cat_agents;
        } else {
            $agents = ['auto' => 'Auto Select'];
        }

        $selected_Agent = $this->tickets->find($ticket_id)->agent->id;
        $select = '<select class="form-control" id="agent_id" name="agent_id">';
        foreach ($agents as $id => $name) {
            $selected = ($id == $selected_Agent) ? 'selected' : '';
            $select .= '<option value="'.$id.'" '.$selected.'>'.$name.'</option>';
        }
        $select .= '</select>';

        return $select;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function permToClose($id)
    {
        $close_ticket_perm = TSetting::grab('close_ticket_perm');

        if ($this->agent->isAdmin() && $close_ticket_perm['admin'] == 'yes') {
            return 'yes';
        }
        if ($this->agent->isAgent() && $close_ticket_perm['agent'] == 'yes') {
            return 'yes';
        }
        if ($this->agent->isTicketOwner($id) && $close_ticket_perm['owner'] == 'yes') {
            return 'yes';
        }

        return 'no';
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function permToReopen($id)
    {
        $reopen_ticket_perm = TSetting::grab('reopen_ticket_perm');
        if ($this->agent->isAdmin() && $reopen_ticket_perm['admin'] == 'yes') {
            return 'yes';
        } elseif ($this->agent->isAgent() && $reopen_ticket_perm['agent'] == 'yes') {
            return 'yes';
        } elseif ($this->agent->isTicketOwner($id) && $reopen_ticket_perm['owner'] == 'yes') {
            return 'yes';
        }

        return 'no';
    }

    /**
     * Calculate average closing period of days per category for number of months.
     *
     * @param int $period
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function monthlyPerfomance($period = 2)
    {
        $categories = Category::all();
        foreach ($categories as $cat) {
            $records['categories'][] = $cat->name;
        }

        for ($m = $period; $m >= 0; $m--) {
            $from = Carbon::now();
            $from->day = 1;
            $from->subMonth($m);
            $to = Carbon::now();
            $to->day = 1;
            $to->subMonth($m);
            $to->endOfMonth();
            $records['interval'][$from->format('F Y')] = [];
            foreach ($categories as $cat) {
                $records['interval'][$from->format('F Y')][] = round($this->intervalPerformance($from, $to, $cat->id), 1);
            }
        }

        return $records;
    }

    /**
     * Calculate the date length it took to solve a ticket.
     *
     * @param Ticket $ticket
     *
     * @return int|false
     */
    public function ticketPerformance($ticket)
    {
        if ($ticket->completed_at == null) {
            return false;
        }

        $created = new Carbon($ticket->created_at);
        $completed = new Carbon($ticket->completed_at);
        $length = $created->diff($completed)->days;

        return $length;
    }

    /**
     * Calculate the average date length it took to solve tickets within date period.
     *
     * @param $from
     * @param $to
     *
     * @return int
     */
    public function intervalPerformance($from, $to, $cat_id = false)
    {
        if ($cat_id) {
            $tickets = Ticket::where('category_id', $cat_id)->whereBetween('completed_at', [$from, $to])->get();
        } else {
            $tickets = Ticket::whereBetween('completed_at', [$from, $to])->get();
        }

        if (empty($tickets->first())) {
            return false;
        }

        $performance_count = 0;
        $counter = 0;
        foreach ($tickets as $ticket) {
            $performance_count += $this->ticketPerformance($ticket);
            $counter++;
        }
        $performance_average = $performance_count / $counter;

        return $performance_average;
    }

    /**
     * Filter Base64 Images Download Images and make there links
     * @return String | content
     */
    protected function imagesToLink($input_content)
    {
        $description = $input_content;
        $dom = new \DomDocument();
        $dom->loadHtml($description, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);    
        $images = $dom->getElementsByTagName('img');
        foreach($images as $k => $img)
        {
            $data = $img->getAttribute('src');
            if(Str::contains($data, ';base64')){
               list($type, $data) = explode(';', $data);
               list(, $data)      = explode(',', $data);
               $data = base64_decode($data);
               $image_name= "tickets_images/" . time().$k.'.png';
//               $path = public_path() . $image_name;
//               file_put_contents($path, $data);
               $store_to_s3 = \Storage::disk('s3')->put($image_name, $data, 'public');
               $img->removeAttribute('src');
               $img->setAttribute('src', \Storage::url($image_name));
           }
        }
        $description = $dom->saveHTML();
        $pattern = '/<img.+src=(.)(.*)\1[^>]*>/iU';
        $callback_fn = 'imgToa';
        $content = preg_replace_callback($pattern, array(&$this,"imgToa"), $description);
        return $content;
    }

    /**
     * Replace Images Tags into Anchor tags
     * @return String
     */
    protected function imgToa($matches)
    {
        return "<a target='_blank' href='".$matches[2]."'> <font color ='black' >View Image</font></a>";
    }

    /**
     * Replace heat map urls to tags
     *
     * @param array $urls
     *
     * @return void
     */
    protected function heatMapURLToTag($urls)
    {
        $html = '<br><p>';

        foreach($urls as $url) {
            if(!$url) {
                continue;
            }
            
            $html .= '<a href="' . $url . '" target="_blank">Heat Map URL</a><br>';
        }

        $html .= '</p>';

        return $html;
    }

    public function storeSupportNotes(Request $request)
    {
       $user_id = Sentinel::getUser()->id;
       $note = new SupportNote();
       $note->notes = $request->note;
       $note->ticket_id = $request->ticket_id;
       $note->user_id = $user_id;
       if($note->save()) {
            return response()->json(['success' => true, 'message' => 'Note added successfully.']);
       } else {
            return response()->json(['success' => false, 'message' => 'There was a problem adding the note.']);
       }
    }

    public function getSupportNotesByTicketId($ticketid)
    {
        $support_notes = SupportNote::where('ticket_id', $ticketid)->with('user')->get();
        return response()->json(['data' => $support_notes],200);
    }

    public function updateSupportNote(Request $request)
    {
        $note = SupportNote::find($request->id);
        $note->notes = $request->note;
        if($note->save()) {
            return response()->json(['success' => true, 'message' => 'Note updated successfully.']);
       } else {
            return response()->json(['success' => false, 'message' => 'There was a problem updating the note.']);
       }
    }
    
    public function deleteSupportNote(Request $request)
    {
        $note = SupportNote::find($request->id);
        if($note) {
           if($note->delete()){
            return response()->json(['success' => true, 'message' => 'Note deleted successfully.']);
           } else {
            return response()->json(['success' => false, 'message' => 'There was a problem deleting the note.']);
           }
        } else {
            return response()->json(['success' => false, 'message' => 'There was a problem deleting the note.']);
        }
    }         

    public function apiStoreTicket(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $priority = Priority::where('name', 'LIKE', '%'.$request->priority.'%')->first();
        $sub_category = Category::where('name', 'LIKE', '%'.$request->module.'%')->first();

        $ticket = new Ticket();
        $ticket->user_id = $user->id; 
        $ticket->priority_id = $priority->id; //not yet confirmed, default?
        $ticket->category_id = $sub_category->id;   //not yet confirmed
        $ticket->subject = $request->subject; //not yet confirmed
        $ticket->content = $request->content; //not yet confirmed
        $ticket->status_id = TSetting::grab('default_status_id'); 
        $ticket->autoSelectAgent();
        if($ticket->save()) {
            //ProcessTicketsToChannels::dispatch($ticket,$request->content);
            return response()->json(['success' => true, 'message' => 'Ticket successfully created.'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Error creating ticket.'], 400);
        }
    } 
    
    public function averageResponseTime()
    {
        $ticketService = new TicketsService();
        $average_thirty_total = $ticketService->getTotalAverageResponseThirtyDays();
        $average_seven_total = $ticketService->getTotalAverageResponseSevenDays();

        return response()->json([ 'thirty_days' => $average_thirty_total, 'seven_days' =>  $average_seven_total],200);
    }

    public function emailReportsSettingsIndex()
    {
        $frequencies = ['Daily' => 'Daily', 'Weekly'  => 'Weekly', 'Monthly' => 'Monthly'];

        $current_user_email = Sentinel::getUser()->email;

        return view('ticketit::admin.email_reports.index')
                ->with('frequencies', $frequencies)
                ->with('current_user_email', $current_user_email);
    }

    public function getAverageByDateRange(Request $request)
    {
        $date_to = $request->date_to;
        $date_from = $request->date_from;

        $ticketService = new TicketsService();
        $average_response_by_date_rage = $ticketService->getAverageByDateRange($date_to,$date_from);
        return response()->json(['average_response_by_date_rage' => $average_response_by_date_rage],200);
    }

    public function addTag($id, Request $request)
    {

        if(isset($id) && isset($request->tag)) {
            $ticket = Ticket::find($id);

            if(isset($request->tag) && $request->tag) {

                $tag = Tags::where('name', $request->tag)->first();

                if ($ticket) {
                    if (!$tag) {
                        $tag = new Tags();
                        $tag->name = ucwords($request->tag);
                        $tag->save();
                    }

                    $ticket->tags()->syncWithoutDetaching(array($tag->id));

                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Tag updated successfully.'],200);
    }

    public function removeTag($id, Request $request) {

        if(isset($id) && isset($request->tag)) {
            $ticket = Ticket::find($id);

            if(isset($request->tag) && $request->tag) {

                $tag = Tags::where('name', $request->tag)->first();

                if ($ticket && $tag) {
                    $ticket->tags()->detach($tag->id);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Tag updated successfully.'],200);
    }

    public function getAllTags(Request $request) {

        $tags_query = Tags::query();

        $input = request('q');

        if($input)
        {
            $tags_query->where('name', 'like', $input.'%')
                ->orWhere('name', 'like', '% '.$input.'%');
        }

        $tags = $tags_query->orderBy('name', 'ASC')->get(
            ['name AS text', 'id']
        )->toArray();


        return response()->json([
            'type' =>'success',
            'data' => $tags
        ]);

    }

    public function getAllUsers(Request $request)
    {
        $data = array();

        $users_query = Sentinel::getUserRepository()
            ->with('roles')
            ->withCount(array('activations' => function($query){
                $query->where('completed', 1);
            }));

        if($request->input('search') && strlen($request->input('search')) > 2 ) {

            $users_query->where(function ($query) use ($request) {
                $query->where('first_name', 'like', '%' . $request->input('search') . '%');
                $query->orWhere('last_name', 'like', '%' . $request->input('search') . '%');
                $query->orWhere('email', 'like', '%' . $request->input('search') . '%');
                $query->orWhereRaw("concat(first_name, ' ', last_name) like '%" . $request->input('search') . "%' ");
            });
        }

        $users = $users_query->get();

        $i = 0;
        if(count($users)>0){
            foreach ($users as $user) {
                if ($user->activations_count > 0) {
                    $data[$i]['text'] = $user->full_name . ' - ' . $user->email . ($user->roles()->first() ? ' - ' . $user->roles()->first()->name : ' - No role associated ');
                } else {
                    $data[$i]['text'] = $user->full_name . ' - ' . $user->email . ($user->roles()->first() ? ' - ' . $user->roles()->first()->name : ' - No role associated ') . ' (Not Activated)';
                }

                $data[$i]['id'] = $user->id;

                $i++;
            }
        }

        return response()->json([
            'type' =>'success',
            'data' => $data
        ]);

    }

    public function getAllTicketPriorities(Request $request) {

        $priorities_query =  Models\Priority::query();

        $input = request('q');

        $priorities = $priorities_query->orderBy('id', 'ASC')->get(
            ['name AS text', 'id']
        )->toArray();


        return response()->json([
            'type' =>'success',
            'data' => $priorities
        ]);

    }

    public function getAllTicketStatuses(Request $request) {

        $statuses_query =  Models\Status::query();

        $statuses = $statuses_query->orderBy('id', 'ASC')->get(
            ['name AS text', 'id']
        )->toArray();


        return response()->json([
            'type' =>'success',
            'data' => $statuses
        ]);

    }

    public function saveDatatableColumnsVisibilitySetting(Request $request) {

        $default_columns =[
            'id'
        ];

        $columns = is_array(request('columns'))?request('columns'):[];

        $columns = array_merge($default_columns,$columns);

        $columns = array_values(array_diff($columns, ["show_all"]));

        if ($request->has('is_completed_tickets_section') && $request->get('is_completed_tickets_section') == 1) {
            session([
                'completed_ticket_datatable_columns' => $columns,
            ]);
        } else {
            session([
                'active_ticket_datatable_columns' => $columns,
            ]);
        }

        return response()->json([
            'type' =>'success'
        ]);
    }

}                             
                                                                                                                