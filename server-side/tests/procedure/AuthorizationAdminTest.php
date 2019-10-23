<?php

class AuthorizationAdminTest extends AbstractTestCase
{
    static protected $db;
    
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();
        self::$db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
    }

    public function testUsers()
    {
        $this->assertSelectAllow(self::$db, 'users');
    }

    public function testUsersFriendUsers()
    {
        $this->assertSelectAllow(self::$db, 'users_friend_users');
    }

    public function testLockCtl()
    {
        $this->assertSelectDeny(self::$db, 'lock_ctl');
    }

    public function testAuthMap()
    {
        $this->assertSelectDeny(self::$db, 'auth_map');
    }

    public function testUsersView()
    {
        $this->assertSelectAllow(self::$db, 'users_view');
    }
}
