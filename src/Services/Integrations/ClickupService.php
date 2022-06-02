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
use Log;

class ClickupService
{
    protected $priority = [
        'Critical' => 1,
        'Normal' => 3,
        'Low' => 4
    ];
    
    public function store_ticket_data($ticket, $images, $content_text)
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
        $image_link = $asana_service->extractLinks($images);
        $image_url = '';
        if(count($image_link)) {
            foreach($image_link as $image) {
                $image_url .= '<a href="' . $image['href'] . '">' . trim($image['text']) . '</a>' . "\n";
            }
        }

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

            if($field['slug'] == 'clickup_developer_status' && $ticket->dev_status_id) {
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

            if($field['slug'] == 'clickup_ticket_status') {
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

            if($field['slug'] == 'clickup_screenshot' && !empty($image_url)) {
                $custom_fields[] = [
                    'id' => $field['value'],
                    'value' => $image_url
                ];
            }
        }

        $params['name'] = $ticket->subject;
        $params['description'] = strip_tags($content_text);
        $params['status'] = 'Open';
        $params['priority'] = $this->priority[$ticket->priority->name];
        $params['custom_fields'] = $custom_fields;
        
        $url = "https://api.clickup.com/api/v2/list/{$clickup_list_id->value}/task";

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $clickup_token->value
            ],
            'json' => $params
        ];

        $data = $client->post($url, $options);
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

}                                                         