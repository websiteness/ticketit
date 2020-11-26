<?php
namespace Kordy\Ticketit\Services\Integrations;
use Kordy\Ticketit\Models\TSetting;

class AsanaService
{
    private function make_api_request($path, $method, $body = [])
    {
        $url = 'https://app.asana.com/api/1.0/' . $path;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->get_auth_token()
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }

    private function get_auth_token()
    {
        return TSetting::getBySlug('asana_token')->value;
    }

    public function store_auth_token($token)
    {
        TSetting::updateOrCreate(
            ['slug' => 'asana_token'],
            ['slug' => 'asana_token', 'value' => $token, 'default' => $token]
        );

        session()->flash('status', 'Token saved!');
    }

    public function get_projects()
    {
        return $this->make_api_request('projects', 'GET');
    }
}