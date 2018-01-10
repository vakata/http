# http

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Scrutinizer Code Quality][ico-code-quality]][link-scrutinizer]
[![Code Coverage][ico-scrutinizer]][link-scrutinizer]

HTTP Request / Response classes, extending Zend Diactoros with just a few helful methods.

## Install

Via Composer

``` bash
$ composer require vakata/http
```

## Usage

``` php
// REQUEST extras
// create a request instance from the current client data
$req = \vakata\http\Request::fromGlobals();
// now you can inspect properties
$req->getQuery('asdf'); // get the "asdf" GET parameter value
$req->getCookie('sessid'); // get the "sessid" cookie value
$req->getPost('pass'); // get the "pass" POST parameter value
$req->getPrefferedResponseLanguage(); // get the preffered response language
// if a parameter is missing a default you pass in can be returned
$req->getQuery('missing', 'default'); // now $a contains "default"
// return values can also be filtered (all filters are listed in the docs)
$req->getPost('user_id', null, 'int');
// you can also get the whole array of parameters
$all = $req->getPost();

// URI extras
$req->getUri()->getSegment(0);
$req->getUri()->linkTo('some/path', [ 'get_param' => 'value' ]);

// RESPONSE extras
$res = new \vakata\http\Response();
$res = $res->expireCookie('sessid');
if ($res->hasCache()) {
    $res = $res->cacheUntil('+7 days');
}
$res = $res->setContentTypeByExtension('json');
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
[link-scrutinizer]: https://scrutinizer-ci.com/g/vakata/http
[link-code-quality]: https://scrutinizer-ci.com/g/vakata/http
[link-downloads]: https://packagist.org/packages/vakata/http
[link-author]: https://github.com/vakata
[link-contributors]: ../../contributors
[link-cc]: https://codeclimate.com/github/vakata/http

