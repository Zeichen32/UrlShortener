<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 16.04.2015
 * Time: 16:06
 */

namespace TwoDevs\UrlShortener\Provider;

use TwoDevs\UrlShortener\Exception\ChainCannotExpandUrlException;
use TwoDevs\UrlShortener\Exception\ChainCannotShortenUrlException;
use TwoDevs\UrlShortener\Exception\ProviderException;
use TwoDevs\UrlShortener\Exception\RateLimitExceededException;
use TwoDevs\UrlShortener\Provider\ExpandableProviderInterface as Expandable;
use TwoDevs\UrlShortener\Utils\Url;
use TwoDevs\UrlShortener\Utils\UrlInterface;

class ChainProvider implements Expandable
{

    /** @var ProviderInterface[]  */
    protected $provider = [];

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
     * Add a new provider to chain
     *
     * @param ProviderInterface $provider
     */
    public function addProvider(ProviderInterface $provider)
    {
        $this->provider[$provider->getName()] = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function shorten($url)
    {
        $url = $this->convertToUrl($url);
        $exceptions = [];

        foreach ($this->provider as $provider) {
            try {
                if ($provider->isEnabled()) {
                    return $provider->shorten($url);
                }
            } catch (RateLimitExceededException $exp) {
                $provider->disable();
            } catch (ProviderException $exp) {
                $exceptions[] = $exp;
            }
        }

        throw new ChainCannotShortenUrlException($this->getName(), $exceptions);
    }

    /**
     * {@inheritdoc}
     */
    public function expand($url)
    {
        $url = $this->convertToUrl($url);
        $exceptions = [];

        foreach ($this->provider as $provider) {
            try {
                if ($provider instanceof Expandable && $provider->isEnabled() && $provider->canExpand($url)) {
                    return $provider->expand($url);
                }
            } catch (RateLimitExceededException $exp) {
                $provider->disable();
            } catch (ProviderException $exp) {
                $exceptions[] = $exp;
            }
        }

        throw new ChainCannotExpandUrlException($this->getName(), $exceptions);
    }

    /**
     * {@inheritdoc}
     */
    public function canExpand(UrlInterface $url)
    {
        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'chain';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * {@inheritdoc}
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit()
    {
        return 0;
    }
}
