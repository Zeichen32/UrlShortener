<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 16.04.2015
 * Time: 14:15
 */

namespace TwoDevs\UrlShortener;

use Ivory\HttpAdapter\HttpAdapterException;
use Ivory\HttpAdapter\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TwoDevs\UrlShortener\Exception\CannotShortenUrlException;
use TwoDevs\UrlShortener\Exception\RateLimitExceededException;
use TwoDevs\UrlShortener\Exception\ShortenerIsDisabledException;
use TwoDevs\UrlShortener\Utils\UrlInterface;
use TwoDevs\UrlShortener\Utils\Url;

class BitlyShortener extends AbstractUrlShortener
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
            $value = $this->convertToUrl($value);
            $value->setPath('/v3/shorten');
            $value->setQuery([
                'domain' => $options['domain'],
                'format' => 'json',
                'access_token' => $options['access_token'],
            ]);

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
            throw new ShortenerIsDisabledException($this->getName());
        }

        $url = $this->convertToUrl($url);
        $cacheKey = $this->getCacheKey($url);

        if ($this->getCache()->contains($cacheKey)) {
            return Url::createFromUrl($this->getCache()->fetch($cacheKey));
        }

        $endpoint = $this->getEndpoint();
        $endpoint->getQuery()->modify(['longUrl' => (string) $url]);

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
        }

        throw new CannotShortenUrlException($this->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'bitly';
    }
}
