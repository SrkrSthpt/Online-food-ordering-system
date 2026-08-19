<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base controller with rendering and response helpers.
 */
abstract class Controller
{
    public function view(string $view, array $data = [], ?string $layout = 'storefront'): Response
    {
        $body = View::render($view, $data, $layout);
        return Response::make($body, 200);
    }

    public function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    public function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }

    public function back(): Response
    {
        return Response::redirect(Session::previousUrl(), 302);
    }

    public function redirectToRoute(string $name, array $params = []): Response
    {
        return Response::redirect(route($name, $params), 302);
    }

    public function validate(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            Session::put('_errors', $validator->errors());
            return [];
        }
        return $data;
    }

    public function errors(): array
    {
        $errors = Session::get('_errors', []);
        Session::forget('_errors');
        return $errors;
    }

    protected function abort(int $status, string $message = ''): Response
    {
        http_response_code($status);
        if (request()->wantsJson()) {
            return Response::json(['message' => $message ?: 'Not found'], $status);
        }
        $layout = $status === 404 ? 'storefront' : 'storefront';
        $body = View::render('errors.' . (string)$status, [
            'title' => $status === 404 ? 'Page not found' : 'Something went wrong',
            'message' => $message,
        ], $layout);
        return Response::make($body, $status);
    }
}
