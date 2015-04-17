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
use Ivory\HttpAdapter\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TwoDevs\UrlShortener\Exception\CannotExpandUrlException;
use TwoDevs\UrlShortener\Exception\CannotShortenUrlException;
use TwoDevs\UrlShortener\Exception\ProviderIsDisabledException;
use TwoDevs\UrlShortener\Exception\RateLimitExceededException;
use TwoDevs\UrlShortener\Utils\UrlInterface;
use TwoDevs\UrlShortener\Utils\Url;

class BitlyProvider extends AbstractProvider implements ExpandableProviderInterface
{
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->setRequired('access_token');
        $optionsResolver->setDefaults([
            'endpoint' => 'https://api-ssl.bitly.com/',
            'domain'    => 'bit.ly',
        ]);
        $optionsResolver->setAllowedTypes('access_token', 'string');
        $optionsResolver->setAllowedTypes('endpoint', ['string', '\TwoDevs\UrlShortener\Utils\UrlInterface']);
        $optionsResolver->setAllowedValues('domain', ['bit.ly', 'j.mp', 'bitly.com']);
        $optionsResolver->setNormalizer('endpoint', function ($options, $value) {
            return $this->convertToUrl($value);
        });
    }

    /**
     * @return string
     */
    protected function getAccessToken()
    {
        return $this->options['access_token'];
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
        $endpoint->setPath('/v3/shorten');
        $endpoint->setQuery([
            'domain' => $this->options['domain'],
            'longUrl' => (string) $url,
            'format' => 'json',
            'access_token' => $this->getAccessToken(),
        ]);

        try {
            $this->addReturned();
            $response = $this->getClient()->get((string) $endpoint);
        } catch (HttpAdapterException $exp) {
            throw new CannotShortenUrlException($this->getName(), '', 0, $exp);
        }

        switch ($response->getStatusCode()) {
            case 200:
                $shortUrl = $this->parseResponse($response);
                $this->getCache()->save($cacheKey, (string) $shortUrl, $this->options['cache_lifetime']);

                return $shortUrl;
            break;

            case 403:
                throw new RateLimitExceededException($this->getName());
            break;

            default:
                throw new CannotShortenUrlException($this->getName());
        }
    }

    protected function parseResponse(ResponseInterface $response)
    {
        $data = @json_decode($response->getBody(), true);
        if (is_array($data) && isset($data['data']['url'])) {
            return Url::createFromUrl($data['data']['url']);
        } elseif (is_array($data) && isset($data['data']['expand'][0]['long_url'])) {
            return Url::createFromUrl($data['data']['expand'][0]['long_url']);
        }

        throw new CannotShortenUrlException($this->getName());
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
        $endpoint->setPath('/v3/expand');
        $endpoint->setQuery([
            'shortUrl' => (string) $url,
            'format' => 'json',
            'access_token' => $this->getAccessToken(),
        ]);

        try {
            $this->addReturned();
            $response = $this->getClient()->get((string) $endpoint);
        } catch (HttpAdapterException $exp) {
            throw new CannotExpandUrlException($this->getName(), '', 0, $exp);
        }

        switch ($response->getStatusCode()) {
            case 200:
                $longUrl = $this->parseResponse($response);
                $this->getCache()->save($cacheKey, (string) $longUrl, $this->options['cache_lifetime']);

                return $longUrl;
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
        return in_array($url->getHost(), ['bit.ly', 'j.mp']);
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'bitly';
    }
}
