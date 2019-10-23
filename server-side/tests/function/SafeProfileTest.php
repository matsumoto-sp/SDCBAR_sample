<?php

class SafeProfileTest extends AbstractTestCase
{
    public function testAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertFunctionDeny($db, 'safe_profile', 'id1', 'id1', 'profile', 'none', 0);
    }

    public function testAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertFunctionDeny($db, 'safe_profile', 'id1', 'id1', 'profile', 'none', 0);
    }

    public function testMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertFunctionDeny($db, 'safe_profile', 'id1', 'id1', 'profile', 'none', 0);
    }

    public function testNone1()
    {
        $db = $this->connect();
        $stm = $db->query("SELECT safe_profile('id1', 'id1', 'profile', 'none', 0)");
        [$profile] = $stm->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($profile, 'profile');
        
    }

    public function testNone2()
    {
        $db = $this->connect();
        $stm = $db->query("SELECT safe_profile('id1', 'id2', 'profile', 'none', 0)");
        [$profile] = $stm->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($profile, null);
        
    }

    public function testFriend1()
    {
        $db = $this->connect();
        $stm = $db->query("SELECT safe_profile('id1', 'id1', 'profile', 'friends', 0)");
        [$profile] = $stm->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($profile, 'profile');
    }

    public function testFriend2()
    {
        $db = $this->connect();
        $stm = $db->query("SELECT safe_profile('id1', 'id2', 'profile', 'friends', 0)");
        [$profile] = $stm->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($profile, 'profile');
    }

    public function testFriend3()
    {
        $db = $this->connect();
        $stm = $db->query("SELECT safe_profile('id1', 'id2', 'profile', 'friends', 1)");
        [$profile] = $stm->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($profile, 'profile');
    }

    public function testFriend4()
    {
        $db = $this->connect();
        $stm = $db->query("SELECT safe_profile('id1', 'id2', 'profile', 'friends', NULL)");
        [$profile] = $stm->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($profile, null);
    }

    public function testMember1()
    {
        $db = $this->connect();
        $stm = $db->query("SELECT safe_profile('anonymous', 'id2', 'profile', 'members', NULL)");
        [$profile] = $stm->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($profile, null);
    }

    public function testMember2()
    {
        $db = $this->connect();
        $stm = $db->query("SELECT safe_profile('id1', 'id2', 'profile', 'members', NULL)");
        [$profile] = $stm->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($profile, 'profile');
    }

    public function testOpen1()
    {
        $db = $this->connect();
        $stm = $db->query("SELECT safe_profile('anonymous', 'id2', 'profile', 'open', NULL)");
        [$profile] = $stm->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($profile, 'profile');
    }

    public function testInvalid1()
    {
        $db = $this->connect();
        try {
            $stm = $db->query("SELECT safe_profile('anonymous', 'anonymous', 'profile', 'aaa', NULL)");
        } catch (Exception $e){
            $this->assertEquals($e->getMessage(),
                "SQLSTATE[01000]: Warning: 1265 Data truncated for column '_profile_open' at row 1");
            return;
        }
        $this->assertTrue(false);
    }
}
