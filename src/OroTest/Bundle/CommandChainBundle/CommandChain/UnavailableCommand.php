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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command Proxy which is used to replace chain member command. Always returns error.
 *
 * @package OroTest\Bundle\CommandChainBundle\CommandChain
 */
class UnavailableCommand extends Command
{
    private Command $command;

    private CommandChainInterface $commandChain;

    /**
     * UnavailableCommand constructor.
     *
     * @param Command               $command
     * @param CommandChainInterface $commandChain
     */
    public function __construct(Command $command, CommandChainInterface $commandChain)
    {
        parent::__construct($command->getName());
        $this->command = $command;
        $this->commandChain = $commandChain;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $errMessage = "Error: {$this->command->getName()} command is a member"
                      . " of {$this->commandChain->getMainCommand()->getName()} command chain"
                      . " and cannot be executed on its own.";

        $errOutput->writeln($errMessage);

        return Command::FAILURE;
    }
}
