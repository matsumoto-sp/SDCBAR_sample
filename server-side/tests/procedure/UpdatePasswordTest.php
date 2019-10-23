<?php

class UpdatePasswordCoreTest  extends AbstractTestCase
{
    public function testAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureDeny($db, 'update_password', 'member_1', 'member_1_pw_u1');
    }

    public function testMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureDeny($db, 'update_password', 'member_1', 'member_1_pw_u1');
    }

    public function testAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db, 'update_password', 'member_1', 'member_1_pw_u1');
    }

    public function testBeforeUpdatePassword()
    {
        $db = self::connect('member_1', 'member_1_pw');
        $this->assertTrue(true);
    }

    /**
     * @depends testBeforeUpdatePassword
     */
    public function testAfterUpdatePassword1()
    {
        $db = self::connect();
        $db->exec("CALL update_password('member_1', 'member_1_pw_u1')");
        $this->expectException(\PDOException::class);
        $db = self::connect('member_1', 'member_1_pw');
    }

    /**
     * @depends testAfterUpdatePassword1
     */
    public function testAfterUpdatePassword2()
    {
        $db = self::connect('member_1', 'member_1_pw_u1');
        $this->assertTrue(true);
    }
}

