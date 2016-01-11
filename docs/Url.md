# vakata\http\Url
A class representing an URL

## Methods

| Name | Description |
|------|-------------|
|[__construct](#vakata\http\url__construct)|Create an instance.|
|[fromRequest](#vakata\http\urlfromrequest)|Create an instance from the current request.|
|[getScheme](#vakata\http\urlgetscheme)|Get the URL scheme (http, https, etc), defaults to `"http"`.|
|[setScheme](#vakata\http\urlsetscheme)|Set the scheme.|
|[getHost](#vakata\http\urlgethost)|Get the host part of the URL (for example - google.com), defaults to `"localhost"`.|
|[setHost](#vakata\http\urlsethost)|Set the host part of the URL.|
|[getPort](#vakata\http\urlgetport)|Get the port, if a standart port is used this will return `null`.|
|[setPort](#vakata\http\urlsetport)|Set the port of the URL.|
|[getUser](#vakata\http\urlgetuser)|Get the user part of the URL (if supplied in the form scheme://user:pass@domain.tld/).|
|[setUser](#vakata\http\urlsetuser)|Set the user part of the URL.|
|[getPass](#vakata\http\urlgetpass)|Get the password part of the URL (if supplied in the form scheme://user:pass@domain.tld/).|
|[setPass](#vakata\http\urlsetpass)|Set the password part of the URL.|
|[getPath](#vakata\http\urlgetpath)|Returns the path part of the URL.|
|[setPath](#vakata\http\urlsetpath)|Set the path part of the URL.|
|[getExtension](#vakata\http\urlgetextension)|get the extension part of the URL (like: html, gif, jpg)|
|[getQuery](#vakata\http\urlgetquery)|get the query part of the URL (after the question mark)|
|[setQuery](#vakata\http\urlsetquery)|Set the query part og the URL.|
|[getFragment](#vakata\http\urlgetfragment)|Get the fragment part of the URL (after the hash sign).|
|[setFragment](#vakata\http\urlsetfragment)|Set the fragment part of the URL|
|[getSegments](#vakata\http\urlgetsegments)|get the path part of the URL as an array (the path string exploded by `/`)|
|[getSegment](#vakata\http\urlgetsegment)|get a specific segment from the path part of the URL|
|[__toString](#vakata\http\url__tostring)|get the entire URL back as a string|
|[linkTo](#vakata\http\urllinkto)|get a link from the current URL to another one|
|[linkFrom](#vakata\http\urllinkfrom)|get a link to the current URL from another one|

---



### vakata\http\Url::__construct
Create an instance.  


```php
public function __construct (  
    string $url  
)   
```

|  | Type | Description |
|-----|-----|-----|
| `$url` | `string` | the URL to parse |

---


### vakata\http\Url::fromRequest
Create an instance from the current request.  


```php
public static function fromRequest () : \vakata\http\Url    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | [`\vakata\http\Url`](Url.md) | the URL instance of the current request |

---


### vakata\http\Url::getScheme
Get the URL scheme (http, https, etc), defaults to `"http"`.  


```php
public function getScheme () : string    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string` | the scheme |

---


### vakata\http\Url::setScheme
Set the scheme.  


```php
public function setScheme (  
    string $scheme  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$scheme` | `string` | the new scheme |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Url::getHost
Get the host part of the URL (for example - google.com), defaults to `"localhost"`.  


```php
public function getHost () : string    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string` | the host |

---


### vakata\http\Url::setHost
Set the host part of the URL.  


```php
public function setHost (  
    string $host  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$host` | `string` | the new host |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Url::getPort
Get the port, if a standart port is used this will return `null`.  


```php
public function getPort () : string, null    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string`, `null` | the port |

---


### vakata\http\Url::setPort
Set the port of the URL.  


```php
public function setPort (  
    string|int $port  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$port` | `string`, `int` | the new port |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Url::getUser
Get the user part of the URL (if supplied in the form scheme://user:pass@domain.tld/).  


```php
public function getUser () : string, null    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string`, `null` | the username |

---


### vakata\http\Url::setUser
Set the user part of the URL.  


```php
public function setUser (  
    string $user  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$user` | `string` | the new user |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Url::getPass
Get the password part of the URL (if supplied in the form scheme://user:pass@domain.tld/).  


```php
public function getPass () : string, null    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string`, `null` | the password |

---


### vakata\http\Url::setPass
Set the password part of the URL.  


```php
public function setPass (  
    string $pass  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$pass` | `string` | the new password |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Url::getPath
Returns the path part of the URL.  


```php
public function getPath (  
    boolean $ext  
) : string    
```

|  | Type | Description |
|-----|-----|-----|
| `$ext` | `boolean` | should the extension (for example .html) be returned (if any), defaults to `true` |
|  |  |  |
| `return` | `string` | the path |

---


### vakata\http\Url::setPath
Set the path part of the URL.  


```php
public function setPath (  
    string $path  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$path` | `string` | the new path |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Url::getExtension
get the extension part of the URL (like: html, gif, jpg)  


```php
public function getExtension (  
    string $default  
) : string, null    
```

|  | Type | Description |
|-----|-----|-----|
| `$default` | `string` | the default to use if the URL does not have an extension (optional) |
|  |  |  |
| `return` | `string`, `null` | the extenstion |

---


### vakata\http\Url::getQuery
get the query part of the URL (after the question mark)  


```php
public function getQuery () : string    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string` | the query |

---


### vakata\http\Url::setQuery
Set the query part og the URL.  


```php
public function setQuery (  
    string $query  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$query` | `string` | the new query |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Url::getFragment
Get the fragment part of the URL (after the hash sign).  


```php
public function getFragment () : string    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string` | the fragment |

---


### vakata\http\Url::setFragment
Set the fragment part of the URL  


```php
public function setFragment (  
    string $fragment  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$fragment` | `string` | the new fragment |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Url::getSegments
get the path part of the URL as an array (the path string exploded by `/`)  


```php
public function getSegments () : array    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `array` | the path segments |

---


### vakata\http\Url::getSegment
get a specific segment from the path part of the URL  


```php
public function getSegment (  
    integer $i,  
    boolean $ext  
) : string, null    
```

|  | Type | Description |
|-----|-----|-----|
| `$i` | `integer` | the index of the segment (can be negative too) |
| `$ext` | `boolean` | should the extension be included (if the segment is the last one), defaults to `true` |
|  |  |  |
| `return` | `string`, `null` | the segment (or null if the index is invalid) |

---


### vakata\http\Url::__toString
get the entire URL back as a string  


```php
public function __toString ()   
```

|  | Type | Description |
|-----|-----|-----|

---


### vakata\http\Url::linkTo
get a link from the current URL to another one  


```php
public function linkTo (  
    \UrlInterface|string $url,  
    boolean $forceAbsolute  
) : string    
```

|  | Type | Description |
|-----|-----|-----|
| `$url` | `\UrlInterface`, `string` | the URL to link to |
| `$forceAbsolute` | `boolean` | should an absolute path be used, defaults to `true`) |
|  |  |  |
| `return` | `string` | the link |

---


### vakata\http\Url::linkFrom
get a link to the current URL from another one  


```php
public function linkFrom (  
    \UrlInterface|string $url,  
    boolean $forceAbsolute  
) : string    
```

|  | Type | Description |
|-----|-----|-----|
| `$url` | `\UrlInterface`, `string` | the URL to link from |
| `$forceAbsolute` | `boolean` | should an absolute path be used, defaults to `true` |
|  |  |  |
| `return` | `string` | the link |

---

