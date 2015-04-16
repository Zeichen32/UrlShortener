<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 16.04.2015
 * Time: 16:06
 */

namespace TwoDevs\UrlShortener;

use TwoDevs\UrlShortener\Exception\CannotShortenUrlException;
use TwoDevs\UrlShortener\Exception\ChainCannotShortenUrlException;
use TwoDevs\UrlShortener\Exception\RateLimitExceededException;
use TwoDevs\UrlShortener\Utils\Url;
use TwoDevs\UrlShortener\Utils\UrlInterface;

class ChainShorter implements UrlShortenerInterface
{

    /** @var UrlShortenerInterface[]  */
    protected $shortener = [];

    /** @var bool */
    protected $enabled = true;

    /**
     * @param string|object $url
     * @return UrlInterface
     */
    protected function convertToUrl($url)
    {
        if (is_scalar($url)) {
            return Url::createFromUrl($url);
        }

        if (!is_scalar($url) && method_exists($url, '__toString')) {
            return Url::createFromUrl((string) $url);
        }

        return $url;
    }

    /**
     * Add a new Shortener to chain
     *
     * @param UrlShortenerInterface $shortener
     */
    public function addShortener(UrlShortenerInterface $shortener)
    {
        $this->shortener[$shortener->getName()] = $shortener;
    }

    /**
     * {@inheritdoc}
     */
    public function shorten($url)
    {
        $url = $this->convertToUrl($url);
        $exceptions = [];

        foreach ($this->shortener as $shortener) {
            try {
                if ($shortener->isEnabled()) {
                    return $shortener->shorten($url);
                }
            } catch (RateLimitExceededException $exp) {
                $shortener->disable();
            } catch (CannotShortenUrlException $exp) {
                $exceptions[] = $exp;
            }
        }

        throw new ChainCannotShortenUrlException($this->getName(), $exceptions);
    }

    /**
     * Return the url shortener name
     *
     * @return string
     */
    public function getName()
    {
        return 'chain';
    }

    /**
     * Return true if shortener is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Enable this shortener
     *
     * @return void
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * Disable this shortener
     *
     * @return void
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Returns the maximum number of urls that can be
     * shorten by `shorten()` method.
     *
     * @return integer
     */
    public function getLimit()
    {
        return 0;
    }
}
