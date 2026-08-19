<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * A single sellable dish.
 */
final class MenuItem extends BaseModel
{
    protected string $table = 'menu_items';
    protected array $fillable = ['restaurant_id', 'name', 'description', 'price', 'image_url', 'rating', 'review_count'];
    protected array $casts = [
        'id' => 'int',
        'restaurant_id' => 'int',
        'price' => 'float',
        'rating' => 'float',
        'review_count' => 'int',
    ];

    public function restaurant(): ?Restaurant
    {
        return Restaurant::find($this->restaurant_id);
    }
}
