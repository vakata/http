# vakata\http\Response  



## Implements:
Psr\Http\Message\MessageInterface, Psr\Http\Message\ResponseInterface

## Extend:

Laminas\Diactoros\Response

## Methods

| Name | Description |
|------|-------------|
|[cacheUntil](#responsecacheuntil)|Make the response cacheable.|
|[expireCookie](#responseexpirecookie)|Expires an existing cookie|
|[getCallback](#responsegetcallback)||
|[hasCache](#responsehascache)||
|[hasCallback](#responsehascallback)||
|[noCache](#responsenocache)|Prevent caching|
|[setBody](#responsesetbody)||
|[setContentTypeByExtension](#responsesetcontenttypebyextension)||
|[withCallback](#responsewithcallback)||
|[withCookie](#responsewithcookie)|Set a cookie|

## Inherited methods

| Name | Description |
|------|-------------|
| [__construct](https://secure.php.net/manual/en/laminas\diactoros\response.__construct.php) | - |
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




<hr />


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




<hr />


### Response::getCallback  

**Description**

```php
 getCallback (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### Response::hasCache  

**Description**

```php
 hasCache (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### Response::hasCallback  

**Description**

```php
 hasCallback (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


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




<hr />


### Response::setBody  

**Description**

```php
 setBody (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### Response::setContentTypeByExtension  

**Description**

```php
 setContentTypeByExtension (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


### Response::withCallback  

**Description**

```php
 withCallback (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`


<hr />


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




<hr />

