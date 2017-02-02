<?php
namespace Swapi;

/**
 * PHP code template class.
 */
class Template
{
    /**
     * Base template path.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->basePath = __DIR__ . "/Templates/";
    }

    /**
     * Compile template.
     *
     * @param  string $templateName
     * @param  array  $replace
     * @return string
     */
    public function compile($templateName, array $replace)
    {
        $template = file_get_contents($this->basePath . $templateName);

        return str_replace(array_keys($replace), array_values($replace), $template);
    }

    /**
     * Write data to file.
     *
     * @param string $filename
     * @param string $data
     */
    public function dump($filename, $data)
    {
        file_put_contents($filename, $data);
    }
}