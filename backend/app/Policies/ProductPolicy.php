<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determina se o usuário pode visualizar qualquer modelo.
     */
    public function viewAny(User $user): bool
    {
        return true; // Qualquer um pode listar produtos
    }

    /**
     * Determina se o usuário pode visualizar o modelo.
     */
    public function view(User $user, Product $product): bool
    {
        return true; // Qualquer um pode visualizar um produto
    }

    /**
     * Determina se o usuário pode criar modelos.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin(); // Apenas admin pode criar
    }

    /**
     * Determina se o usuário pode atualizar o modelo.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin(); // Apenas admin pode editar
    }

    /**
     * Determina se o usuário pode deletar o modelo.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin(); // Apenas admin pode deletar
    }

    /**
     * Determina se o usuário pode restaurar o modelo.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->isAdmin(); // Apenas admin pode restaurar (soft delete)
    }

    /**
     * Determina se o usuário pode deletar permanentemente o modelo.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->isAdmin(); // Apenas admin pode deletar permanentemente
    }
}
