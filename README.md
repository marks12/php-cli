# php-cli
Php cli application skeleton

## Requirements

1. PHP 7.x
1. libssh2 library

## About

This cli php script help you do some operations. It contains some base classes 
like ssh client or preconfigured comfortable (for me) echo class.

## Using

If you need do some operations just create some user function in `Cli` class.
For example if you want make operations in destination server, you may create 
one function like this:

```PHP

    /**
     * Check ssh client
     */
    private function checkSshCommand(string $server)
    {
        $this->echo->msg("Try connect to server $server");

        $ssh = new Ssh_clien($server);
        $this->echo->msg(

            $ssh->exec([
                "su admin\r",
                "cd /home/admin/web",
                "dir",
                "pwd",
                "exit"
            ])
        );

        $this->echo->msg('Finish');
    }

```

##### This simple function `checkSsh` do:

1. connect to destination server as `root`;
1. login as admin user;
1. change directory to `/home/admin/web`
1. show current derictory;
1. logout from admin user to `root`

## Create functions

1. For creating some special function you need create some method 
named like `yourMegaMethodCommand` with `Command` word in the end of name.
1. If you want send parameters to your method just set var with 
type and default parameter (if need) like this.

```PHP
private function checkSshCommand(string $server) 
{
    //... METHOD BODY
}
```
1. Dont forget add some description for your method.

```PHP
/**
* Your functions very clear description
*/
private function checkSshCommand(string $server) 
{
    //... METHOD BODY
}
```
## Run commands

For running command in cli.php you can view functions help like this:

```BASH
$ ./cli.php

Hello! This is cli php script for do some operations
USAGE: $cli.php command [--param1] [--param2] [param2Value]
Please use follow commands:
checkSsh:
   check ssh client
   --server (string)
test:
   Testing current utility
   --name (string)
   --is_required (bool)
   --number (int)
   --price (float) <4.18>
   --color (string) <RED>
test2:
   Test 2 some comment
   multiple
   lines
   --name <Ralf>
help:
   current help message
man:
   Show only one command description
   --cmd
```
 
 or just run method
 
 ```BASH
 $ ./cli.php checkSsh --server servername.com
 
 Try connect to server servername.com
 domain1.com  docs.domain1.com  ds.domain1.com  m.domain1.com  domain2.com
 /home/admin/web
 
 Finish
 ```
 
