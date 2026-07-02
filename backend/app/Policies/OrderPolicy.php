<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determina se o usuário pode visualizar qualquer modelo.
     */
    public function viewAny(User $user): bool
    {
        return true; // Usuários autenticados podem listar seus próprios pedidos
    }

    /**
     * Determina se o usuário pode visualizar o modelo.
     */
    public function view(User $user, Order $order): bool
    {
        // Usuário pode ver seu próprio pedido ou admin pode ver qualquer pedido
        return $user->id === $order->user_id || $user->isAdmin();
    }

    /**
     * Determina se o usuário pode criar modelos.
     */
    public function create(User $user): bool
    {
        return true; // Qualquer usuário autenticado pode criar pedidos
    }

    /**
     * Determina se o usuário pode atualizar o modelo.
     */
    public function update(User $user, Order $order): bool
    {
        // Apenas admin pode atualizar pedidos
        return $user->isAdmin();
    }

    /**
     * Determina se o usuário pode deletar o modelo.
     */
    public function delete(User $user, Order $order): bool
    {
        // Apenas admin pode deletar pedidos
        return $user->isAdmin();
    }

    /**
     * Determina se o usuário pode atualizar o status do modelo.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        // Apenas admin pode atualizar status de pedidos
        return $user->isAdmin();
    }
}
