<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 16.04.2015
 * Time: 14:15
 */

namespace TwoDevs\UrlShortener\Provider;

use Ivory\HttpAdapter\HttpAdapterException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TwoDevs\UrlShortener\Exception\CannotExpandUrlException;
use TwoDevs\UrlShortener\Exception\CannotShortenUrlException;
use TwoDevs\UrlShortener\Exception\ProviderIsDisabledException;
use TwoDevs\UrlShortener\Exception\RateLimitExceededException;
use TwoDevs\UrlShortener\Utils\UrlInterface;
use TwoDevs\UrlShortener\Utils\Url;

class IsgdProvider extends AbstractProvider implements ExpandableProviderInterface
{
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->setDefaults([
            'endpoint' => 'http://is.gd/',
            'max_results' => self::DEFAULT_MAX_RESULTS,
        ]);
        $optionsResolver->setAllowedTypes('endpoint', ['string', '\TwoDevs\UrlShortener\Utils\UrlInterface']);
        $optionsResolver->setNormalizer('endpoint', function ($options, $value) {
            return $this->convertToUrl($value);
        });
    }

    /**
     * @return UrlInterface
     */
    protected function getEndpoint()
    {
        return $this->options['endpoint'];
    }

    /**
     * {@inheritdoc}
     */
    public function shorten($url)
    {
        if (!$this->isEnabled()) {
            throw new ProviderIsDisabledException($this->getName());
        }

        $url = $this->convertToUrl($url);
        $cacheKey = $this->getCacheKey($url);

        if ($this->getCache()->contains($cacheKey)) {
            return Url::createFromUrl($this->getCache()->fetch($cacheKey));
        }

        $endpoint = $this->getEndpoint();
        $endpoint->setPath('/create.php');
        $endpoint->setQuery([
            'url' => (string) $url,
            'format' => 'json'
        ]);

        try {
            $this->addReturned();
            $response = $this->getClient()->get((string) $endpoint);
        } catch (HttpAdapterException $exp) {
            throw new CannotShortenUrlException($this->getName(), '', 0, $exp);
        }

        switch ($response->getStatusCode()) {
            case 200:
                $data = @json_decode($response->getBody(), true);
                if (is_array($data) && isset($data['shorturl'])) {
                    $shortUrl =  Url::createFromUrl($data['shorturl']);
                    $this->getCache()->save($cacheKey, (string) $shortUrl, $this->options['cache_lifetime']);
                    return $shortUrl;
                }
                break;

            case 502:
                throw new RateLimitExceededException($this->getName());
            break;

            default:
                throw new CannotShortenUrlException($this->getName());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function expand($url)
    {
        if (!$this->isEnabled()) {
            throw new ProviderIsDisabledException($this->getName());
        }

        $url = $this->convertToUrl($url);

        if (!$this->canExpand($url)) {
            throw new CannotExpandUrlException($this->getName());
        }

        $cacheKey = $this->getCacheKey($url, 'expand');

        if ($this->getCache()->contains($cacheKey)) {
            return Url::createFromUrl($this->getCache()->fetch($cacheKey));
        }

        $endpoint = $this->getEndpoint();
        $endpoint->setPath('/forward.php');
        $endpoint->setQuery([
            'shorturl' => (string) $url,
            'format' => 'json',
        ]);

        try {
            $this->addReturned();
            $response = $this->getClient()->get((string) $endpoint);
        } catch (HttpAdapterException $exp) {
            throw new CannotExpandUrlException($this->getName(), '', 0, $exp);
        }

        switch ($response->getStatusCode()) {
            case 200:
                $data = @json_decode($response->getBody(), true);
                if (is_array($data) && isset($data['url'])) {
                    $longUrl =  Url::createFromUrl($data['url']);
                    $this->getCache()->save($cacheKey, (string) $longUrl, $this->options['cache_lifetime']);
                    return $longUrl;
                }
                break;

            case 502:
                throw new RateLimitExceededException($this->getName());
                break;

            default:
                throw new CannotExpandUrlException($this->getName());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function canExpand(UrlInterface $url)
    {
        return ('is.gd' == $url->getHost());
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'isgd';
    }
}
