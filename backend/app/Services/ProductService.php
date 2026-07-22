<?php

namespace App\Services;

use App\DTOs\ProductDTO;
use App\Events\ProductCreated;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductService
{
    protected ProductRepository $repository;

    protected ImageService $imageService;

    protected const CACHE_TTL = 3600; // 1 hora = 3600 segundos

    protected const CACHE_TAG = 'products';

    protected const CATEGORY_CACHE_TAG = 'category_products';

    public function __construct()
    {
        $this->repository = new ProductRepository;
        $this->imageService = new ImageService;
    }

    public function getAllProducts(array $filters = [])
    {
        // Para buscar com filtros, não usa cache
        return $this->repository->getWithFilters($filters);
    }

    public function getProductById(int $id)
    {
        $cacheKey = "product.{$id}";

        return Cache::tags([self::CACHE_TAG])
            ->remember($cacheKey, self::CACHE_TTL, function () use ($id) {
                return $this->repository->find($id);
            });
    }

    public function createProduct(ProductDTO $dto): ?ProductDTO
    {
        $data = $dto->toArray();
        $data['slug'] = Str::slug($data['name']);

        unset($data['image_path']);

        $product = $this->repository->create($data);

        if ($product && $dto->tags) {
            $product->tags()->attach($dto->tags);
        }

        // Invalidar cache de produtos e categorias com tags inteligentes
        Cache::tags([self::CACHE_TAG, self::CATEGORY_CACHE_TAG])->flush();

        // Disparar evento de produto criado
        ProductCreated::dispatch($product);

        return $product ? ProductDTO::fromArray($product->toArray()) : null;
    }

    public function updateProduct(int $id, ProductDTO $dto): ?ProductDTO
    {
        $data = $dto->toArray();
        $data['slug'] = Str::slug($data['name']);

        unset($data['image_path']);

        $product = $this->repository->update($id, $data);

        if ($product && $dto->tags !== null) {
            $product->tags()->sync($dto->tags);
        }

        // Invalidar cache do produto e categorias relacionadas
        Cache::tags([self::CACHE_TAG, self::CATEGORY_CACHE_TAG])->flush();

        return $product ? ProductDTO::fromArray($product->toArray()) : null;
    }

    public function deleteProduct(int $id): bool
    {
        $product = $this->repository->find($id);

        if ($product && $product->image_path) {
            $this->imageService->deleteProductImage($product->image_path);
        }

        $deleted = $this->repository->delete($id);

        // Invalidar cache ao deletar
        Cache::tags([self::CACHE_TAG, self::CATEGORY_CACHE_TAG])->flush();

        return $deleted !== null;
    }

    public function checkStockAvailability(int $productId, int $quantity): bool
    {
        $product = $this->repository->find($productId);

        return $product && $product->quantity >= $quantity;
    }

    public function getLowStockProducts(array $filters = [])
    {
        $filters['per_page'] = $filters['per_page'] ?? 10;

        return $this->repository->getLowStock($filters);
    }

    public function searchProducts(string $query)
    {
        return $this->repository->searchByName($query);
    }

    public function getProductsByCategory(int $categoryId, array $filters = [])
    {
        if (! empty($filters['search']) || ! empty($filters['min_price']) || ! empty($filters['max_price']) || ! empty($filters['sort'])) {
            return $this->repository->getByCategoryWithFilters($categoryId, $filters);
        }

        $cacheKey = "products.category.{$categoryId}";

        return Cache::tags([self::CATEGORY_CACHE_TAG, "category.{$categoryId}"])
            ->remember($cacheKey, self::CACHE_TTL, function () use ($categoryId) {
                return $this->repository->getByCategory($categoryId);
            });
    }

    public function uploadProductImage(int $productId, $imageFile): bool
    {
        try {
            $product = $this->repository->find($productId);

            if (! $product) {
                return false;
            }

            // Deletar imagem antiga se existir
            if ($product->image_path) {
                $this->imageService->deleteProductImage($product->image_path);
            }

            // Upload da nova imagem
            $fileName = $this->imageService->uploadProductImage($imageFile);

            if ($fileName) {
                $this->repository->update($productId, ['image_path' => $fileName]);
                Cache::tags([self::CACHE_TAG, self::CATEGORY_CACHE_TAG])->flush();

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Erro ao fazer upload de imagem do produto: '.$e->getMessage());

            return false;
        }
    }

    public function deleteProductImage(int $productId): bool
    {
        try {
            $product = $this->repository->find($productId);

            if (! $product || ! $product->image_path) {
                return false;
            }

            $this->imageService->deleteProductImage($product->image_path);
            $this->repository->update($productId, ['image_path' => null]);
            Cache::tags([self::CACHE_TAG, self::CATEGORY_CACHE_TAG])->flush();

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao deletar imagem do produto: '.$e->getMessage());

            return false;
        }
    }
}
