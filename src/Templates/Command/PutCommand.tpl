<?php
namespace %command_namespace%;

use %command_namespace%\Abstractions\PutCommand;

/**
 * %command_title%
 */
class %command_name% extends PutCommand
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getRequestUri()
    {
        return '%command_url%';
    }
}