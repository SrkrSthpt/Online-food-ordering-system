<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * Restaurant shown in the storefront.
 */
final class Restaurant extends BaseModel
{
    protected string $table = 'restaurants';
    protected array $fillable = ['name', 'location', 'rating', 'review_count', 'image_url'];
    protected array $casts = [
        'id' => 'int',
        'rating' => 'float',
        'review_count' => 'int',
    ];

    /**
     * @return MenuItem[]
     */
    public function menuItems(): array
    {
        return MenuItem::where('restaurant_id', $this->id);
    }

    public function itemCount(): int
    {
        return count($this->menuItems());
    }
}
