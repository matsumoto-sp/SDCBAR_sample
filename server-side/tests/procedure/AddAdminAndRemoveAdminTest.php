<?php

class AddAdminAndRemoveAdminTest extends AbstractTestCase
{
    public function testAddAdminMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureDeny($db, 'add_admin', 'member_1');
    }

    public function testRemoveAdminMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureDeny($db, 'remove_admin', 'member_1');
    }

    public function testAddAdminAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db, 'add_admin', 'member_1');
    }

    public function testRemoveAdminAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db, 'remove_admin', 'member_1');
    }

    public function testBeforeAddAdmin1()
    {
        $db = $this->connect();
        $db->exec("CALL add_user('member_10', 'member_10_pw', 'member_10_profile', 'member_10@example.com')");
        [$is_admin] = $db->query("SELECT is_admin FROM users WHERE login_name = 'member_10'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($is_admin, 0);
    }

    /**
     * @depends testBeforeAddAdmin1
     */
    public function testBeforeAddAdmin2()
    {
        $db = $this->connect('member_10', 'member_10_pw');
        $this->assertProcedureDeny($db,
            'add_user', 'member_11', 'member_11_pw', 'member_11_profile', 'member_1@example.com');
    }

    /**
     * @depends testBeforeAddAdmin2
     */
    public function testAddAdminAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureAllow($db, 'add_admin', 'member_10');
    }

    /**
     * @depends testAddAdminAdminPermission
     */
    public function testAfterAddAmin1()
    {
        $db = $this->connect();
        [$is_admin] = $db->query("SELECT is_admin FROM users WHERE login_name = 'member_10'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($is_admin, 1);
    }

    /**
     * @depends testAfterAddAmin1
     */
    public function testAfterAddAmin2()
    {
        $db = $this->connect('member_10', 'member_10_pw');
        $this->assertProcedureAllow($db,
            'add_user', 'member_11', 'member_11_pw', 'member_11_profile', 'member_1@example.com');
        $this->assertProcedureAllow($db, 'remove_user', 'member_11');
    }

    /**
     * @depends testAfterAddAmin2
     */
    public function testRemoveAdminAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureAllow($db, 'remove_admin', 'member_10');
    }

    /**
     * @depends testRemoveAdminAdminPermission
     */
    public function testAfterRemoveadmin1()
    {
        $db = $this->connect();
        [$is_admin] = $db->query("SELECT is_admin FROM users WHERE login_name = 'member_10'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($is_admin, 0);
    }

    /**
     * @depends testAfterRemoveadmin1
     */
    public function testAfterRemoveadmin2()
    {
        $db = $this->connect('member_10', 'member_10_pw');
        $this->assertProcedureDeny($db,
            'add_user', 'member_11', 'member_11_pw', 'member_11_profile', 'member_1@example.com');
    }
}
