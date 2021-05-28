<?php
declare(strict_types=1);

/*
 * This file is part of the V00V4N OroTestTask7 Project.
 *
 * (c) Volodymyr Sarnytskyi <v00v4n@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace OroTest\Bundle\CommandChainBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class OroTestCommandChainExtension
 *
 * @package OroTest\Bundle\CommandChainBundle\DependencyInjection
 */
class OroTestCommandChainExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        /** @noinspection PhpUnhandledExceptionInspection */
        $loader->load('services.yaml');
        if ('test' === $container->getParameter('kernel.environment')) {
            $loader->load('services_test.yaml');
        }
    }
}
