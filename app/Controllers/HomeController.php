<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\MenuItem;
use App\Models\Restaurant;

/**
 * Storefront home page.
 */
final class HomeController extends Controller
{
    public function index(): \App\Core\Response
    {
        $featured = Restaurant::all();
        usort($featured, fn (Restaurant $a, Restaurant $b) => $b->id <=> $a->id);
        $featured = array_slice($featured, 0, 6);

        $counts = [];
        foreach ($featured as $restaurant) {
            $counts[$restaurant->id] = $restaurant->itemCount();
        }

        return $this->view('home', [
            'title' => config('app.name', 'Bitezy'),
            'restaurants' => $featured,
            'counts' => $counts,
        ]);
    }
}
