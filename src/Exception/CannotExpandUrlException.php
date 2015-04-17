<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 17.04.2015
 * Time: 21:53
 */

namespace TwoDevs\UrlShortener\Exception;

class CannotExpandUrlException extends ProviderException
{
    protected $message = 'Cannot expand url';
}
