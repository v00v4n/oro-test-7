<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $configRelPath = '../../config/';
        $configFullPath = realpath(__DIR__ . '/' . $configRelPath) . '/';

        $container->import($configRelPath . '{packages}/*.yaml');
        $container->import($configRelPath . '{packages}/' . $this->environment . '/*.yaml');

        if (is_file($configFullPath . 'services.yaml')) {
            $container->import($configRelPath . 'services.yaml');
            $container->import($configRelPath . '{services}_' . $this->environment . '.yaml');
        } elseif (is_file($path = $configFullPath . 'services.php')) {
            /** @noinspection PhpIncludeInspection */
            (require $path)($container->withPath($path), $this);
        }
    }
}
