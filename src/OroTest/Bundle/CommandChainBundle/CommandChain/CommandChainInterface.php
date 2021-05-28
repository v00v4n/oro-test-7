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

/**
 * Describes a Command Chain instance.
 *
 * Allows to add command in chain and to get information about commands.
 *
 * @package OroTest\Bundle\CommandChainBundle\CommandChain
 */
interface CommandChainInterface
{
    /**
     * Adds new command to chain and returns command proxy that
     * MUST be registered back to Application (see {@see Application::add()})
     *
     * @param Command $command Command to be added to chain
     *
     * @return Command Command proxy that MUST be registered back to Application
     */
    public function addCommand(Command $command): Command;

    /**
     * Returns list of all commands in chain.
     *
     * @return Command[]
     */
    public function getCommandList(): array;

    /**
     * Returns main command from chain.
     *
     * @return Command|null
     */
    public function getMainCommand(): ?Command;
}
