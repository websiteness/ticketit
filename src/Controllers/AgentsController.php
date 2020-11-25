<?php

namespace Kordy\Ticketit\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Kordy\Ticketit\Models\Agent;
use Kordy\Ticketit\Models\TSetting;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Sentinel;
use App\User;

use Kordy\Ticketit\Repositories\CategoriesRepository;
use Kordy\Ticketit\Repositories\NotificationSettingsRepository;
use Kordy\Ticketit\Services\NotificationService;

class AgentsController extends Controller
{
    public function index()
    {
        // Sentinel::findRoleBySlug('ticket-agent');
        if(Sentinel::inRole('client')){
            $first_admin = Sentinel::getUser()->admin_user;
        }elseif (Sentinel::inRole('admin') || Sentinel::inRole('super-admin')) {
            $first_admin = Sentinel::getUser();
        }

        /* $agents = Agent::agents()->where('parent_user_id',$first_admin->id)->with(['agentOpenTickets' => function ($query) {
            $query->addSelect(['id', 'agent_id']);
        }])->get(); */

        $users = Agent::all();
        $agents = Agent::agents()->get();

        return view('ticketit::admin.agent.index', compact('agents', 'users'));
    }

    public function create()
    {
        // $users = Agent::paginate(TSetting::grab('paginate_items'));
        $users = Agent::all();
        $agents = Agent::Agents()->get();
        // dd($agents);

        return view('ticketit::admin.agent.create', compact('users', 'agents'));
    }

    public function store(Request $request)
    {
        $agent = Agent::find($request->agent);
        $agent->ticketit_agent = 1;
        $agent->save();

        return redirect()->back();
    }

    /* public function store(Request $request)
    {
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'confirm_password' => 'required'
        ];

        $this->validate($request, $rules);

        $user_info = new User();
        $user_info->name = $request->get('first_name').' '.$request->get('last_name');
        $user_info->first_name = $request->get('first_name');
        $user_info->last_name = $request->get('last_name');
        $user_info->email = $request->get('email');
        $user_info->ticketit_agent = 1;
        $user_info->parent_user_id = Sentinel::getUser()->id;
        $user_info->password = bcrypt($request->get('password'));
        $user_info->save();

        $role = Sentinel::findRoleByName('Agent');

        $role->users()->attach($user_info);

        $user = Sentinel::findById($user_info->id);

        $activation = Activation::create($user);
        Activation::complete($user, $activation->code);

        return redirect()->action('\Kordy\Ticketit\Controllers\AgentsController@index');
    	// $rules = [
     //        'agents' => 'required|array|min:1',
     //    ];

     //    if(LaravelVersion::min('5.2')){
     //    	$rules['agents.*'] = 'integer|exists:users,id';
     //    }

    	// $this->validate($request, $rules);

        // $agents_list = $this->addAgents($request->input('agents'));
        // $agents_names = implode(',', $agents_list);

        // Session::flash('status', trans('ticketit::lang.agents-are-added-to-agents', ['names' => $agents_names]));

        // return redirect()->action('\Kordy\Ticketit\Controllers\AgentsController@index');
    }*/

    public function update($id, Request $request)
    {
        $this->syncAgentCategories($id, $request);

        Session::flash('status', trans('ticketit::lang.agents-joined-categories-ok'));

        return redirect()->action('\Kordy\Ticketit\Controllers\AgentsController@index');
    }

    public function destroy($id)
    {
        $agent = $this->removeAgent($id);

        Session::flash('status', trans('ticketit::lang.agents-is-removed-from-team', ['name' => $agent->name]));

        return redirect()->action('\Kordy\Ticketit\Controllers\AgentsController@index');
    }

    /**
     * Assign users as agents.
     *
     * @param $user_ids
     *
     * @return array
     */
    public function addAgents($user_ids)
    {
        $users = Agent::find($user_ids);
        foreach ($users as $user) {
            $user->ticketit_agent = true;
            $user->save();
            $users_list[] = $user->name;
        }

        return $users_list;
    }

    /**
     * Remove user from the agents.
     *
     * @param $id
     *
     * @return mixed
     */
    public function removeAgent($id)
    {
        $agent = Agent::find($id);
        $agent->ticketit_agent = false;
        $agent->save();

        // Remove him from tickets categories as well
        if (version_compare(app()->version(), '5.2.0', '>=')) {
            $agent_cats = $agent->categories->pluck('id')->toArray();
        } else { // if Laravel 5.1
            $agent_cats = $agent->categories->lists('id')->toArray();
        }

        $agent->categories()->detach($agent_cats);

        return $agent;
    }

    /**
     * Sync Agent categories with the selected categories got from update form.
     *
     * @param $id
     * @param Request $request
     */
    public function syncAgentCategories($id, Request $request)
    {
        $form_cats = ($request->input('agent_cats') == null) ? [] : $request->input('agent_cats');
        $agent = Agent::find($id);
        $agent->categories()->sync($form_cats);
    }

    public function viewNotifications($agent_id, CategoriesRepository $cr, NotificationService $ns, NotificationSettingsRepository $nsr)
    {
        // dd($ns->getAllNotificationSubscribers('new', 6));
        $agent = Agent::find($agent_id);
        
        $categories = $cr->getSubCategories();

        $events = $ns->eventList();

        $agent_settings = $ns->getUserSettings($agent_id);

        return view('ticketit::admin.agent.settings', compact('agent', 'categories', 'events', 'agent_settings'));
    }

    public function saveNotificationSettings(Request $request, $agent_id, NotificationService $ns)
    {
        $ns->saveSettings($agent_id, $request->all());

        return redirect()->back();
    }
}
