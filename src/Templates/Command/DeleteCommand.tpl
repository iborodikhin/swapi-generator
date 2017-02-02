<?php
namespace %command_namespace%;

use %command_namespace%\Abstractions\DeleteCommand;

/**
 * %command_title%
 */
class %command_name% extends DeleteCommand
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