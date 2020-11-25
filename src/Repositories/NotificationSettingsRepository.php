<?php
namespace Kordy\Ticketit\Repositories;

use Kordy\Ticketit\Models\NotificationSetting;

class NotificationSettingsRepository {

    public function updateOrCreate($where, $values)
    {
        return NotificationSetting::updateOrCreate($where, $values);
    }
    
    public function deleteWhere($where)
    {
        return NotificationSetting::where($where)->delete();
    }

    public function getSettingsByUserId($user_id)
    {
        return NotificationSetting::where('user_id', $user_id)->get();
    }

    public function getAllEventSubscribers($event)
    {
        return NotificationSetting::with('agent')
                                    ->where('event', $event)
                                    ->where('all', true)
                                    ->get();
    }

    public function getOwnEventSubscriber($event, $user_id)
    {
        return NotificationSetting::with('agent')
                                    ->where('user_id', $user_id)
                                    ->where('event', $event)
                                    ->where('own', true)
                                    ->get();
    }

    public function getAllCategorySubscribers($category_id)
    {
        return NotificationSetting::with('agent')
                                    ->where('category_id', $category_id)
                                    ->where('all', true)
                                    ->get();
    }

    public function getCategorySubscriberAll($category_id, $user_id)
    {
        return NotificationSetting::where('user_id', $user_id)
                                    ->where('category_id', $category_id)
                                    ->where('all', true)
                                    ->first();
    }

    public function getCategorySubscriber($category_id, $user_id)
    {
        return NotificationSetting::where('user_id', $user_id)
                                    ->where('category_id', $category_id)
                                    ->first();
    }
}