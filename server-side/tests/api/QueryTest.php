<?php

class QueryTest extends AbstractApiTestCase
{
    public function testBasic()
    {
        $r = $this->post('/api/query.php', [
            'sql' => 'SELECT 1 AS A'
        ]);
        $this->assertJsonStringEqualsJsonString('[{"A":"1"}]', $r->getBody());
    }

    public function testInvalidSql()
    {
        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $r = $this->post('/api/query.php', [
            'sql' => 'AAA'
        ]);
    }

    public function testSqlNotExists()
    {
        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $r = $this->post('/api/query.php', [
        ]);
    }

    public function testCreateAccount()
    {
        $r = $this->post('/api/query.php', [
            'sql' => "CALL add_user('member_10', 'member_10_pw', 'member_10 profile', 'member_10@example.com')"
        ]);
        $this->connect('member_10', 'member_10_pw');
        $this->assertTrue(true);
    }

}