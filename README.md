# Command Chain Demo Application

## Setting command chain set

Edit `orotest.command_chain.chains_list` parameter in `config/service.yml` to set your own command chains set: 
```yaml
# config/service.yml
parameters:
    orotest.command_chain.chains_list:
        - [foo:hello, bar:hi]
#        - [debug:config, debug:router] # you can register several command chains
#        - [bar:hi, foo:hello, debug:config] # throws Exception: command already exists in other chain
```

##  Command chain log file

Default location of command chain log file : `var/log/%kernel.environment%.chain-command.log`. 

## Running command chain

```shell
# put any first command from any chain in `orotest.command_chain.chains_list` instead of `foo:hello`
php bin/console foo:hello
```

## Configuring command chain service
                            
See [Command Chain Bundle](./src/OroTest/Bundle/CommandChainBundle/README.md).
