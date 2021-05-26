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

use JetBrains\PhpStorm\Pure;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Class CommandChain
 * @package App\Bundle\ChainCommandBundle\Service
 */
class CommandChain implements CommandChainInterface
{
    private LoggerInterface $logger;

    /**
     * @var Command[]
     */
    private array $commandList = [];

    private ?Command $mainCommand = null;

    /**
     * CommandChain constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function addCommand(Command $command): Command
    {
        $name = $command->getName();
        if ($this->hasCommand($name)) {
            throw new \LogicException("Command with name '$name' is already registered in chain");
        }
        $this->commandList[$name] = $command;

        $mainCommand = $this->mainCommand;
        if (!$mainCommand) {
            return $this->processMainCommand($command);
        }

        return $this->processSecondaryCommand($command);
    }

    #[Pure] public function hasCommand(Command|string $command): bool
    {
        if ($command instanceof Command) {
            $command = $command->getName();
        }

        return isset($this->commandList[$command]);
    }

    protected function processMainCommand(Command $command): Command
    {
        $this->mainCommand = $command;

        $this->writeLog(
            "{$command->getName()} is a master command of a command chain that has registered member commands"
        );

        return new ChainingCommand($this);
    }

    public function writeLog(string $message): void
    {
        $this->logger->info($message);
    }

    protected function processSecondaryCommand(Command $command): Command
    {
        $this->writeLog(
            "{$command->getName()} registered as a member of {$this->mainCommand->getName()} command chain"
        );

        return new UnavailableCommand($command, $this);
    }

    public function getMainCommand(): ?Command
    {
        return $this->mainCommand;
    }

    /**
     * @inheritDoc
     */
    public function getCommandList(): array
    {
        return $this->commandList;
    }
}
