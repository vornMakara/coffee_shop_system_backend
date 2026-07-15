<?php

namespace App\Modules\Catalog\Category\Services;

use App\Modules\Catalog\Category\Models\Category;

class CategoryService
{
    public function getCategories(array $filters = [])
    {
        $query = Category::query();
        
        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }
        
        return $query->orderBy('sort_order')->get();
    }

    public function createCategory(array $data)
    {
        return Category::create($data);
    }

    public function updateCategory(Category $category, array $data)
    {
        $category->update($data);
        return $category;
    }

    public function deleteCategory(Category $category)
    {
        return $category->delete();
    }
}
