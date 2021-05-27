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
namespace OroTest\Bundle\CommandChainBundle\Tests\Unit;

use OroTest\Bundle\CommandChainBundle\CommandChain\CommandChain;
use OroTest\Bundle\CommandChainBundle\CommandChain\UnavailableCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class UnavailableCommandTest
 *
 * @package OroTest\Bundle\CommandChainBundle\Tests\Unit
 * @covers \OroTest\Bundle\CommandChainBundle\CommandChain\UnavailableCommand
 */
class UnavailableCommandTest extends TestCase
{
    public function testExecute()
    {
        $command = new Command('member:command');
        $mainCommand = new Command('main:command');

        $chain = $this->createMock(CommandChain::class);
        $chain->method('getMainCommand')->willReturn($mainCommand);

        $input = $this->createMock(InputInterface::class);
        $output = new BufferedOutput();
        /** @noinspection PhpUnhandledExceptionInspection */
        $resultCode = (new UnavailableCommand($command, $chain))->run($input, $output);
        $this->assertEquals(Command::FAILURE, $resultCode);

        $actual = $output->fetch();

        $expected = "Error: {$command->getName()} command is a member"
                    . " of {$mainCommand->getName()} command chain"
                    . " and cannot be executed on its own." . \PHP_EOL;

        $this->assertSame($expected, $actual);
    }
}
