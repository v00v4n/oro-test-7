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

namespace OroTest\Bundle\CommandChainBundle\Tests\Functional\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MemberCommand
 *
 * @package OroTest\Bundle\CommandChainBundle\Tests\Functional\Command
 */
class MemberCommand extends Command
{
    protected static $defaultName = 'member:command';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Member command executed!');

        return Command::SUCCESS;
    }
}
