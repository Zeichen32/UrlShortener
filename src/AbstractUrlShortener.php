<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 16.04.2015
 * Time: 14:20
 */

namespace TwoDevs\UrlShortener;

use Ivory\HttpAdapter\HttpAdapterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TwoDevs\UrlShortener\Utils\Url;
use TwoDevs\Cache\ArrayCache;
use TwoDevs\Cache\CacheInterface;
use TwoDevs\UrlShortener\Utils\UrlInterface;

abstract class AbstractUrlShortener implements UrlShortenerInterface
{
    const DEFAULT_MAX_RESULTS = 2;
    const NO_MAX_RESULTS = 0;

    /** @var HttpAdapterInterface */
    protected $client;

    /** @var array */
    protected $options;

    /** @var CacheInterface */
    protected $cache;

    /** @var bool */
    protected $enabled = true;

    /** @var int */
    protected $returned = 1;

    /**
     * @param HttpAdapterInterface $client
     * @param array $options
     * @param CacheInterface $cache
     */
    public function __construct(HttpAdapterInterface $client, array $options = [], CacheInterface $cache = null)
    {
        $this->client = $client;
        $this->cache = $cache ? $cache : new ArrayCache();

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    /**
     * @return CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults([
            'cache_lifetime' => 0,
            'max_results' => self::NO_MAX_RESULTS,
        ]);
        $optionsResolver->setAllowedTypes('cache_lifetime', 'int');
        $optionsResolver->setAllowedTypes('max_results', 'int');
    }

    /**
     * @param UrlInterface $url
     * @return string
     */
    protected function getCacheKey(UrlInterface $url)
    {
        return sprintf('%s_%s', md5($this->getName()), md5($url));
    }

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
     * @return HttpAdapterInterface
     */
    protected function getClient()
    {
        return $this->client;
    }

    protected function addReturned()
    {
        $this->returned++;
    }

    /**
     * Return true if shortener is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        if ($this->returned > $this->getLimit() && $this->getLimit() > 0) {
            $this->disable();
        }

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
        return $this->options['max_results'];
    }
}
