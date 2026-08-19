<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * Payment record for an order (paypal / stripe / cod).
 */
final class Payment extends BaseModel
{
    protected string $table = 'payments';
    protected array $fillable = ['order_id', 'amount', 'method', 'status', 'transaction_id'];
    protected array $casts = [
        'id' => 'int',
        'order_id' => 'int',
        'amount' => 'float',
    ];

    public function order(): ?Order
    {
        return Order::find($this->order_id);
    }
}
