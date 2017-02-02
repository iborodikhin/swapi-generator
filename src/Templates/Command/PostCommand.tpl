<?php
namespace %command_namespace%;

use %command_namespace%\Abstractions\PostCommand;

/**
 * %command_title%
 */
class %command_name% extends PostCommand
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