<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 16.04.2015
 * Time: 14:02
 */

namespace TwoDevs\UrlShortener\Provider;

use TwoDevs\UrlShortener\Utils\UrlInterface;

interface ProviderInterface
{
    /**
     * Convert a long url into a short url
     *
     * @param string|UrlInterface $url
     * @return string
     */
    public function shorten($url);

    /**
     * Return the url shortener name
     *
     * @return string
     */
    public function getName();

    /**
     * Return true if shortener is enabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Enable this shortener
     *
     * @return void
     */
    public function enable();

    /**
     * Disable this shortener
     *
     * @return void
     */
    public function disable();

    /**
     * Returns the maximum number of urls that can be
     * shorten by `shorten()` method.
     *
     * @return integer
     */
    public function getLimit();
}
