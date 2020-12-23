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
use Kordy\Ticketit\Services\Integrations\AsanaService;

class TicketsController extends Controller
{
    protected $tickets;
    protected $agent;

    public function __construct(Ticket $tickets, Agent $agent)
    {
        Cache::flush();
        // $this->middleware('Kordy\Ticketit\Middleware\ResAccessMiddleware', ['only' => ['show']]);
        $this->middleware('Kordy\Ticketit\Middleware\IsAgentMiddleware', ['only' => ['edit']]);
        $this->middleware('Kordy\Ticketit\Middleware\IsAdminMiddleware', ['only' => ['destroy']]);

        $this->tickets = $tickets;
        $this->agent = $agent;
    }

    public function data(Request $request, $complete = false)
    {
        if (LaravelVersion::min('5.4')) {
            $datatables = app(\Yajra\DataTables\DataTables::class);
        } else {
            $datatables = app(\Yajra\Datatables\Datatables::class);
        }

        $user = $this->agent->find(Sentinel::getUser()->id);

        if ($user->isAdmin()) {
            if ($complete) {
                $collection = Ticket::complete()->adminUserTickets($user->id, true);
            } else {
                $collection = Ticket::active()->adminUserTickets($user->id, true);

            }
        } elseif ($user->isAgent()) {
            if ($complete) {
                // $collection = Ticket::complete()->agentUserTickets($user->id);
                $collection = Ticket::complete()->adminUserTickets($user->id, true);
            } else {
                // $collection = Ticket::active()->agentUserTickets($user->id);
                $collection = Ticket::active()->adminUserTickets($user->id, true);
            }
        } else {
            if ($complete) {
                $collection = Ticket::userTickets($user->id)->complete();
            } else {
                $collection = Ticket::userTickets($user->id)->active();
            }
        }
        // dd($collection->get());
        $collection
            ->join('users', 'users.id', '=', 'ticketit.user_id')
            ->join('ticketit_statuses', 'ticketit_statuses.id', '=', 'ticketit.status_id')
            ->join('ticketit_priorities', 'ticketit_priorities.id', '=', 'ticketit.priority_id')
            ->join('ticketit_categories', 'ticketit_categories.id', '=', 'ticketit.category_id')
            ->select([
                'ticketit.id',
                'ticketit.status_id',
                'ticketit.subject AS subject',
                'ticketit_statuses.name AS status',
                'ticketit_statuses.color AS color_status',
                'ticketit_priorities.color AS color_priority',
                'ticketit_categories.color AS color_category',
                'ticketit.id AS agent',
                'ticketit.updated_at AS updated_at',
                'ticketit_priorities.name AS priority',
                // 'users.name AS owner',
                DB::raw('CONCAT(users.first_name ," ", users.last_name) as owner'),
                'ticketit.agent_id',
                'ticketit_categories.name AS category',
            ]);

        // check if filters are applied
        if($request->user) {
            $collection->where('ticketit.user_id', $request->user);
        }
        if($request->status) {
            $collection->where('ticketit.status_id', $request->status);
        }
        if($request->sub_category) {
            $collection->where('ticketit.category_id', $request->sub_category);
        }
        if($request->message) {
            $message = str_replace('}}', ' ', str_replace('{{', ' ', $request->message));

            $collection->where('ticketit.html', 'like', '%'.$message.'%');
        }
        if($request->filter_hide_closed_tickets) {
            $collection->where('ticketit.status_id', '!=', 4);
        }
        

        $collection = $datatables->of($collection);

        $this->renderTicketTable($collection);

        $collection->editColumn('updated_at', '{!! \Carbon\Carbon::parse($updated_at)->format("m/d/Y") . " (" . \Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $updated_at)->diffForHumans("", true, false, 2) . " ago)" !!}');

        // method rawColumns was introduced in laravel-datatables 7, which is only compatible with >L5.4
        // in previous laravel-datatables versions escaping columns wasn't defaut
        if (LaravelVersion::min('5.4')) {
            $collection->rawColumns(['subject', 'status', 'priority', 'category', 'agent']);
        }

        return $collection->make(true);
    }

    public function renderTicketTable($collection)
    {
        $collection->editColumn('subject', function ($ticket) {
            return '<span class="ticket-subject">' . (string) link_to_route(
                TSetting::grab('main_route').'.show',
                str_limit($ticket->subject, 30, '...'),
                $ticket->id
            )
            . '</span>';
        });

        $collection->editColumn('status', function ($ticket) {
            $color = $ticket->color_status;
            $status = e($ticket->status);
            
            if($ticket->status_id == 2) {
                $status = 'Waiting on feedback from ' . e($ticket->owner);
            }

            return "<div style='color: $color'>$status</div>";
        });

        $collection->editColumn('priority', function ($ticket) {
            $color = $ticket->color_priority;
            $priority = e($ticket->priority);

            return "<div style='color: $color'>$priority</div>";
        });

        $collection->editColumn('category', function ($ticket) {
            $color = $ticket->color_category;
            $category = e($ticket->category);

            return "<div style='color: $color'>$category</div>";
        });

        $collection->editColumn('agent', function ($ticket) {
            $ticket = $this->tickets->find($ticket->id);

            return e($ticket->agent->name);
        });

        $collection->addColumn('resolved', function ($ticket) {
            return link_to_route(TSetting::grab('main_route').'.complete', 'Resolved', $ticket->id, ['class' => 'btn btn-success btn-sm']);
        });

        return $collection;
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

        $complete = false;

        return view('ticketit::index', compact('complete', 'users', 'statuses', 'sub_categories'));
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

        return view('ticketit::index', compact('complete', 'users', 'statuses', 'sub_categories'));
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
        $this->validate($request, [
            'subject'     => 'required|min:3',
            'content'     => 'required|min:6',
            'priority_id' => 'required|exists:ticketit_priorities,id',
            'category_id' => 'required|exists:ticketit_categories,id',
        ]);

        $ticket = new Ticket();

        $ticket->subject = $request->subject;

        $content = $this->imagesToLink($request->get('content'));

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

        // push ticket to asana
        try {
            $asana_service->push_ticket($ticket->id);
        } catch(\Exception $e) {
            \Log::error('Tickets Error: failed to push tickets to Asana');
            \Log::error($e->getMessage());
        }

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

        // $cat_agents = Models\Category::find($ticket->category_id)->agents()->where('parent_user_id',$first_admin->id)->agentsLists();

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

        return view('ticketit::tickets.show',
            compact('ticket', 'status_lists', 'priority_lists', 'category_lists', 'subcategories', 'selected_category', 'selected_subcategory', 'agent_lists', 'comments',
                'close_perm', 'reopen_perm'));
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

        if($request->content) {
            $content = $this->imagesToLink($request->get('content'));
            $ticket->setPurifiedContent($content);
        }

        if($request->status_id) {
            $ticket->status_id = $request->status_id;

            // complete asana task
            if($request->status_id == 4) {
                try {
                    $asana_service->complete_task($ticket->id);
                } catch(\Exception $e) {
                    \Log::error('Tickets Error: failed to mark ticket as complete on Asana');
                    \Log::error($e->getMessage());
                }
            }
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

        $ticket->save();

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
    public function complete($id, AsanaService $asana_service)
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
            try {
                $asana_service->complete_task($id);
            } catch(\Exception $e) {
                \Log::error('Tickets Error: failed to mark ticket as complete on Asana');
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
    public function reopen($id)
    {
        if ($this->permToReopen($id) == 'yes') {
            $ticket = $this->tickets->findOrFail($id);
            $ticket->completed_at = null;

            if (TSetting::grab('default_reopen_status_id')) {
                $ticket->status_id = TSetting::grab('default_reopen_status_id');
            }

            $subject = $ticket->subject;
            $ticket->save();

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
        $cat_agents = Models\Category::find($category_id)->agents()->where('parent_user_id',$first_admin->id)->agentsLists();
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
               $image_name= "/tickets_images/" . time().$k.'.png';
               $path = public_path() . $image_name;
               file_put_contents($path, $data);
               $img->removeAttribute('src');
               $img->setAttribute('src', url($image_name));
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
    
    
}
