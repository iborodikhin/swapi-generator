<?php
namespace %command_namespace%;

use %command_namespace%\Abstractions\GetCommand;

/**
 * %command_title%
 */
class %command_name% extends GetCommand
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