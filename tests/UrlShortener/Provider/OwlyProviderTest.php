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

use TwoDevs\UrlShortener\Provider\OwlyProvider;

class OwlyProviderTest extends AbstractProviderTest
{
    public function testShortenSuccessfully()
    {
        $response = $this->loadResponseFile('owly', 'shorten_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new OwlyProvider($client, ['key' => '1234']);

        $shortUrl = $adapter->shorten('http://hootsuite.com');
        $this->assertInstanceOf('TwoDevs\UrlShortener\Utils\UrlInterface', $shortUrl);
        $this->assertEquals('http://ow.ly/2blNn6', (string) $shortUrl);
    }

    public function testExpandSuccessfully()
    {
        $response = $this->loadResponseFile('owly', 'expand_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new OwlyProvider($client, ['key' => '1234']);

        $shortUrl = $adapter->expand('http://ow.ly/2blNn6');
        $this->assertInstanceOf('TwoDevs\UrlShortener\Utils\UrlInterface', $shortUrl);
        $this->assertEquals('http://hootsuite.com/', (string) $shortUrl);
    }

    /** @expectedException \TwoDevs\UrlShortener\Exception\CannotExpandUrlException */
    public function testExpandNotSupportedDomain()
    {
        $response = $this->loadResponseFile('owly', 'expand_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new OwlyProvider($client, ['key' => '1234']);
        $adapter->expand('http://example.org');
    }
}
