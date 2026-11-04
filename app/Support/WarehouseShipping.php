<?php

namespace App\Support;

/**
 * Bulk orders: once a buyer orders more than THRESHOLD pieces of one item,
 * the seller ships it straight from their own warehouse instead of through
 * Cartzy logistics. Keep THRESHOLD in sync with the mobile app.
 */
final class WarehouseShipping
{
    public const THRESHOLD = 100;

    public const NOTE = "Orders of more than 100 pieces ship directly from the seller's warehouse instead of through Cartzy logistics.";

    public static function applies(int $quantity): bool
    {
        return $quantity > self::THRESHOLD;
    }
}
