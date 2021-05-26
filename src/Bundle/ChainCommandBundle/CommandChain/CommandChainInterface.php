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

use Symfony\Component\Console\Command\Command;

/**
 * Interface CommandChainInterface
 * @package App\Bundle\ChainCommandBundle\CommandChain
 */
interface CommandChainInterface
{
    /**
     * @param Command $command
     * @return Command
     */
    public function addCommand(Command $command): Command;

    /**
     * @return Command[]
     */
    public function getCommandList(): array;

    /**
     * @return Command|null
     */
    public function getMainCommand(): ?Command;

    public function writeLog(string $message): void;
}
