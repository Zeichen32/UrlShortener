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

class OwlyProvider extends AbstractProvider implements ExpandableProviderInterface
{
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->setRequired('key');
        $optionsResolver->setDefaults([
            'endpoint' => 'http://ow.ly/',
        ]);
        $optionsResolver->setAllowedTypes('key', 'string');
        $optionsResolver->setAllowedTypes('endpoint', ['string', '\TwoDevs\UrlShortener\Utils\UrlInterface']);
        $optionsResolver->setNormalizer('endpoint', function ($options, $value) {
            return $this->convertToUrl($value);
        });
    }

    /**
     * @return string
     */
    protected function getAccessToken()
    {
        return $this->options['key'];
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
        $endpoint->setPath('/api/1.1/url/shorten');
        $endpoint->setQuery([
            'longUrl' => (string) $url,
            'apiKey' => $this->getAccessToken(),
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
                if (is_array($data) && isset($data['results']['shortUrl'])) {
                    $shortUrl =  Url::createFromUrl($data['results']['shortUrl']);
                    $this->getCache()->save($cacheKey, (string) $shortUrl, $this->options['cache_lifetime']);
                    return $shortUrl;
                }
                break;

            case 403:
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
        $endpoint->setPath('/api/1.1/url/expand');
        $endpoint->setQuery([
            'shortUrl' => (string) $url,
            'apiKey' => $this->getAccessToken(),
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
                if (is_array($data) && isset($data['results']['longUrl'])) {
                    $longUrl =  Url::createFromUrl($data['results']['longUrl']);
                    $this->getCache()->save($cacheKey, (string) $longUrl, $this->options['cache_lifetime']);
                    return $longUrl;
                }
                break;

            case 403:
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
        return ('ow.ly' == $url->getHost());
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'owly';
    }
}
