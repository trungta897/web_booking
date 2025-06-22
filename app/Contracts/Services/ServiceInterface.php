<?php

namespace App\Contracts\Services;

interface ServiceInterface
{
    /**
     * Execute operation within transaction
     */
    public function executeTransaction(callable $callback);

    /**
     * Log activity
     */
    public function logActivity(string $action, array $data = []): void;

    /**
     * Format currency for display
     */
    public function formatCurrency(float $amount): string;

    /**
     * Format date for display
     */
    public function formatDate(\DateTime $date): string;

    /**
     * Handle service errors
     */
    public function handleError(\Exception $e, string $context = ''): void;
}
