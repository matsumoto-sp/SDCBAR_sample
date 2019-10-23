<?php

class SetupTablesPrivTest extends AbstractTestCase
{
    static protected $startup_files = [
        __dir__ . '/sql/SetupTablesPrivTest_cleanup.sql',
        __dir__ . '/sql/SetupTablesPrivTest_startup.sql',
    ];

    static protected $cleanup_files = [
    ];

    public function testAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db, 'setup_tables_priv', 'admin', '%', 'admin');
    }

    public function testAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureDeny($db, 'setup_tables_priv', 'admin', '%', 'admin');
    }

    public function testMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureDeny($db, 'setup_tables_priv', 'admin', '%', 'admin');
    }

    public function testSelectAllow()
    {
        $db = $this->connect();
        $db->exec("CALL setup_tables_priv('test_role_select', '%', 'member_1')");
        $db->exec('FLUSH PRIVILEGES');
        $dbMember = $this->connect('member_1', 'member_1_pw');
        $this->assertSqlAllow($dbMember, 'test_table_1', 'SELECT',
            'SELECT * FROM test_table_1');
        $this->assertSqlDeny($dbMember, 'test_table_1', 'INSERT',
            'INSERT INTO test_table_1 (id) VALUES(100)');
        $this->assertSqlDeny($dbMember, 'test_table_1', 'UPDATE',
            'UPDATE test_table_1 SET id=100 WHERE id = -1');
        $this->assertSqlDeny($dbMember, 'test_table_1', 'DELETE',
            'DELETE FROM test_table_1 WHERE id = 0');
    }

    public function testInsertAllow()
    {
        $db = $this->connect();
        $db->exec("CALL setup_tables_priv('test_role_insert', '%', 'member_2')");
        $db->exec('FLUSH PRIVILEGES');
        $dbMember = $this->connect('member_2', 'member_2_pw');
        $this->assertSqlDeny($dbMember, 'test_table_1', 'SELECT',
            'SELECT * FROM test_table_1');
        $this->assertSqlAllow($dbMember, 'test_table_1', 'INSERT',
            'INSERT INTO test_table_1 (id) VALUES(100)');
        $this->assertSqlDeny($dbMember, 'test_table_1', 'UPDATE',
            'UPDATE test_table_1 SET id=100 WHERE id = -1');
        $this->assertSqlDeny($dbMember, 'test_table_1', 'DELETE',
            'DELETE FROM test_table_1 WHERE id = 0');
    }

    public function testUpdateAllow()
    {
        $db = $this->connect();
        $db->exec("CALL setup_tables_priv('test_role_update', '%', 'member_3')");
        $db->exec('FLUSH PRIVILEGES');
        $dbMember = $this->connect('member_3', 'member_3_pw');
        $this->assertSqlAllow($dbMember, 'test_table_1', 'SELECT',
            'SELECT * FROM test_table_1');
        $this->assertSqlDeny($dbMember, 'test_table_1', 'INSERT',
            'INSERT INTO test_table_1 (id) VALUES(100)');
        $this->assertSqlAllow($dbMember, 'test_table_1', 'UPDATE',
            'UPDATE test_table_1 SET id=101 WHERE id = 102');
        $this->assertSqlDeny($dbMember, 'test_table_1', 'DELETE',
            'DELETE FROM test_table_1 WHERE id = 0');
    }

    public function testDeleteAllow()
    {
        $db = $this->connect();
        $db->exec("CALL setup_tables_priv('test_role_delete', '%', 'member_4')");
        $db->exec('FLUSH PRIVILEGES');
        $dbMember = $this->connect('member_4', 'member_4_pw');
        $this->assertSqlAllow($dbMember, 'test_table_1', 'SELECT',
            'SELECT * FROM test_table_1');
        $this->assertSqlDeny($dbMember, 'test_table_1', 'INSERT',
            'INSERT INTO test_table_1 (id) VALUES(100)');
        $this->assertSqlDeny($dbMember, 'test_table_1', 'UPDATE',
            'UPDATE test_table_1 SET id=101 WHERE id = 102');
        $this->assertSqlAllow($dbMember, 'test_table_1', 'DELETE',
            'DELETE FROM test_table_1 WHERE id = 0');
    }
}
