<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 17.04.2015
 * Time: 22:59
 */

namespace TwoDevs\UrlShortener\Tests\Provider;

abstract class AbstractProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $body
     * @param int $statusCode
     * @param array $headers
     *
     * @return \Ivory\HttpAdapter\GuzzleHttpHttpAdapter
     */
    protected function getClientWithResponse($body, $statusCode = 200, $headers = array())
    {
        $guzzle = new \GuzzleHttp\Client();
        $body = \GuzzleHttp\Stream\Stream::factory($body);
        $mock = new \GuzzleHttp\Subscriber\Mock(array(
            new \GuzzleHttp\Message\Response($statusCode, $headers, $body)
        ));
        $guzzle->getEmitter()->attach($mock);
        $client = new \Ivory\HttpAdapter\GuzzleHttpHttpAdapter($guzzle);
        return $client;
    }

    protected function getClientWithMultipleResponse(array $responses)
    {
        $guzzle = new \GuzzleHttp\Client();
        $mock = new \GuzzleHttp\Subscriber\Mock($responses);
        $guzzle->getEmitter()->attach($mock);
        $client = new \Ivory\HttpAdapter\GuzzleHttpHttpAdapter($guzzle);
        return $client;
    }

    protected function loadResponseFile($provider, $file)
    {
        return \GuzzleHttp\Stream\Stream::factory(
            file_get_contents(__DIR__ . '/../fixtures/' . $provider . '/' . $file)
        );
    }
}
