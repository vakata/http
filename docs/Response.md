# vakata\http\Response  



## Implements:
Psr\Http\Message\MessageInterface, Psr\Http\Message\ResponseInterface

## Extend:

Zend\Diactoros\Response

## Methods

| Name | Description |
|------|-------------|
|[cacheUntil](#responsecacheuntil)|Make the response cacheable.|
|[expireCookie](#responseexpirecookie)|Expires an existing cookie|
|[hasCache](#responsehascache)||
|[noCache](#responsenocache)|Prevent caching|
|[setBody](#responsesetbody)||
|[setContentTypeByExtension](#responsesetcontenttypebyextension)||
|[withCookie](#responsewithcookie)|Set a cookie|

## Inherited methods

| Name | Description |
|------|-------------|
|__construct|-|
|getBody|Gets the body of the message.|
|getHeader|Retrieves a message header value by the given case-insensitive name.|
|getHeaderLine|Retrieves a comma-separated string of the values for a single header.|
|getHeaders|Retrieves all message headers.|
|getProtocolVersion|Retrieves the HTTP protocol version as a string.|
|getReasonPhrase|{@inheritdoc}|
|getStatusCode|{@inheritdoc}|
|hasHeader|Checks if a header exists by the given case-insensitive name.|
|withAddedHeader|Return an instance with the specified header appended with the
given value.|
|withBody|Return an instance with the specified message body.|
|withHeader|Return an instance with the provided header, replacing any existing
values of any headers with the same case-insensitive name.|
|withProtocolVersion|Return an instance with the specified HTTP protocol version.|
|withStatus|{@inheritdoc}|
|withoutHeader|Return an instance without the specified header.|



### Response::cacheUntil  

**Description**

```php
public cacheUntil (int|string $expires)
```

Make the response cacheable. 

 

**Parameters**

* `(int|string) $expires`
: when should the request expire - either a timestamp or strtotime expression  

**Return Values**

`self`





### Response::expireCookie  

**Description**

```php
public expireCookie (string $name, string $extra)
```

Expires an existing cookie 

 

**Parameters**

* `(string) $name`
: the cookie name  
* `(string) $extra`
: optional extra params for the cookie (semicolon delimited)  

**Return Values**

`self`





### Response::hasCache  

**Description**

```php
public hasCache (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Response::noCache  

**Description**

```php
public noCache (void)
```

Prevent caching 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`self`





### Response::setBody  

**Description**

```php
public setBody (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Response::setContentTypeByExtension  

**Description**

```php
public setContentTypeByExtension (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Response::withCookie  

**Description**

```php
public withCookie (string $name, string $value, string $extra)
```

Set a cookie 

 

**Parameters**

* `(string) $name`
: the cookie name  
* `(string) $value`
: the cookie value  
* `(string) $extra`
: optional extra params for the cookie (semicolon delimited)  

**Return Values**

`self`




