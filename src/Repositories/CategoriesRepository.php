<?php
namespace Kordy\Ticketit\Repositories;

use Kordy\Ticketit\Models\Category;

class CategoriesRepository {

    public function getAll()
    {
        return Category::all();
    }

    public function getCategoriesCount()
    {
        return Category::withCount(['tickets' => function($query) {
            $query->whereNull('completed_at');
        }])
        ->whereNotNull('parent')
        ->get();
    }

    public function getSubCategories()
    {
        return Category::whereNotNull('parent')->get();
    }

    public function getById($id)
    {
        return Category::find($id);
    }
}