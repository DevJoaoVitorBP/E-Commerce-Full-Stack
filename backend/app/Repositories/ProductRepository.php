<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository extends BaseRepository
{
    private array $sortableFields = ['id', 'name', 'price', 'created_at', 'updated_at', 'quantity', 'min_quantity', 'category_id'];

    public function __construct()
    {
        $this->model = new Product;
    }

    public function getActive()
    {
        return $this->model->active()->paginate(15);
    }

    public function getByCategory(int $categoryId)
    {
        return $this->model->where('category_id', $categoryId)->paginate(15);
    }

    public function getByCategoryWithFilters(int $categoryId, array $filters)
    {
        $query = $this->model->active()->where('category_id', $categoryId);

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (! empty($filters['sort'])) {
            $sortBy = $filters['sort'];
            $direction = $filters['sort_direction'] ?? 'asc';

            if (in_array($sortBy, $this->sortableFields)) {
                if (! in_array(strtolower($direction), ['asc', 'desc'])) {
                    $direction = 'asc';
                }
                $query->orderBy($sortBy, $direction);
            }
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function searchByName(string $query)
    {
        if (empty(trim($query))) {
            return $this->model->paginate(15);
        }

        return $this->model
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->paginate(15);
    }

    public function getLowStock(array $filters = [])
    {
        $per_page = $filters['per_page'] ?? 10;

        return $this->model->lowStock()->paginate($per_page);
    }

    public function getWithFilters(array $filters)
    {
        $query = $this->model->active();

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (! empty($filters['sort'])) {
            $sortBy = $filters['sort'];
            $direction = $filters['sort_direction'] ?? 'asc';

            // Validar contra whitelist para prevenir SQL injection
            if (in_array($sortBy, $this->sortableFields)) {
                if (! in_array(strtolower($direction), ['asc', 'desc'])) {
                    $direction = 'asc';
                }
                $query->orderBy($sortBy, $direction);
            }
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }
}
