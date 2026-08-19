<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Restaurant;
use App\Models\User;

/**
 * Manage system users and their roles. Admins create couriers and link
 * managers to the restaurant they run.
 */
final class UserController extends Controller
{
    public function index(): Response
    {
        return $this->view('admin.users', [
            'title' => 'Manage Users',
            'users' => User::all(),
            'restaurants' => Restaurant::all(),
        ], 'admin');
    }

    public function store(): Response
    {
        $role = (string)request()->input('role', 'customer');

        $validator = Validator::make(request()->all(), [
            'name' => 'required|max:100',
            'email' => 'required|email|max:190|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:customer,manager,delivery,admin',
        ]);
        if ($validator->fails()) {
            Session::flash('error', implode(' ', $validator->allErrors()));
            return $this->redirect('/admin/users');
        }

        User::create([
            'name' => (string)request()->input('name'),
            'email' => (string)request()->input('email'),
            'password' => password_hash((string)request()->input('password'), PASSWORD_ARGON2ID),
            'role' => $role,
            'restaurant_id' => $role === 'manager' ? (int)request()->input('restaurant_id', 0) ?: null : null,
        ]);

        Session::flash('success', 'User created.');
        return $this->redirect('/admin/users');
    }

    public function update(): Response
    {
        $user = User::find((int)request()->input('id'));
        if ($user === null) {
            Session::flash('error', 'User not found.');
            return $this->redirect('/admin/users');
        }

        $role = (string)request()->input('role', (string)$user->role);
        if (in_array($role, ['customer', 'manager', 'delivery', 'admin'], true)) {
            $user->update([
                'role' => $role,
                'restaurant_id' => $role === 'manager' ? (int)request()->input('restaurant_id', 0) ?: null : null,
                'is_active' => (int)(bool)request()->input('is_active', 1),
            ]);
            Session::flash('success', 'User updated.');
        } else {
            Session::flash('error', 'Invalid role.');
        }

        return $this->redirect('/admin/users');
    }
}
