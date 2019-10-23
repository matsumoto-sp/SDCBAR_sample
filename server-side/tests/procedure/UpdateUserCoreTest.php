<?php

class UpdateUserCoreTest  extends AbstractTestCase
{
    public function testAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureDeny($db, 'update_user_core', 'member_1', 'member_1 prof_u1', 'member_1u@example.com', 'none');
    }

    public function testMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureDeny($db, 'update_user_core', 'member_1', 'member_1 prof_u1', 'member_1u@example.com', 'none');
    }

    public function testAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db, 'update_user_core', 'member_1', 'member_1 prof_u1', 'member_1u@example.com', 'none');
    }

    public function testBeforeUpdateUserCore()
    {
        $db = self::connect();
        [$prof, $email, $profileOpen] = 
            $db->query("SELECT profile, email, profile_open FROM users WHERE login_name='member_1'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($prof, 'member_1 profile');
        $this->assertEquals($email, 'member_1@example.com');
        $this->assertEquals($profileOpen, 'none');
    }

    /**
     * @depends testBeforeUpdateUserCore
     */
    public function testBasic()
    {
        $db = self::connect();
        $db->exec("CALL update_user_core('member_1', 'member_1 profile_u1', 'member_1u1@example.com', 'members')");
        [$prof, $email, $profileOpen] = 
            $db->query("SELECT profile, email, profile_open FROM users WHERE login_name='member_1'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($prof, 'member_1 profile_u1');
        $this->assertEquals($email, 'member_1u1@example.com');
        $this->assertEquals($profileOpen, 'members');
    }

    /**
     * @depends testBasic
     */
    public function testOnlyEmailUpdate()
    {
        $db = self::connect();
        $db->exec("CALL update_user_core('member_1', NULL, 'member_1u2@example.com', NULL)");
        [$prof, $email, $profileOpen] = 
            $db->query("SELECT profile, email, profile_open FROM users WHERE login_name='member_1'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($prof, 'member_1 profile_u1');
        $this->assertEquals($email, 'member_1u2@example.com');
        $this->assertEquals($profileOpen, 'members');
    }

    /**
     * @depends testOnlyEmailUpdate
     */
    public function testOnlyProfileUpdate1()
    {
        $db = self::connect();
        $db->exec("CALL update_user_core('member_1', 'member_1 profile_u2', NULL, NULL)");
        [$prof, $email, $profileOpen] = 
            $db->query("SELECT profile, email, profile_open FROM users WHERE login_name='member_1'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($prof, 'member_1 profile_u2');
        $this->assertEquals($email, 'member_1u2@example.com');
        $this->assertEquals($profileOpen, 'members');
    }

    /**
     * @depends testOnlyProfileUpdate1
     */
    public function testOnlyProfileUpdate2()
    {
        $db = self::connect();
        $db->exec("CALL update_user_core('member_1', 'member_1 profile_u3', '', NULL)");
        [$prof, $email, $profileOpen] = 
            $db->query("SELECT profile, email, profile_open FROM users WHERE login_name='member_1'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($prof, 'member_1 profile_u3');
        $this->assertEquals($email, 'member_1u2@example.com');
        $this->assertEquals($profileOpen, 'members');
    }
}
