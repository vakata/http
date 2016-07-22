# vakata\http\Message
An abstract class representing an HTTP message (either a request or a response)

## Methods

| Name | Description |
|------|-------------|
|[getProtocolVersion](#vakata\http\messagegetprotocolversion)|get the current HTTP version|
|[setProtocolVersion](#vakata\http\messagesetprotocolversion)|set the HTTP version to use|
|[getHeaders](#vakata\http\messagegetheaders)|Retrieve all set headers.|
|[setHeader](#vakata\http\messagesetheader)|Add a header to the message.|
|[hasHeader](#vakata\http\messagehasheader)|Is a specific header set on the message.|
|[getHeader](#vakata\http\messagegetheader)|Retieve a header value by name.|
|[removeHeader](#vakata\http\messageremoveheader)|Remove a header from the message by name.|
|[removeHeaders](#vakata\http\messageremoveheaders)|Remove all headers from the message.|
|[getBody](#vakata\http\messagegetbody)|get the message body (as a stream resource or as a string)|
|[setBody](#vakata\http\messagesetbody)|set the message body (either set to a stream resource or a string)|

---



### vakata\http\Message::getProtocolVersion
get the current HTTP version  


```php
public function getProtocolVersion () : string    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string` | the protocol version |

---


### vakata\http\Message::setProtocolVersion
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


### vakata\http\Message::getHeaders
Retrieve all set headers.  


```php
public function getHeaders () : array    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `array` | all headers of the message |

---


### vakata\http\Message::setHeader
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


### vakata\http\Message::hasHeader
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


### vakata\http\Message::getHeader
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


### vakata\http\Message::removeHeader
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


### vakata\http\Message::removeHeaders
Remove all headers from the message.  


```php
public function removeHeaders () : self    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Message::getBody
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


### vakata\http\Message::setBody
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

