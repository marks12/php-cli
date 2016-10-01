#!/usr/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: tsv
 * Date: 08.09.16
 * Time: 22:20
 */

namespace Cli;

/**
 * Ру: - это не пи уай, это "ЭР" и "У". Можете проверить. Означает русский по-русски русскими буквами.
 *
 * Привет мир. Этот скрипт являет собой набор базовых функций для простого создания CLI приложения. Для добавления
 * своих операций необходимо создать метод внутри сласса CLI (как в примере) прописать код нужных операций,
 * добавить при необходимости входные параметры с указанием их типов и не забыть про комменты перед функцией чтобы
 * хелпу было что показывать. Если ваши параметры не обязательные, устанавливайте значения по умолчанию. Если обязательные
 * не ставьте значения по умолчанию. В дебрях кода есть SSH клиент, ECHO класс для вывода в консоль, Запрос данных
 * у оператора в интерактивном режиме (PROMPT).
 *
 * EN:
 * This is CLI basic class for make any operation and write results to console. Just add new private function with typed
 * parameters and write some description.
 * Basic class contain SSH client, Colored ECHO object with function msg, error, info, warning and some other
 * useful operations.
 *
 * Class Cli
 * @package Cli
 */
class Cli
{
    private $echo;
    private $config;

    const CMD_DESCTIPTION = [
        'help' => 'current help message',
        'man' => 'Show only one command description'
    ];

    /**
     * get check ssh client
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

    /**
     * Testing current utility
     */
    private function testCommand(string $name, bool $is_required, int $number, float $price = 4.18, string $color = 'RED')
    {
        $this->echo->msg(['Hello, ', $name, 'you chose color', $color]);
        $this->echo->msg('');
        $this->echo->info('is_required=' . $is_required);
        $this->echo->info('number=' . $number);
        $this->echo->info('price=' . $price);
        $this->echo->msg('');
        $this->echo->error('ERRORS in program are RED');
        $this->echo->warn('Yellow message show some worning');
        $this->echo->success('Green messages means success operations');
        $this->echo->info('Cyan message require input some information.');
    }

    /**
     * Test 2 some comment
     * multiple
     * lines
     * @param string $name
     */
    private function test2Command($name = 'Ralf')
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

    const MESSAGE_NO_DESC = '[Description not exists]';
    const MESSAGE_ARR_HELLO = [
        '',
        'Hello! This is cli php script for do some operations',
        'USAGE: $cli.php command [--param1] [--param2] [param2Value]',
        'Please use follow commands:',
    ];
    const SEPARATOR = '  -  ';
    const SPACER = '   ';
    const FUNC_SUFFIX = 'Command';

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

    private function manCommand($cmd)
    {
        $help_message = $this->getCommandsList($cmd);
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

            if ($parameter['required']) {
                if (!isset($options[$parameter['name']])) {

                    $is_type = $parameter['type'] !== null ? ' (' . $parameter['type'] . ')' : '';

                    $this->echo->error('Function `' . $this->shortName($command_name) . '` require parameter `' . $parameter['name'] . $is_type . '`, but not set');
                    $this->echo->info('Please add');
                    $this->echo->msg('--' . $parameter['name'] . ' "some value if need"');
                    $this->echo->info('to your command for call this function');
                    die();
                }
            }

        }

        return true;
    }

    private function shortName($name)
    {
        return str_replace(self::FUNC_SUFFIX, '', $name);
    }

    private function getOptions()
    {
        global $argv;

        $parameter = '';

        $parameters = [];

        for ($index = 2; $index < count($argv); $index++) {

            if (!$parameter) {

                if ($argv[$index][0] != '-') {

                    $this->echo->error('You set unsupported parameter ' . $argv[$index]);
                    $this->helpCommand();
                    die();

                } else {
                    $parameter = str_replace('-', '', $argv[$index]);
                }

            } else {

                if ($argv[$index][0] == '-') {

                    $parameters[$parameter] = false;
                    $parameter = str_replace('-', '', $argv[$index]);

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
                $obj['description'] = $method->getDocComment();
                $obj['params'] = array_map(

                    function ($value) {

                        return [
                            'name' => $value->name,
                            'required' => !$value->isDefaultValueAvailable(),
                            'default' => $value->isDefaultValueAvailable() ? $value->getDefaultValue() : null,
                            'type' => $value->hasType() ? $value->getType()->__toString() : null,
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
            call_user_func_array([$this, $command_name], $this->getCommandValues($command_name));
        }
    }

    private function getCommandValues($command_name)
    {
        $values = [];

        $options = $this->getOptions();
        $params = $this->getCommandParams($command_name);

        foreach ($params['params'] as $parameter) {

            if (isset($options[$parameter['name']])) {

                $type = $parameter['type'];
                $value = $options[$parameter['name']];

                $values[] = $this->validateValue($type, $value);
            }

        }

        return $values;
    }

    private function validateValue($type, $value)
    {
        switch (strtolower($type)) {
            case 'bool':

                if(is_bool($value) || is_int($value))
                    return (bool)$value;

                if(preg_match("/true/is",$value) || (int)$value > 0)
                    return true;
                else
                    return false;

                break;

            case 'float':
                return (float)$value;
                break;

            case 'int':
                return (int)$value;
                break;

            case 'string':
                return trim($value);
                break;

            default:
                return $value;
        }
    }

    private function is_command($command)
    {
        preg_match("/([a-z0-9A-Z_]+)" . self::FUNC_SUFFIX . "$/", $command, $command_match);
        return $command_match;
    }

    private function getCommandsList($only_command = null)
    {
        $methods_list = [];

        foreach (get_class_methods($this) as $method) {

            $command_match = $this->is_command($method);

            if ($command_match) {

                $command_name = $command_match[1];

                if ($only_command && $command_name != $only_command)
                    continue;

                $methods_list[] = implode('',
                    [
                        MyEcho::COLOR_BEGIN_YELLOW . $command_name . MyEcho::COLOR_END . ':' . PHP_EOL,
                        self::SPACER . $this->getCommandDescription($command_name),
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

        $params = $this->getCommandParams($command_name . self::FUNC_SUFFIX);

        if (isset($params['params']) && is_array($params['params'])) {

            foreach ($params['params'] as $param) {
                $text .= PHP_EOL . self::SPACER . '--' . $param['name'];
                $text .= $param['type'] ? ' (' . $param['type'] . ')' : '';
                $text .= $param['default'] ? ' <' . $param['default'] . '>' : '';
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
            return $this->getCodeCommentOrDefault($command);
        }
    }

    private function getCodeCommentOrDefault($command)
    {
        $codeComment = $this->getCodeComment($command);
        return $codeComment ? $codeComment : self::MESSAGE_NO_DESC;
    }

    private function getCodeComment($command)
    {
        $params = $this->getCommandParams($command . self::FUNC_SUFFIX);
        return is_string($params['description']) ? $this->clearCommentDescription($params['description']) : false;
    }

    private function clearCommentDescription($description)
    {
        $description = preg_replace('/\/\/+/is',' ', trim($description));
        $description = preg_replace('/\s+/is',' ', $description);
        $description = preg_replace('/\/?[*]+\/?/is',' ', $description);
        $description = preg_replace('/@.\S+.*/is',' ', $description);
        $description = preg_replace('/\s{3}/is', PHP_EOL . self::SPACER, $description);

        return trim($description);
    }

    private function getCommandMethod()
    {

        global $argv;

        if (isset($argv) && isset($argv[1]) && $argv[1]) {
            return $argv[1] . self::FUNC_SUFFIX;
        }

        return 'help' . self::FUNC_SUFFIX;
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
    const START_CHAR = '';
    const END_CHAR = "\n";

    const COLOR_BEGIN_RED = "\033[0;31m";
    const COLOR_BEGIN_GREEN = "\033[0;32m";
    const COLOR_BEGIN_YELLOW = "\033[0;33m";
    const COLOR_BEGIN_WHITE = "\033[0;38m";
    const COLOR_BEGIN_CYAN = "\033[0;36m";

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

        print $this->getColorBegin($color);
        print(self::START_CHAR);
        print($message);
        print self::COLOR_END;
        print(self::END_CHAR);
    }

    private function getColorBegin($color)
    {
        return constant('self::COLOR_BEGIN_' . strtoupper($color));
    }

    public function __destruct()
    {
        print(self::END_CHAR);
        // TODO: Implement __destruct() method.
    }
}


class Ssh_clien
{
    // SSH Host
    private $ssh_host = 'myserver.example.com';
    // SSH Port
    private $ssh_port = 22;
    // SSH Server Fingerprint
    private $ssh_server_fp = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
    // SSH Username
    private $ssh_auth_user = 'root';
    // SSH Public Key File
    private $ssh_auth_pub = '/home/REPLACE_USERNAME/.ssh/id_rsa.pub';
    // SSH Private Key File
    private $ssh_auth_priv = '/home/REPLACE_USERNAME/.ssh/id_rsa';
    // SSH Private Key Passphrase (null == no passphrase)
    private $ssh_auth_pass;
    // SSH Connection
    private $connection;

    private $shell;

    public function __construct($host)
    {
        $this->ssh_host = $host;
        $this->ssh_auth_pub = str_replace('REPLACE_USERNAME', get_current_user(), $this->ssh_auth_pub);
        $this->ssh_auth_priv = str_replace('REPLACE_USERNAME', get_current_user(), $this->ssh_auth_priv);

        $this->connect();
    }

    private function connect() {
        if (!($this->connection = ssh2_connect($this->ssh_host, $this->ssh_port))) {
            throw new \Exception('Cannot connect to server');
        }
//        $fingerprint = ssh2_fingerprint($this->connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);
//        if (strcmp($this->ssh_server_fp, $fingerprint) !== 0) {
//            throw new \Exception('Unable to verify server identity!');
//        }
        if (!ssh2_auth_pubkey_file($this->connection, $this->ssh_auth_user, $this->ssh_auth_pub, $this->ssh_auth_priv, $this->ssh_auth_pass)) {
            throw new \Exception('Autentication rejected by server');
        }
    }

    public function shell($cmd)
    {
        if (!$this->shell) {
            $this->shell = ssh2_shell($this->connection, 'xterm');
        }

        fwrite( $this->shell, $cmd);
    }

    public function exec($cmd_list) {

        $data = "";

        if(is_array($cmd_list)) {

            $cmd = implode("; ", $cmd_list) . "\n";

        } else {
            $cmd = $cmd_list . "\n";
        }

        if (!($stream = ssh2_exec($this->connection, $cmd . PHP_EOL . chr(10)))) {
            throw new \Exception('SSH command failed');
        }

        stream_set_blocking($stream, true);
        while ($buf = fread($stream, 4096)) {
            $data .= $buf;
        }
        fclose($stream);

        return $data;
    }

    public function disconnect() {

        $this->exec('echo "EXITING" && exit;');
        $this->connection = null;
        unset($this->connection);

    }

    public function __destruct() {
        $this->disconnect();
    }
}

$deploy = new Cli();
