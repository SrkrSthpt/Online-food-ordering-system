<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;

/**
 * Sign-in / sign-out.
 */
final class SessionController extends Controller
{
    public function create(): Response
    {
        if (auth()->check()) {
            return $this->redirect($this->homeFor(auth()->user()->roleSlug()));
        }
        return $this->view('auth.login', ['title' => 'Sign in']);
    }

    public function store(): Response
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            Session::put('_errors', $validator->errors());
            return back()->withInput();
        }

        if (
            !auth()->attempt(
                (string)request()->input('email'),
                (string)request()->input('password')
            )
        ) {
            Session::flash('error', 'Invalid email or password.');
            return back()->withInput();
        }

        Session::flash('success', 'Welcome back!');
        return $this->redirect($this->homeFor(auth()->user()->roleSlug()));
    }

    public function destroy(): Response
    {
        auth()->logout();
        Session::flash('success', 'You have been signed out.');
        return $this->redirect('/');
    }

    private function homeFor(string $role): string
    {
        return match ($role) {
            'admin', 'manager' => '/admin',
            default => '/',
        };
    }
}
