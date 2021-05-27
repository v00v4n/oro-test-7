# Command Chain Demo Application

Edit `orotest.command_chain.chains_list` parameter in `config/service.yml` to set your own command chains: 
```yaml
# config/service.yml
parameters:
    orotest.command_chain.chains_list:
        - [foo:hello, bar:hi]
#        - [debug:config, debug:router] # you can register several command chains
#        - [bar:hi, foo:hello, debug:config] # throws Exception: command already exists in other chain
```

Command chain log file default location: `var/log/dev.chain-command.log`
