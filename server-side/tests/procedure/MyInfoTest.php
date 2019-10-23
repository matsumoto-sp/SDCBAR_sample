<?php

class MyInfoTest  extends AbstractTestCase
{
    public function testAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureAllow($db, 'my_info');
    }

    public function testMemberPermission()
    {
        $db = self::connect('member_1', 'member_1_pw');
        $this->assertProcedureAllow($db, 'my_info');
    }

    public function testAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db, 'my_info');
    }

    public function testResult()
    {
        $db = self::connect('member_1', 'member_1_pw');
        $info = json_encode($db->query('CALL my_info()')->fetch(\PDO::FETCH_ASSOC));
        
        $this->assertJsonStringEqualsJsonString(<<< EOS
            {
                "user_id": "2",
                "login_name": "member_1",
                "profile": "member_1 profile",
                "email": "member_1@example.com",
                "profile_open": "none",
                "is_admin": "0",
                "role": "member"
            }
EOS
            , $info);
    }

}