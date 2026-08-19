<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\MenuItem;
use App\Models\Restaurant;

/**
 * Manage menu items (create / edit / delete).
 */
final class MenuItemController extends Controller
{
    public function index(): Response
    {
        $items = [];
        foreach (MenuItem::all() as $item) {
            $item->restaurant_name = (string)($item->restaurant()?->name ?? '');
            $items[] = $item;
        }
        usort($items, function (MenuItem $a, MenuItem $b) {
            return strcasecmp((string)$a->restaurant_name, (string)$b->restaurant_name)
                ?: strcasecmp((string)$a->name, (string)$b->name);
        });

        $restaurants = Restaurant::all();
        usort($restaurants, fn (Restaurant $a, Restaurant $b) => strcasecmp((string)$a->name, (string)$b->name));

        return $this->view('admin.menu-items', [
            'title' => 'Manage Menu Items',
            'items' => $items,
            'restaurants' => $restaurants,
        ], 'admin');
    }

    public function store(): Response
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required|max:150',
            'restaurant_id' => 'required|exists:restaurants,id',
            'price' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            Session::put('_errors', $validator->errors());
            Session::flash('error', 'Item name, restaurant and a valid price are required.');
            return $this->redirect('/admin/menu-items');
        }

        MenuItem::create([
            'restaurant_id' => (int)request()->input('restaurant_id'),
            'name' => (string)request()->input('name'),
            'description' => (string)request()->input('description', ''),
            'price' => (float)request()->input('price'),
            'rating' => $this->rating(),
        ]);

        Session::flash('success', 'Menu item added.');
        return $this->redirect('/admin/menu-items');
    }

    public function update(): Response
    {
        $id = (int)request()->input('id');
        $item = MenuItem::find($id);
        if ($item === null) {
            Session::flash('error', 'Menu item not found.');
            return $this->redirect('/admin/menu-items');
        }

        $validator = Validator::make(request()->all(), [
            'name' => 'required|max:150',
            'restaurant_id' => 'required|exists:restaurants,id',
            'price' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            Session::put('_errors', $validator->errors());
            Session::flash('error', 'Item name, restaurant and a valid price are required.');
            return $this->redirect('/admin/menu-items');
        }

        $item->update([
            'restaurant_id' => (int)request()->input('restaurant_id'),
            'name' => (string)request()->input('name'),
            'description' => (string)request()->input('description', ''),
            'price' => (float)request()->input('price'),
            'rating' => $this->rating(),
        ]);

        Session::flash('success', 'Menu item updated.');
        return $this->redirect('/admin/menu-items');
    }

    public function destroy(): Response
    {
        $id = (int)request()->input('id');
        $item = MenuItem::find($id);
        if ($item === null) {
            return $this->json(['success' => false, 'message' => 'Menu item not found']);
        }
        $item->delete();

        return $this->json(['success' => true, 'message' => 'Menu item deleted']);
    }

    private function rating(): float
    {
        $rating = (float)request()->input('rating', 4.5);
        return round(min(5, max(0, $rating)), 1);
    }
}
