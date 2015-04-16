<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 16.04.2015
 * Time: 14:16
 */

namespace TwoDevs\UrlShortener\Exception;

use Exception;

class CannotShortenUrlException extends \RuntimeException
{
    protected $message = 'Cannot shorten url';
    protected $provider;

    public function __construct($provider, $message = "", $code = 0, Exception $previous = null)
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
