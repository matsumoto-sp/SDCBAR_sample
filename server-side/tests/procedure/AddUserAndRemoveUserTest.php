<?php

class AddUserAndRemoveUserTest extends AbstractTestCase
{
    public function testAddUserAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureDeny($db,
            'add_user_core', 'admin_2', 'admin_2_pw', 'admin_2_porf', 'admin_2@example.com');
    }

    public function testAddUserMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureDeny($db,
            'add_user', 'member_10', 'member_10_pw', 'member_10_profile', 'member_10@example.com');
    }

    public function testRemoveUserMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureDeny($db, 'remove_user');
    }

    public function testRemoveUserAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db, 'remove_user', 'member_11');
    }

    public function testNoUserLogin()
    {
        $this->expectException(\PDOException::class);
        $this->connect('member_11', 'member_11_pw');
    }

    public function testCreateAnonymous()
    {
        $this->expectException(\PDOException::class);
        $db = self::connect('anonymous');
        $this->assertProcedureAllow($db,
            'add_user', 'anonymous', 'anonymous_pw', 'anonymous_profile', 'anonymous@example.com');
    }

    /**
     * @depends testNoUserLogin
     */
    public function testAddUserAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureAllow($db,
            'add_user', 'member_11', 'member_11_pw', 'member_11_profile', 'member_11@example.com');
    }

    /**
     * @depends testAddUserAnonymousPermission
     */
    public function testUserLogin()
    {
        $this->connect('member_11', 'member_11_pw');
        $this->assertTrue(true);
    }

    /**
     * @depends testUserLogin
     */
    public function testRemoveUserAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureAllow($db, 'remove_user', 'member_11');
    }

}
