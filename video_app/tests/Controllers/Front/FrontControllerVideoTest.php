<?php

namespace App\Tests\Controllers\Front;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontControllerVideoTest extends WebTestCase
{
    public function testNoResults()
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->request('GET', '/');

        $crawler = $client->submitForm('Search video', [
            'query' => 'aaa'
        ]);


    }
}
