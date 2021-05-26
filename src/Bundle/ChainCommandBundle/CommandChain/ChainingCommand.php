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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ChainingCommand
 * @package App\Bundle\ChainCommandBundle\CommandChain
 */
class ChainingCommand extends Command
{
    private CommandChainInterface $commandChain;
    private bool $configured = false;

    public function __construct(CommandChainInterface $commandChain)
    {
//        $this->getApplication()
        $mainCommand = $commandChain->getMainCommand();
        if (!$mainCommand) {
            throw new \LogicException('Main command is not set in chain yet');
        }
        $this->commandChain = $commandChain;

        parent::__construct($mainCommand->getName());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureAll();

        $mainCommand = $this->commandChain->getMainCommand();
        foreach ($this->commandChain->getCommandList() as $command) {
            if ($command === $mainCommand) {
                $this->commandChain->writeLog("Executing {$mainCommand->getName()} command itself first:");
            } else {
                $this->commandChain->writeLog("Executing {$mainCommand->getName()} chain members:");
            }

            $bufferOutput = new BufferedOutput();
            $result = $command->execute($input, $bufferOutput);

            $buffer = $bufferOutput->fetch();
            $output->write($buffer);
            $this->commandChain->writeLog(rtrim($buffer));

            if (Command::SUCCESS != $result) {
                return $result;
            }
        }

        $this->commandChain->writeLog("Execution of {$mainCommand->getName()} chain completed.");

        return Command::SUCCESS;
    }

    protected function configureAll()
    {
        if ($this->configured) {
            return;
        }
        $this->configured = true;

        // todo: reconsider solution

        $definition = $this->getDefinition();
        $commands = $this->commandChain->getCommandList();
        foreach ($commands as $command) {
            $command->configure();
            $originalDefinition = $command->getDefinition();
            $arguments = $originalDefinition->getArguments();
            $definition->addArguments($arguments);
            $options = $originalDefinition->getOptions();
            $definition->addOptions($options);
        }
    }
}
