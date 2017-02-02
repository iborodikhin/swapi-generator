<?php
namespace Baz;

use Baz\Abstractions\GetCommand;

/**
 * Make bar
 */
class FooGetBar extends GetCommand
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getRequestUri()
    {
        return '/foo/getBar.{_format}';
    }
}