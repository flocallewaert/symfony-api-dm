<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UsersControllerTest extends WebTestCase
{
    // test protocols
    public function testPostAnonUser()
    {
        $client = static::createClient();
        $client->request('POST', '/api/signup', [], [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
                // 'X-AUTH-TOKEN' => 'admin'
            ],
            '{"email": "aaa.gmail.com", "firstName": "aaa", "lastName": "aaa"}'
        );

        $response = $client->getResponse();
        $content = $response->getContent();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }
}