# vakata\http\Response
A class representing an HTTP response.

## Methods

| Name | Description |
|------|-------------|
|[__construct](#vakata\http\response__construct)|Create an instance.|
|[fromStream](#vakata\http\responsefromstream)|Create an instance from a stream resource.|
|[fromFile](#vakata\http\responsefromfile)|Create an instance from a file.|
|[fromString](#vakata\http\responsefromstring)|Create an instance from an input string.|
|[setHeader](#vakata\http\responsesetheader)|Add a header to the message.|
|[getStatusCode](#vakata\http\responsegetstatuscode)|get the currently set status code|
|[setStatusCode](#vakata\http\responsesetstatuscode)|sets the status code|
|[setContentTypeByExtension](#vakata\http\responsesetcontenttypebyextension)|Set the Content-Type header by using a file extension.|
|[cacheUntil](#vakata\http\responsecacheuntil)|Make the response cacheable.|
|[enableCors](#vakata\http\responseenablecors)|Enable CORS|
|[__toString](#vakata\http\response__tostring)|get the entire response as a string|
|[send](#vakata\http\responsesend)|Send the response to the client.|
|[getProtocolVersion](#vakata\http\responsegetprotocolversion)|get the current HTTP version|
|[setProtocolVersion](#vakata\http\responsesetprotocolversion)|set the HTTP version to use|
|[getHeaders](#vakata\http\responsegetheaders)|Retrieve all set headers.|
|[hasHeader](#vakata\http\responsehasheader)|Is a specific header set on the message.|
|[getHeader](#vakata\http\responsegetheader)|Retieve a header value by name.|
|[removeHeader](#vakata\http\responseremoveheader)|Remove a header from the message by name.|
|[removeHeaders](#vakata\http\responseremoveheaders)|Remove all headers from the message.|
|[getBody](#vakata\http\responsegetbody)|get the message body (as a stream resource or as a string)|
|[setBody](#vakata\http\responsesetbody)|set the message body (either set to a stream resource or a string)|

---



### vakata\http\Response::__construct
Create an instance.  


```php
public function __construct (  
    integer $status  
)   
```

|  | Type | Description |
|-----|-----|-----|
| `$status` | `integer` | the status code to use |

---


### vakata\http\Response::fromStream
Create an instance from a stream resource.  


```php
public static function fromStream (  
    \stream $stream,  
    string $name  
) : \vakata\http\Response    
```

|  | Type | Description |
|-----|-----|-----|
| `$stream` | `\stream` | a stream resource |
| `$name` | `string` | optional name to serve the file with |
|  |  |  |
| `return` | [`\vakata\http\Response`](Response.md) | the response instance |

---


### vakata\http\Response::fromFile
Create an instance from a file.  


```php
public static function fromFile (  
    string $file,  
    string $name,  
    string $hash,  
    string $cached  
) : \vakata\http\Response    
```

|  | Type | Description |
|-----|-----|-----|
| `$file` | `string` | a path to a file |
| `$name` | `string` | optional name to serve the file with |
| `$hash` | `string` | optional string to use as ETag |
| `$cached` | `string` | optional strtotime expression used for caching validity |
|  |  |  |
| `return` | [`\vakata\http\Response`](Response.md) | the response instance |

---


### vakata\http\Response::fromString
Create an instance from an input string.  


```php
public static function fromString (  
    string $str  
) : \vakata\http\Response    
```

|  | Type | Description |
|-----|-----|-----|
| `$str` | `string` | the stringified response |
|  |  |  |
| `return` | [`\vakata\http\Response`](Response.md) | the response instance |

---


### vakata\http\Response::setHeader
Add a header to the message.  


```php
public function setHeader (  
    string $header,  
    string $value  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$header` | `string` | the header name |
| `$value` | `string` | the header value |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Response::getStatusCode
get the currently set status code  


```php
public function getStatusCode () : integer    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `integer` | the status code |

---


### vakata\http\Response::setStatusCode
sets the status code  


```php
public function setStatusCode (  
    integer $code,  
    string $reason  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$code` | `integer` | the new status code |
| `$reason` | `string` | optional reason, if not set the default will be used |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Response::setContentTypeByExtension
Set the Content-Type header by using a file extension.  


```php
public function setContentTypeByExtension (  
    string $type  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$type` | `string` | the extension |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Response::cacheUntil
Make the response cacheable.  


```php
public function cacheUntil (  
    int|string $expires  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$expires` | `int`, `string` | when should the request expire - either a timestamp or strtotime expression |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Response::enableCors
Enable CORS  


```php
public function enableCors (  
    string $origin,  
    string $creds,  
    integer $age,  
    array $methods,  
    array $headers  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$origin` | `string` | the host to allow CORS for, defaults to `'*'` |
| `$creds` | `string` | are credentials allowed, defaults to `false` |
| `$age` | `integer` | the max age, defaults to `3600` |
| `$methods` | `array` | allowed methods, defaults to all |
| `$headers` | `array` | allowed headers, defaults to `['Authorization']` |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Response::__toString
get the entire response as a string  


```php
public function __toString ()   
```

|  | Type | Description |
|-----|-----|-----|

---


### vakata\http\Response::send
Send the response to the client.  


```php
public function send (  
    \RequestInterface|null $req  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$req` | `\RequestInterface`, `null` | optional request object that triggered this response |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Response::getProtocolVersion
get the current HTTP version  


```php
public function getProtocolVersion () : string    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string` | the protocol version |

---


### vakata\http\Response::setProtocolVersion
set the HTTP version to use  


```php
public function setProtocolVersion (  
    string $version  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$version` | `string` | the HTTP version to use |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Response::getHeaders
Retrieve all set headers.  


```php
public function getHeaders () : array    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `array` | all headers of the message |

---


### vakata\http\Response::hasHeader
Is a specific header set on the message.  


```php
public function hasHeader (  
    string $header  
) : boolean    
```

|  | Type | Description |
|-----|-----|-----|
| `$header` | `string` | the header name |
|  |  |  |
| `return` | `boolean` |  |

---


### vakata\http\Response::getHeader
Retieve a header value by name.  


```php
public function getHeader (  
    string $header  
) : string    
```

|  | Type | Description |
|-----|-----|-----|
| `$header` | `string` | the header name |
|  |  |  |
| `return` | `string` | the header value |

---


### vakata\http\Response::removeHeader
Remove a header from the message by name.  


```php
public function removeHeader (  
    string $header  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$header` | `string` | the header name |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Response::removeHeaders
Remove all headers from the message.  


```php
public function removeHeaders () : self    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Response::getBody
get the message body (as a stream resource or as a string)  


```php
public function getBody (  
    boolean $asString  
) : mixed    
```

|  | Type | Description |
|-----|-----|-----|
| `$asString` | `boolean` | should the content be returned as a string (defaults to `false`) |
|  |  |  |
| `return` | `mixed` | the body |

---


### vakata\http\Response::setBody
set the message body (either set to a stream resource or a string)  


```php
public function setBody (  
    mixed $body  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$body` | `mixed` | the body to use |
|  |  |  |
| `return` | `self` |  |

---

