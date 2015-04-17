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

use TwoDevs\UrlShortener\Provider\VgdProvider;

class VgdProviderTest extends AbstractProviderTest
{
    public function testShortenSuccessfully()
    {
        $response = $this->loadResponseFile('vgd', 'shorten_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new VgdProvider($client);

        $shortUrl = $adapter->shorten('http://www.google.com/');
        $this->assertInstanceOf('TwoDevs\UrlShortener\Utils\UrlInterface', $shortUrl);
        $this->assertEquals('http://v.gd/CqwB1V', (string) $shortUrl);
    }

    public function testExpandSuccessfully()
    {
        $response = $this->loadResponseFile('vgd', 'expand_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new VgdProvider($client);

        $shortUrl = $adapter->expand('http://v.gd/CqwB1V');
        $this->assertInstanceOf('TwoDevs\UrlShortener\Utils\UrlInterface', $shortUrl);
        $this->assertEquals('http://www.google.com/', (string) $shortUrl);
    }

    /** @expectedException \TwoDevs\UrlShortener\Exception\CannotExpandUrlException */
    public function testExpandNotSupportedDomain()
    {
        $response = $this->loadResponseFile('vgd', 'expand_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new VgdProvider($client);
        $adapter->expand('http://example.org');
    }
}
