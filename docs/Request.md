# vakata\http\Request  



## Implements:
Psr\Http\Message\RequestInterface, Psr\Http\Message\MessageInterface, Psr\Http\Message\ServerRequestInterface

## Extend:

Laminas\Diactoros\ServerRequest

## Methods

| Name | Description |
|------|-------------|
|[fixedQueryParams](#requestfixedqueryparams)||
|[fromGlobals](#requestfromglobals)|Create an instance from globals|
|[fromString](#requestfromstring)||
|[getAuthorization](#requestgetauthorization)|Get any authorization details supplied with the request.|
|[getCertificate](#requestgetcertificate)||
|[getCertificateNumber](#requestgetcertificatenumber)||
|[getCookie](#requestgetcookie)|Gets a value from a cookie that came with the request|
|[getPost](#requestgetpost)|Get a param from the request body (if it is in JSON format it will be parsed out as well)|
|[getPreferredResponseFormat](#requestgetpreferredresponseformat)|Get the preffered response language (parses the Accept-Language header if present).|
|[getPreferredResponseFormats](#requestgetpreferredresponseformats)|Get the prefered response formats.|
|[getPreferredResponseLanguage](#requestgetpreferredresponselanguage)|Get the preffered response language (parses the Accept-Language header if present).|
|[getPreferredResponseLanguages](#requestgetpreferredresponselanguages)|Get the prefered response languages (parses the Accept-Language header if present).|
|[getQuery](#requestgetquery)|Get a GET param from the request URL|
|[getUrl](#requestgeturl)|Get the Uri object|
|[hasCertificate](#requesthascertificate)||
|[isAjax](#requestisajax)|Determine if this is an AJAX request|
|[isCors](#requestiscors)|Determine if this is an CORS request|
|[withCertificate](#requestwithcertificate)||

## Inherited methods

| Name | Description |
|------|-------------|
| [__construct](https://secure.php.net/manual/en/laminas\diactoros\serverrequest.__construct.php) | - |
|getAttribute|{@inheritdoc}|
|getAttributes|{@inheritdoc}|
|getBody|Gets the body of the message.|
|getCookieParams|{@inheritdoc}|
|getHeader|Retrieves a message header value by the given case-insensitive name.|
|getHeaderLine|Retrieves a comma-separated string of the values for a single header.|
|getHeaders|Retrieves all message headers.|
|getMethod|Retrieves the HTTP method of the request.|
|getParsedBody|{@inheritdoc}|
|getProtocolVersion|Retrieves the HTTP protocol version as a string.|
|getQueryParams|{@inheritdoc}|
|getRequestTarget|Retrieves the message's request target.|
|getServerParams|{@inheritdoc}|
|getUploadedFiles|{@inheritdoc}|
|getUri|Retrieves the URI instance.|
|hasHeader|Checks if a header exists by the given case-insensitive name.|
|withAddedHeader|Return an instance with the specified header appended with the
given value.|
|withAttribute|{@inheritdoc}|
|withBody|Return an instance with the specified message body.|
|withCookieParams|{@inheritdoc}|
|withHeader|Return an instance with the provided header, replacing any existing
values of any headers with the same case-insensitive name.|
|withMethod|Return an instance with the provided HTTP method.|
|withParsedBody|{@inheritdoc}|
|withProtocolVersion|Return an instance with the specified HTTP protocol version.|
|withQueryParams|{@inheritdoc}|
|withRequestTarget|Create a new instance with a specific request-target.|
|withUploadedFiles|{@inheritdoc}|
|withUri|Returns an instance with the provided URI.|
|withoutAttribute|{@inheritdoc}|
|withoutHeader|Return an instance without the specified header.|



### Request::fixedQueryParams  

**Description**

```php
 fixedQueryParams (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### Request::fromGlobals  

**Description**

```php
public static fromGlobals (array $server, array $query, array $body, array $cookies, array $files)
```

Create an instance from globals 

 

**Parameters**

* `(array) $server`
* `(array) $query`
* `(array) $body`
* `(array) $cookies`
* `(array) $files`

**Return Values**

`\Request`




<hr />


### Request::fromString  

**Description**

```php
 fromString (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### Request::getAuthorization  

**Description**

```php
public getAuthorization (void)
```

Get any authorization details supplied with the request. 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`array|null`

> array of extracted values or null (possible keys are username, password and token)


<hr />


### Request::getCertificate  

**Description**

```php
 getCertificate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### Request::getCertificateNumber  

**Description**

```php
 getCertificateNumber (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### Request::getCookie  

**Description**

```php
public getCookie (string $key, mixed $default, string $mode)
```

Gets a value from a cookie that came with the request 

 

**Parameters**

* `(string) $key`
: the cookie name  
* `(mixed) $default`
: optional default value to return if the key is not present (default to `null`)  
* `(string) $mode`
: optional cleanup of the value, available modes are: int, float, nohtml, escape, string  

**Return Values**

`mixed`

> the value (or values)


<hr />


### Request::getPost  

**Description**

```php
public getPost (string $key, mixed $default, string $mode)
```

Get a param from the request body (if it is in JSON format it will be parsed out as well) 

 

**Parameters**

* `(string) $key`
: the param name  
* `(mixed) $default`
: optional default value to return if the key is not present (default to `null`)  
* `(string) $mode`
: optional cleanup of the value, available modes are: int, float, nohtml, escape, string  

**Return Values**

`mixed`

> the value (or values if no key was specified)


<hr />


### Request::getPreferredResponseFormat  

**Description**

```php
public getPreferredResponseFormat (string $default, array|null $allowed)
```

Get the preffered response language (parses the Accept-Language header if present). 

 

**Parameters**

* `(string) $default`
: the default code to return if the header is not found  
* `(array|null) $allowed`
: an optional list of lowercase language codes to intersect with, defaults to null  

**Return Values**

`string`

> the prefered language code


<hr />


### Request::getPreferredResponseFormats  

**Description**

```php
public getPreferredResponseFormats (string $default)
```

Get the prefered response formats. 

 

**Parameters**

* `(string) $default`
: the default value to return if the Accept header is not present.  

**Return Values**

`string[]`

> the desired response formats


<hr />


### Request::getPreferredResponseLanguage  

**Description**

```php
public getPreferredResponseLanguage (string $default, array|null $allowed)
```

Get the preffered response language (parses the Accept-Language header if present). 

 

**Parameters**

* `(string) $default`
: the default code to return if the header is not found  
* `(array|null) $allowed`
: an optional list of lowercase language codes to intersect with, defaults to null  

**Return Values**

`string`

> the prefered language code


<hr />


### Request::getPreferredResponseLanguages  

**Description**

```php
public getPreferredResponseLanguages (bool $shortNames)
```

Get the prefered response languages (parses the Accept-Language header if present). 

 

**Parameters**

* `(bool) $shortNames`
: should values like "en-US", be truncated to "en", defaults to true  

**Return Values**

`array`

> array of ordered lowercase language codes


<hr />


### Request::getQuery  

**Description**

```php
public getQuery (string $key, mixed $default, string $mode)
```

Get a GET param from the request URL 

 

**Parameters**

* `(string) $key`
: the GET param name  
* `(mixed) $default`
: optional default value to return if the key is not present (default to `null`)  
* `(string) $mode`
: optional cleanup of the value, available modes are: int, float, nohtml, escape, string  

**Return Values**

`mixed`

> the value (or values)


<hr />


### Request::getUrl  

**Description**

```php
public getUrl (void)
```

Get the Uri object 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`\Uri`




<hr />


### Request::hasCertificate  

**Description**

```php
 hasCertificate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### Request::isAjax  

**Description**

```php
public isAjax (void)
```

Determine if this is an AJAX request 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`bool`

> is the request AJAX


<hr />


### Request::isCors  

**Description**

```php
public isCors (void)
```

Determine if this is an CORS request 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`bool`

> is the request CORS


<hr />


### Request::withCertificate  

**Description**

```php
 withCertificate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />

