<?php
use PHPUnit\Framework\TestCase;

class AbstractTestCase extends TestCase
{
    const ADMIN_PASSWORD = 'ahThip9ivoh0';
    const ERROR_HEADER = 'SQLSTATE[42000]: Syntax error or access violation:';
    static protected $config;
    static protected $base_statup_files = [
        __dir__ . '/../../sql/cleanup.sql',
        __dir__ . '/../sql/cleanup.sql',
        __dir__ . '/../../sql/build.sql',
        __dir__ . '/../sql/startup.sql',
    ];
    static protected $startup_files = [];

    static protected $base_cleanup_files = [
    ];
    static protected $cleanup_files = [];
    
    protected function qimplode($glue, $pieces)
    {
        $tmp = [];
        foreach ($pieces as $piece) {
            $tmp[] = "'{$piece}'";
        }
        return implode($glue, $tmp);
    }

    static protected function error_host_name()
    {
        return self::$config->db->host == 'localhost' ? 'localhost' : '%';
    }

    protected function assertSqlAllow($db, $table, $type, $sql)
    {
        $db->query($sql);
        $this->assertTrue(true);
    }
    
    protected function assertSqlDeny($db, $table, $type, $sql)
    {
        try {
            $db->query($sql);
        } catch (Exception $e){
            $expectMessage = sprintf("%s 1142 %s command denied to user '%s'@'%s' for table '%s'",
                self::ERROR_HEADER, $type, $db->login_name, '<hostname>', $table);
            $errorMessage = preg_replace("/@'([^']+)'/", "@'<hostname>'", $e->getMessage());
            $this->assertEquals($errorMessage, $expectMessage);
            return;
        }
        $this->assertTrue(false);
    }

    protected function assertSelectAllow($db, $table)
    {
        $this->assertSqlAllow($db, $table, 'SELECT', "SELECT * FROM {$table} LIMIT 1");
    }
    
    protected function assertSelectDeny($db, $table)
    {
        $this->assertSqlDeny($db, $table, 'SELECT', "SELECT * FROM {$table} LIMIT 1");
    }

    protected function assertProcedureAllow($db, $procedure, ...$params)
    {
        $db->exec(sprintf('CALL %s(%s)', $procedure, $this->qimplode(',', $params)));
        $this->assertTrue(true);
    }

    protected function assertProcedureDeny($db, $procedure, ...$params)
    {
        try {
            $db->exec(sprintf('CALL %s(%s)', $procedure, $this->qimplode(',', $params)));
        } catch (Exception $e){
            $mess = sprintf("%s 1370 execute command denied to user '%s'@'%s' for routine '%s.%s'",
                self::ERROR_HEADER, $db->login_name, self::error_host_name(), self::$config->db->db, $procedure);
            $this->assertEquals($e->getMessage(), $mess);
            return;
        }
        $this->assertTrue(false);
    }
    
    protected function assertFunctionAllow($db, $procedure, ...$params)
    {
        $db->query(sprintf('SELECT %s(%s)', $procedure, $this->qimplode(',', $params)));
        $this->assertTrue(true);
    }

    protected function assertFunctionDeny($db, $procedure, ...$params)
    {
        try {
            $db->query(sprintf('SELECT %s(%s)', $procedure, $this->qimplode(',', $params)));
        } catch (Exception $e){
            $mess = sprintf("%s 1370 execute command denied to user '%s'@'%s' for routine '%s.%s'",
                self::ERROR_HEADER, $db->login_name, self::error_host_name(), self::$config->db->db, $procedure);
            $this->assertEquals($e->getMessage(), $mess);
            return;
        }
        $this->assertTrue(false);
    }
    
    static protected function connect($user = null, $pass = null)
    {
        $host = self::$config->db->host;
        if ($user === null) {
            $user = self::$config->root->user;
            $pass = self::$config->root->password;
            $host = self::$config->root->host;
        }
        $pdo =  new DbConnector(
            sprintf('mysql:host=%s;dbname=%s', $host, self::$config->db->db),
            $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    static protected function do_sql_files($files)
    {
        foreach ($files as $file) {
            exec(sprintf("MYSQL_PWD=%s mysql -u%s -h%s %s 2>&1 1>/dev/null < %s",
                escapeshellarg(self::$config->root->password),
                escapeshellarg(self::$config->root->user), 
                escapeshellarg(self::$config->root->host), 
                escapeshellarg(self::$config->db->db),
                escapeshellarg($file)), $lines, $result);
            if ($result) {
                throw new Exception(sprintf("%s: %s", $file, implode("\n", $lines)));
            }
        }
    }

    public static function setUpBeforeClass() : void
    {
        self::$config = json_decode(file_get_contents(__dir__ . '/../../config.json'));
        self::do_sql_files(array_merge(static::$base_statup_files, static::$startup_files));
    }

    public static function tearDownAfterClass() : void
    {
        self::do_sql_files(array_merge(static::$base_cleanup_files, static::$cleanup_files));
    }
}
