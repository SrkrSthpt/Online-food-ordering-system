<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * A single status change recorded for an order (audit timeline).
 */
final class OrderStatusLog extends BaseModel
{
    protected string $table = 'order_status_logs';
    protected array $fillable = ['order_id', 'status', 'note'];
    protected array $casts = [
        'id' => 'int',
        'order_id' => 'int',
    ];

    public function order(): ?Order
    {
        return Order::find($this->order_id);
    }
}
