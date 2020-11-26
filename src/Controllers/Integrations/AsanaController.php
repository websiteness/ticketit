<?php
namespace Kordy\Ticketit\Controllers\Integrations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kordy\Ticketit\Models\TSetting;
use Kordy\Ticketit\Services\Integrations\AsanaService;

class AsanaController extends Controller
{
    public function __construct()
    {

    }

    public function index(AsanaService $asana_service)
    {
        $projects = $asana_service->get_projects();
        
        return view('ticketit::admin.asana.index');
    }

    public function store_token(Request $request, AsanaService $asana_service)
    {
        $asana_service->store_auth_token($request->token);

        return redirect()->back();
    }
}