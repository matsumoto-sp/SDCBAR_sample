<?php

class SetupRoleAndRemoveRoleTest extends AbstractTestCase
{
    static protected $startup_files = [
        __dir__ . '/sql/SetupRoleAndRemoveRoleTest_cleanup.sql',
        __dir__ . '/sql/SetupRoleAndRemoveRoleTest_startup.sql',
    ];

    static protected $cleanup_files = [
    ];

    public function testSetupRoleAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db, 'setup_role', 'admin', 'admin');
    }

    public function testRemoveRoleAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db, 'remove_role', 'admin');
    }

    public function testSetupRoleAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureDeny($db, 'setup_role', 'admin', 'admin');
    }

    public function testRemoveRoleAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureDeny($db, 'remove_role', 'admin');
    }

    public function testMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureDeny($db, 'setup_role', 'admin', 'admin');
    }

    public function testSetupRoleMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureDeny($db, 'setup_role', 'admin', 'admin');
    }

    public function testRemoveRoleMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureDeny($db, 'remove_role', 'admin');
    }

    public function testBeforeSetupRole()
    {
        $db = $this->connect('setup_role_1');
        $this->assertSelectDeny($db, 'test_table_1');
        $this->assertFunctionDeny($db, 'test_function_1');
        $this->assertProcedureDeny($db, 'test_procedure_1');
    }

    /**
     * @depends testBeforeSetupRole
     */
    public function testAfterSetupRole()
    {
        $db = $this->connect();
        $db->exec("CALL setup_role('test_role', 'setup_role_1')");
        $db->exec('FLUSH PRIVILEGES');
        $db = $this->connect('setup_role_1');
        $this->assertSelectAllow($db, 'test_table_1');
        $this->assertFunctionAllow($db, 'test_function_1');
        $this->assertProcedureAllow($db, 'test_procedure_1');
    }

    /**
     * @depends testAfterSetupRole
     */
    public function testAfterRemoveRole()
    {
        $db = $this->connect();
        $db->exec("CALL remove_role('setup_role_1')");
        $db->exec('FLUSH PRIVILEGES');
        /*
         * If there is no access right to the entity, the database cannot be accessed.
         * In order to prevent it, access is given to the dummy table.
         */
        $db->exec("GRANT SELECT ON test_table_2 TO setup_role_1@'%'");
        $db = $this->connect('setup_role_1');
        $this->assertSelectDeny($db, 'test_table_1');
        $this->assertFunctionDeny($db, 'test_function_1');
        $this->assertProcedureDeny($db, 'test_procedure_1');
    }

}
