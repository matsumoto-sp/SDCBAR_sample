<?php

class MyLoginNameTest extends AbstractTestCase
{
    public function testAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertFunctionAllow($db, 'my_login_name');
    }

    public function testAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertFunctionAllow($db, 'my_login_name');
    }

    public function testMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertFunctionAllow($db, 'my_login_name');
    }

    public function testBasic()
    {
        $db = $this->connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $stm = $db->query('SELECT my_login_name()');
        [$id] = $stm->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($id, 'admin');
    }
}
