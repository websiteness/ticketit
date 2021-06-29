<?php

namespace Kordy\Ticketit\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Kordy\Ticketit\Models;
use Kordy\Ticketit\Models\Comment;
use Kordy\Ticketit\Controllers\NotificationsController;
use Kordy\Ticketit\Models\Ticket;
use Sentinel;
use Illuminate\Support\Str;
use Kordy\Ticketit\Services\Integrations\AsanaService;
use Kordy\Ticketit\Services\Integrations\InfinityService;

class CommentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('Kordy\Ticketit\Middleware\IsAgentMiddleware', ['only' => ['edit', 'update', 'destroy']]);
        $this->middleware('Kordy\Ticketit\Middleware\IsAdminMiddleware', ['only' => ['edit']]);
        $this->middleware('Kordy\Ticketit\Middleware\ResAccessMiddleware', ['only' => 'store']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, AsanaService $asana_service)
    {
        $this->validate($request, [
            'ticket_id'   => 'required|exists:ticketit,id',
            'content'     => 'required|min:6',
        ]);

        if($request->has('status_change') && $request->get('status_change')){
            // check if status realy changed then send combined email otherwise send only comment do other wise
            $ticket = Models\Ticket::find($request->get('ticket_id'));
            if($ticket->status->id !== (int)$request->get('status_change')){
                session(['com_stat_both' => true]);
            }else{
                session(['com_stat_both' => false]);
            }

        }

        $comment = new Models\Comment();

        $content = $this->imagesToLink($request->content);

        $comment->setPurifiedContent($content);

        $comment->ticket_id = $request->get('ticket_id');
        $comment->user_id = \Sentinel::getuser()->id;
        $comment->save();

        if(session('com_stat_both', false)){

            $ticket = Models\Ticket::find($comment->ticket_id);
            $ticket->updated_at = $comment->created_at;
            $ticket->status_id = $request->status_change;

            if($request->status_change == 4)
            {
                $ticket->completed_at = Carbon::now();
            }
            
            $ticket->save();

            session(['com_stat_both' => false]);
        }else{
            $ticket = Models\Ticket::find($comment->ticket_id);
            $ticket->updated_at = $comment->created_at;
            $ticket->save();
        }

        // Change status 'Waiting on support' if a user replies
        if(!Sentinel::getuser()->ticketit_admin && !Sentinel::getuser()->ticketit_agent) {
            $ticket = Ticket::find($request->ticket_id);
            $ticket->status_id = 1;
            $ticket->save();

            $asana_service->update_task_status_tag($ticket);
        }

        // update asana task
        $asana_service->update_ticket($ticket->id);
        $infinity_service = new InfinityService();
        $update_ticket = $infinity_service->updateTicket($ticket);

        if(!$update_ticket) {
            \Log::error('Tickets Error: failed to update ticket.');
        
        }

        // update task tag if status was changed
        if($request->status_change)
        {
            $asana_service->update_task_status_tag($ticket);
        }

        // complete asana task
        if($request->status_change && $request->status_change == 4) {
            try {
                $asana_service->complete_task($ticket->id);
            } catch(\Exception $e) {
                \Log::error('Tickets Error: failed to mark ticket as complete on Asana');
                \Log::error($e->getMessage());
            }
        }

        return back()->with('status', trans('ticketit::lang.comment-has-been-added-ok'));
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        //
    
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
        $comment = Comment::find($id);

        $content = $this->imagesToLink($request->content);
        
        $comment->setPurifiedContent($content);
        $comment->save();

        // update asana task
        $asana_service->update_ticket($comment->ticket_id);

        return redirect()->back();
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
        Comment::destroy($id);

        return redirect()->back();
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
}
