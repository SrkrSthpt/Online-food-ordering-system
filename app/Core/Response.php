<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP response. Headers are set before the body is sent.
 */
final class Response
{
    private int $status;
    private array $headers;
    private string $body;
    private bool $sent = false;

    public function __construct(string $body = '', int $status = 200, array $headers = [])
    {
        $this->body = $body;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function make(string $body = '', int $status = 200, array $headers = []): self
    {
        return new self($body, $status, $headers);
    }

    public static function json(mixed $data, int $status = 200): self
    {
        $headers = ['Content-Type' => 'application/json; charset=UTF-8'];
        return new self(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $status, $headers);
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self('', $status, ['Location' => $url]);
    }

    public function with(string $type, string $message): self
    {
        Session::flash($type, $message);
        return $this;
    }

    public function withInput(): self
    {
        Session::put('_old_input', request()->all());
        return $this;
    }

    public function status(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function body(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function send(): void
    {
        if ($this->sent) {
            return;
        }
        $this->sent = true;
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value, true);
        }
        echo $this->body;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
