<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

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

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $configRelPath = '../../config/';
        $configFullPath = realpath(__DIR__ . '/' . $configRelPath) . '/';

        $routes->import($configRelPath . '{routes}/' . $this->environment . '/*.yaml');
        $routes->import($configRelPath . '{routes}/*.yaml');

        if (is_file($configFullPath . 'routes.yaml')) {
            $routes->import($configRelPath . '{routes.yaml');
        } elseif (is_file($path = $configFullPath . 'routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
    }
}
