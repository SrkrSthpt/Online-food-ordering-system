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
 * Manage restaurants (create / edit / delete).
 */
final class RestaurantController extends Controller
{
    public function index(): Response
    {
        $restaurants = Restaurant::all();
        usort($restaurants, fn (Restaurant $a, Restaurant $b) => strcasecmp((string)$a->name, (string)$b->name));

        return $this->view('admin.restaurants', [
            'title' => 'Manage Restaurants',
            'restaurants' => $restaurants,
        ], 'admin');
    }

    public function store(): Response
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required|max:150',
        ]);
        if ($validator->fails()) {
            Session::put('_errors', $validator->errors());
            Session::flash('error', 'Restaurant name is required.');
            return $this->redirect('/admin/restaurants');
        }

        Restaurant::create([
            'name' => (string)request()->input('name'),
            'location' => (string)request()->input('location', ''),
            'image_url' => (string)request()->input('image_url', ''),
        ]);

        Session::flash('success', 'Restaurant added.');
        return $this->redirect('/admin/restaurants');
    }

    public function update(): Response
    {
        $id = (int)request()->input('id');
        $restaurant = Restaurant::find($id);
        if ($restaurant === null) {
            Session::flash('error', 'Restaurant not found.');
            return $this->redirect('/admin/restaurants');
        }

        $validator = Validator::make(request()->all(), [
            'name' => 'required|max:150',
        ]);
        if ($validator->fails()) {
            Session::put('_errors', $validator->errors());
            Session::flash('error', 'Restaurant name is required.');
            return $this->redirect('/admin/restaurants');
        }

        $restaurant->update([
            'name' => (string)request()->input('name'),
            'location' => (string)request()->input('location', ''),
            'image_url' => (string)request()->input('image_url', ''),
        ]);

        Session::flash('success', 'Restaurant updated.');
        return $this->redirect('/admin/restaurants');
    }

    public function destroy(): Response
    {
        $id = (int)request()->input('id');
        $restaurant = Restaurant::find($id);
        if ($restaurant === null) {
            return $this->json(['success' => false, 'message' => 'Restaurant not found']);
        }

        MenuItem::where('restaurant_id', $id) && $this->deleteMenuItems((int)$id);
        $restaurant->delete();

        return $this->json(['success' => true, 'message' => 'Restaurant deleted']);
    }

    private function deleteMenuItems(int $restaurantId): void
    {
        foreach (MenuItem::where('restaurant_id', $restaurantId) as $item) {
            $item->delete();
        }
    }
}
