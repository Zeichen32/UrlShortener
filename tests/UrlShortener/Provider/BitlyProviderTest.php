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

class BitlyTest extends AbstractProviderTest
{
    public function testShortenSuccessfully()
    {
        $response = $this->loadResponseFile('bitly', 'shorten_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new BitlyProvider($client, ['access_token' => '1234']);

        $shortUrl = $adapter->shorten('http://google.com/');
        $this->assertInstanceOf('TwoDevs\UrlShortener\Utils\UrlInterface', $shortUrl);
        $this->assertEquals('http://bit.ly/ze6poY', (string) $shortUrl);
    }

    public function testExpandSuccessfully()
    {
        $response = $this->loadResponseFile('bitly', 'expand_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new BitlyProvider($client, ['access_token' => '1234']);

        $shortUrl = $adapter->expand('http://bit.ly/ze6poY');
        $this->assertInstanceOf('TwoDevs\UrlShortener\Utils\UrlInterface', $shortUrl);
        $this->assertEquals('http://google.com/', (string) $shortUrl);
    }

    /** @expectedException \TwoDevs\UrlShortener\Exception\CannotExpandUrlException */
    public function testExpandNotSupportedDomain()
    {
        $response = $this->loadResponseFile('bitly', 'expand_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new BitlyProvider($client, ['access_token' => '1234']);
        $adapter->expand('http://example.org');
    }
}
