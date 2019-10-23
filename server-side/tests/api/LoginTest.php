<?php

class LoginTest extends AbstractApiTestCase
{
    public function testMember()
    {
        $r = $this->post('/api/login.php', [
            'loginName' => 'member_1',
            'password' => 'member_1_pw'
        ]);
        $this->assertEquals(json_decode($r->getBody())->role, 'member');
        $r = $this->post('/api/login.php', [
            'loginName' => 'member_2',
            'password' => 'member_2_pw'
        ]);
        $this->assertEquals(json_decode($r->getBody())->role, 'member');
    }

    public function testAnonymous()
    {
        $r = $this->post('/api/login.php', [
            'loginName' => 'anonymous',
            'password' => ''
        ]);
        $this->assertEquals(json_decode($r->getBody())->role, 'anonymous');
    }

    public function testAdmin()
    {
        $r = $this->post('/api/login.php', [
            'loginName' => 'admin',
            'password' => AbstractTestCase::ADMIN_PASSWORD
        ]);
        $this->assertEquals(json_decode($r->getBody())->role, 'admin');
    }

    public function testNoPassword()
    {
        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $r = $this->post('/api/login.php', [
            'loginName' => 'member_1',
        ]);
    }

    public function testMismatchPassword()
    {
        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $r = $this->post('/api/login.php', [
            'loginName' => 'member_1',
            'password' => 'aaaa'
        ]);
    }

    public function testNoUser()
    {
        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $r = $this->post('/api/login.php', [
            'loginName' => 'member_20',
            'password' => 'aaaa'
        ]);
    }
}
