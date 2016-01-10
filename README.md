# http

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Code Climate][ico-cc]][link-cc]
[![Tests Coverage][ico-cc-coverage]][link-cc]

HTTP Request / Response classes, along with helper Url and Upload classes.

## Install

Via Composer

``` bash
$ composer require vakata/http
```

## Usage

``` php
// create a request instance from the current client data
$req = \vakata\http\Request::fromRequest();
// now you can inspect properties
$req->getHeader('Content-Type');
$req->getQuery('asdf'); // get the "asdf" GET parameter value
$req->getCookie('sessid'); // get the "sessid" cookie value
$req->getPost('pass'); // get the "pass" POST parameter value
$req->getBody(true); // get the entire body of the request as a string
$body = $req->getBody(); // get the body as a stream
$body = stream_get_contents($body);

// if a parameter is missing a default you pass in can be returned
$a = $req->getQuery('missing', 'default'); // now $a contains "default"

// return values can also be filtered (all filters are listed in the docs)
$a = $req->getPost('user_id', null, 'int');

// you can also get the whole array of parameters
$all - $req->getPost();

// request instances can be created manually too
$req = new \vakata\http\Request();

// set* methods can be chained
$req->setMethod('GET')->setUrl('https://www.google.com');

// the URL property is actually an instance of the Url class
$url = new \vakata\http\Url('https://domain.tld/path');

// you can retrieve parts of the URL
$url->getHost();

// or set them
$url->setHost('otherdomain.tld');

// this works on the instance inside the request too
$req->getUrl()->setPath('/path');

// you can send a request instance too
$res = $req->send();

// the result is a response instance, which you can inspect
$res->hasHeader('Content-Type');
$res->getBody(true);

// you can also send a response to the client
$res->send();

// a response can also be created manually
$res = new \vakata\http\Response();
$res->setBody("asdf");
// send a response but respect a sepcific request
$res->send(\vakata\http\Request::fromRequest());

// this makes it simple to create a simple proxy or example
\vakata\http\Request::fromRequest()->setUrl('http://a.tld')->send()->send();

// when using fromRequest the request is populated with all uploaded data too
$up = $req->getUpload("file_key");

// the returned instance can be inspected
$up->getSize();
$up->getBody(true);

// or as frequesntly needed - moved
$up->saveAs("/path/for/file");

// creating uploads manually for sending with a request is easy
$req->addUpload("key", new \vakata\http\Upload("file.txt", "/path/to/file"));

// you can also use a string
$req->addUpload("key", new \vakata\http\Upload("file.txt", null, "contents"));

// or a stream
$stream = fopen("http://www.google.com");
$req->addUpload("key", new \vakata\http\Upload("file.txt", null, $stream));
```

Read more in the [API docs](docs/README.md)

## Testing

``` bash
$ composer test
```


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email github@vakata.com instead of using the issue tracker.

## Credits

- [vakata][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information. 

[ico-version]: https://img.shields.io/packagist/v/vakata/http.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/vakata/http/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/vakata/http.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/vakata/http.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/vakata/http.svg?style=flat-square
[ico-cc]: https://img.shields.io/codeclimate/github/vakata/http.svg?style=flat-square
[ico-cc-coverage]: https://img.shields.io/codeclimate/coverage/github/vakata/http.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/vakata/http
[link-travis]: https://travis-ci.org/vakata/http
[link-scrutinizer]: https://scrutinizer-ci.com/g/vakata/http/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/vakata/http
[link-downloads]: https://packagist.org/packages/vakata/http
[link-author]: https://github.com/vakata
[link-contributors]: ../../contributors
[link-cc]: https://codeclimate.com/github/vakata/http

