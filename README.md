UrlShortener Library
=========================================

This library helps you to generate shortlinks for long url using different URL Shorteners.

Supportet URL Shortener
-----------------------

* [Bitly](https://bitly.com)
* [Google UrlShortener](http://goo.gl/)
* [Tiny-URL](http://www.tiny-url.info/)

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
    $shorter = new \TwoDevs\UrlShortener\BitlyShortener($adapter, ['access_token' => 'your-token']));
    
    $shortUrl = $shorter->shorten('http://example.org');
    
    var_dump( (string) $shortUrl );
```

Using chain provider
-------------

```php
    // Create a client
    $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
    
    // Create the Adapter
    $adapter = new \Ivory\HttpAdapter\GuzzleHttpHttpAdapter($client);
    
    // Create ChainProvider and attach bitly, google shortener and Tiny-Url
    $shorter  = new \TwoDevs\UrlShortener\ChainShorter();
    $shorter->addShortener(new \TwoDevs\UrlShortener\BitlyShortener($adapter, ['access_token' => 'your-token']));
    $shorter->addShortener(new \TwoDevs\UrlShortener\GoogleShortener($adapter, ['key' => 'your-key']));
    $shorter->addShortener(new \TwoDevs\UrlShortener\TinyUrlShortener($adapter));
    
    $shortUrl = $shorter->shorten('http://example.org');
    
    var_dump( (string) $shortUrl );
```


License
-------

The TwoDevs UrlShortener is under the MIT license. For the full copyright and license information, please read the
[LICENSE](/LICENSE) file that was distributed with this source code.
