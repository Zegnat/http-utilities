<?php

declare(strict_types=1);

namespace Zegnat\Http;

use Psr\Http\Message\ResponseInterface;

final class ResponseEmitter
{
    public function emit(ResponseInterface $response)
    {
        $status = [
            'HTTP/' . $response->getProtocolVersion(),
            $response->getStatusCode()
        ];
        if (strlen($response->getReasonPhrase()) > 0) {
            $status[] = $response->getReasonPhrase();
        }
        header(implode(' ', $status));
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }
        echo $response->getBody();
    }
}
