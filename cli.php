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
    private $echo;cat
    private $config;
    private $command_parameters;


    private function testCommand(string $name, bool $is_required, int $number, float $price = 4.18, string $color = 'RED')
    {
        $this->echo->msg(['Hello, ', $name, 'you chose color', $color]);
        $this->echo->error('ERRORS in program are RED');
        $this->echo->warn('Yellow message show some worning');
        $this->echo->success('Green messages means success operations');
        $this->echo->info('Cyan message require input some information.');
    }

    private function test2Command($name)
    {
        $this->echo->msg(['Hello, ', $name, 'you chose color', $name]);
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
        'Hello! This is cli php script for do some operations',
        'USAGE: $cli.php command [--param1] [--param2] [paramX] [-p]',
        'Please use follow commands:',
        ''
    ];

    const SEPARATOR = '  -  ';

    public function __construct()
    {
        $this->config = new Config();
        $this->echo = new MyEcho();

        $command_name = $this->getCommandMethod();

        if ($this->is_valid_call($command_name)) {
            $this->execute($command_name);
        } else {
            $this->helpCommand();
        }
    }


    private function helpCommand()
    {
        $help_message = array_merge(self::MESSAGE_ARR_HELLO, $this->getCommandsList());
        $this->echo->msg($help_message);
    }

    private function is_valid_call($command_name)
    {
        if (!method_exists($this, $command_name)) {
            return false;
        }

        $params = $this->getCommandParams($command_name);

        $options = $this->getOptions();

        foreach ($params['params'] as $parameter) {

            if($parameter['required']) {
                if(!isset($options[$parameter['name']])) {

                    $is_type = $parameter['type'] !== null ? ' (' . $parameter['type'] .')' : '';

                    $this->echo->error('Function `' . $command_name . '` require parameter `' . $parameter['name'] . $is_type . '`, but not set');
                    $this->echo->info('Please add');
                    $this->echo->msg('--' . $parameter['name'] . ' "some value if need"');
                    $this->echo->info('to your command for call this function');
                    die();
                }
            }

        }

        return true;
    }

    private function getOptions()
    {
        global $argv;

        $parameter = '';

        $parameters = [];

        for ($index = 2; $index < count($argv); $index++) {

            if(!$parameter) {

                if($argv[$index][0] != '-') {

                    $this->echo->error('You set unsupported parameter ' . $argv[$index]);
                    $this->helpCommand();
                    die();

                } else {
                    $parameter = str_replace('-','', $argv[$index]);
                }

            } else {

                if($argv[$index][0] == '-') {

                    $parameters[$parameter] = false;
                    $parameter = str_replace('-','', $argv[$index]);

                } else {

                    $parameters[$parameter] = $argv[$index];
                    $parameter = '';

                }
            }
        }

        return $parameters;
    }

    private function getCommandParams($command_name = '')
    {
        $class = new \ReflectionClass(__CLASS__);

        $methods = [];

        foreach ($class->getMethods() as $method) {

            if ($this->is_command($method->name)) {

                $obj = [];
                $obj['action'] = $method->name;
                $obj['params'] = array_map(

                    function ($value)
                    {

                        return [
                            'name' => $value->name,
                            'required' => !$value->isDefaultValueAvailable(),
                            'default' => $value->isDefaultValueAvailable() ? $value->getDefaultValue() : null,
                            'type' => $value->hasType() ? $value->getType()->__toString() : null
                        ];
                    },
                    $method->getParameters()
                );

                if ($command_name && $command_name == $method->name) {
                    return $obj;
                } else {
                    $methods[] = $obj;
                }
            }
        }

        return $methods;
    }

    private function execute($command_name)
    {
        if (method_exists($this, $command_name)) {
//            $this->$command_name();
            call_user_func_array([$this, $command_name], $this->getCommandValues($command_name));
        }
    }

    private function getCommandValues($command_name)
    {
        $values = [];

        $options = $this->getOptions();
        $params = $this->getCommandParams($command_name);

        foreach ($params['params'] as $parameter) {

            if ($parameter['required']) {
                if (isset($options[$parameter['name']])) {
                    $values[] = $options[$parameter['name']];
                }
            }
        }

        return $values;
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

            if ($command_match) {

                $command_name = $command_match[1];

                $methods_list[] = implode('',
                    [
                        $command_match[1],
                        self::SEPARATOR,
                        $this->getCommandDescription($command_name),
                        $this->getCommandParamsHelp($command_name)
                    ]
                );
            }
        }

        return $methods_list;
    }

    private function getCommandParamsHelp($command_name)
    {
        $text = '';

        $params = $this->getCommandParams($command_name);

//        var_dump($command_name);
//        var_dump($params);

        if(isset($params['params']) && is_array($params['params'])) {

            foreach ($params['params'] as $param) {
                $text .= '--' . $param['name'] . '(' .$param['type']. ') ';
            }
        }

        return $text;
    }

    private function getCommandDescription($command)
    {
        $descriptions = self::CMD_DESCTIPTION;

        if (isset($descriptions[$command])) {
            return $descriptions[$command];
        } else {
            return self::MESSAGE_NO_DESC;
        }
    }

    private function getCommandMethod()
    {

        global $argv;

        if (isset($argv) && isset($argv[1]) && $argv[1]) {
            return $argv[1] . 'Command';
        }

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

            if (is_array($message)) {
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
        if (is_array($message)) {
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
