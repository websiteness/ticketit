<?php
namespace Kordy\Ticketit\Services\Integrations;
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

class InfinityService
{
    private $pre_token;

    private function make_api_request($path, $method, $post = [])
    {
        $url = 'https://app.startinfinity.com/api/' . $path;

        $token = $this->pre_token ?? $this->get_auth_token();

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ],
        ]);

        if($post) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
        }

        $response = curl_exec($curl);

        $status_code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        curl_close($curl);

        $valid_status_codes = [200, 201];

        if(!in_array($status_code, $valid_status_codes)) {
           return false;
        //return $response;
        }

        return json_decode($response, true);
    }

    

    public function get_auth_token()
    {
        $token = TSetting::getBySlug('infinity_token');
        if( isset( $token->value ) ){
            return $token->value;
        }
        return null;
    }

    
    public function get_workspaces()
    {
        return $this->make_api_request('me/workspaces', 'GET');
    }

    public function get_boards()
    {
        return $this->make_api_request('me/boards', 'GET');
    }

    public function store_auth_token($token)
    {
        $this->pre_token = $token;
        
        $workspaces = $this->get_workspaces();

        // validate token
        if(!$workspaces) {
            session()->flash('warning', 'Token authentication failed.');
            return;
        }

        TSetting::updateOrCreate(
            ['slug' => 'infinity_token'],
            ['slug' => 'infinity_token', 'value' => $token, 'default' => $token]
        );

        session()->flash('status', 'Token saved!');
        return;
    }

    public function store_workspace($id)
    {
        TSetting::updateOrCreate(
            ['slug' => 'infinity_workspace_id'],
            ['slug' => 'infinity_workspace_id', 'value' => $id, 'default' => $id]
        );

        session()->flash('status', 'Successfully saved!');
    }

    
    public function store_board($board)
    {
        TSetting::updateOrCreate(
            ['slug' => 'infinity_board_id'],
            ['slug' => 'infinity_board_id', 'value' => $board, 'default' => $board]
        );
        session()->flash('status', 'Successfully saved!');
    }

    public function get_folders($ws_id, $b_id)
    {
        $route = $ws_id.'/folder/'.$b_id;
        return $this->make_api_request($route, 'GET');
    }

        
    public function store_folder($folder)
    {
        TSetting::updateOrCreate(
            ['slug' => 'infinity_folder_id'],
            ['slug' => 'infinity_folder_id', 'value' => $folder, 'default' => $folder]
        );
        session()->flash('status', 'Successfully saved!');
    }

    public function get_attributes($workspace_id, $board_id)
    {
        $route = $workspace_id.'/'.$board_id.'/attributes';
        return $this->make_api_request($route, 'GET');
    }

    public function store_fields($fields)
    {
        foreach($fields as $key => $value) {
            TSetting::updateOrCreate(
                ['slug' => $key],
                ['slug' => $key, 'value' => $value, 'default' => $value]
            );
        }

        session()->flash('status', 'Successfully saved!');
    }
    
    public function store_ticket_data($ticket)
    {
        $client = new Client();
        $asana_service = new AsanaService();
        $infinity_slugs =  TSetting::where('slug', 'like', 'infinity%')->get();
        $fields = collect($infinity_slugs)->whereNotIn('slug',['infinity_token','infinity_workspace_id','infinity_board_id', 'infinity_folder_id'])->toArray();
        $key_fields =  collect($fields)->pluck('slug')->toArray();
        $infinity_token = collect($infinity_slugs)->where('slug','infinity_token')->toArray();
        $infinity_folder_id = collect($infinity_slugs)->where('slug','infinity_folder_id')->toArray(); 
        $infinity_workspace_id = collect($infinity_slugs)->where('slug','infinity_workspace_id')->toArray();
        $infinity_board_id = collect($infinity_slugs)->where('slug','infinity_board_id')->toArray();
        $infinity_values = [];
        $x = 0;

        foreach ($key_fields as $key_field) {
            foreach($fields as $key => $field) {
                if($key_field == $field['slug'] && $field['slug'] == 'infinity_description'){
                    $infinity_values[$x++] = [
                        'attribute_id' => $field['value'],
                        'data' => $asana_service->build_html_notes($ticket),
                    ];
                }
                if($key_field == $field['slug'] && $field['slug'] == 'infinity_subject'){
                    $infinity_values[$x++] = [
                        'attribute_id' => $field['value'],
                        'data' => $ticket->subject,
                    ];
                }
                if($key_field == $field['slug'] && $field['slug'] == 'infinity_ticket_owner'){
                    $infinity_values[$x++] = [
                        'attribute_id' => $field['value'],
                        'data' => Sentinel::findById($ticket->user_id)->getFullNameAttribute(),
                    ];
                }
                if($key_field == $field['slug'] && $field['slug'] == 'infinity_ticket_id'){
                    $infinity_values[$x++] = [
                        'attribute_id' => $field['value'],
                        'data' => (string)$ticket->id,
                    ];
                }
            }
        }     

        $infinity_data = [
            "folder_id" => array_shift($infinity_folder_id)['value'],
            "values" => $infinity_values 
        ];

        try {
            $url = "https://app.startinfinity.com/api/v1/".array_shift($infinity_workspace_id)['value']."/".array_shift($infinity_board_id)['value']."/items";
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer ".array_shift($infinity_token)['value'].""
                ],
                'json' => $infinity_data
            ];

            $data = $client->post($url, $options);
            $res = $data->getBody();
            if (json_decode($res)->id) {
                $ticket = Ticket::find($ticket->id);
                $ticket->infinity_item_id = json_decode($res)->id;
                $ticket->save();
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function get_user_by_workspace()
    {
        $workspace_id = TSetting::where('slug','infinity_workspace_id')->first();
        //Todo
        //Get user by role (ticket agent)
        //get user @ infinity by workspace id
        
    }

    //Todo
    // get array of users
    //create array of users 
    //store to 1 slug only
    public function store_mapped_users($users)
    {
        
    }
}