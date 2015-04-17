<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 17.04.2015
 * Time: 22:48
 */

namespace TwoDevs\UrlShortener\Tests\Provider;

use TwoDevs\UrlShortener\Provider\TinyUrlProvider;

class TinyUrlProviderTest extends AbstractProviderTest
{
    public function testShortenSuccessfully()
    {
        $response = $this->loadResponseFile('tinyurl', 'shorten_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json']);
        $adapter = new TinyUrlProvider($client);

        $shortUrl = $adapter->shorten('http://www.google.com');
        $this->assertEquals('http://tw.gs/17r605', $shortUrl);
    }
}
