<?php

class UsersViewTest extends AbstractTestCase
{
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();
        $dbMember1 = self::connect('member_1', 'member_1_pw');
        $dbMember2 = self::connect('member_2', 'member_2_pw');
        $dbMember3 = self::connect('member_3', 'member_3_pw');
        $dbMember4 = self::connect('member_4', 'member_4_pw');
        $dbMember1->exec("CALL update_me(NULL, NULL, NULL, 'none')");
        $dbMember1->exec("CALL add_friend('member_5')");
        $dbMember2->exec("CALL update_me(NULL, NULL, NULL, 'friends')");
        $dbMember2->exec("CALL add_friend('member_5')");
        $dbMember3->exec("CALL update_me(NULL, NULL, NULL, 'members')");
        $dbMember3->exec("CALL add_friend('member_5')");
        $dbMember4->exec("CALL update_me(NULL, NULL, NULL, 'open')");
        $dbMember4->exec("CALL add_friend('member_5')");
    }

    protected function getProfile($myLoginName, $targetLoginName)
    {
        $db = $this->connect($myLoginName, $myLoginName == 'anonymous' ? '' : $myLoginName . '_pw');
        [$profile] = $db->query(
            "SELECT profile FROM users_view WHERE login_name = '$targetLoginName'")->fetch(\PDO::FETCH_NUM);
        return $profile;
    }

    public function testAnonymousNone()
    {
        $this->assertEquals($this->getProfile('anonymous', 'member_1'), NULL);
    }

    public function testAnonymousFriends()
    {
        $this->assertEquals($this->getProfile('anonymous', 'member_2'), NULL);
    }

    public function testAnonymousMembers()
    {
        $this->assertEquals($this->getProfile('anonymous', 'member_3'), NULL);
    }

    public function testAnonymousOpen()
    {
        $this->assertEquals($this->getProfile('anonymous', 'member_4'), 'member_4 profile');
    }

    public function testMemberNone()
    {
        $this->assertEquals($this->getProfile('member_6', 'member_1'), NULL);
    }

    public function testMemberFriends()
    {
        $this->assertEquals($this->getProfile('member_6', 'member_2'), NULL);
    }

    public function testMemberMembers()
    {
        $this->assertEquals($this->getProfile('member_6', 'member_3'), 'member_3 profile');
    }

    public function testMemberOpen()
    {
        $this->assertEquals($this->getProfile('member_6', 'member_4'), 'member_4 profile');
    }

    public function testFriendNone()
    {
        $this->assertEquals($this->getProfile('member_5', 'member_1'), NULL);
    }

    public function testFriendFriends()
    {
        $this->assertEquals($this->getProfile('member_5', 'member_2'), 'member_2 profile');
        $this->assertEquals($this->getProfile('member_2', 'member_5'), NULL);
    }

    public function testFriendMembers()
    {
        $this->assertEquals($this->getProfile('member_5', 'member_3'), 'member_3 profile');
    }

    public function testFriendOpen()
    {
        $this->assertEquals($this->getProfile('member_5', 'member_4'), 'member_4 profile');
    }

    public function testMeNone()
    {
        $this->assertEquals($this->getProfile('member_1', 'member_1'), 'member_1 profile');
    }

    public function testMeFriends()
    {
        $this->assertEquals($this->getProfile('member_2', 'member_2'), 'member_2 profile');
    }

    public function testMeMembers()
    {
        $this->assertEquals($this->getProfile('member_3', 'member_3'), 'member_3 profile');
    }

    public function testMeOpen()
    {
        $this->assertEquals($this->getProfile('member_4', 'member_4'), 'member_4 profile');
    }
}
