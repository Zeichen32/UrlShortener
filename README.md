UrlShortener Library
=========================================

[![Latest Stable Version](https://poser.pugx.org/twodevs/url-shortener/v/stable.svg)](https://packagist.org/packages/twodevs/url-shortener)
[![License](https://poser.pugx.org/twodevs/url-shortener/license.svg)](https://packagist.org/packages/twodevs/url-shortener)
[![Build Status](https://travis-ci.org/Zeichen32/UrlShortener.svg)](https://travis-ci.org/Zeichen32/UrlShortener)

This library helps you to generate shortlinks for long url using different URL Shorteners.

Supportet URL Shortener
-----------------------

* [Bitly](https://bitly.com)
* [Google UrlShortener](http://goo.gl/)
* [Tiny-URL](http://www.tiny-url.info/)
* [Ow.ly](http://ow.ly/)
* [Is.gd](http://is.gd/)
* [V.gd](http://v.gd/)

Installation
------------

The preferred way to install this library is to use [Composer](http://getcomposer.org).

```bash
    $ composer require twodevs/url-shortener ~1.0
```

Choose a http client support by [Ivory HttpAdapter](https://github.com/egeloen/ivory-http-adapter)

```bash
    $ composer require guzzlehttp/guzzle ~5.0
```

General Usage
-------------

```php
    // Create a client
    $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
    
    // Create the Adapter
    $adapter = new \Ivory\HttpAdapter\GuzzleHttpHttpAdapter($client);
    
    // Create BitlyShortener
    $shorter = new \TwoDevs\UrlShortener\Provider\BitlyProvider($adapter, ['access_token' => 'your-token']));
    
    // Shorten a long url
    $shortUrl = $shorter->shorten('http://example.org');
    
    // Expand a short url
    $longUrl = $shorter->expand($shortUrl);
    
    var_dump( (string) $shortUrl );
    var_dump( (string) $longUrl );
```

Using chain provider
-------------

```php
    // Create a client
    $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
    
    // Create the Adapter
    $adapter = new \Ivory\HttpAdapter\GuzzleHttpHttpAdapter($client);
    
    // Create ChainProvider and attach bitly, google shortener and Tiny-Url
    $shorter  = new \TwoDevs\UrlShortener\Provider\ChainProvider();
    $shorter->addProvider(new \TwoDevs\UrlShortener\Provider\BitlyProvider($adapter, ['access_token' => 'your-token']));
    $shorter->addProvider(new \TwoDevs\UrlShortener\Provider\GoogleProvider($adapter, ['key' => 'your-key']));
    $shorter->addProvider(new \TwoDevs\UrlShortener\Provider\OwlyProvider($adapter, ['key' => 'your-key']));
    $shorter->addProvider(new \TwoDevs\UrlShortener\Provider\TinyUrlProvider($adapter));
    $shorter->addProvider(new \TwoDevs\UrlShortener\Provider\IsgdProvider($adapter));
    $shorter->addProvider(new \TwoDevs\UrlShortener\Provider\VgdUrlProvider($adapter));
    
    // Shorten a long url
    $shortUrl = $shorter->shorten('http://example.org');
    
    // Expand a short url
    $longUrl = $shorter->expand($shortUrl);
    
    var_dump( (string) $shortUrl );
    var_dump( (string) $longUrl );
```


License
-------

The TwoDevs UrlShortener is under the MIT license. For the full copyright and license information, please read the
[LICENSE](/LICENSE) file that was distributed with this source code.
