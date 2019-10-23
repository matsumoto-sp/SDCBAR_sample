<?php

class UserIdTest extends AbstractTestCase
{
    public function testAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertFunctionAllow($db, 'user_id', 'member_1');
    }

    public function testAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertFunctionAllow($db, 'user_id', 'member_1');
    }

    public function testMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertFunctionAllow($db, 'user_id', 'member_1');
    }

    public function testBasic()
    {
        $db = self::connect();
        [$adminId] = $db->query("SELECT user_id('admin')")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($adminId, 1);
        [$member1Id] = $db->query("SELECT user_id('member_1')")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($member1Id, 2);
    }
    public function testNoMatch()
    {
        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage('SQLSTATE[45000]: <<Unknown error>>: 1001 User not exists.');
        $db = self::connect();
        $db->query("SELECT user_id('aaaaa')")->fetch(\PDO::FETCH_NUM);
    }
}
