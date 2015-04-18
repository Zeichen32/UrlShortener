<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 16.04.2015
 * Time: 14:19
 */

namespace TwoDevs\UrlShortener\Exception;

class ChainCannotExpandUrlException extends CannotExpandUrlException
{
    private $exceptions;

    public function __construct($provider, array $exceptions, $message = "", $code = 0)
    {
        $this->exceptions = $exceptions;
        parent::__construct($provider, $message, $code, count($exceptions) > 0 ? $exceptions[0] : null);
    }

    /**
     * @return array
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }
}
