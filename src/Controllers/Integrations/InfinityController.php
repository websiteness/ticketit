<?php
namespace Kordy\Ticketit\Controllers\Integrations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kordy\Ticketit\Models\TSetting;
use Kordy\Ticketit\Services\Integrations\InfinityService;
use Kordy\Ticketit\Services\CategoriesService;
use Kordy\Ticketit\Models\Agent;
use Kordy\Ticketit\Models\Status;
use Kordy\Ticketit\Repositories\StatusRepository;


class InfinityController extends Controller
{
    public function __construct()
    {

    }

    public function index(InfinityService $infinity_service)
    {
        $token = $infinity_service->get_auth_token();
        $workspaces = $infinity_service->get_workspaces();
        $boards = $infinity_service->get_boards();
        $selected_workspace = TSetting::getBySlug('infinity_workspace_id');
        $selected_board = TSetting::getBySlug('infinity_board_id');
        $selected_folder =  TSetting::getBySlug('infinity_folder_id');
        
        return view('ticketit::admin.infinity.index', compact('token', 'workspaces', 'boards', 'selected_workspace', 'selected_board', 'selected_folder'));
    } 

    public function store_token(Request $request, InfinityService $infinity_service)
    {
        $infinity_service->store_auth_token($request->token); 
        return redirect()->back();
    }

    /**
     * Workspaces
    */
    public function get_workspace_list(InfinityService $infinity_service)
    {
        return $infinity_service->get_workspaces();
    }


    public function get_boards(InfinityService $infinity_service) {
        $boards = $infinity_service->get_boards();
        return $boards;
    }

    public function store_workspace(Request $request, InfinityService $infinity_service)
    {
        $infinity_service->store_workspace($request->workspace);
        return redirect()->back();
    }

    public function store_board(Request $request, InfinityService $infinity_service)
    {
        $infinity_service->store_board($request->board);   
        $infinity_service->store_folder($request->folder);   
        return redirect()->back();
    }

    // public function store_folder(Request $request, InfinityService $infinity_service)
    // {
    //     $infinity_service->store_folder($request->folder);   
    //     return redirect()->back();
    // }


    public function get_folders($wsid, $bid)
    {   
        $infinity_service = new InfinityService();
        $folders = $infinity_service->get_folders($wsid, $bid);
        return $folders;
    }

    public function tickets_mapping_index()
    {   
        $fields = [
            'infinity_ticket_id' => 'Ticket ID',
            'infinity_subject' =>  'Subject',
            'infinity_description' => 'Description',
            'infinity_user_name' => 'User Name',
            'infinity_ticket_status' => 'Status',
            'infinity_ticket_agent_id' => 'Developer'
        ];
        $selected_fields = [];
        $selected_workspace = TSetting::getBySlug('infinity_workspace_id');
        $selected_board = TSetting::getBySlug('infinity_board_id');
        foreach($fields as $key => $value) {
            $field = TSetting::getBySlug($key);
            if(isset($field->slug)) {
                array_push($selected_fields,['slug' => $field->slug, 'value' => $field->value]);
            }
        }     
        $selected_fields_slugs = collect($selected_fields)->pluck('slug')->toArray();
        $infinity_service = new InfinityService();   
        
        if(isset($selected_workspace->value) && isset($selected_board->value)) {
            $attributes = $infinity_service->get_attributes($selected_workspace->value, $selected_board->value);
        } else {
            $attributes = false;
        }
  
     

        return view('ticketit::admin.infinity.tickets_mapping', compact('attributes', 'fields', 'selected_fields', 'selected_fields_slugs' ));
    }

    public function store_fields(Request $request)
    {      
        $infinity_service = new InfinityService();
        $folders = $infinity_service->store_fields($request->except(['_token']));
        return redirect()->back();
    }

    public function user_mapping_index()
    {
        $infinity_service = new InfinityService();
        $workspace_users = $infinity_service->get_user_by_workspace();
        $agents = Agent::agents()->get();
        return view('ticketit::admin.infinity.users_mapping', compact('agents','workspace_users'));
    }

    public function store_mapped_users(Request $request)
    {
        $infinity_service = new InfinityService();
        $users = collect($request->only('users'))->values()->shift();
        $data = $infinity_service->store_mapped_users($users);

        return redirect()->back();
    }

    public function ticket_status_mapping_index()
    {

        $infinity_service = new InfinityService();
        $infinity_statuses =  $infinity_service->get_statuses();
   
            $ticket_statuses = Status::all();
      
        return view('ticketit::admin.infinity.ticket_status_mapping', compact('infinity_statuses', 'ticket_statuses'));
    }

    public function store_mapped_status(Request $request)
    {
        $statuses = collect($request->only('statuses'))->values()->shift();
        $infinity_service = new InfinityService();
        $infinity_service->store_mapped_status($statuses);
        return redirect()->back();

    }
}