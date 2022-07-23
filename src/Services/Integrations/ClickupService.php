<?php
namespace Kordy\Ticketit\Services\Integrations;

use Exception;
use Kordy\Ticketit\Models\Agent;
use Kordy\Ticketit\Models\TSetting;
use Kordy\Ticketit\Repositories\CategoriesRepository;
use Kordy\Ticketit\Repositories\TicketsRepository;
use Kordy\Ticketit\Repositories\SettingsRepository;
use Kordy\Ticketit\Repositories\CommentsRepository;
use Kordy\Ticketit\Repositories\StatusRepository;
use Sentinel;
use GuzzleHttp\Client;
use Kordy\Ticketit\Models\Ticket;
use Kordy\Ticketit\Services\Integrations\AsanaService;
use Kordy\Ticketit\Models\Status;
use Kordy\Ticketit\Models\Category;
use App\Models\TicketsDeveloperStatus;
use App\Jobs\ClickupUpdateTask;
use Log;

class ClickupService
{
    protected $priority = [
        'Critical' => 1,
        'Normal' => 3,
        'Low' => 4
    ];
    
    public function save($type, $ticket, $images, $content_text, $changes = [])
    {
        $client = new Client();
        $asana_service = new AsanaService();
        $params = [];
        $custom_fields = [];
        $settings = TSetting::where('slug', 'like', 'clickup%')->get();
        $fields = collect($settings)->whereNotIn('slug', [ 'clickup_space_id', 'clickup_list_id' ])->toArray();
        $key_fields =  collect($fields)->pluck('slug')->toArray();
        $clickup_list_id = collect($settings)->where('slug','clickup_list_id')->first();
        $clickup_token = collect($settings)->where('slug','clickup_token')->first();

        $status_id = Status::where('id', $ticket->status_id)->first();
        $image_url = !empty($images) ? $this->evaluateLinks($images) : '';

        foreach ($fields as $field) {
            if($field['slug'] == 'clickup_version') {
                $custom_fields[] = [
                    'id' => $field['value'],
                    'value' => $field['default']
                ];
            }

            if($field['slug'] == 'clickup_module') {
                $custom_fields[] = [
                    'id' => $field['value'],
                    'value' => $ticket->category->clickup_category_id
                ];
            }

            if($field['slug'] == 'clickup_developer_status' && $ticket->dev_status_id && !empty($changes['dev_status_id'])) {
                $dev_status_id = TicketsDeveloperStatus::where('id', $ticket->dev_status_id)->first();
                $custom_fields[] = [
                    'id' => $field['value'],
                    'value' => $dev_status_id->infinity_item_id
                ];
            }

            if($field['slug'] == 'clickup_username') {
                $custom_fields[] = [
                    'id' => $field['value'],
                    'value' => Sentinel::findById($ticket->user_id)->getFullNameAttribute()
                ];
            }

            if($field['slug'] == 'clickup_account_email') {
                $custom_fields[] = [
                    'id' => $field['value'],
                    'value' => Sentinel::findById($ticket->user_id)->email
                ];
            }

            if($field['slug'] == 'clickup_ticket_id') {
                $custom_fields[] = [
                    'id' => $field['value'],
                    'value' => (string)$ticket->id
                ];
            }

            if($field['slug'] == 'clickup_ticket_status' && !empty($changes['status_id'])) {
                $custom_fields[] = [
                    'id' => $field['value'],
                    'value' => $status_id->clickup_status_id
                ];
            }

            if($field['slug'] == 'clickup_ticket_link') {
                $custom_fields[] = [
                    'id' => $field['value'],
                    'value' => url("/tickets/{$ticket->id}")
                ];
            }
        }

        $params['name'] = $ticket->subject;
        if($content_text) {
            $params['markdown_description'] = str_replace('View Image', '', html_entity_decode(strip_tags($content_text)))."\n{$image_url}";
        }

        if($ticket->agent && !empty($changes['agent_id'])) {
            $params['assignees'] = [
                'add' => [$ticket->agent->clickup_member_id]
            ];
        }
        
        if(!empty($changes['priority_id'])) {
            $params['priority'] = $this->priority[$ticket->priority->name];
        }

        $params['status'] = $status_id->name == 'Ticket Closed' ? 'Closed' : 'Not Started';
        $params['custom_fields'] = $custom_fields;

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $clickup_token->value
            ],
            'json' => $params
        ];

        if($type == 'create') { 
            $data = $client->post("https://api.clickup.com/api/v2/list/{$clickup_list_id->value}/task", $options);
        } else {
            $data = $client->put("https://api.clickup.com/api/v2/task/{$ticket->clickup_item_id}", $options);
            
            if(count($changes)) {
                ClickupUpdateTask::dispatch($ticket, $changes);
            }
        }

        $res = $data->getBody()->getContents();

        if (isset(json_decode($res)->id)) {
            $_ticket = Ticket::find($ticket->id);
            $_ticket->clickup_item_id = json_decode($res)->id;
            $_ticket->save();      
            \Log::info('Ticket sucessfully sent');
            return true;
        } else {
            \Log::info('Error updating ticket.');
            return false;
        }         
    }

    public function updateTicketStatus($ticket, $status) {
        $client = new Client();
        $params = [];
        $settings = TSetting::where('slug', 'like', 'clickup%')->get();
        $clickup_token = collect($settings)->where('slug','clickup_token')->first();

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $clickup_token->value
            ],
            'json' => [ 'status' => $status == 'close' ? 'Closed' : 'In progress' ]
        ];
        
        $data = $client->put("https://api.clickup.com/api/v2/task/{$ticket->clickup_item_id}", $options);
        $res = $data->getBody()->getContents();

        if (isset(json_decode($res)->id)) {   
            \Log::info('Ticket sucessfully sent');
            return true;
        } else {
            \Log::info('Error updating ticket.');
            return false;
        }   
    }

    public function saveComment($comment, $ticket = null, $status_change = null) {
        $client = new Client();
        $params = [];
        $settings = TSetting::where('slug', 'like', 'clickup%')->get();
        $clickup_token = collect($settings)->where('slug','clickup_token')->first();
        $image_url = !empty($comment->html) ? $this->evaluateLinks($comment->html) : '';

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $clickup_token->value
            ],
            'json' => [ 
                'comment_text' => "From: ".$comment->user->name."\n\n".$comment->content."\n".$image_url 
            ]
        ];

        if(!empty($ticket)) {
            $data = $client->post("https://api.clickup.com/api/v2/task/{$ticket->clickup_item_id}/comment", $options);
        } else {
            $data = $client->put("https://api.clickup.com/api/v2/comment/{$comment->clickup_item_id}", $options);
        }

        $res = $data->getBody()->getContents();
        $res = json_decode($res);

        if($status_change) {
            ClickupUpdateTask::dispatch((object) [ 
                'status_id' => $status_change,
                'clickup_item_id' => $ticket->clickup_item_id
            ], [ 'status_id' => $status_change ]);
        }

        if (isset($res->id)) {
            if(!empty($ticket)) {
                $comment->clickup_item_id = $res->id;  
                $comment->save();
            }
            \Log::info('Comment sucessfully sent');
            return true;
        } else {
            \Log::info('Error updating comment.');
            return false;
        }   
    }

    public function deleteComment($comment) {
        $client = new Client();
        $params = [];
        $settings = TSetting::where('slug', 'like', 'clickup%')->get();
        $clickup_token = collect($settings)->where('slug','clickup_token')->first();

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $clickup_token->value
            ]
        ];

        $client->delete("https://api.clickup.com/api/v2/comment/{$comment->clickup_item_id}", $options);
    }

    public function evaluateLinks($data) {
        $result = '';
        $dom = new \DomDocument();
        $dom->loadHtml($data, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);

        $images = $dom->getElementsByTagName('a');
        foreach($images as $k => $img) {
            $result .= "[{$img->textContent}]({$img->getAttribute('href')})\n";
        }

        return $result;
    }

}                                                         