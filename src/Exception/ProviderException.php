<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 17.04.2015
 * Time: 22:25
 */

namespace TwoDevs\UrlShortener\Exception;

class ProviderException extends \RuntimeException
{
    protected $message = 'Provider exception';
    protected $provider;

    public function __construct($provider, $message = "", $code = 0, \Exception $previous = null)
    {
        if (!$message) {
            $message = $this->message;
        }

        $message = sprintf('[%s] %s', $provider, $message);
        $this->provider = $provider;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
