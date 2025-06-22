<?php

namespace App\Services;

use App\Contracts\Services\ServiceInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseService implements ServiceInterface
{
    /**
     * Execute a database transaction
     */
    public function executeTransaction(callable $callback)
    {
        try {
            DB::beginTransaction();

            $result = $callback();

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(get_class($this).' error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Log service activity
     */
    public function logActivity(string $action, array $data = []): void
    {
        Log::info(get_class($this).': '.$action, $data);
    }

    /**
     * Log service error
     */
    protected function logError(string $message, ?Exception $e = null, array $context = []): void
    {
        $errorContext = array_merge($context, [
            'message' => $message,
            'exception' => $e ? $e->getMessage() : null,
            'trace' => $e ? $e->getTraceAsString() : null,
        ]);

        Log::error(get_class($this).' Error: '.$message, $errorContext);
    }

    /**
     * Format currency
     */
    public function formatCurrency(float $amount): string
    {
        return number_format($amount, 2).' VND';
    }

    /**
     * Format date for display
     */
    public function formatDate(\DateTime $date): string
    {
        return $date->format('d-m-Y');
    }

    /**
     * Handle service errors
     */
    public function handleError(Exception $e, string $context = ''): void
    {
        $this->logError($context ?: 'Service error occurred', $e);
        throw $e;
    }

    /**
     * Format datetime for display
     */
    protected function formatDateTime(\DateTime $dateTime, string $format = 'd-m-Y H:i'): string
    {
        return $dateTime->format($format);
    }
}
