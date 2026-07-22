<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\ProductDTO;
use App\Http\Requests\FilterProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    use ApiResponses;

    protected ProductService $service;

    public function __construct()
    {
        $this->service = new ProductService;
    }

    public function index(FilterProductRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $products = $this->service->getAllProducts($filters);

        return $this->paginatedResponse(
            ProductResource::collection($products),
            'Produtos listados com sucesso'
        );
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->service->getProductById($id);

        if (! $product) {
            return $this->notFoundResponse('Produto n�o encontrado');
        }

        return $this->successResponse(new ProductResource($product), 'Produto obtido com sucesso');
    }

    public function lowStock(): JsonResponse
    {
        $filters = request()->only('per_page', 'page');
        $filters['per_page'] = $filters['per_page'] ?? 10;
        $products = $this->service->getLowStockProducts($filters);

        return $this->paginatedResponse(
            ProductResource::collection($products),
            'Produtos com estoque baixo listados com sucesso'
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $dto = ProductDTO::fromArray($request->validated());
        $product = $this->service->createProduct($dto);

        if (! $product) {
            return $this->errorResponse('Erro ao criar produto', null, 400);
        }

        // Fazer upload de imagem se fornecida
        if ($request->hasFile('image')) {
            $this->service->uploadProductImage($product->id, $request->file('image'));
        }

        return $this->createdResponse(
            new ProductResource($this->service->getProductById($product->id)),
            'Produto criado com sucesso'
        );
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->service->getProductById($id);

        if (! $product) {
            return $this->notFoundResponse('Produto n�o encontrado');
        }

        $dto = ProductDTO::fromArray($request->validated());
        $updated = $this->service->updateProduct($id, $dto);

        if (! $updated) {
            return $this->errorResponse('Erro ao atualizar produto', null, 400);
        }
        // Fazer upload de imagem se fornecida
        if ($request->hasFile('image')) {
            $this->service->uploadProductImage($id, $request->file('image'));
        }

        return $this->successResponse(
            new ProductResource($this->service->getProductById($id)),
            'Produto atualizado com sucesso'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $product = $this->service->getProductById($id);

        if (! $product) {
            return $this->notFoundResponse('Produto n�o encontrado');
        }

        $deleted = $this->service->deleteProduct($id);

        if (! $deleted) {
            return $this->errorResponse('Erro ao deletar produto', null, 400);
        }

        return $this->successResponse(null, 'Produto deletado com sucesso');
    }

    public function uploadImage(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = $this->service->getProductById($id);

        if (! $product) {
            return $this->notFoundResponse('Produto não encontrado');
        }

        $uploaded = $this->service->uploadProductImage($id, $request->file('image'));

        if (! $uploaded) {
            return $this->errorResponse('Erro ao fazer upload da imagem', null, 400);
        }

        return $this->successResponse(
            new ProductResource($this->service->getProductById($id)),
            'Imagem enviada com sucesso'
        );
    }

    public function deleteImage(int $id): JsonResponse
    {
        $product = $this->service->getProductById($id);

        if (! $product) {
            return $this->notFoundResponse('Produto não encontrado');
        }

        $deleted = $this->service->deleteProductImage($id);

        if (! $deleted) {
            return $this->errorResponse('Erro ao deletar imagem', null, 400);
        }

        return $this->successResponse(
            new ProductResource($this->service->getProductById($id)),
            'Imagem deletada com sucesso'
        );
    }
}
