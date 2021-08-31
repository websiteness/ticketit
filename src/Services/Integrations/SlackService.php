<?php
namespace Kordy\Ticketit\Services\Integrations;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Kordy\Ticketit\Models\TicketsAgentSettings;

class SlackService
{
    public function sendTicketToAgentByZone($ticket)
    {
        $zone_id = $ticket->zone_id;

        if(isset($zone_id)) {

            $user_id = DB::table('ticketit_categories_users')->where('category_id', $zone_id )->value('user_id');
            $agent = TicketsAgentSettings::where('user_id', $user_id)->where('slug', 'slack_webhook_url')->first();    

            if(isset($agent) && $agent->slug == "slack_webhook_url" ){ 
                $content = $this->formatMessage($ticket);
                $data = [
                    "text" => "$content"
                ];
                $options = [
                    "headers" => [
                        "Content-Type" => "application/json"
                    ],
                    "json" => $data
                ];
    
                $client = new Client();
                $data = $client->post($agent->data, $options);
        
                if($data->getStatusCode() == 200) {
                    return true;
                } else {
                    return false;
                }
               
            } else {
                return false;
            }
        }
    }
                                                                     
                                                                                
    public function formatMessage($ticket)
    {
        $url = url("/tickets/{$ticket->id}");
        $content = "";
        $content .= "*Priority:* ".$ticket->priority->name."\n";
        $content .= "*User:* ".$ticket->user->name."\n";
        $content .= "*Link to Ticket:* ".$url;
        $content .= "\n";
        $content .= "\n";
        $content .= "\n";
        $content .= 'Hi '.$ticket->agent->name.",";
        $content .= "\n".$ticket->user->name.' has submitted a '. $ticket->priority->name .' ticket concerning '. $ticket->subject. ' on '. date_format($ticket->created_at,"m/d/Y").".";
        $content .= "\n";
        $content .= "\n";
        $content .= $url."\n";
        $content .= "\n";
        $content .= " Please explore the issue and let me know an approximate timeline, so I can get back to ". $ticket->user->name. ". Thank you.";
        return $content;
    }
} 
                                                                                                                                          