<?php

class AddFriendAndRemoveFriendTest extends AbstractTestCase
{
    public function testAddFriendAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureAllow($db, 'add_friend', 'member_1');
    }

    /**
     * @depends testAddFriendAdminPermission
     */
    public function testRemoveFriendAdminPermission()
    {
        $db = self::connect('admin', AbstractTestCase::ADMIN_PASSWORD);
        $this->assertProcedureAllow($db, 'remove_friend', 'member_1');
    }

    public function testAddFriendMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureAllow($db, 'add_friend', 'member_2');
    }

    /**
     * @depends testAddFriendAdminPermission
     */
    public function testRemoveFriendMemberPermission()
    {
        $db = $this->connect('member_1', 'member_1_pw');
        $this->assertProcedureAllow($db, 'remove_friend', 'member_2');
    }

    public function testAddFriendAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db, 'add_friend', 'member_2');
    }

    /**
     * @depends testAddFriendAdminPermission
     */
    public function testRemoveFriendAnonymousPermission()
    {
        $db = self::connect('anonymous');
        $this->assertProcedureDeny($db, 'remove_friend', 'member_2');
    }

    public function testBasic()
    {
        /*
            The correspondence between 'user_id' and 'login_name' is as follows.

            +---------+------------+
            | user_id | login_name |
            +---------+------------+
            |       1 | admin      |
            |       2 | member_1   |
            |       3 | member_2   |
            |       4 | member_3   |
            |       5 | member_4   |
            |       6 | member_5   |
            |       7 | member_6   |
            |       8 | member_7   |
            |       9 | member_8   |
            |      10 | member_9   |
            +---------+------------+
        */

        $db = $this->connect();
        $db->exec("DELETE FROM users_friend_users");
        $dbMember3 = $this->connect('member_3', 'member_3_pw');
        $dbMember4 = $this->connect('member_4', 'member_4_pw');
        $dbMember5 = $this->connect('member_5', 'member_5_pw');

        $dbMember3->exec("CALL add_friend('member_5')");
        $stm = $db->query("SELECT user_id, friend_user_id FROM users_friend_users ORDER BY user_id, friend_user_id");
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['4', '6']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), false);

        $dbMember3->exec("CALL remove_friend('member_5')");
        $stm = $db->query("SELECT user_id, friend_user_id FROM users_friend_users ORDER BY user_id, friend_user_id");
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), false);

        $dbMember4->exec("CALL add_friend('member_3')");
        $dbMember4->exec("CALL add_friend('member_5')");
        $dbMember5->exec("CALL add_friend('member_6')");
        $stm = $db->query("SELECT user_id, friend_user_id FROM users_friend_users ORDER BY user_id, friend_user_id");
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['5', '4']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['5', '6']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['6', '7']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), false);

        $dbMember4->exec("CALL add_friend('member_3')");
        $stm = $db->query("SELECT user_id, friend_user_id FROM users_friend_users ORDER BY user_id, friend_user_id");
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['5', '4']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['5', '6']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['6', '7']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), false);

        $dbMember5->exec("CALL add_friend('member_4')");
        $stm = $db->query("SELECT user_id, friend_user_id FROM users_friend_users ORDER BY user_id, friend_user_id");
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['5', '4']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['5', '6']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['6', '5']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['6', '7']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), false);

        $dbMember5->exec("CALL remove_friend('member_4')");
        $stm = $db->query("SELECT user_id, friend_user_id FROM users_friend_users ORDER BY user_id, friend_user_id");
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['5', '4']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['5', '6']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['6', '7']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), false);


        $dbMember5->exec("CALL remove_friend('admin')");
        $stm = $db->query("SELECT user_id, friend_user_id FROM users_friend_users ORDER BY user_id, friend_user_id");
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['5', '4']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['5', '6']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['6', '7']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), false);
    }

    public function testAddFriendFailed()
    {
        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage('SQLSTATE[45000]: <<Unknown error>>: 1001 User not exists.');
        $db = $this->connect('member_1', 'member_1_pw');
        $db->exec("CALL add_friend('a')");
    }

    public function testRemoveFriendFailed()
    {
        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage('SQLSTATE[45000]: <<Unknown error>>: 1001 User not exists.');
        $db = $this->connect('member_1', 'member_1_pw');
        $db->exec("CALL remove_friend('a')");
    }

    public function testAutoDelete()
    {
        $db = $this->connect();
        $db->exec("DELETE FROM users_friend_users");
        $db->exec("CALL add_user('member_10', 'member_10_pw', 'member_10_profile', 'member_10@example.com')");
        [$member10Id] = $db->query("SELECT user_id FROM users WHERE login_name = 'member_10'")->fetch(\PDO::FETCH_NUM);
        $this->assertEquals($member10Id, 11);
        $dbMember1 = $this->connect('member_1', 'member_1_pw');
        $dbMember1->exec("CALL add_friend('member_10')");
        $dbMember1->exec("CALL add_friend('member_3')");
        $stm = $db->query("SELECT user_id, friend_user_id FROM users_friend_users ORDER BY user_id, friend_user_id");
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['2', '4']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['2', '11']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), false);
        $db->exec("CALL remove_user('member_10')");
        $stm = $db->query("SELECT user_id, friend_user_id FROM users_friend_users ORDER BY user_id, friend_user_id");
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), ['2', '4']);
        $this->assertSame($stm->fetch(\PDO::FETCH_NUM), false);
    }
}
