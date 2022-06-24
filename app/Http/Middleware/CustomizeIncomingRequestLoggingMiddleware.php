<?php

namespace App\Http\Middleware;

use Closure;
use Garbetjie\RequestLogging\Http\Middleware\Middleware;
use Garbetjie\RequestLogging\Http\RequestEntry;
use Garbetjie\RequestLogging\Http\ResponseEntry;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class CustomizeIncomingRequestLoggingMiddleware extends Middleware implements MiddlewareInterface
{
    /**
     * PSR-compliant middleware handler.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $entry = $this->logger->request($request, $this->logger::DIRECTION_IN);
        $response = $handler->handle($request);
        $this->logger->response($entry, $response);
        return $response;
    }

    /**
     * Laravel middleware handler.
     *
     * @param SymfonyRequest $request
     * @param Closure $next
     * @return SymfonyResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        $entry = $this->logger->request($request, $this->logger::DIRECTION_IN);
        $response = $next($request);

        $this->logger->context(
            function (RequestEntry $entry): array {
                // Return an array containing the context to log.
                return ['hello' => 2];
            },
            function (ResponseEntry $entry): array {
                // Return an array containing the context to log.
                return [
                    'url' => $entry->request()->url(),
                    'status' => $entry->response()->status(),
                    'body' => $entry->response()->getContent(),
                ];
            }
        );

        $this->logger->response($entry, $response);
        return $response;
    }
}
