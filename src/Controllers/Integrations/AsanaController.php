<?php
namespace Kordy\Ticketit\Controllers\Integrations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kordy\Ticketit\Models\TSetting;
use Kordy\Ticketit\Services\Integrations\AsanaService;
use Kordy\Ticketit\Services\CategoriesService;
use Kordy\Ticketit\Models\Agent;
use Kordy\Ticketit\Repositories\StatusRepository;

class AsanaController extends Controller
{
    public function __construct()
    {

    }

    public function index(AsanaService $asana_service)
    {
        $token = $asana_service->get_auth_token();
        $project = $asana_service->get_set_project();
        $workspace = $asana_service->get_workspace();
        $tags = $asana_service->get_setting_tags();

        // dd($asana_service->push_ticket());

        return view('ticketit::admin.asana.index', compact('token', 'project', 'workspace', 'tags'));
    } 

    public function store_token(Request $request, AsanaService $asana_service)
    {
        $asana_service->store_auth_token($request->token); 

        return redirect()->back();
    }

    public function get_projects(AsanaService $asana_service) {
        $projects = $asana_service->get_projects();

        return $projects;
    }

    public function store_project(Request $request, AsanaService $asana_service)
    {
        $asana_service->store_project($request->projects);

        return redirect()->back();
    }

    /**
     * Categories Page
     */
    public function categories_index(CategoriesService $categories_services, AsanaService $asana_service) {
        $categories = $categories_services->getAllCategories();
        $sections =  $asana_service->get_sections();

        // dd($sections);

        return view('ticketit::admin.asana.category.index', compact('categories', 'sections'));
    }

    public function get_sections(AsanaService $asana_service) {
        return  $asana_service->get_sections();
    }

    public function map_sections(Request $request, AsanaService $asana_service)
    {
        $asana_service->map_sections($request->sections);
        return redirect()->back();
    }

    /**
     * Users Page
     */
    public function users_index(AsanaService $asana_service) {
        $agents = Agent::agents()->get();
        $users = $asana_service->get_users();

        return view('ticketit::admin.asana.users.index', compact('agents', 'users'));
    }

    public function map_users(Request $request, AsanaService $asana_service){
        $asana_service->map_users($request->users);
        return redirect()->back();
    }


    /**
     * Tags
     */
    public function get_tag_list(AsanaService $asana_service)
    {
        return $asana_service->get_tags();
    }


    /**
     * Workspaces
     */
    public function get_workspace_list(AsanaService $asana_service)
    {
        return $asana_service->get_workspaces();
    }

    public function store_workspace(Request $request, AsanaService $asana_service)
    {
        $asana_service->store_workspace($request->workspace);
        
        return redirect()->back();
    }

    /**
     * Configs
     */
    public function store_settings(Request $request, AsanaService $asana_service)
    {
        $asana_service->store_project($request->project);
        $asana_service->store_tags($request->tags);
        
        return redirect()->back();
    }

    /**
     * Status
     */
    public function status_index(StatusRepository $status_repository, AsanaService $asana_service) {
        $statuses = $status_repository->getAll();
        $tags =  $asana_service->get_tags();

        // dd($statuses);

        return view('ticketit::admin.asana.status.index', compact('statuses', 'tags'));
    }

    public function map_statuses(Request $request, AsanaService $asana_service)
    {
        $asana_service->map_statuses($request->statuses);
        return redirect()->back();
    }
}