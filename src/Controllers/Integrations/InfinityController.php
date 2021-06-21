<?php
namespace Kordy\Ticketit\Controllers\Integrations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kordy\Ticketit\Models\TSetting;
use Kordy\Ticketit\Services\Integrations\InfinityService;
use Kordy\Ticketit\Services\CategoriesService;
use Kordy\Ticketit\Models\Agent;
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
        $selected_workspace = TSetting::getBySlug('infinity_workspace_id');
        $selected_board = TSetting::getBySlug('infinity_board_id');

        $infinity_service = new InfinityService();
        $attributes = $infinity_service->get_attributes($selected_workspace->value, $selected_board->value);
        return view('ticketit::admin.infinity.tickets_mapping', compact('attributes'));
    }
}