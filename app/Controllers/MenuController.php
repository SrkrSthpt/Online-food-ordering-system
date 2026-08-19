<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\MenuItem;
use App\Models\Restaurant;

/**
 * Menu browsing: a single restaurant's menu, or every dish across the site.
 */
final class MenuController extends Controller
{
    public function index(Request $request): Response
    {
        $restaurantId = (int)$request->input('restaurant', 0);
        $restaurant = $restaurantId > 0 ? Restaurant::find($restaurantId) : null;

        if ($restaurant !== null) {
            $items = MenuItem::where('restaurant_id', $restaurant->id);
            usort($items, fn (MenuItem $a, MenuItem $b) => strcasecmp((string)$a->name, (string)$b->name));
        } else {
            $items = [];
            foreach (
                Database::connect()->query(
                    'SELECT mi.id FROM menu_items mi JOIN restaurants r ON mi.restaurant_id = r.id ORDER BY r.name, mi.name'
                ) as $row
            ) {
                $model = MenuItem::find((int)$row['id']);
                if ($model !== null) {
                    $items[] = $model;
                }
            }
        }

        $restaurants = Restaurant::all();
        usort($restaurants, fn (Restaurant $a, Restaurant $b) => strcasecmp((string)$a->name, (string)$b->name));

        return $this->view('menu.index', [
            'restaurant' => $restaurant,
            'items' => $items,
            'restaurants' => $restaurants,
        ]);
    }
}
