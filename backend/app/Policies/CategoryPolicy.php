<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Determina se o usuário pode visualizar qualquer modelo.
     */
    public function viewAny(User $user): bool
    {
        return true; // Qualquer um pode listar categorias
    }

    /**
     * Determina se o usuário pode visualizar o modelo.
     */
    public function view(User $user, Category $category): bool
    {
        return true; // Qualquer um pode visualizar uma categoria
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
    public function update(User $user, Category $category): bool
    {
        return $user->isAdmin(); // Apenas admin pode editar
    }

    /**
     * Determina se o usuário pode deletar o modelo.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->isAdmin(); // Apenas admin pode deletar
    }
}
