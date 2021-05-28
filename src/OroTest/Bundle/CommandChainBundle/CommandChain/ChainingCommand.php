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

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command Proxy that executes all commands in chain instead of only main command.
 *
 * @package OroTest\Bundle\CommandChainBundle\CommandChain
 */
class ChainingCommand extends Command
{
    private CommandChainInterface $commandChain;
    private LoggerInterface $logger;
    private bool $configured = false;

    /**
     * ChainingCommand constructor.
     *
     * @param CommandChainInterface $commandChain
     * @param LoggerInterface       $logger
     */
    public function __construct(CommandChainInterface $commandChain, LoggerInterface $logger)
    {
//        $this->getApplication()
        $mainCommand = $commandChain->getMainCommand();
        if (!$mainCommand) {
            throw new \LogicException('Main command is not set in chain yet');
        }

        parent::__construct($mainCommand->getName());

        $this->commandChain = $commandChain;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureAll();

        $mainCommand = $this->commandChain->getMainCommand();
        $mainCommandName = $mainCommand->getName();

        foreach ($this->commandChain->getCommandList() as $command) {
            if ($command->getName() === $mainCommandName) {
                $this->logger->info("Executing {$mainCommandName} command itself first:");
            } else {
                $this->logger->info("Executing {$mainCommandName} chain members:");
            }

            $bufferOutput = new BufferedOutput;
            $result = $command->execute($input, $bufferOutput);

            $buffer = $bufferOutput->fetch();
            $output->write($buffer);
            $this->logger->info(rtrim($buffer));

            if (Command::SUCCESS != $result) {
                return $result;
            }
        }

        $this->logger->info("Execution of {$mainCommandName} chain completed.");

        return Command::SUCCESS;
    }

    /**
     * Copies arguments and options definitions from every command in chain to $this command
     * to prevent from InvalidArgumentException when argument/option does not exist in $input during execution.
     */
    protected function configureAll()
    {
        if ($this->configured) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        $this->configured = true;

        // todo: reconsider solution

        $definition = $this->getDefinition();
        $commands = $this->commandChain->getCommandList();
        foreach ($commands as $command) {
            $command->configure();
            $originalDefinition = $command->getDefinition();

            $arguments = $originalDefinition->getArguments();
            foreach ($arguments as $argument) {
                // @codeCoverageIgnoreStart
                if (!$definition->hasArgument($argument->getName())) {
                    $definition->addArgument($argument);
                }
                // @codeCoverageIgnoreEnd
            }

            $options = $originalDefinition->getOptions();
            foreach ($options as $option) {
                // @codeCoverageIgnoreStart
                if (!$definition->hasOption($option->getName())) {
                    $definition->addOption($option);
                }
                // @codeCoverageIgnoreEnd
            }
        }
    }
}
