# vakata\http\Upload
A class representing uploaded files in an HTML multipart request.

## Methods

| Name | Description |
|------|-------------|
|[__construct](#vakata\http\upload__construct)|Create an instance|
|[fromRequest](#vakata\http\uploadfromrequest)|Create an instance from the current client request|
|[hasSize](#vakata\http\uploadhassize)|Returns whether the upload has a defined size.|
|[getSize](#vakata\http\uploadgetsize)|Get the size of the file.|
|[setSize](#vakata\http\uploadsetsize)|Set the file size.|
|[getName](#vakata\http\uploadgetname)|Get the name of the file.|
|[setName](#vakata\http\uploadsetname)|Set the file name.|
|[getPath](#vakata\http\uploadgetpath)|Get the file path (if available)|
|[setPath](#vakata\http\uploadsetpath)|Set the file path (which also updates the body of the upload)|
|[getBody](#vakata\http\uploadgetbody)|Get the file body (as a string or as a stream resource)|
|[setBody](#vakata\http\uploadsetbody)|Set the upload file body (either set to a stream resource or a string).|
|[saveAs](#vakata\http\uploadsaveas)|Save the file to a location.|
|[appendTo](#vakata\http\uploadappendto)|Append the file to a location.|

---



### vakata\http\Upload::__construct
Create an instance  


```php
public function __construct (  
    string $name,  
    string $path,  
    \stream|string $body  
)   
```

|  | Type | Description |
|-----|-----|-----|
| `$name` | `string` | the file name (optional - defaults to an empty string) |
| `$path` | `string` | the path to the file (if it is a file on the filesystem) |
| `$body` | `\stream`, `string` | the contents of the file - optional (either a stream resource or string) |

---


### vakata\http\Upload::fromRequest
Create an instance from the current client request  


```php
public static function fromRequest (  
    string $key  
) : \Upload    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the key in the $_FILES array |
|  |  |  |
| `return` | `\Upload` | the instance |

---


### vakata\http\Upload::hasSize
Returns whether the upload has a defined size.  


```php
public function hasSize () : boolean    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `boolean` | whether the upload has a defined size |

---


### vakata\http\Upload::getSize
Get the size of the file.  


```php
public function getSize () : string    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string` | the size in bytes |

---


### vakata\http\Upload::setSize
Set the file size.  


```php
public function setSize (  
    string $size  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$size` | `string` | the size |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Upload::getName
Get the name of the file.  


```php
public function getName () : string    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string` | the name |

---


### vakata\http\Upload::setName
Set the file name.  


```php
public function setName (  
    string $name  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$name` | `string` | the name |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Upload::getPath
Get the file path (if available)  


```php
public function getPath () : string    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `string` | the path |

---


### vakata\http\Upload::setPath
Set the file path (which also updates the body of the upload)  


```php
public function setPath (  
    string $path  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$path` | `string` | the path |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Upload::getBody
Get the file body (as a string or as a stream resource)  


```php
public function getBody (  
    boolean $asString  
) : \stream, string    
```

|  | Type | Description |
|-----|-----|-----|
| `$asString` | `boolean` | should a string be returned (defaults to `false`) |
|  |  |  |
| `return` | `\stream`, `string` | the body of the file |

---


### vakata\http\Upload::setBody
Set the upload file body (either set to a stream resource or a string).  


```php
public function setBody (  
    \stream|string $body  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$body` | `\stream`, `string` | the body to use |
|  |  |  |
| `return` | `self` |  |

---


### vakata\http\Upload::saveAs
Save the file to a location.  


```php
public function saveAs (  
    string $dest  
) : bool    
```

|  | Type | Description |
|-----|-----|-----|
| `$dest` | `string` | the destination (a file system path) |
|  |  |  |
| `return` | `bool` | was the file saved |

---


### vakata\http\Upload::appendTo
Append the file to a location.  


```php
public function appendTo (  
    string $dest  
) : bool    
```

|  | Type | Description |
|-----|-----|-----|
| `$dest` | `string` | the destination (a file system path) |
|  |  |  |
| `return` | `bool` | was the file saved |

---

