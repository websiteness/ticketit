<?php
namespace Kordy\Ticketit\Services\Integrations;
use Kordy\Ticketit\Models\Agent;
use Kordy\Ticketit\Models\TSetting;
use Kordy\Ticketit\Repositories\CategoriesRepository;
use Kordy\Ticketit\Repositories\TicketsRepository;
use Kordy\Ticketit\Repositories\SettingsRepository;
use Kordy\Ticketit\Repositories\CommentsRepository;

class AsanaService
{
    private $pre_token;

    private function make_api_request($path, $method, $post = [])
    {
        $url = 'https://app.asana.com/api/1.0/' . $path;

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

        return json_decode($response, true)['data'];
    }

    public function get_auth_token()
    {
        $token = TSetting::getBySlug('asana_token');
        if( isset( $token->value ) ){
            return $token->value;
        }
        return null;
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
            ['slug' => 'asana_token'],
            ['slug' => 'asana_token', 'value' => $token, 'default' => $token]
        );

        session()->flash('status', 'Token saved!');
        return;
    }

    public function get_project_id() {
        $id = TSetting::getBySlug('asana_project_gid');

        if( isset($id->value) ){
            return $id->value;
        }

        return false;
    }

    public function get_projects()
    {
        $workspace_gid = $this->get_workspace()->value;

        $url = 'workspaces/' . $workspace_gid . '/projects';

        return $this->make_api_request($url, 'GET');
    }

    public function store_project($gid) {

        TSetting::updateOrCreate(
            ['slug' => 'asana_project_gid'],
            ['slug' => 'asana_project_gid', 'value' => $gid, 'default' => $gid]
        );

        session()->flash('status', 'Project saved!');
    }

    public function get_set_project()
    {
        return TSetting::where('slug', 'asana_project_gid')->first();
    }

    public function get_sections()
    {
        $project_id = $this->get_project_id();

        return $this->make_api_request('projects/'.$project_id.'/sections', 'GET');
    }

    public function get_users()
    {
        $workspace = $this->get_workspace();

        if(!$workspace) {
            return null;
        }

        $url = 'workspaces/' . $workspace->value . '/users';

        return $this->make_api_request($url, 'GET');
    }

    public function map_sections($sections)
    {
        foreach($sections as $key => $section) {

            $category_repository = new CategoriesRepository;
            $category_repository->update_asana_gid($key, $section);
        }

        session()->flash('status', 'Successfully saved!');
    }

    public function map_users($users)
    {
        foreach($users as $key => $user) {
            $agent = Agent::find($key);
            $agent->ticketit_asana_gid = $user;
            $agent->save();
        }

        session()->flash('status', 'Successfully saved!');
    }

    public function get_tags()
    {
        $workspace_gid = $this->get_workspace()->value;

        $url = 'workspaces/' . $workspace_gid . '/tags';

        return $this->make_api_request($url, 'GET');
    }

    public function get_setting_tags()
    {
        $tags = TSetting::where('slug', 'asana_tags_gid')->first();

        if($tags) {
            return json_decode($tags->value, true);
        }

        return $tags;
    }
    

    public function store_tags($tags)
    {
        $gids = json_encode($tags);

        TSetting::updateOrCreate(
            ['slug' => 'asana_tags_gid'],
            ['slug' => 'asana_tags_gid', 'value' => $gids, 'default' => $gids]
        );

        session()->flash('status', 'Successfully saved!');
    }

    public function get_workspaces()
    {
        return $this->make_api_request('workspaces', 'GET');
    }

    public function get_workspace()
    {
        return TSetting::where('slug', 'asana_workspace_gid')->first();
    }

    public function store_workspace($gid)
    {
        TSetting::updateOrCreate(
            ['slug' => 'asana_workspace_gid'],
            ['slug' => 'asana_workspace_gid', 'value' => $gid, 'default' => $gid]
        );

        session()->flash('status', 'Successfully saved!');
    }

    public function push_ticket($ticket_id)
    {
        if(!$this->get_auth_token()) {
            return false;
        }

        $ticket_repository = new TicketsRepository;
        $ticket = $ticket_repository->getById($ticket_id);

        $agent = Agent::find($ticket->agent_id);

        $tags = $this->get_setting_tags();

        $subject = 'Ticket #' . $ticket->id . ' - ' . $ticket->subject;

        $post = [
            'data' => [
                'name' => $subject,
                'completed' => false,
                'html_notes' => $this->build_html_notes($ticket),
                'projects' => [
                    $this->get_project_id()
                ]
            ]
        ];

        if($agent->ticketit_asana_gid) {
            $post['data']['assignee'] = $agent->ticketit_asana_gid;
        }

        if($tags) {
            $post['data']['tags'] = $tags;
        }

        $response = $this->make_api_request('tasks', 'POST', $post);

        // set ticket gid
        if($response) {
            $ticket_repository = new TicketsRepository;
            $ticket_repository->updateById($ticket->id, ['asana_task_gid' => $response['gid']]);
        }

        // add this task to it's section/category
        $this->assign_task_section($response['gid'], $ticket->category_id);
    }

    public function update_ticket($ticket_id)
    {
        try {
            if(!$this->get_auth_token()) {
                return false;
            }
    
            $ticket_repository = new TicketsRepository;
            $ticket = $ticket_repository->getById($ticket_id);
    
            if(!$ticket->asana_task_gid) {
                return false;
            }
    
            $post = [
                'data' => [
                    'html_notes' => $this->build_html_notes($ticket)
                ]
            ];
    
            $this->make_api_request('tasks/' . $ticket->asana_task_gid, 'PUT', $post);
        } catch(\Exception $e) {
            \Log::error('Tickets Error: failed to update ticket on Asana');
            \Log::error($e->getMessage());
        }
    }

    public function assign_task_section($task_gid, $category_id)
    {
        $category_repository = new CategoriesRepository;
        $category = $category_repository->getById($category_id);

        if(!$category->asana_section_gid) {
            return false;
        }

        $post = [
            'data' => [
                'task' => $task_gid
            ]
        ];

        $url = 'sections/' . $category->asana_section_gid . '/addTask';

        $this->make_api_request($url, 'POST', $post);
    }

    public function build_html_notes($ticket)
    {
        $settings_repository = new SettingsRepository;
        $main_route = $settings_repository->getBySlug('main_route');

        $images = $this->extractLinks($ticket->html);

        $ticket_url = '<a href="' . route($main_route->value.'.show', $ticket->id) . '">' . route($main_route->value.'.show', $ticket->id) .  '</a>';

        $category = $ticket->category->parent_category ? $ticket->category->parent_category->name : $ticket->category->name;
        $sub_category = $ticket->category->parent_category ? $ticket->category->name : '';

        $ticket_content = str_replace('Heat Map URL', '', (str_replace('View Image', "\n", $ticket->content)));
        
        $content = 'Ticket URL: ' . $ticket_url . "\n";
        $content .= 'Ticket #: ' . $ticket->id . "\n";
        $content .= 'Date: ' . $ticket->created_at->toDayDateTimeString() . "\n";
        $content .= 'Title: ' . $ticket->subject . "\n";
        $content .= 'User: ' . $ticket->user->name . "\n";
        $content .= 'Category: ' . $category . "\n";

        if($ticket->category->parent_category) {
            $content .= 'Sub Category: ' . $sub_category . "\n";
        }

        $content .= 'Status: ' . $ticket->status->name . "\n\n";
        $content .= $ticket_content . "\n";
        $content .= 'Images:' . "\n";

        foreach($images as $image) {
            $content .= '<a href="' . $image['href'] . '">' . trim($image['text']) . '</a>' . "\n";
        }

        // add comment
        $comments_repository = new CommentsRepository;

        $comments = $comments_repository->getAllByTicketId($ticket->id);

        if($comments) {
            $content .= "\n\n";

            foreach($comments as $comment) {
                // set comment owner
                if($comment->user_id == $ticket->user_id) {
                    $content .= '<strong>==============USER RESPONSE=========</strong>';
                } else {
                    $content .= '<strong>==============LG RESPONSE===========</strong>';
                }

                $content .= "\n";
                $content .= $comment->content . "\n";

                $images = $this->extractLinks($comment->html);

                if($images) {
                    $content .= 'Images:' . "\n";
                    
                    foreach($images as $image) {
                        $content .= '<a href="' . $image['href'] . '">' . trim($image['text']) . '</a>' . "\n";
                    }
                }
            }
        }

        return '<body>' . $content . '</body>';
    }

    public function complete_task($ticket_id)
    {
        $ticket_repository = new TicketsRepository;
        $ticket = $ticket_repository->getById($ticket_id);

        $url = 'tasks/' . $ticket->asana_task_gid;

        $post = [
            'data' => [
                'completed' => true
            ]
        ];

        $this->make_api_request($url, 'PUT', $post);
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
}