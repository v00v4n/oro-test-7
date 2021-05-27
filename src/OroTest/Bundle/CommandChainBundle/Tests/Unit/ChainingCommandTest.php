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
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ChainingCommandTest
 *
 * @package OroTest\Bundle\CommandChainBundle\Tests\Unit
 * @covers  \OroTest\Bundle\CommandChainBundle\CommandChain\ChainingCommand
 */
class ChainingCommandTest extends TestCase
{
    /**
     * @dataProvider executionDataProvider
     */
    public function testExecute(array $commandsData, array $expectedExecutions, int $expectedResult)
    {
        $executions = [];
        /** @noinspection PhpUnhandledExceptionInspection */
        $result = $this->createAndRunChainingCommand($commandsData, $executions);

        $this->assertSame($expectedResult, $result);
        $this->assertSame($expectedExecutions, $executions);
    }

    public function testExecuteNoMainCommand()
    {
        $this->expectException(\LogicException::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->createAndRunChainingCommand([]);
    }

    public function executionDataProvider(): array
    {
        return [
            'success' => [
                'commandsData' => [
                    ['main:command', Command::SUCCESS],
                    ['member1:command', Command::SUCCESS],
                    ['member2:command', Command::SUCCESS],
                    ['member3:command', Command::SUCCESS],
                ],
                'expectedExecutions' => ['main:command', 'member1:command', 'member2:command', 'member3:command'],
                'expectedResult' => Command::SUCCESS,
            ],
            'failure' => [
                'commandsData' => [
                    ['main:command', Command::SUCCESS],
                    ['member1:command', Command::FAILURE],
                    ['member2:command', Command::SUCCESS],
                    ['member3:command', Command::SUCCESS],
                ],
                'expectedExecutions' => ['main:command', 'member1:command', null, null],
                'expectedResult' => Command::FAILURE,
            ],
        ];
    }

    /**
     * @param array      $commandsData
     * @param array|null $executions
     *
     * @return int
     * @throws \Exception
     */
    public function createAndRunChainingCommand(array $commandsData, array &$executions = null): int
    {
        $commands = [];
        foreach ($commandsData as $i => [$name, $result]) {
            $commands[] = $this->createCommand($name, $result, $executions[$i]);
        }

        $chain = $this->createMock(CommandChain::class);
        $chain->method('getMainCommand')->willReturn($commands[0] ?? null);
        $chain->method('getCommandList')->willReturn($commands);

        $logger = $this->createMock(LoggerInterface::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        return (new ChainingCommand($chain, $logger))->run($input, $output);
    }

    protected function createCommand(string $name, int $result, &$executed): Command
    {
        return new class($name, $result, $executed) extends Command {

            private mixed $executed;
            private int $result;

            public function __construct(string $name, int $result, &$executed)
            {
                parent::__construct($name);
                $this->executed = &$executed;
                $this->result = $result;
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->executed = $this->getName();

                return $this->result;
            }
        };
    }
}
