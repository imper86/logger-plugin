<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 28.10.2019
 * Time: 13:36
 */

namespace Http\Client\Common\Plugin\ContextBuilder;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

interface ContextBuilderInterface
{
    public function build(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?int $milliseconds = null,
        ?Throwable $exception = null
    ): array;
}
