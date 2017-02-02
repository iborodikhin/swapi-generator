<?php
namespace Swapi;

use Swagger\Swagger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Command to generate API client code.
 */
class GenerateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('generate')
            ->setDescription('Generate API client code')
            ->setHelp('Generates API client code based on Swagger annotations')
            ->addOption('source', 's', InputOption::VALUE_REQUIRED, 'Source code directory')
            ->addOption('destination', 'd', InputOption::VALUE_REQUIRED, 'Destination directory')
            ->addOption('namespace', 'p', InputOption::VALUE_REQUIRED, 'API client code namespace');
    }

    /**
     * {@inheritdoc}
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return null|integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commands    = [];
        $destination = $input->getOption('destination');
        $namespace   = $input->getOption('namespace');
        $template    = new Template();
        $swagger     = new Swagger($input->getOption('source'));
        $factory     = new CommandFactory($namespace);

        mkdir($destination . "/src", 0755, true);
        mkdir($destination . "/src/Abstractions", 0755, true);
        mkdir($destination . "/src/Command", 0755, true);
        mkdir($destination . "/src/Exceptions", 0755, true);

        $finder = new Finder();
        $files  = $finder->in(__DIR__ . '/Templates')->exclude('Command')->notName('Api.tpl')->files();

        foreach ($files as $file) {
            $path = $file->getRealPath();
            $name = str_replace(__DIR__ . '/Templates/', '', $path);
            $info = $template->compile($name, ['%api_namespace%' => $namespace]);

            $template->dump($destination . "/src/" . str_replace('.tpl', '.php', $name), $info);
        }

        $resources = $swagger->getResourceList();
        foreach ($resources['apis'] as $resource) {

            $info = $swagger->getResource($resource['path']);
            foreach ($info['apis'] as $cmd) {
                $name    = ucfirst($cmd['operations'][0]['type']) . ucfirst($cmd['operations'][0]['nickname']);
                $command = $factory->create(
                    $name,
                    $info['basePath'] . $cmd['path'],
                    $cmd['operations'][0]['method'],
                    $cmd['operations'][0]['summary']
                );
                $commands[mb_strtolower($name)] = $name;

                $template->dump($destination . "/src/Command/" . $name . ".php", $command);
            }
        }

        $api = $template->compile(
            'Api.tpl',
            [
                '%api_namespace%' => $namespace,
                '%api_commands%' => var_export($commands, true),
            ]
        );
        $template->dump($destination . "/src/Api.php", $api);

        $composer = [
            "name"        => mb_strtolower($namespace) . "/api-client-php",
            "description" => "PHP API client library for " . $namespace,
            "require"     => [
                "php"                => ">=5.4.0",
                "kriswallsmith/buzz" => "*",
            ],
            "autoload"    => [
                "psr-4" => [
                    $namespace . "\\" => "src/",
                ],
            ],
        ];
        $template->dump($destination . "/composer.json", json_encode($composer));
    }
}