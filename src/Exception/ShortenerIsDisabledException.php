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

class ShortenerIsDisabledException extends CannotShortenUrlException
{
    protected $message = 'This shortener is disabled';
}
