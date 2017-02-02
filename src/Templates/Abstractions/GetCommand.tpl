<?php
namespace %api_namespace%\Abstractions;

use Buzz\Message\RequestInterface;

/**
 * Base GET command class.
 */
abstract class GetCommand extends Command
{
    /**
     * Request method.
     *
     * @var string
     */
    protected $requestMethod = RequestInterface::METHOD_GET;
}