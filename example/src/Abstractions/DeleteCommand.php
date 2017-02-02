<?php
namespace Baz\Abstractions;

use Buzz\Message\RequestInterface;

/**
 * Base DELETE command class.
 */
abstract class DeleteCommand extends Command
{
    /**
     * Request method.
     *
     * @var string
     */
    protected $requestMethod = RequestInterface::METHOD_DELETE;
}