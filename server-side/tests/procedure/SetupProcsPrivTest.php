<?php

class SetupProcsPrivTest extends AbstractTestCase
{
    static protected $startup_files = [
        __dir__ . '/sql/SetupProcsPrivTest_cleanup.sql',
        __dir__ . '/sql/SetupProcsPrivTest_startup.sql',
    ];

    static protected $cleanup_files = [
    ];

    public function testAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db, 'setup_procs_priv', 'admin', '%', 'admin');
    }

    public function testAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureDeny($db, 'setup_procs_priv', 'admin', '%', 'admin');
    }

    public function testMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureDeny($db, 'setup_procs_priv', 'admin', '%', 'admin');
    }

    public function testProcedureAllow()
    {
        $db = $this->connect();
        $db->exec("CALL setup_procs_priv('test_role_procedure', '%', 'member_2')");
        $db->exec('FLUSH PRIVILEGES');
        $db_member_2 = $this->connect('member_2', 'member_2_pw');
        $this->assertProcedureAllow($db_member_2, 'test_procdure');
    }

    public function testProcedureDeny()
    {
        $db_member_3 = $this->connect('member_3', 'member_3_pw');
        $this->assertProcedureDeny($db_member_3, 'test_procdure');
    }

    public function testFunctionAllow()
    {
        $db = $this->connect();
        $db->exec("CALL setup_procs_priv('test_role_function', '%', 'member_4')");
        $db->exec('FLUSH PRIVILEGES');
        $db_member_4 = $this->connect('member_4', 'member_4_pw');
        $this->assertFunctionAllow($db_member_4, 'test_function');
    }

    public function testFunctionDeny()
    {
        $db_member_5 = $this->connect('member_5', 'member_5_pw');
        $this->assertFunctionDeny($db_member_5, 'test_function');
    }
}
