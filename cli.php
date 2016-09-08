#!/usr/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: tsv
 * Date: 08.09.16
 * Time: 22:20
 */

namespace Cli;


class Cli
{
    private $echo;
    private $config;
    private $command_parameters;


    private function testCommand($name, $color = 'RED')
    {
        $this->echo->msg(['Hello, ', $name, 'you chose color', $color]);
        $this->echo->error('ERRORS in program are RED');
        $this->echo->warn('Yellow message show some worning');
        $this->echo->success('Green messages means success operations');
        $this->echo->info('Cyan message require input some information.');
    }

    /**
     * SYSTEM FUNCTIONS
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     */

    /**
     * Deploy constructor.
     */
    const CMD_DESCTIPTION = [
        'help' => 'current help message',
//        'your_command' => 'Your command description'
    ];

    const MESSAGE_NO_DESC = '[Description not exists]';

    const MESSAGE_ARR_HELLO = [
        '',
        'Hello! This is delploy script for manage application based on tsv (serveon.ru) application schema.',
        'USAGE: $deploy command [params1] [param2] [paramX]',
        'Please use follow commands:',
        ''
    ];

    const SEPARATOR = '  -  ';

    public function __construct()
    {
        $this->config = new Config();
        $this->echo = new MyEcho();

        if($this->is_valid_call()) {
            $this->execute();
        } else {
            $this->helpCommand();
        }
    }


    private function helpCommand()
    {
        $help_message = array_merge(self::MESSAGE_ARR_HELLO, $this->getCommandsList());
        $this->echo->msg($help_message);
    }

    private function is_valid_call()
    {
        $method = $this->getCommandMethod();

        if(!method_exists($this, $method)) {
            return false;
        }

        $class = new \ReflectionClass(__CLASS__);
        $methods = [];
        foreach($class->getMethods() as $method){
            if($this->is_command($method->name)){

                $obj = [];
                $obj['action'] = $method->name;
                $obj['params'] = array_map(
                    function($value){
                        return $value->name.
                        ($value->isDefaultValueAvailable() ? '='.$value->getDefaultValue() : '');
                    },
                    $method->getParameters()
                );
                $methods[] = $obj;
            }
        }

        var_dump($methods);

        return true;
    }

    private function execute()
    {
        $method = $this->getCommandMethod();

        if(method_exists($this, $method)) {
            $this->$method();
        }
    }

    private function is_command($command)
    {
        preg_match("/([a-z0-9A-Z_]+)Command$/", $command, $command_match);
        return $command_match;
    }

    private function getCommandsList()
    {
        $methods_list = [];

        foreach (get_class_methods($this) as $method) {

            $command_match = $this->is_command($method);

            if($command_match) {
                $methods_list[] =  $command_match[1] . self::SEPARATOR . $this->getCmdDesc($command_match[1]);
            }
        }

        return $methods_list;
    }

    private function getCmdDesc($command)
    {
        $descriptions = self::CMD_DESCTIPTION;

        if(isset($descriptions[$command])) {
            return $descriptions[$command];
        } else {
            return self::MESSAGE_NO_DESC;
        }
    }

    private function getCommandMethod()
    {
        return 'helpCommand';
    }
}

/**
 * Class Config working with current folder stored config JSON file
 * @package Cli
 */
class Config
{
    public function __construct()
    {

    }
}

/**
 * Class MyEcho
 *
 * This class help show messages to user stdout (console).
 * Base future is - show colored text (white by default)
 *
 * @package Cli
 */
class MyEcho
{
    const COLOR_RED = 'red';
    const COLOR_GREEN = 'green';
    const COLOR_YELLOW = 'yellow';
    const COLOR_WHITE = 'white';
    const COLOR_CYAN = 'cyan';
    const START_CHAR = '   ';
    const END_CHAR = "\n";

    private $color_begin = [
        self::COLOR_RED => "\033[1;31m",
        self::COLOR_GREEN => "\033[1;32m",
        self::COLOR_YELLOW => "\033[1;33m",
        self::COLOR_WHITE => "\033[1;37m",
        self::COLOR_CYAN => "\033[1;36m",
    ];

    const COLOR_END = "\033[0m";

    public function warn($message)
    {
        $this->message($message, self::COLOR_YELLOW);
    }

    public function error($message)
    {
        $this->message($message, self::COLOR_RED);
    }

    public function success($message)
    {
        $this->message($message, self::COLOR_GREEN);
    }

    public function info($message)
    {
        $this->message($message, self::COLOR_CYAN);
    }

    private function msg_array(array $messages, $color)
    {
        foreach ($messages as $message) {

            if(is_array($message)) {
                $this->msg_array($message, $color);
            } else {
                $this->message($message, $color);
            }
        }
    }

    public function msg($message)
    {
        $this->message($message);
    }

    private function message($message, $color = self::COLOR_WHITE)
    {
        if(is_array($message)) {
            $this->msg_array($message, $color);
            return true;
        }

        print $this->color_begin[$color];
        print(self::START_CHAR);
        print($message);
        print self::COLOR_END;
        print(self::END_CHAR);
    }

    public function __destruct()
    {
        print(self::END_CHAR);
        // TODO: Implement __destruct() method.
    }
}

$deploy = new Cli();
