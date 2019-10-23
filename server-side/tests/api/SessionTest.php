<?php

class SessionTest extends AbstractApiTestCase
{
    public function testNoSession()
    {
        $r = $this->post('/api/query.php', [
            'sql' => 'SELECT my_login_name() AS name, my_role() AS role'
        ]);
        $this->assertJsonStringEqualsJsonString('[{"name":"anonymous", "role":"anonymous"}]', $r->getBody());
    }

    public function testMember()
    {
        $r = $this->post('/api/login.php', [
            'loginName' => 'member_1',
            'password' => 'member_1_pw'
        ]);
        $session_id = $this->sessionId($r);
        $r = $this->post('/api/query.php', [
            'sql' => 'SELECT my_login_name() AS name, my_role() AS role'
        ], $session_id);
        $this->assertJsonStringEqualsJsonString('[{"name":"member_1", "role":"member"}]', $r->getBody());
    }

    public function testAdmin()
    {
        $r = $this->post('/api/login.php', [
            'loginName' => 'admin',
            'password' => AbstractTestCase::ADMIN_PASSWORD
        ]);
        $session_id = $this->sessionId($r);
        $r = $this->post('/api/query.php', [
            'sql' => 'SELECT my_login_name() AS name, my_role() AS role'
        ], $session_id);
        $this->assertJsonStringEqualsJsonString('[{"name":"admin", "role":"admin"}]', $r->getBody());
    }
}
