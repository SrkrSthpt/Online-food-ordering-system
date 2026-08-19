<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

/**
 * Account registration (always a customer).
 */
final class RegisterController extends Controller
{
    public function create(): Response
    {
        return $this->view('auth.register', ['title' => 'Create account']);
    }

    public function store(): Response
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required|max:100',
            'email' => 'required|email|max:190|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);
        if ($validator->fails()) {
            Session::put('_errors', $validator->errors());
            return back()->withInput();
        }

        $user = User::create([
            'name' => (string)request()->input('name'),
            'email' => (string)request()->input('email'),
            'password' => password_hash((string)request()->input('password'), PASSWORD_ARGON2ID),
            'role' => 'customer',
        ]);

        auth()->login($user);
        Session::flash('success', 'Account created. Welcome to Bitezy!');
        return $this->redirect('/');
    }
}
