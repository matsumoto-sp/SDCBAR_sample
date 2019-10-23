<?php

class MyRoleTest extends AbstractTestCase
{
    public function testAdmin()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        [$role] = $db->query("SELECT my_role()")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($role, 'admin');
    }

    public function testMember1()
    {
        $db = self::connect('member_1', 'member_1_pw');
        [$role] = $db->query("SELECT my_role()")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($role, 'member');
    }

    public function testMember2()
    {
        $db = self::connect('member_2', 'member_2_pw');
        [$role] = $db->query("SELECT my_role()")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($role, 'member');
    }

    public function testAnonymous()
    {
        $db = self::connect('anonymous');
        [$role] = $db->query("SELECT my_role()")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($role, 'anonymous');
    }

    public function testNoUser()
    {
        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage('SQLSTATE[45000]: <<Unknown error>>: 1001 User not exists.');
        $db = self::connect();
        [$role] = $db->query("SELECT my_role()")->fetch(\PDO::FETCH_NUM);
    }

}
