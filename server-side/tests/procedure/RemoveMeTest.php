<?php

class RemoveMeTest extends AbstractTestCase
{
    public function testAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureDeny($db, 'remove_me');
    }
    
    public function testAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db, 'remove_me');
    }
    
    public function testBeforeAddUser()
    {
        $db = $this->connect();
        [$meberExsts] = $db->query("SELECT COUNT(1) FROM users WHERE login_name = 'member_10'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($meberExsts, 0);
    }
    
    /**
     * @depends testBeforeAddUser
     */
    public function testAfterAddUser()
    {
        $db = $this->connect();
        $db->exec("CALL add_user('member_10', 'member_10_pw', 'member_10 profile', 'member_10@example.com')");
        [$meberExsts] = $db->query("SELECT COUNT(1) FROM users WHERE login_name = 'member_10'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($meberExsts, 1);
    }

    /**
     * @depends testAfterAddUser
     */
    public function testMemberPermission()
    {
        $db = self::connect('member_10', 'member_10_pw');
        $this->assertProcedureAllow($db, 'remove_me');
    }

    /**
     * @depends testMemberPermission
     */
    public function testAfterRemoveMe()
    {
        $db = $this->connect();
        [$meberExsts] = $db->query("SELECT COUNT(1) FROM users WHERE login_name = 'member_10'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($meberExsts, 0);
    }
}
