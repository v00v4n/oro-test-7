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

use App\Kernel;
use OroTest\Bundle\CommandChainBundle\CommandChain\CommandChain;
use OroTest\Bundle\CommandChainBundle\CommandChain\CommandChainInterface;
use OroTest\Bundle\CommandChainBundle\CommandChain\CommandChainManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CommandChainManagerTest
 *
 * @package OroTest\Bundle\CommandChainBundle\Tests\Unit
 * @covers  \OroTest\Bundle\CommandChainBundle\CommandChain\CommandChainManager
 */
class CommandChainManagerTest extends TestCase
{
    public function testRegisterApplicationChains()
    {
        /** @var CommandChain[] $chains */
        $chainsList = [
            ['foo:hello', 'bar:hi'],
            ['main:command', 'member1:command', 'member2:command'],
        ];
        $registeredChainsList = $this->registerApplicationChains($chainsList);

        $this->assertSame($chainsList, $registeredChainsList);
    }

    public function testRegisterApplicationEmptyChains()
    {
        /** @var CommandChain[] $chains */
        $chainsList = [];
        $registeredChainsList = $this->registerApplicationChains($chainsList);

        $this->assertSame($chainsList, $registeredChainsList);
    }

    public function testRegisterApplicationDuplicatedCommand()
    {
        $this->expectException(\LogicException::class);

        $chainsList = [
            ['foo:hello', 'bar:hi'],
            ['main:command', 'bar:hi'],
        ];
        $this->registerApplicationChains($chainsList);
    }

    public function testRegisterCommands()
    {
        $application = $this->buildApplication();

        $callsCount = &CommandChainManagerStaticMock::$callsCount;

        $this->assertSame(0, $callsCount);
        CommandChainManagerStaticMock::registerCommands($application);
        $this->assertSame(1, $callsCount);
        CommandChainManagerStaticMock::registerCommands($application);
        $this->assertSame(1, $callsCount);

        $newApplication = $this->buildApplication();
        CommandChainManagerStaticMock::registerCommands($newApplication);
        $this->assertSame(2, $callsCount);
        CommandChainManagerStaticMock::registerCommands($newApplication);
        $this->assertSame(2, $callsCount);
    }

    protected function registerApplicationChains(array $chainsList): array
    {
        $registeredChains = [];
        $application = $this->buildApplication($chainsList, $registeredChains);
        $manager = new CommandChainManager($application);
        $manager->registerApplicationChains();

        return $registeredChains;
    }

    protected function buildApplication(array $chainsList = [], &$registeredChains = null): Application
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('getParameter')
            ->will($this->returnCallback(function ($name) use ($chainsList) {
                assert($name == 'orotest.command_chain.chains_list');

                return $chainsList;
            }));

        $container->method('get')
            ->will($this->returnCallback(function ($name) use (&$registeredChains) {
                assert($name == 'orotest.command_chain');

                $registeredChainsItem = &$registeredChains[count($registeredChains)];

                $chain = $this->createMock(CommandChainInterface::class);
                $chain->method('addCommand')->will(
                    $this->returnCallback(
                        function (Command $command) use (&$registeredChainsItem) {
                            $registeredChainsItem[] = $command->getName();

                            return $command;
                        }
                    )
                );


                return $chain;
            }));

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getContainer')->willReturn($container);

        $application = $this->createMock(Application::class);
        $application->method('getKernel')->willReturn($kernel);
        $application->method('find')
            ->will(
                $this->returnCallback(
                    fn ($name) => new Command($name)
                )
            );


        return $application;
    }
}
