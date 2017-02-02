<?php
namespace %api_namespace%\Abstractions;

use Buzz\Message\RequestInterface;

/**
 * Base PUT command class.
 */
abstract class PutCommand extends Command
{
    /**
     * Request method.
     *
     * @var string
     */
    protected $requestMethod = RequestInterface::METHOD_PUT;
}