<?php

declare(strict_types=1);

namespace Zegnat\Http;

use Interop\Http\Factory\ServerRequestFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

final class ServerRequestFromGlobals
{
    private $requestFactory = null;
    private $streamFactory = null;

    public function __construct(
        ServerRequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    public function create(): ServerRequestInterface
    {
        $serverRequest = $this->requestFactory
            ->createServerRequestFromArray($_SERVER)
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET);
        if (isset($_SERVER['SERVER_PROTOCOL'])
            && preg_match('@HTTP/(\d+(?:\.\d+)?)@A', $_SERVER['SERVER_PROTOCOL'], $protocol) === 1
        ) {
            $serverRequest = $serverRequest->withProtocolVersion($protocol[1]);
        }
        $headers = getallheaders();
        if (false !== $headers) {
            foreach ($headers as $name => $value) {
                $serverRequest = $serverRequest->withHeader($name, $value);
            }
        }
        if ($serverRequest->getMethod() === 'POST'
            && in_array(
                $serverRequest->getHeaderLine('Content-Type'),
                ['application/x-www-form-urlencoded', 'multipart/form-data']
            )
        ) {
            $serverRequest = $serverRequest->withParsedBody($_POST);
        }
        $body = $this->streamFactory->createStreamFromFile('php://input', 'rb');
        $serverRequest = $serverRequest->withBody($body);
        return $serverRequest;
    }
}
