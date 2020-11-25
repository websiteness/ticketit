<?php

namespace Kordy\Ticketit\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Kordy\Ticketit\Models\Comment;
use Kordy\Ticketit\Models\TSetting;
use Kordy\Ticketit\Models\Ticket;
use Kordy\Ticketit\Notifications\TicketNotification;
use Sentinel;

use Kordy\Ticketit\Services\NotificationService;


class NotificationsController extends Controller
{
    private const OWNER = 'LG SUPPORT';

    public function newComment(Comment $comment)
    {
        $ticket = $comment->ticket;
        $notification_owner = $comment->ticket->user;
        $template = 'ticketit::emails.comment';
        $data = ['comment' => serialize($comment), 'ticket' => serialize($ticket)];

        // send notif if comment was created by support
        if($comment->user_id != $ticket->user_id) {
            $this->sendNotification($template, $data, $ticket, $notification_owner, trans('ticketit::lang.notify-new-comment-from').self::OWNER.trans('ticketit::lang.notify-on').$ticket->subject, 'comment');
        }

        // send notification to agents
        try {
            $notif_service = new NotificationService;
            $agents = $notif_service->getAllNotificationSubscribers('replies', $ticket->category_id, $ticket->agent_id);

            foreach($agents as $agent) {
                // dont send if is the one created the comment
                if($agent->id == $comment->user_id) {
                    return false;
                }

                $this->sendNotification($template, $data, $ticket, $agent, trans('ticketit::lang.notify-new-comment-from').self::OWNER.trans('ticketit::lang.notify-on').$ticket->subject, 'comment');
            }
        } catch(\Exception $e) {
            \Log::error('Ticket Error on line '. $e->getLine());
            \Log::error($e->getMessage());
        }
        
        /* if($notification_owner->email !== Sentinel::getUser()->email){
            $this->sendNotification($template, $data, $ticket, $notification_owner,
                // trans('ticketit::lang.notify-new-comment-from').$notification_owner->name.trans('ticketit::lang.notify-on').$ticket->subject, 'comment');
                trans('ticketit::lang.notify-new-comment-from').self::OWNER.trans('ticketit::lang.notify-on').$ticket->subject, 'comment');
        } */
    }

    public function ticketStatusUpdated(Ticket $ticket, Ticket $original_ticket)
    {
        $notification_owner = $ticket->user;
        $template = 'ticketit::emails.status';
        $data = [
            'ticket'             => serialize($ticket),
            'notification_owner' => serialize($notification_owner),
            'original_ticket'    => serialize($original_ticket),
        ];

        if (strtotime($ticket->completed_at)) {
            $this->sendNotification($template, $data, $ticket, $notification_owner, $notification_owner->name.trans('ticketit::lang.notify-updated').$ticket->subject.trans('ticketit::lang.notify-status-to-complete'), 'status');
            
            // send notification to agents
            try {
                $notif_service = new NotificationService;
                $agents = $notif_service->getAllNotificationSubscribers('closed', $ticket->category_id, $ticket->agent_id);

                foreach($agents as $agent) {
                    $this->sendNotification($template, $data, $ticket, $agent, $notification_owner->name.trans('ticketit::lang.notify-updated').$ticket->subject.trans('ticketit::lang.notify-status-to-complete'), 'status');
                }
            } catch(\Exception $e) {
                \Log::error('Ticket Error on line '. $e->getLine());
                \Log::error($e->getMessage());
            }
            
        } else {
            $this->sendNotification($template, $data, $ticket, $notification_owner,
                // $notification_owner->name.trans('ticketit::lang.notify-updated').$ticket->subject.trans('ticketit::lang.notify-status-to').$ticket->status->name, 'status');
                self::OWNER.trans('ticketit::lang.notify-updated').$ticket->subject.trans('ticketit::lang.notify-status-to').$ticket->status->name, 'status');
        }
    }

    public function ticketAgentUpdated(Ticket $ticket, Ticket $original_ticket)
    {
        $notification_owner = $ticket->agent;
        $template = 'ticketit::emails.transfer';
        $data = [
            'ticket'             => serialize($ticket),
            'notification_owner' => serialize($notification_owner),
            'original_ticket'    => serialize($original_ticket),
        ];

        // send notification to agents
        try {
            $notif_service = new NotificationService;
            $agents = $notif_service->getAllNotificationSubscribers('ticket_assigned', $ticket->category_id, $ticket->agent_id);

            foreach($agents as $agent) {
                // $this->sendNotification($template, $data, $ticket, $agent, $original_ticket->agent->name.trans('ticketit::lang.notify-transferred').$ticket->subject.trans('ticketit::lang.notify-to-you'), 'agent');
                $this->sendNotification($template, $data, $ticket, $agent, $original_ticket->agent->name.trans('ticketit::lang.notify-transferred').$ticket->subject.' to '.$notification_owner->name, 'agent');
            }
        } catch(\Exception $e) {
            \Log::error('Ticket Error on line '. $e->getLine());
            \Log::error($e->getMessage());
        }
    }

    public function newTicketNotifyAgent(Ticket $ticket)
    {
        $notification_owner = Sentinel::getUser();
        $template = 'ticketit::emails.assigned';
        $data = [
            'ticket'             => serialize($ticket),
            'notification_owner' => serialize($notification_owner),
            'images'             => serialize($this->extractLinks($ticket->html))
        ];

        // $this->sendNotification($template, $data, $ticket, $ticket->agent, $notification_owner->name.trans('ticketit::lang.notify-created-ticket').$ticket->subject, 'new-ticket');

        // send notification to agents
        try {
            $notif_service = new NotificationService;
            $agents = $notif_service->getAllNotificationSubscribers('new', $ticket->category_id, $ticket->agent_id);
    
            foreach($agents as $agent) {
                $this->sendNotification($template, $data, $ticket, $agent, $notification_owner->name.trans('ticketit::lang.notify-created-ticket').$ticket->subject, 'new-ticket');
            }
        } catch(\Exception $e) {
            \Log::error('Ticket Error on line '. $e->getLine());
            \Log::error($e->getMessage());
        }

        $template = 'ticketit::emails.assigned-zapier';
        $this->sendNotification($template, $data, $ticket, $ticket->agent,
            $notification_owner->name.trans('ticketit::lang.notify-created-ticket').$ticket->subject, 'new-ticket-zapier');

    }

    public function newTicketNotifyUser(Ticket $ticket)
    {
        $notification_owner = Sentinel::getUser();
        $template = 'ticketit::emails.assigned';
        $data = [
            'ticket'             => serialize($ticket),
            'notification_owner' => serialize($notification_owner),
            'images'             => serialize($this->extractLinks($ticket->html))
        ];

        $this->sendNotification($template, $data, $ticket, $notification_owner, $notification_owner->name.trans('ticketit::lang.notify-created-ticket').$ticket->subject, 'new-ticket');
    }

    /**
     * Extract Links from content
     * @return Array | array
     */
    protected function extractLinks($content)
    {
        //Get the page's HTML source using file_get_contents.
        $htmlDom = new \DomDocument();
        //Parse the HTML of the page using DOMDocument::loadHTML
        $htmlDom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);    
        //Extract the links from the HTML.
        $links = $htmlDom->getElementsByTagName('a');
        //Array that will contain our extracted links.
        $extractedLinks = array();
        //We can do this because the DOMNodeList object is traversable.
        foreach($links as $link)
        {
            $linkText = $link->nodeValue;
            $linkHref = $link->getAttribute('href');
            //If the link is empty, skip it and don't
            if(strlen(trim($linkHref)) == 0){
                continue;
            }
            //Skip if it is a hashtag / anchor link.
            if($linkHref[0] == '#'){
                continue;
            }
            //Add the link to our $extractedLinks array.
            $extractedLinks[] = array(
                'text' => $linkText,
                'href' => $linkHref
            );
        }
        return $extractedLinks;
    }

    public function newCommentAndStatus(Comment $comment, Ticket $ticket, Ticket $original_ticket)
    {
        $notification_owner = Sentinel::getUser();
        $template = 'ticketit::emails.comment-status';
        $data = [
            'comment'            => serialize($comment),
            'ticket'             => serialize($ticket),
            'notification_owner' => serialize($notification_owner),
            'original_ticket'    => serialize($original_ticket),
        ];

        // dont send notif if comment was created by the ticket owner
        if($comment->user_id == $ticket->user_id) {
            return false;
        }
        
        if (strtotime($ticket->completed_at)) {
            $this->sendNotification($template, $data, $ticket, $notification_owner,
                // $notification_owner->name.trans('ticketit::lang.comment-notify-updated').$ticket->subject.trans('ticketit::lang.notify-status-to-complete'), 'status');
                self::OWNER.trans('ticketit::lang.comment-notify-updated').$ticket->subject.trans('ticketit::lang.notify-status-to-complete'), 'status');
        } else {
            $this->sendNotification($template, $data, $ticket, $notification_owner,
                // $notification_owner->name.trans('ticketit::lang.comment-notify-updated').$ticket->subject.trans('ticketit::lang.notify-status-to').$ticket->status->name, 'status');
                self::OWNER.trans('ticketit::lang.comment-notify-updated').$ticket->subject.trans('ticketit::lang.notify-status-to').$ticket->status->name, 'status');
        }
    }

    /**
     * Send email notifications from the action owner to other involved users.
     *
     * @param string $template
     * @param array  $data
     * @param object $ticket
     * @param object $notification_owner
     */
    public function sendNotification($template, $data, $ticket, $notification_owner, $subject, $type)
    {
        /**
         * @var User
         */
        $to = $notification_owner;
        $setting = new TSetting();
        $notify_data = [
            'channels'  =>  ['database'],
            'title'     =>  'Ticket Support Notification',
            'message'   =>  $subject,
            'action'    =>  route($setting->grab('main_route').'.show', $ticket->id),
            'image'     =>  '#'
        ];

        /* if($type == 'comment'){
            $to = $notification_owner;
            $notify_data['title'] = 'New Comment on Ticket';
        }
        else if($type == 'new-ticket'){
            $to = $ticket->agent;
            $notify_data['title'] = 'New Ticket created';
        }
        else if ($type !== 'agent') {
            $to = $ticket->user;
            $notify_data['title'] = 'Ticket Notification';
            if ($ticket->user->email != $notification_owner->email) {
                $to = $ticket->user;
            }

            if ($ticket->agent->email != $notification_owner->email) {
                $to = $ticket->agent;
            }
        }
        else {
            $to = $ticket->agent;
        } */

        /* if(env('TICKET_SYSTEM', 'prod') == 'dev'){
            $to = ['email' => env('DEVELOPER_EMAIL',''), 'name' => 'Ticket Testing'];
            $to = (object) $to;
        } */

        if($type == 'new-ticket-zapier'){
            // $to = [$to];
            $zapp =  (object)['email' => env('TICKETS_SECOND_EMAIL',''), 'name' => env('APP_NAME')];
            // array_push($to, $zapp);
            $to = $zapp;
        }


        if (LaravelVersion::lt('5.4')) {
            $mail_callback = function ($m) use ($to, $notification_owner, $subject) {
                $m->to($to->email, $to->name);

                $m->replyTo($notification_owner->email, $notification_owner->name);

                $m->subject($subject);
            };

            if (TSetting::grab('queue_emails') == 'yes') {
                Mail::queue($template, $data, $mail_callback);
            } else {
                Mail::send($template, $data, $mail_callback);
            }
        } elseif (LaravelVersion::min('5.4')) {
            $mail = new \Kordy\Ticketit\Mail\TicketitNotification($template, $data, $notification_owner, $subject);

            if (TSetting::grab('queue_emails') == 'yes') {
                Mail::to($to)->queue($mail);
            } else {
                Mail::to($to)->send($mail);
            }

            // Send User Inapp Notification when email sent
            if(isset($to->id)){
                $user = Sentinel::findUserById($to->id);
                {
                    $user->notify(new TicketNotification($notify_data));
                }
            }
        }
    }
}
