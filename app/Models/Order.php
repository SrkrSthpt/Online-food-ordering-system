<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * An order placed by a customer.
 */
final class Order extends BaseModel
{
    protected string $table = 'orders';
    protected array $fillable = ['user_id', 'restaurant_id', 'delivery_person_id', 'status', 'total', 'eta'];
    protected array $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'restaurant_id' => 'int',
        'delivery_person_id' => 'int',
        'total' => 'float',
    ];

    /**
     * Full delivery pipeline. Each step is driven by a specific role:
     * pending (customer places) -> accepted/preparing/prepared (hotel) ->
     * out_for_delivery/delivered (courier).
     */
    public const STATUSES = [
        'pending',
        'accepted',
        'preparing',
        'prepared',
        'out_for_delivery',
        'delivered',
    ];

    public function statusIndex(): int
    {
        $index = array_search((string)$this->status, self::STATUSES, true);
        return $index === false ? 0 : $index;
    }

    public function user(): ?User
    {
        return User::find($this->user_id);
    }

    public function restaurant(): ?Restaurant
    {
        return $this->restaurant_id !== null ? Restaurant::find($this->restaurant_id) : null;
    }

    public function deliveryPerson(): ?User
    {
        return $this->delivery_person_id !== null ? User::find($this->delivery_person_id) : null;
    }

    /**
     * @return OrderItem[]
     */
    public function items(): array
    {
        return OrderItem::where('order_id', $this->id);
    }

    public function payment(): ?Payment
    {
        return Payment::firstWhere('order_id', $this->id);
    }

    /**
     * @return OrderStatusLog[]
     */
    public function statusLogs(): array
    {
        return OrderStatusLog::where('order_id', $this->id);
    }
}
