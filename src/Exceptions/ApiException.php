<?php

declare(strict_types=1);

namespace Bagdock\Exceptions;

use Psr\Http\Message\ResponseInterface;

class ApiException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        public readonly string $errorCode = 'unknown_error',
        public readonly ?string $requestId = null,
    ) {
        parent::__construct($message, $statusCode);
    }

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody()->getContents(), true) ?? [];

        return new self(
            message: $body['message'] ?? "Request failed with status {$response->getStatusCode()}",
            statusCode: $response->getStatusCode(),
            errorCode: $body['code'] ?? 'unknown_error',
            requestId: $body['request_id'] ?? $response->getHeaderLine('x-request-id') ?: null,
        );
    }
}
