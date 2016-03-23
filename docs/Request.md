# vakata\http\Request


## Methods

| Name | Description |
|------|-------------|
|[__construct](#vakata\http\request__construct)|Create an instance.|
|[fromRequest](#vakata\http\requestfromrequest)|create a request instance from the current user request|
|[fromString](#vakata\http\requestfromstring)|Create an instance from a stringified request.|
|[getMethod](#vakata\http\requestgetmethod)|get the HTTP verb used (GET / POST / PUT / etc), defaults to `GET`|
|[setMethod](#vakata\http\requestsetmethod)|set the HTTP verb|
|[getUrl](#vakata\http\requestgeturl)|get the URL instance for this request|
|[setUrl](#vakata\http\requestseturl)|set the URL instance associated with the request|
|[addUpload](#vakata\http\requestaddupload)|add a file to be uploaded (as multipart form data)|
|[hasUploads](#vakata\http\requesthasuploads)|Returns whether there are any files attached to the request.|
|[hasUpload](#vakata\http\requesthasupload)|Does an uploaded file by the specified key exist on this request.|
|[getUpload](#vakata\http\requestgetupload)|Get the upload file instance for the specified key.|
|[getUploads](#vakata\http\requestgetuploads)|Get the array of files that are about to be uploaded.|
|[removeUpload](#vakata\http\requestremoveupload)|Remove a file from the request.|
|[removeUploads](#vakata\http\requestremoveuploads)|Clean all files associated with the request.|
|[getAuthorization](#vakata\http\requestgetauthorization)|Get any authorization details supplied with the request.|
|[getPreferedResponseLanguage](#vakata\http\requestgetpreferedresponselanguage)|Get the preffered response language (parses the Accept-Language header if present).|
|[getPreferedResponseFormat](#vakata\http\requestgetpreferedresponseformat)|Get the prefered response format.|
|[getCookie](#vakata\http\requestgetcookie)|Gets a value from a cookie that came with the request|
|[getQuery](#vakata\http\requestgetquery)|Get a GET param from the request URL|
|[getPost](#vakata\http\requestgetpost)|Get a param from the request body (if it is in JSON format it will be parsed out as well)|
|[isAjax](#vakata\http\requestisajax)|Determine if this is an AJAX request|
|[isCors](#vakata\http\requestiscors)|Is the request AJAX from another domain|
|[__toString](#vakata\http\request__tostring)|get the entire request as a string|
|[addChecksum](#vakata\http\requestaddchecksum)|add a checksum POST param to the request body|
|[validateChecksum](#vakata\http\requestvalidatechecksum)|Validate the checksum field of the request|
|[send](#vakata\http\requestsend)|Send the request.|
|[getProtocolVersion](#vakata\http\requestgetprotocolversion)|get the current HTTP version|
|[setProtocolVersion](#vakata\http\requestsetprotocolversion)|set the HTTP version to use|
|[getHeaders](#vakata\http\requestgetheaders)|Retrieve all set headers.|
|[setHeader](#vakata\http\requestsetheader)|Add a header to the message.|
|[hasHeader](#vakata\http\requesthasheader)|Is a specific header set on the message.|
|[getHeader](#vakata\http\requestgetheader)|Retieve a header value by name.|
|[removeHeader](#vakata\http\requestremoveheader)|Remove a header from the message by name.|
|[removeHeaders](#vakata\http\requestremoveheaders)|Remove all headers from the message.|
|[getBody](#vakata\http\requestgetbody)|get the message body (as a stream resource or as a string)|
|[setBody](#vakata\http\requestsetbody)|set the message body (either set to a stream resource or a string)|

---



### vakata\http\Request::__construct
Create an instance.  


```php
public function __construct (  
    string $method,  
    string $url  
)   
```

|  | Type | Description |
|-----|-----|-----|
| `$method` | `string` | the method for the request |
| `$url` | `string` | the URL for the request |

---


### vakata\http\Request::fromRequest
create a request instance from the current user request  


```php
public static function fromRequest () : \vakata\http\Request    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | [`\vakata\http\Request`](Request.md) | the instance |

---


### vakata\http\Request::fromString
Create an instance from a stringified request.  


```php
public static function fromString (  
    string $str  
) : \vakata\http\Request    
```

|  | Type | Description |
|-----|-----|-----|
| `$str` | `string` | the stringified request |
|  |  |  |
| `return` | [`\vakata\http\Request`](Request.md) | the request instance |

---


### vakata\http\Request::getMethod
get the HTTP verb used (GET / POST / PUT / etc), defaults to `GET`  


```php
public function getMethod () : string    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string` | the verb |

---


### vakata\http\Request::setMethod
set the HTTP verb  


```php
public function setMethod (  
    string $method  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$method` | `string` | the verb |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Request::getUrl
get the URL instance for this request  


```php
public function getUrl () : \vakata\http\Url    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | [`\vakata\http\Url`](Url.md) | the URL instance |

---


### vakata\http\Request::setUrl
set the URL instance associated with the request  


```php
public function setUrl (  
    \vakata\http\Url|string $url  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$url` | `\vakata\http\Url`, `string` | the URL for this request |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Request::addUpload
add a file to be uploaded (as multipart form data)  


```php
public function addUpload (  
    string $key,  
    \UploadInterface|\stream|string $content,  
    string $name  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the multipart name |
| `$content` | `\UploadInterface`, `\stream`, `string` | the file contents |
| `$name` | `string` | the file name to submit under |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Request::hasUploads
Returns whether there are any files attached to the request.  


```php
public function hasUploads () : boolean    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `boolean` | are there are any files attached |

---


### vakata\http\Request::hasUpload
Does an uploaded file by the specified key exist on this request.  


```php
public function hasUpload (  
    string $key  
) : boolean    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the multipart name |
|  |  |  |
| `return` | `boolean` | does the file exist |

---


### vakata\http\Request::getUpload
Get the upload file instance for the specified key.  


```php
public function getUpload (  
    string $key  
) : \vakata\http\Upload    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the multipart name |
|  |  |  |
| `return` | [`\vakata\http\Upload`](Upload.md) | the file |

---


### vakata\http\Request::getUploads
Get the array of files that are about to be uploaded.  


```php
public function getUploads () : array    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `array` | an array of Upload objects |

---


### vakata\http\Request::removeUpload
Remove a file from the request.  


```php
public function removeUpload (  
    string $key  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the multipart name |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Request::removeUploads
Clean all files associated with the request.  


```php
public function removeUploads () : self    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Request::getAuthorization
Get any authorization details supplied with the request.  


```php
public function getAuthorization () : array, null    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `array`, `null` | array of extracted values or null (possible keys are username, password and token) |

---


### vakata\http\Request::getPreferedResponseLanguage
Get the preffered response language (parses the Accept-Language header if present).  


```php
public function getPreferedResponseLanguage (  
    string $default  
) : string    
```

|  | Type | Description |
|-----|-----|-----|
| `$default` | `string` | the default code to return if the header is not found |
|  |  |  |
| `return` | `string` | the prefered language code |

---


### vakata\http\Request::getPreferedResponseFormat
Get the prefered response format.  


```php
public function getPreferedResponseFormat (  
    string $default  
) : string    
```

|  | Type | Description |
|-----|-----|-----|
| `$default` | `string` | the default value to return if the Accept header is not present. |
|  |  |  |
| `return` | `string` | the desired response format |

---


### vakata\http\Request::getCookie
Gets a value from a cookie that came with the request  


```php
public function getCookie (  
    string $key,  
    string $default,  
    string $mode  
) : string    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the cookie name |
| `$default` | `string` | optional default value to return if the key is not present (default to `null`) |
| `$mode` | `string` | optional cleanup of the value, available modes are: int, float, nohtml, escape, string |
|  |  |  |
| `return` | `string` | the value |

---


### vakata\http\Request::getQuery
Get a GET param from the request URL  


```php
public function getQuery (  
    string $key,  
    string $default,  
    string $mode  
) : string    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the GET param name |
| `$default` | `string` | optional default value to return if the key is not present (default to `null`) |
| `$mode` | `string` | optional cleanup of the value, available modes are: int, float, nohtml, escape, string |
|  |  |  |
| `return` | `string` | the value |

---


### vakata\http\Request::getPost
Get a param from the request body (if it is in JSON format it will be parsed out as well)  


```php
public function getPost (  
    string $key,  
    string $default,  
    string $mode  
) : string    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the param name |
| `$default` | `string` | optional default value to return if the key is not present (default to `null`) |
| `$mode` | `string` | optional cleanup of the value, available modes are: int, float, nohtml, escape, string |
|  |  |  |
| `return` | `string` | the value |

---


### vakata\http\Request::isAjax
Determine if this is an AJAX request  


```php
public function isAjax () : boolean    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `boolean` | is the request AJAX |

---


### vakata\http\Request::isCors
Is the request AJAX from another domain  


```php
public function isCors () : boolean    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `boolean` | is this a CORS request |

---


### vakata\http\Request::__toString
get the entire request as a string  


```php
public function __toString ()   
```

|  | Type | Description |
|-----|-----|-----|

---


### vakata\http\Request::addChecksum
add a checksum POST param to the request body  


```php
public function addChecksum (  
    string $key,  
    string $field,  
    string $algo  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the secret key |
| `$field` | `string` | the POST key name, defaults to `checksum` |
| `$algo` | `string` | the algorythm to use, defaults to `sha1` |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Request::validateChecksum
Validate the checksum field of the request  


```php
public function validateChecksum (  
    string $key,  
    string $field,  
    string $algo  
) : boolean    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the secret key |
| `$field` | `string` | the POST field name, defaults to `checksum` |
| `$algo` | `string` | the algorithm to use, defaults to `sha1` |
|  |  |  |
| `return` | `boolean` | is the checksum valid |

---


### vakata\http\Request::send
Send the request.  


```php
public function send (  
    boolean $closeConnection  
) : \vakata\http\Response    
```

|  | Type | Description |
|-----|-----|-----|
| `$closeConnection` | `boolean` | should a "Connection: close" header be added (defaults to true) |
|  |  |  |
| `return` | [`\vakata\http\Response`](Response.md) | the response. |

---


### vakata\http\Request::getProtocolVersion
get the current HTTP version  


```php
public function getProtocolVersion () : string    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string` | the protocol version |

---


### vakata\http\Request::setProtocolVersion
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


### vakata\http\Request::getHeaders
Retrieve all set headers.  


```php
public function getHeaders () : array    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `array` | all headers of the message |

---


### vakata\http\Request::setHeader
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


### vakata\http\Request::hasHeader
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


### vakata\http\Request::getHeader
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


### vakata\http\Request::removeHeader
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


### vakata\http\Request::removeHeaders
Remove all headers from the message.  


```php
public function removeHeaders () : self    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Request::getBody
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


### vakata\http\Request::setBody
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

