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

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Class CommandChain
 *
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
     *
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
        if (array_key_exists($name, $this->commandList)) {
            throw new \LogicException("Command with name '$name' is already registered in chain");
        }
        $this->commandList[$name] = $command;

        $mainCommand = $this->getMainCommand();
        if (!$mainCommand) {
            return $this->processMainCommand($command);
        }

        return $this->processMemberCommand($command);
    }

    /**
     * @inheritDoc
     */
    public function getCommandList(): array
    {
        return $this->commandList;
    }

    /**
     * @inheritDoc
     */
    public function getMainCommand(): ?Command
    {
        return $this->mainCommand;
    }

    /**
     * Processes main command after registration
     *
     * @param Command $command Main command
     *
     * @return Command Command proxy for main command
     */
    protected function processMainCommand(Command $command): Command
    {
        $this->mainCommand = $command;
        $this->onMainCommandRegistered($command);

        return $this->createMainCommandProxy();
    }

    /**
     * Processes member command after registration
     *
     * @param Command $command Member command
     *
     * @return Command Command proxy for member command
     */
    protected function processMemberCommand(Command $command): Command
    {
        $this->onMemberCommandRegistered($command);

        return $this->createMemberCommandProxy($command);
    }

    /**
     * Main command registration handler
     *
     * @param Command $command
     */
    protected function onMainCommandRegistered(Command $command): void
    {
        $this->getLogger()->info(
            "{$command->getName()} is a master command of a command chain that has registered member commands"
        );
    }

    /**
     * Member command registration handler
     *
     * @param Command $command
     */
    protected function onMemberCommandRegistered(Command $command): void
    {
        $this->getLogger()->info(
            "{$command->getName()} registered as a member of {$this->getMainCommand()->getName()} command chain"
        );
    }

    /**
     * Creates command proxy for member command
     *
     * @return Command
     */
    protected function createMainCommandProxy(): Command
    {
        return new ChainingCommand($this, $this->getLogger());
    }

    /**
     * Creates command proxy for member command
     *
     * @param Command $command
     *
     * @return Command
     */
    protected function createMemberCommandProxy(Command $command): Command
    {
        return new UnavailableCommand($command, $this);
    }

    /**
     * Returns logger
     *
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
