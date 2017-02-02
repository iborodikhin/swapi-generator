<?php
namespace Swapi;

/**
 * Command factory.
 */
class CommandFactory
{
    /**
     * Namespace.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Template object.
     *
     * @var \Swapi\Template
     */
    protected $template;

    /**
     * Constructor.
     *
     * @param $namespace
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
        $this->template  = new Template();
    }

    /**
     * Factory method.
     *
     * @param  string $name
     * @param  string $url
     * @param  string $method
     * @param  string $comment
     * @return string
     */
    public function create($name, $url, $method, $comment)
    {
        $replace = [
            '%command_name%'      => $name,
            '%command_url%'       => $url,
            '%command_title%'     => $comment,
            '%command_namespace%' => $this->namespace,
        ];

        switch (mb_strtoupper($method)) {
            case 'GET':
                return $this->createGet($replace);
                break;
            case 'POST':
                return $this->createPost($replace);
                break;
            case 'PUT':
                return $this->createPut($replace);
                break;
            case 'DELETE':
                return $this->createDelete($replace);
                break;
        }

        throw new \InvalidArgumentException(sprintf('Method %s not supported', mb_strtoupper($method)));
    }

    /**
     * Create GET command.
     *
     * @param  array  $replace
     * @return string
     */
    protected function createGet(array $replace)
    {
        return $this->template->compile('Command/GetCommand.tpl', $replace);
    }

    /**
     * Create POST command.
     *
     * @param  array  $replace
     * @return string
     */
    protected function createPost(array $replace)
    {
        return $this->template->compile('Command/PostCommand.tpl', $replace);
    }

    /**
     * Create PUT command.
     *
     * @param  array  $replace
     * @return string
     */
    protected function createPut(array $replace)
    {
        return $this->template->compile('Command/PutCommand.tpl', $replace);
    }

    /**
     * Create DELETE command.
     *
     * @param  array  $replace
     * @return string
     */
    protected function createDelete(array $replace)
    {
        return $this->template->compile('Command/DeleteCommand.tpl', $replace);
    }
}