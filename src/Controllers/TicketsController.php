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
            ->leftjoin('tickets_developer_status', 'tickets_developer_status.id', '=', 'ticketit.dev_status_id')
            ->leftjoin('ticketit_categories AS ticketit_zone', 'ticketit_zone.id', '=', 'ticketit.zone_id')
            ->leftjoin('ticketit_ticket_tags as ttt','ttt.ticket_id','=', 'ticketit.id')
            ->leftjoin('ticketit_tags as tt','ttt.ticketit_tags_id','=', 'tt.id')
            ->select([
                'ticketit.id',
                'ticketit.user_id',
                'ticketit.status_id',
                'ticketit.subject AS subject',
                'ticketit_statuses.name AS status',
                'ticketit_statuses.color AS color_status',
                'ticketit_priorities.color AS color_priority',
                'ticketit_categories.color AS color_category',
                'ticketit.id AS agent',
                'ticketit.updated_at AS updated_at',
                'ticketit_priorities.name AS priority',
                'ticketit_zone.name AS zone',
                // 'users.name AS owner',
                DB::raw('CONCAT(users.first_name ," ", users.last_name) as owner'),
                'ticketit.agent_id',
                'ticketit_categories.name AS category',
                'tickets_developer_status.name AS dev_status'
            ]);
            
                                                                     
        // check if filters are applied
        if($request->user) {
            $collection->where('ticketit.user_id', $request->user);
        }
        if($request->status) {
            if($request->status == 'no_response') {
                $collection->whereDoesntHave('comments', function($query) {
                    $query->where('ticketit_comments.user_id', '!=', 'ticketit.user_id');
                });
            } elseif($request->status == 'overdue') {
                $settings_repository = new SettingsRepository;
                $overdue_hours = $settings_repository->getOverdueHours();
                $datetime_now = \Carbon\Carbon::now()->subHours($overdue_hours);

                $collection->where('ticketit.created_at', '<', $datetime_now)
                    ->whereDoesntHave('comments');
            } else {
                $collection->where('ticketit.status_id', $request->status);
            }
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
        if($request->last_reply) {
            // $comments_repository = new CommentsRepository;

            if($request->last_reply == 'user') {
                $collection->where(function($query) {
                    $query->where('ticketit.user_id', function($query) {
                        return $query->from('ticketit_comments')->select('ticketit_comments.user_id')->whereColumn('ticketit_comments.ticket_id', 'ticketit.id')->orderBy('ticketit_comments.id', 'desc')->limit(1);
                    })
                    ->orWhereDoesntHave('comments');
                });
            } else {
                $collection->where('ticketit.user_id', '!=', function($query) {
                    return $query->from('ticketit_comments')->select('ticketit_comments.user_id')->whereColumn('ticketit_comments.ticket_id', 'ticketit.id')->orderBy('ticketit_comments.id', 'desc')->limit(1);
                });
            }
        }
   
        if(!is_null($request->tags) && !empty($request->tags) && $request->tags != "null"){
            $tag_ids = explode(',', $request->tags);
            $collection->whereIn('tt.id', $tag_ids);
        } 

        // $collection->orderBy('ticketit.id', 'asc');
        $collection = $datatables->of($collection);
    

        $this->renderTicketTable($collection);

        $collection->editColumn('updated_at', '{!! \Carbon\Carbon::parse($updated_at)->format("m/d/Y") . " (" . \Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $updated_at)->diffForHumans("", true, false, 2) . " ago)" !!}');
       
            $collection->addColumn('tags', function($ticket) {
                $tickets = Ticket::where('id', $ticket->id)->first();
                $tags = $tickets->tags;
                $new_tags = [];
                foreach($tags as $tag) {
                    array_push($new_tags, "<span class='label label-primary ml-3'>{$tag->name}</span>" );
                }
                return implode("", $new_tags);
            });
  
        // method rawColumns was introduced in laravel-datatables 7, which is only compatible with >L5.4
        // in previous laravel-datatables versions escaping columns wasn't defaut
        if (LaravelVersion::min('5.4')) {
            $collection->rawColumns(['subject', 'status', 'priority', 'category', 'agent', 'zone', 'tags']);
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

        $collection->editColumn('last_reply', function ($ticket) {
            $comments_repository = new CommentsRepository;
            $comment = $comments_repository->getLastCommentByTicketId($ticket->id);

            if(!$comment) {
                return 'User';
            }

            if($ticket->user_id == $comment->user_id) {
                return 'User';
            } else {
                return 'Support';
            }
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
        $tags = Tags::all();
        $complete = false;

        return view('ticketit::index', compact('complete', 'users', 'statuses', 'sub_categories', 'tags'));
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

        ProcessTicketsToChannels::dispatch($ticket,$content);

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
            $plan_names = '';
            try {
               $plan_names = !empty($ticket->user->account->get_plan_names()) ? implode(', ', $ticket->user->account->get_plan_names()) : '';
            } catch (\Exception $e) {
                $plan_names = '';
                \Log::info($e->getMessage());
            }
            
            $dev_statuses = TicketsDeveloperStatus::all()->pluck('name', 'id')->toArray();
            $scripts = Scripts::all();

            return view('ticketit::tickets.show', compact('ticket', 'status_lists', 'priority_lists', 'category_lists', 'subcategories', 'selected_category', 'selected_subcategory', 'agent_lists', 'comments',
                    'close_perm', 'reopen_perm', 'plan_names', 'dev_statuses', 'scripts'));
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
        $ticket->save();
        
        if($request->status_id) {

            try {
                $infinity_service = new InfinityService();
                $infinity_service->updateTicket($ticket, $content);
            } catch(\Exception $e) {
                \Log::error('Tickets Error: failed to update ticket on Infinity');
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
    public function complete($id, AsanaService $asana_service, InfinityService $infinity_service)
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
                $infinity_service->close_ticket($ticket);
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
                                                                       
}                             
                                                                                  