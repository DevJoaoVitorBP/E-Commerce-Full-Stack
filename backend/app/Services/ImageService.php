<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    protected const DISK = 'public';

    protected const DIRECTORY = 'products';

    protected const MAX_FILE_SIZE = 2048; // KB

    public function uploadProductImage(UploadedFile $file): ?string
    {
        try {
            // Gerar nome único para o arquivo
            $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();

            // Armazenar arquivo na pasta public/products
            $path = Storage::disk(self::DISK)->putFileAs(
                self::DIRECTORY,
                $file,
                $fileName
            );

            return $path ? basename($path) : null;
        } catch (\Exception $e) {
            Log::error('Erro ao fazer upload de imagem: '.$e->getMessage());

            return null;
        }
    }

    public function deleteProductImage(?string $imagePath): bool
    {
        try {
            if (! $imagePath) {
                return false;
            }

            $fullPath = self::DIRECTORY.'/'.$imagePath;

            if (Storage::disk(self::DISK)->exists($fullPath)) {
                Storage::disk(self::DISK)->delete($fullPath);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Erro ao deletar imagem: '.$e->getMessage());

            return false;
        }
    }

    public function getImageUrl(?string $imagePath): ?string
    {
        if (! $imagePath) {
            return null;
        }

        return asset('storage/'.self::DIRECTORY.'/'.$imagePath);
    }
}
