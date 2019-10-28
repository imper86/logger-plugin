<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 28.10.2019
 * Time: 13:40
 */

namespace Http\Client\Common\Plugin\ContextBuilder;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ContextBuilder implements ContextBuilderInterface
{
    public function build(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?int $milliseconds = null,
        ?Throwable $exception = null
    ): array
    {
        $context = [
            'uri' => $request->getUri()->__toString(),
            'method' => $request->getMethod(),
            'requestHeaders' => $request->getHeaders(),
            'requestBody' => $request->getBody()->__toString(),
        ];

        if ($response) {
            $context = array_merge($context, [
                'statusCode' => $response->getStatusCode(),
                'responseHeaders' => $response->getHeaders(),
                'responseBody' => $response->getBody()->__toString(),
            ]);
        }

        if ($milliseconds) {
            $context['milliseconds'] = $milliseconds;
        }

        if ($exception) {
            $context['exception'] = $exception->__toString();
        }

        return $context;
    }
}
