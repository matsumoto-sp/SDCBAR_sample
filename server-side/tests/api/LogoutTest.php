<?php

class LogoutTest extends AbstractApiTestCase
{
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
        $this->post('/api/logout.php', [], $session_id);
        $r = $this->post('/api/query.php', [
            'sql' => 'SELECT my_login_name() AS name, my_role() AS role'
        ], $session_id);
        $this->assertJsonStringEqualsJsonString('[{"name":"anonymous", "role":"anonymous"}]', $r->getBody());
    }
}
