<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private array $categoryCache = [];

    /** Extensões de imagem aceitas ao salvar no disco */
    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function run(): void
    {
        // Buscar produtos reais da DummyJSON API
        try {
            $response = Http::timeout(30)->get(
                'https://dummyjson.com/products?limit=100&select=title,description,price,category,images,stock'
            );
        } catch (ConnectionException $e) {
            $this->command->warn('Não foi possível conectar à API ('.$e->getMessage().'). Usando factory.');
            $this->runWithFactory();

            return;
        }

        if (! $response->successful()) {
            $this->command->warn('Falha ao buscar produtos da API (HTTP '.$response->status().'). Usando factory.');
            $this->runWithFactory();

            return;
        }

        $apiProducts = $response->json('products', []);

        if (empty($apiProducts)) {
            $this->command->warn('API retornou lista vazia. Usando factory.');
            $this->runWithFactory();

            return;
        }

        $this->command->info('Importando '.count($apiProducts).' produtos reais...');

        foreach ($apiProducts as $index => $data) {
            $category = $this->findOrCreateCategory($data['category']);

            // updateOrCreate pelo nome torna o seeder seguro para rodar mais de uma vez
            $product = Product::updateOrCreate(
                ['name' => $data['title']],
                [
                    'slug' => $this->uniqueProductSlug($data['title']),
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'cost_price' => round($data['price'] * 0.6, 2), // 40% de margem
                    'quantity' => $data['stock'] ?? rand(5, 100),
                    'category_id' => $category->id,
                    'active' => true,
                ]
            );

            // Tags aleatórias (só na criação, para não embaralhar tags já atribuídas)
            if ($product->wasRecentlyCreated) {
                $tags = Tag::inRandomOrder()->limit(rand(1, 4))->pluck('id');
                $product->tags()->attach($tags);
            }

            // Baixar imagem apenas se o produto ainda não tiver uma
            $imageUrl = $data['images'][0] ?? null;
            if ($imageUrl && ! $product->image_path) {
                $this->downloadAndSaveImage($product, $imageUrl);
            }

            $this->command->info("[{$index}] {$product->name}");
        }

        $this->command->info('✅ Produtos importados com sucesso!');
    }

    /**
     * Gera um slug único para o produto, evitando colisão (ex.: nomes repetidos
     * entre categorias diferentes da DummyJSON).
     */
    private function uniqueProductSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (
            Product::where('slug', $slug)
                ->where('name', '!=', $title)
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function findOrCreateCategory(string $apiCategory): Category
    {
        if (isset($this->categoryCache[$apiCategory])) {
            return $this->categoryCache[$apiCategory];
        }

        // Mapear categorias da API para nomes legíveis em português
        $map = [
            'smartphones' => 'Smartphones',
            'laptops' => 'Notebooks',
            'fragrances' => 'Perfumes',
            'skincare' => 'Cuidados com a Pele',
            'groceries' => 'Alimentos',
            'home-decoration' => 'Decoração',
            'furniture' => 'Móveis',
            'tops' => 'Camisetas',
            'womens-dresses' => 'Vestidos',
            'womens-shoes' => 'Calçados Femininos',
            'mens-shirts' => 'Camisas Masculinas',
            'mens-shoes' => 'Calçados Masculinos',
            'mens-watches' => 'Relógios Masculinos',
            'womens-watches' => 'Relógios Femininos',
            'womens-bags' => 'Bolsas',
            'womens-jewellery' => 'Joias',
            'sunglasses' => 'Óculos de Sol',
            'automotive' => 'Automotivo',
            'motorcycle' => 'Motos',
            'lighting' => 'Iluminação',
        ];

        $name = $map[$apiCategory] ?? Str::title(str_replace('-', ' ', $apiCategory));

        $category = Category::firstOrCreate(
            ['name' => $name],
            [
                'slug' => Str::slug($name),
                'description' => "Categoria: {$name}",
                'active' => true,
            ]
        );

        $this->categoryCache[$apiCategory] = $category;

        return $category;
    }

    private function downloadAndSaveImage(Product $product, string $url): void
    {
        try {
            $response = Http::timeout(15)->get($url);

            if (! $response->successful()) {
                return;
            }

            $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));

            if (! in_array($ext, self::ALLOWED_IMAGE_EXTENSIONS, true)) {
                $ext = 'jpg';
            }

            $fileName = Str::uuid().'.'.$ext;
            Storage::disk('public')->put("products/{$fileName}", $response->body());
            $product->update(['image_path' => $fileName]);
        } catch (\Exception $e) {
            // Imagem é opcional — falha no download não deve interromper o seeder
        }
    }

    private function runWithFactory(): void
    {
        Product::factory(50)->create()->each(function (Product $product) {
            $tags = Tag::inRandomOrder()->limit(rand(1, 5))->pluck('id');
            $product->tags()->attach($tags);
        });
    }
}
