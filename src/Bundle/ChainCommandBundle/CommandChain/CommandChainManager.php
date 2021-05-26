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

namespace App\Bundle\ChainCommandBundle\CommandChain;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CommandChainManager
 * @package App\Bundle\ChainCommandBundle\CommandChain
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
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function registerApplicationChains()
    {
        $commandChainList = $this->getContainer()->getParameter('app.command_chain.chains_list');
        if (!$commandChainList) {
            return;
        }

        foreach ($commandChainList as $names) {
            $newCommands = $this->registerChain(...$names);
            $this->application->addCommands($newCommands);
        }
    }

    /**
     * @param Command|string ...$commands
     * @return Command[]
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
                throw new \LogicException("Command {$name} already exists in some command chain");
            }
            $this->allCommandsNames[] = $name;
            $resultCommands[] = $chain->addCommand($command);
        }

        return $resultCommands;
    }


    protected function getContainer(): ContainerInterface
    {
        return $this->application->getKernel()->getContainer();
    }

    protected function createCommandChain(): CommandChain
    {
        $chain = $this->getContainer()->get('app.command_chain'); // non shared service
        assert($chain instanceof CommandChain);

        return $chain;
    }
}
