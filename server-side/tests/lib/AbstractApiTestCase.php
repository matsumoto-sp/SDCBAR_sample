<?php

class AbstractApiTestCase extends AbstractTestCase
{
    static protected $server_pid;

    public static function waitForStartup()
    {
        [$host, $port] = explode(':', self::$config->test->apiServer);
        for ($i = 0; $i < 100; $i++) {
            if (@fsockopen($host, $port, $errno, $errstr, 10) === false) {
                usleep(5000);
                continue;
            }
            return;
        }
        throw new Exception('Could not connect to api server.');
    }

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();
        $dir = __dir__ . "/../../";
        self::$server_pid = exec(sprintf('cd %s; php -S %s > /dev/null 2>&1 & echo $!',$dir, self::$config->test->apiServer), $r, $lines);
        self::waitForStartup();
    }

    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();
        posix_kill(self::$server_pid, 9);
    }

    protected function post($path, $params, $session_id = null)
    {
        $url = sprintf("http://%s%s", self::$config->test->apiServer, $path);
        $client = new GuzzleHttp\Client();
        $request_params = ['form_params' => $params];
        if ($session_id !== null) {
            $request_params['headers']['Cookie'] = session_name() . '=' . $session_id;
        }
        $result = $client->post($url, $request_params);
        return $result;
    }

    protected function sessionId($http_result)
    {
        foreach ($http_result->getHeader('Set-Cookie') as $cookie) {
            if (preg_match('/^' . session_name() . '=([^;]+)/', $cookie, $match)) {
                return $match[1];
            }
        }
        return null;
    }
}
