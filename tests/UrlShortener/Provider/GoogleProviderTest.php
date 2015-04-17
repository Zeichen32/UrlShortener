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

use GuzzleHttp\Exception\BadResponseException;
use TwoDevs\UrlShortener\Provider\GoogleProvider;

class GoogleProviderTest extends AbstractProviderTest
{
    public function testShortenSuccessfully()
    {
        $response = $this->loadResponseFile('google', 'shorten_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new GoogleProvider($client);

        $shortUrl = $adapter->shorten('http://www.google.com/');
        $this->assertEquals('http://goo.gl/fbsS', $shortUrl);
    }

    public function testExpandSuccessfully()
    {
        $response = $this->loadResponseFile('google', 'expand_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new GoogleProvider($client);

        $shortUrl = $adapter->expand('http://goo.gl/fbsS');
        $this->assertEquals('http://www.google.com/', $shortUrl);
    }

    /** @expectedException \TwoDevs\UrlShortener\Exception\CannotExpandUrlException */
    public function testExpandNotSupportedDomain()
    {
        $response = $this->loadResponseFile('google', 'expand_successfully_response.json');
        $client = $this->getClientWithResponse($response, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        $adapter = new GoogleProvider($client);
        $adapter->expand('http://example.org');
    }
}
