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
namespace OroTest\Bundle\CommandChainBundle\Tests\Functional;

use OroTest\Bundle\CommandChainBundle\CommandChain\CommandChainManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Class RunChainTest
 *
 * @package Functional
 * @coversNothing
 */
class RunChainTest extends TestCase
{
    private Application $application;
    private string $logFile;

    protected function setUp(): void
    {
        $this->application = $this->createApplication();

        $kernel = $this->application->getKernel();
        // $dir = $kernel->getContainer()->getParameter('kernel.logs_dir');
        $dir = $kernel->getProjectDir() . '/var/log';
        $this->logFile = $dir . '/' . $kernel->getEnvironment() . '.chain-command.log';
        file_put_contents($this->logFile, '');
    }

    protected function tearDown(): void
    {
        $kernel = $this->application->getKernel();
        $container = $kernel->getContainer();
        if ($container instanceof ResetInterface) {
            $container->reset();
        }

        $kernel->shutdown();
        unset($this->application);
    }

    public function testExecute()
    {
        $commandsData = [
            ($mainCommand = 'main:command') => 'Main command executed!',
            'member:command' => 'Member command executed!',
        ];

        $application = $this->application;

        // log file
        $command = $application->find($mainCommand);
        $commandTester = new CommandTester($command);
        $commandTester->execute([], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();

        $expectedOutput = array_reduce(
            $commandsData,
            fn ($carry, $message) => $carry . $message . \PHP_EOL
        );

        $this->assertSame($expectedOutput, $output);

        $this->assertSameLog([
            'main:command is a master command of a command chain that has registered member commands',
            'member:command registered as a member of main:command command chain',
            'Executing main:command command itself first:',
            'Main command executed!',
            'Executing main:command chain members:',
            'Member command executed!',
            'Execution of main:command chain completed.',
        ]);
    }

    public function testMemberExecute()
    {
        $application = $this->application;

        $command = $application->find('member:command');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $expectedOutput = 'Error: member:command command is a member of'
                          . ' main:command command chain and cannot be executed on its own.' . \PHP_EOL;

        $this->assertSame($expectedOutput, $output);
        $this->assertSameLog([
            'main:command is a master command of a command chain that has registered member commands',
            'member:command registered as a member of main:command command chain',
        ]);
    }

    private function assertSameLog(array $expectedLines): void
    {
        $log = file_get_contents($this->logFile);
        // remove (and check format of) timestamps
        $log = preg_replace('/^\[\d{4}(-\d{2}){2} \d{2}(:\d{2}){2}\] /m', '[] ', $log);

        $expectedLog = array_reduce(
            $expectedLines,
            fn ($cary, $line) => "{$cary}[] $line" . \PHP_EOL
        );

        $this->assertSame($expectedLog, $log);
    }


    private function createApplication(): Application
    {
        $kernel = new class('test', true) extends BaseKernel {
            use MicroKernelTrait;

            protected function configureContainer(ContainerConfigurator $container): void
            {
            }
        };

        return new class($kernel) extends Application {

            private bool $commandChainRegistered = false;

            protected function registerCommands()
            {
                parent::registerCommands();

                if (!$this->commandChainRegistered) {
                    $this->commandChainRegistered = true;
                    (new CommandChainManager($this))->registerApplicationChains();
                }
            }
        };
    }
}
