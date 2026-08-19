<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Models\Restaurant;
use App\Models\User;

/**
 * Manager/admin dashboard with the same KPIs as the legacy admin/index.php.
 */
final class DashboardController extends Controller
{
    public function index(): Response
    {
        $db = Database::connect();

        $totalRevenue = (float)$db->scalar("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'delivered'");
        $totalOrders = (int)$db->scalar('SELECT COUNT(*) FROM orders');
        $activeOrders = (int)$db->scalar("SELECT COUNT(*) FROM orders WHERE status != 'delivered'");
        $totalUsers = User::count();
        $totalRestaurants = Restaurant::count();

        $recentOrders = $db->query(
            'SELECT o.*, u.name AS user_name, p.status AS payment_status
             FROM orders o
             JOIN users u ON u.id = o.user_id
             LEFT JOIN payments p ON p.order_id = o.id
             ORDER BY o.created_at DESC
             LIMIT 5'
        );

        $dailySales = $db->query(
            "SELECT DATE(created_at) AS date, SUM(total) AS total
             FROM orders
             WHERE status = 'delivered' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY DATE(created_at) ORDER BY date"
        );

        $monthlySales = $db->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total) AS total
             FROM orders
             WHERE status = 'delivered' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month"
        );

        return $this->view('admin.dashboard', [
            'title' => 'Admin Dashboard',
            'user' => auth()->user(),
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'activeOrders' => $activeOrders,
            'totalUsers' => $totalUsers,
            'totalRestaurants' => $totalRestaurants,
            'recentOrders' => $recentOrders,
            'dailySales' => $dailySales,
            'monthlySales' => $monthlySales,
        ], 'admin');
    }
}
