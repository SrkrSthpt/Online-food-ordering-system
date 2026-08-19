<?php

declare(strict_types=1);

namespace App\Controllers\Api\v1;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

/**
 * Delete restaurant / menu item via AJAX (legacy api/delete-item.php).
 * Manager or admin only; the role is enforced by the 'manager' route group.
 */
final class AdminController extends Controller
{
    public function delete(Request $request): Response
    {
        $id = (int)$request->input('id');
        $type = (string)$request->input('type');

        if ($id <= 0) {
            return $this->json(['success' => false, 'message' => 'Invalid ID']);
        }

        try {
            if ($type === 'restaurant') {
                foreach (\App\Models\MenuItem::where('restaurant_id', $id) as $item) {
                    $item->delete();
                }
                $restaurant = \App\Models\Restaurant::find($id);
                if ($restaurant === null) {
                    return $this->json(['success' => false, 'message' => 'Restaurant not found']);
                }
                $restaurant->delete();
            } elseif ($type === 'menu') {
                $item = \App\Models\MenuItem::find($id);
                if ($item === null) {
                    return $this->json(['success' => false, 'message' => 'Menu item not found']);
                }
                $item->delete();
            } else {
                return $this->json(['success' => false, 'message' => 'Invalid type']);
            }
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()]);
        }

        return $this->json(['success' => true, 'message' => 'Deleted']);
    }
}
