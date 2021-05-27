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

use OroTest\Bundle\CommandChainBundle\CommandChain\ChainingCommand;
use OroTest\Bundle\CommandChainBundle\CommandChain\CommandChain;
use OroTest\Bundle\CommandChainBundle\CommandChain\UnavailableCommand;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Class CommandChainTest
 *
 * @package OroTest\Bundle\CommandChainBundle\Tests\Unit
 *
 * @covers \OroTest\Bundle\CommandChainBundle\CommandChain\CommandChain
 * @covers \OroTest\Bundle\CommandChainBundle\CommandChain\ChainingCommand::__construct
 * @covers \OroTest\Bundle\CommandChainBundle\CommandChain\UnavailableCommand::__construct
 */
class CommandChainTest extends TestCase
{
    private CommandChain $commandChain;

    public function setUp(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $this->commandChain = new CommandChain($logger);
    }

    public function testAddCommand()
    {
        /** @var Command[] $commands */
        $commands = [
            $mainCommand = new Command('main:command'),
            new Command('member1:command'),
            new Command('member2:command'),
        ];

        foreach ($commands as $i => $command) {
            $proxy = $this->commandChain->addCommand($command);
            if ($i == 0) {
                $this->assertInstanceOf(ChainingCommand::class, $proxy, "proxy for {$command->getName()}");
            } else {
                $this->assertInstanceOf(UnavailableCommand::class, $proxy, "proxy for {$command->getName()}");
            }
        }

        $chainCommands = $this->commandChain->getCommandList();
        foreach ($commands as $i => $command) {
            $this->assertSame($command, $chainCommands[$command->getName()], $command->getName());
        };

        $this->assertSame($mainCommand, $this->commandChain->getMainCommand());
    }


    public function testAddCommandException()
    {
        $this->expectException(\LogicException::class);
        $this->commandChain->addCommand(new Command('duplicated:command'));
        $this->commandChain->addCommand(new Command('duplicated:command'));
    }
}
