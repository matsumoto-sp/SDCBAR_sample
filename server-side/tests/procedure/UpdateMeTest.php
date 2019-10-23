<?php

class UpdateMeTest  extends AbstractTestCase
{
    public function testAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureAllow($db,
            'update_me', 'admin_1_pw_u1', 'admin_1 profile_u1', 'admin_1_u1@example.com', 'friends');
    }

    public function testMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureAllow($db,
            'update_me', 'member_1_pw_u1', 'member_1 profile_u1', 'member_1_u1@example.com', 'members');
    }

    public function testAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db,
            'update_me', 'member_1_pw_u1', 'member_1 profile_u1', 'member_1_u1@example.com', 'friends');
    }

    /**
     * @depends testAdminPermission
     */
    public function testAfterUpdateMeForAdmin1()
    {
        $db = self::connect('admin', 'admin_1_pw_u1');
        $this->assertTrue(true);
    }

    /**
     * @depends testAfterUpdateMeForAdmin1
     */
    public function testAfterUpdateMeForAdmin2()
    {
        $db = self::connect();
        [$prof, $email, $profileOpen] = 
            $db->query("SELECT profile, email, profile_open FROM users WHERE login_name='admin'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($prof, 'admin_1 profile_u1');
        $this->assertEquals($email, 'admin_1_u1@example.com');
        $this->assertEquals($profileOpen, 'friends');
    }

    /**
     * @depends testAdminPermission
     */
    public function testAfterUpdateMeForMember1()
    {
        $db = self::connect('member_1', 'member_1_pw_u1');
        $this->assertTrue(true);
    }

    /**
     * @depends testAfterUpdateMeForMember1
     */
    public function testAfterUpdateMeForMember2()
    {
        $db = self::connect();
        [$prof, $email, $profileOpen] = 
            $db->query("SELECT profile, email, profile_open FROM users WHERE login_name='member_1'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($prof, 'member_1 profile_u1');
        $this->assertEquals($email, 'member_1_u1@example.com');
        $this->assertEquals($profileOpen, 'members');
    }
}
