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

use TwoDevs\UrlShortener\Provider\IsgdProvider;

class IsgdProviderTest extends AbstractProviderTest
{
    public function testShortenSuccessfully()
    {
        $response = $this->loadResponseFile('isgd', 'shorten_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new IsgdProvider($client);

        $shortUrl = $adapter->shorten('http://www.google.com/');
        $this->assertInstanceOf('TwoDevs\UrlShortener\Utils\UrlInterface', $shortUrl);
        $this->assertEquals('http://is.gd/C8FkRE', (string) $shortUrl);
    }

    public function testExpandSuccessfully()
    {
        $response = $this->loadResponseFile('isgd', 'expand_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new IsgdProvider($client);

        $shortUrl = $adapter->expand('http://is.gd/C8FkRE');
        $this->assertInstanceOf('TwoDevs\UrlShortener\Utils\UrlInterface', $shortUrl);
        $this->assertEquals('http://www.google.com/', (string) $shortUrl);
    }

    /** @expectedException \TwoDevs\UrlShortener\Exception\CannotExpandUrlException */
    public function testExpandNotSupportedDomain()
    {
        $response = $this->loadResponseFile('isgd', 'expand_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new IsgdProvider($client);
        $adapter->expand('http://example.org');
    }
}
