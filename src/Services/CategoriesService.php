<?php
namespace Kordy\Ticketit\Services;

use Kordy\Ticketit\Repositories\CategoriesRepository;

class CategoriesService {

    public function getAllCategories()
    {
        $cr = new CategoriesRepository;

        return $cr->getAll();
    }

    public function setCategoryOwners($owners)
    {
        foreach($owners as $key => $owner) {
            $categories_repository = new CategoriesRepository;

            $category = $categories_repository->getById($key);

            if(!$owner) {
                $category->agents()->sync([]);
                continue;
            }

            $category->agents()->sync([$owner]);
        }
    }
}