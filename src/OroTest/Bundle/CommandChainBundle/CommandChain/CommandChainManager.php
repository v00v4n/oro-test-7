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

namespace OroTest\Bundle\CommandChainBundle\CommandChain;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides tools for adding Command Chains to Application.
 *
 * @package OroTest\Bundle\CommandChainBundle\CommandChain
 */
class CommandChainManager
{
    private Application $application;

    /**
     * @var string[]
     */
    private array $allCommandsNames = [];

    /**
     * CommandChainManager constructor.
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Registers Command Chain to Application
     */
    public function registerApplicationChains()
    {
        $commandChainList = $this->getContainer()->getParameter('orotest.command_chain.chains_list');
        if (!$commandChainList) {
            return;
        }

        foreach ($commandChainList as $names) {
            $newCommands = $this->registerChain(...$names);
            $this->application->addCommands($newCommands);
        }
    }

    /**
     * Creates new Command Chain and registers $commands to it.
     *
     * Returns list of command proxies that MUST be added back to Application (see {@see Application::addCommands()}).
     *
     * @param Command|string ...$commands Commands to be added to chain
     *
     * @return Command[] Commands proxies
     */
    public function registerChain(Command|string ...$commands): array
    {
        $chain = $this->createCommandChain();
        $resultCommands = [];
        foreach ($commands as $command) {
            if (!$command instanceof Command) {
                $command = $this->application->find($command);
            }

            $name = $command->getName();
            if (in_array($name, $this->allCommandsNames)) {
                throw new \LogicException("Command {$name} already exists in this or other command chain");
            }
            $this->allCommandsNames[] = $name;
            $resultCommands[] = $chain->addCommand($command);
        }

        return $resultCommands;
    }

    /**
     * Returns container from Application Kernel.
     *
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->application->getKernel()->getContainer();
    }

    /**
     * Creates new instance of CommandChainInterface.
     *
     * @return CommandChainInterface
     */
    protected function createCommandChain(): CommandChainInterface
    {
        $chain = $this->getContainer()->get('orotest.command_chain'); // non shared service
        assert($chain instanceof CommandChainInterface);

        return $chain;
    }

    /**
     * Application integration shortcut
     *
     * @param Application $application
     */
    public static function registerCommands(Application $application): void
    {
        static $applicationMap;
        if (!$applicationMap) {
            $applicationMap = new \WeakMap;
        }

        if (!empty($applicationMap[$application])) {
            // prevent repeated call
            return;
        }
        $applicationMap[$application] = true;

        (new static($application))->registerApplicationChains();
    }
}
