<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * Line item within an order. This table has no created timestamp columns.
 */
final class OrderItem extends BaseModel
{
    protected string $table = 'order_items';
    protected bool $timestamps = false;
    protected array $fillable = ['order_id', 'menu_item_id', 'quantity', 'price'];
    protected array $casts = [
        'id' => 'int',
        'order_id' => 'int',
        'menu_item_id' => 'int',
        'quantity' => 'int',
        'price' => 'float',
    ];

    public function menuItem(): ?MenuItem
    {
        return MenuItem::find($this->menu_item_id);
    }
}
