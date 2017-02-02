<?php
namespace %api_namespace%\Abstractions;

use Buzz\Message\RequestInterface;

/**
 * Base POST command class.
 */
abstract class PostCommand extends Command
{
    /**
     * Request method.
     *
     * @var string
     */
    protected $requestMethod = RequestInterface::METHOD_POST;
}