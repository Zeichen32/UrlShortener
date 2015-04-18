<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 17.04.2015
 * Time: 21:36
 */

namespace TwoDevs\UrlShortener\Provider;

use TwoDevs\UrlShortener\Utils\UrlInterface;

interface ExpandableProviderInterface extends ProviderInterface
{
    /**
     * Convert a short url into a short url
     *
     * @param string|UrlInterface $url
     * @return UrlInterface
     */
    public function expand($url);

    /**
     * Checks if a url can be expands by this provider
     *
     * @param string|UrlInterface $url
     *
     * @return boolean
     */
    public function canExpand(UrlInterface $url);
}
