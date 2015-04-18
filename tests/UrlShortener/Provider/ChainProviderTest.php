<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 17.04.2015
 * Time: 23:19
 */

namespace TwoDevs\UrlShortener\Tests\Provider;

use TwoDevs\UrlShortener\Provider\BitlyProvider;
use TwoDevs\UrlShortener\Provider\ChainProvider;
use TwoDevs\UrlShortener\Provider\GoogleProvider;

class ChainProviderTest extends AbstractProviderTest
{
    public function testShortenSuccessfully()
    {
        $response1 = $this->loadResponseFile('google', 'shorten_successfully_response.json');
        $response2 = $this->loadResponseFile('bitly', 'shorten_successfully_response.json');
        $response = array(
            new \GuzzleHttp\Message\Response(200, ['Content-Type' => 'application/json; charset=UTF-8'], $response1),
            new \GuzzleHttp\Message\Response(200, ['Content-Type' => 'application/json; charset=UTF-8'], $response2),
        );

        $client = $this->getClientWithMultipleResponse($response);
        $adapter = new ChainProvider();
        $adapter->addProvider(new GoogleProvider($client, ['max_results' => 1]));
        $adapter->addProvider(new BitlyProvider($client, ['access_token' => '1234', 'max_results' => 2]));

        $shortUrl = $adapter->shorten('http://www.google.com/');
        $this->assertInstanceOf('TwoDevs\UrlShortener\Utils\UrlInterface', $shortUrl);
        $this->assertEquals('http://goo.gl/fbsS', (string) $shortUrl);

        $shortUrl = $adapter->shorten('http://www.google.com/');
        $this->assertInstanceOf('TwoDevs\UrlShortener\Utils\UrlInterface', $shortUrl);
        $this->assertEquals('http://bit.ly/ze6poY', (string) $shortUrl);
    }
}
