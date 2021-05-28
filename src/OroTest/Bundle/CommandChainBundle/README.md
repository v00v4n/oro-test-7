# Oro Test Command Chain 

Implements https://github.com/mbessolov/test-tasks/blob/master/7.md

## Configuring

At first, you need to inject Command Chain in your `Application::registerCommands()`: 
```php
use OroTest\Bundle\CommandChainBundle\CommandChain\CommandChainManager;
use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;
// ...
class Application extends BaseApplication
{
    protected function registerCommands()
    {
        parent::registerCommands();
        CommandChainManager::registerCommands($this);
    }
}
```

Default configuration:
```yaml
services:
    orotest.command_chain:
        class: OroTest\Bundle\CommandChainBundle\CommandChain\CommandChain
        public: true
        shared: false # this is obligatory
        arguments:
            $logger: '@orotest.command_chain.logger'

    orotest.command_chain.logger:
        parent: logger
        class: Symfony\Component\HttpKernel\Log\Logger
        arguments:
            $minLevel: info
            $output: "%kernel.logs_dir%/%kernel.environment%.chain-command.log"
            $formatter:
                - '@orotest.command_chain.log_formatter'
                - formatLog

    orotest.command_chain.log_formatter:
        class: OroTest\Bundle\CommandChainBundle\CommandChain\LogFormatter
```

To change command chain implementation configure `orotest.command_chain` service.

You can change log file location or other logger options by configuring `orotest.command_chain.logger` service.

To change only log formatter configure `orotest.command_chain.log_formatter` service.

## Defining Command Chain

In your application define parameter `orotest.command_chain.chains_list`:
```yaml
# config/service.yml
parameters:
    orotest.command_chain.chains_list:
        - [main:command, member1:command, member2:command]
        - [main2:command, member21:command, member22:command]
#        - [debug:config, debug:router] # you can register several command chains
#        - [bar:hi, foo:hello, debug:config] # throws Exception: command already exists in other chain
```

## Running command chain

```shell
php bin/console main:command
# php bin/console member1:command # error
php bin/console main2:command
```
