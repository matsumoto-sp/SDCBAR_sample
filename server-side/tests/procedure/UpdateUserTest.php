<?php

class UpdateUserTest  extends AbstractTestCase
{
    public function testAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureAllow($db,
            'update_user', 'member_1', 'member_1_pw_u1', 'member_1 profile_u1', 'member_1_u1@example.com', 'open');
    }

    /**
     * @depends testAdminPermission
     */
    public function testMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw_u1');
        $this->assertProcedureDeny($db,
            'update_user', 'member_1', 'member_1_pw_u1', 'member_1 profile_u1', 'member_1_u1@example.com', 'open');
    }

    public function testAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db,
            'update_user', 'member_1', 'member_1_pw_u1', 'member_1 profile_u1', 'member_1_u1@example.com', 'open');
    }

    /**
     * @depends testAdminPermission
     */
    public function testAfterUpdateUser()
    {
        $db = self::connect();
        [$prof, $email, $profileOpen] = 
            $db->query("SELECT profile, email, profile_open FROM users WHERE login_name='member_1'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($prof, 'member_1 profile_u1');
        $this->assertEquals($email, 'member_1_u1@example.com');
        $this->assertEquals($profileOpen, 'open');
    }
}
