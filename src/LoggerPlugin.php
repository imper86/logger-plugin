<?php

namespace Http\Client\Common\Plugin;

use Http\Client\Common\Plugin;
use Http\Client\Common\Plugin\ContextBuilder\ContextBuilderInterface;
use Http\Client\Exception;
use Http\Message\Formatter;
use Http\Message\Formatter\SimpleFormatter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Log request, response and exception for an HTTP Client.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
final class LoggerPlugin implements Plugin
{
    use VersionBridgePlugin;

    private $logger;

    private $formatter;
    /**
     * @var ContextBuilderInterface|null
     */
    private $contextBuilder;

    public function __construct(
        LoggerInterface $logger,
        ?Formatter $formatter = null,
        ?ContextBuilderInterface $contextBuilder = null
    )
    {
        $this->logger = $logger;
        $this->formatter = $formatter ?: new SimpleFormatter();
        $this->contextBuilder = $contextBuilder;
    }

    protected function doHandleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $start = microtime(true);
        $this->logger->info(
            sprintf("Sending request:\n%s", $this->formatter->formatRequest($request)),
            $this->contextBuilder->build($request, null)
        );

        return $next($request)->then(function (ResponseInterface $response) use ($request, $start) {
            $milliseconds = (int) round((microtime(true) - $start) * 1000);
            $this->logger->info(
                sprintf("Received response:\n%s\n\nfor request:\n%s", $this->formatter->formatResponse($response), $this->formatter->formatRequest($request)),
                $this->contextBuilder->build($request, $response, $milliseconds)
            );

            return $response;
        }, function (Exception $exception) use ($request, $start) {
            $milliseconds = (int) round((microtime(true) - $start) * 1000);
            if ($exception instanceof Exception\HttpException) {
                $this->logger->error(
                    sprintf("Error:\n%s\nwith response:\n%s\n\nwhen sending request:\n%s", $exception->getMessage(), $this->formatter->formatResponse($exception->getResponse()), $this->formatter->formatRequest($request)),
                    $this->contextBuilder->build($request, $exception->getResponse(), $milliseconds, $exception)
                );
            } else {
                $this->logger->error(
                    sprintf("Error:\n%s\nwhen sending request:\n%s", $exception->getMessage(), $this->formatter->formatRequest($request)),
                    $this->contextBuilder->build($request, null, $milliseconds, $exception)
                );
            }

            throw $exception;
        });
    }
}
