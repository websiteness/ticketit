<?php
namespace Kordy\Ticketit\Repositories;

use Kordy\Ticketit\Models\Category;

class CategoriesRepository {

    public function getCategoriesCount()
    {
        return Category::withCount(['tickets' => function($query) {
            $query->whereNull('completed_at');
        }])
        ->whereNotNull('parent')
        ->get();
    }
}