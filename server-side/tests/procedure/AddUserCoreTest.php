<?php

class AddUserCoreTest extends AbstractTestCase
{
    public function testAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db,
            'add_user_core', 'member_11', 'pass_11', 'member_11 porfile', 'member_11@example.com');
    }

    public function testAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureDeny($db,
            'add_user_core', 'admin_2', 'admin_2_pwpass2', 'admin_2 porfile', 'admin_2@example.com');
    }

    public function testMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureDeny($db,
            'add_user_core', 'member_10', 'member_10_pw', 'member10 porfile', 'member_10@example.com');
    }

    public function testAddUserCore()
    {
        $db = self::connect();
        $db->exec("CALL add_user_core('member_10', 'member_10_pw', 'member_10 profile', 'member_10@example.com')");
        $db->exec('FLUSH PRIVILEGES');
        $stm = $db->query("SELECT * FROM users WHERE login_name='member_10'");
        $row = $stm->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals($row['login_name'], 'member_10');
        $this->assertEquals($row['profile'], 'member_10 profile');
        $this->assertEquals($row['email'], 'member_10@example.com');
        $this->assertEquals($row['is_admin'], 0);
        $this->assertFalse($stm->fetch(\PDO::FETCH_ASSOC));
        $this->assertTrue($db->exec('DROP USER member_10@localhost') === 0);
        $this->assertTrue($db->exec('DROP USER member_10@`%`') === 0);
    }

}
