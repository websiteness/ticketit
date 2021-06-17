<?php
namespace Kordy\Ticketit\Services\Integrations;
use Kordy\Ticketit\Models\Agent;
use Kordy\Ticketit\Models\TSetting;
use Kordy\Ticketit\Repositories\CategoriesRepository;
use Kordy\Ticketit\Repositories\TicketsRepository;
use Kordy\Ticketit\Repositories\SettingsRepository;
use Kordy\Ticketit\Repositories\CommentsRepository;
use Kordy\Ticketit\Repositories\StatusRepository;

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


}