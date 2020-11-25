<?php
namespace Kordy\Ticketit\Services;

use Kordy\Ticketit\Repositories\NotificationSettingsRepository;
use Kordy\Ticketit\Repositories\CategoriesRepository;

class NotificationService {

    private $notification_setting_repository;

    public function __construct()
    {
        $this->notification_setting_repository = new NotificationSettingsRepository;
    }

    public function eventList()
    {
        return [
            [
                'key' => 'new',
                'value' => 'New'
            ],
            [
                'key' => 'replies',
                'value' => 'Replies'
            ],
            [
                'key' => 'closed',
                'value' => 'Closed'
            ],
            [
                'key' => 'ticket_assigned',
                'value' => 'Ticket Assigned'
            ],
        ];
    }

    public function getUserSettings($user_id)
    {
        $settings = $this->notification_setting_repository->getSettingsByUserId($user_id);

        $active_settings = [];
        
        foreach($settings as $setting) {
            $key = $setting->event ?? $setting->category_id;

            $active_settings[$key]['all'] = $setting->all;
            $active_settings[$key]['own'] = $setting->own;
        }

        return $active_settings;
    }

    public function saveSettings($user_id, $request)
    {
        $cr = new CategoriesRepository;

        // save the events
        if(isset($request['events'])) {
            foreach($request['events'] as $key => $value) {
                $where = [
                    'user_id' => $user_id,
                    'event' => $key
                ];
    
                $data = [
                    'event' => $key,
                    'all' => isset($value['all']) ? true : false,
                    'own' => isset($value['own']) ? true : false
                ];
    
                $this->notification_setting_repository->updateOrCreate($where, $data);
            }
    
            $active_events = array_keys($request['events']);
    
            // delete inactive events
            foreach($this->eventList() as $event) {
                if(!in_array($event['key'], $active_events)) {
                    $where = [
                        ['user_id', $user_id],
                        ['event', $event['key']]
                    ];
        
                    $this->notification_setting_repository->deleteWhere($where);
                }
            }
        } else {
            $where = [
                ['user_id', $user_id],
                ['event', '!=', null]
            ];

            $this->notification_setting_repository->deleteWhere($where);
        }
        

        if(isset($request['categories'])) {
            // save the categories
            foreach($request['categories'] as $key => $value) {
                $where = [
                    'user_id' => $user_id,
                    'category_id' => $key
                ];

                $data = [
                    'category_id' => $key,
                    'all' => isset($value['all']) ? true : false,
                    'own' => isset($value['own']) ? true : false
                ];

                $this->notification_setting_repository->updateOrCreate($where, $data);
            }

            $active_categories = array_keys($request['categories']);
            $sub_categories = $cr->getSubCategories();

            // delete inactive categoires
            foreach($sub_categories as $category) {
                if(!in_array($category->id, $active_categories)) {
                    $where = [
                        ['user_id', $user_id],
                        ['category_id', $category->id]
                    ];
        
                    $this->notification_setting_repository->deleteWhere($where);
                }
            }
        } else {
            $where = [
                ['user_id', $user_id],
                ['category_id', '!=', null]
            ];

            $this->notification_setting_repository->deleteWhere($where);
        }
    }

    public function getAllNotificationSubscribers($event, $category_id, $ticket_agent_id)
    {
        $subscribers = $this->notification_setting_repository->getAllEventSubscribers($event);

        $ticket_agent_included = false;

        foreach($subscribers as $key => $subscriber) {
            $category_subscriber = $this->notification_setting_repository->getCategorySubscriberAll($category_id, $subscriber->agent->id);

            if(!$category_subscriber) {
                unset($subscribers[$key]);
                continue;
            }

            // check if current ticket agent wne is included
            if($subscriber->user_id == $ticket_agent_id) {
                $ticket_agent_included = true;
            }
        }

        // rerieve agent owner own subscription if it's not existing in $subscribers collection
        if(!$ticket_agent_included) {
            $subscriber = $this->notification_setting_repository->getOwnEventSubscriber($event, $ticket_agent_id);

            if(count($subscriber)) {
                $category_subscriber = $this->notification_setting_repository->getCategorySubscriber($category_id, $ticket_agent_id);

                if($category_subscriber) {
                    // return $subscriber;
                    $subscribers->prepend($subscriber[0]);
                }
            }
        }

        // return $subscribers;

        return $subscribers->pluck('agent');
    }
}