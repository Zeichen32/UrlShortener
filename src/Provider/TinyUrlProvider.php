<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 16.04.2015
 * Time: 15:23
 */

namespace TwoDevs\UrlShortener\Provider;

use Ivory\HttpAdapter\HttpAdapterException;
use Ivory\HttpAdapter\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TwoDevs\UrlShortener\Exception\CannotShortenUrlException;
use TwoDevs\UrlShortener\Exception\ProviderIsDisabledException;
use TwoDevs\UrlShortener\Exception\RateLimitExceededException;
use TwoDevs\UrlShortener\Utils\Url;
use TwoDevs\UrlShortener\Utils\UrlInterface;

class TinyUrlProvider extends AbstractProvider
{
    const MODE_RAND = 'random';
    const MODE_CREATE = 'create';

    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->setDefaults([
            'endpoint' => 'http://tiny-url.info',
            'key' => null,
            'mode' => self::MODE_RAND,
            'provider' => null,
        ]);

        $optionsResolver->setAllowedTypes('key', ['null', 'string']);
        $optionsResolver->setAllowedTypes('provider', ['null', 'string']);
        $optionsResolver->setAllowedTypes('endpoint', ['string', '\TwoDevs\UrlShortener\Utils\UrlInterface']);
        $optionsResolver->setAllowedValues('mode', [ self::MODE_RAND, self::MODE_CREATE ]);

        $optionsResolver->setNormalizer('key', function ($options, $value) {
            if ($options['mode'] == self::MODE_RAND) {
                return null;
            }
            return $value;
        });

        $optionsResolver->setNormalizer('provider', function ($options, $value) {
            if ($options['mode'] == self::MODE_RAND) {
                return null;
            }
            return $value;
        });

        $optionsResolver->setNormalizer('endpoint', function ($options, $value) {
            $value = $this->convertToUrl($value);
            $path = ($options['mode'] == self::MODE_RAND) ? '/api/v1/random' : '/api/v1/create';
            $value->setPath($path);

            return $value;
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

        $postData = ['format' => 'json', 'url' => (string) $url];
        if ($this->options['mode'] == self::MODE_CREATE) {
            $postData['apikey'] = $this->options['key'];
            $postData['provider'] = $this->options['provider'];
        }

        try {
            $this->addReturned();
            $response = $this->getClient()->post((string) $endpoint, [], $postData);
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

        if (is_array($data) && isset($data['shorturl'])) {
            return Url::createFromUrl($data['shorturl']);
        }

        throw new CannotShortenUrlException($this->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tinyUrl';
    }
}
