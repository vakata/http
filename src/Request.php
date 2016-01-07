<?php

namespace vakata\http;

class Request extends Message implements RequestInterface
{
    protected $method = 'GET';
    protected $files = [];

    /**
     * create a request instance from the current user request
     * @method fromRequest
     * @return \vakata\http\Request      the instance
     */
    public static function fromRequest()
    {
        $req = new self();
        $http = explode('/', isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1');
        if (isset($http[1])) {
            $req->setProtocolVersion($http[1]);
        }
        $req->setMethod(strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET'));

        $req->setUrl(Url::fromRequest());

        $headers = [];
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        }
        foreach ($_SERVER as $key => $val) {
            if (strncmp($key, 'HTTP_', 5) === 0) {
                $headers[$key] = $val;
            }
        }
        foreach ($headers as $key => $value) {
            $req->setHeader($key, $value);
        }

        $req->setBody(file_get_contents('php://input'));

        if (isset($_FILES) && count($_FILES)) {
            foreach ($_FILES as $k => $v) {
                $req->addFile($k, Upload::fromRequest($k));
            }
        }
    }
    /**
     * get the HTTP verb used (GET / POST / PUT / etc), defaults to `GET`
     * @method getMethod
     * @return string    the verb
     */
    public function getMethod()
    {
        return $this->method;
    }
    /**
     * set the HTTP verb
     * @method setMethod
     * @param  string    $method the verb
     * @return  self
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }
    /**
     * get the URL instance for this request
     * @method getUrl
     * @return \vakata\http\Url the URL instance
     */
    public function getUrl()
    {
        return $this->url;
    }
    /**
     * set the URL instance associated with the request
     * @method setUrl
     * @param  \vakata\http\Url|string $url the URL for this request
     * @return  self
     */
    public function setUrl($url)
    {
        $this->url = is_string($url) ? new Url($url) : $url;
        return $this;
    }
    /**
     * add a file to be uploaded (as multipart form data)
     * @method addFile
     * @param  string  $key     the multipart name
     * @param  UploadInterface|stream|string  $content the file contents
     * @param  string  $name    the file name to submit under
     * @return  self
     */
    public function addFile($key, $content, $name = null)
    {
        $this->files[$key] = $content instanceof UploadInterface ? $content : new Upload($name, null, $content);
        return $this;
    }
    /**
     * Does an uploaded file by the specified key exist on this request.
     * @method hasFile
     * @param  string  $key the multipart name
     * @return boolean      does the file exist
     */
    public function hasFile($key)
    {
        return isset($this->files[$key]);
    }
    /**
     * Get the upload file instance for the specified key.
     * @method getFile
     * @param  string  $key the multipart name
     * @return \vakata\http\Upload       the file
     */
    public function getFile($key)
    {
        return isset($this->files[$key]) ? $this->files[$key] : null;
    }
    /**
     * Get the array of files that are about to be uploaded.
     * @method getFiles
     * @return array   an array of Upload objects
     */
    public function getFiles()
    {
        return $this->files;
    }
    /**
     * Remove a file from the request.
     * @method removeFile
     * @param  string     $key the multipart name
     * @return self
     */
    public function removeFile($key)
    {
        if (isset($this->files[$key])) {
            unset($this->files[$key]);
        }
        return $this;
    }
    /**
     * Clean all files associated with the request.
     * @method removeFiles
     * @return self
     */
    public function removeFiles() {
        $this->files = [];
        return $this;
    }

    /**
     * Get any authorization details supplied with the request.
     * @method getAuthorization
     * @return array|null           array of extracted values or null (possible keys are username, password and token)
     */
    public function getAuthorization()
    {
        if (!$this->hasHeader('Authorization')) {
            return null;
        }
        $temp = explode(' ', $this->getHeader('Authorization'));
        switch (strtolower($temp[0])) {
            case 'basic':
                $temp[1] = base64_decode($temp[1]);
                $temp[1] = explode(':', $temp[1], 2);
                return ['username' => $temp[1][0], 'password' => $temp[1][1]];
            case 'token':
            case 'oauth':
            case 'bearer':
                return ['token' => $temp[1]];
            default:
                return null;
        }
    }
    /**
     * Get the preffered response language (parses the Accept-Language header if present).
     * @method getPreferedResponseLanguage
     * @param  string                      $default the default code to return if the header is not found
     * @return string                      the prefered language code
     */
    public function getPreferedResponseLanguage($default = 'en')
    {
        $acpt = $this->getHeader('Accept-Language') ?: $default;
        $acpt = explode(',', $acpt);
        foreach ($acpt as $k => $v) {
            $v = array_pad(explode(';', $v, 2), 2, 'q=1');
            $v[1] = (float) array_pad(explode('q=', $v[1], 2), 2, '1')[1];
            $v[0] = $v[0]; //explode('-', $v[0], 2)[0];
            $v[2] = $k;
            $acpt[$k] = $v;
        }
        uasort($acpt, function ($a, $b) {
            if ($a[1] > $b[1]) {
                return -1;
            }
            if ($a[1] < $b[1]) {
                return 1;
            }
            return $b[2] > $b[1] ? -1 : 1;
        });
        return $acpt[0][0];
    }
    /**
     * Get the prefered response format.
     * @method getPreferedResponseFormat
     * @param  string                    $default the default value to return if the Accept header is not present.
     * @return string                    the desired response format
     */
    public function getPreferedResponseFormat($default = 'html')
    {
        // parse accept header (uses default instead of 406 header)
        $acpt = $this->getHeader('Accept') ?: 'application/'.$default;
        $acpt = explode(',', $acpt);
        foreach ($acpt as $k => $v) {
            $v = array_pad(explode(';', $v, 2), 2, 'q=1');
            $v[1] = (float) array_pad(explode('q=', $v[1], 2), 2, '1')[1];
            $v[0] = explode('+', array_pad(explode('/', $v[0], 2), 2, 'json')[1])[0];
            $v[2] = $k;
            $acpt[$k] = $v;
        }
        uasort($acpt, function ($a, $b) {
            if ($a[1] > $b[1]) {
                return -1;
            }
            if ($a[1] < $b[1]) {
                return 1;
            }

            return $b[2] > $b[1] ? -1 : 1;
        });
        return $acpt[0][0] == '*' ? $default : $acpt[0][0];
    }

    protected function cleanValue($value, $mode = null)
    {
        if (is_array($value)) {
            $temp = [];
            foreach ($value as $k => $v) {
                $temp[$k] = $this->cleanValue($v, $mode);
            }
            return $temp;
        }
        // normalize newlines
        if (strpos($value, "\r") !== false) {
            $value = str_replace(array("\r\n", "\r", "\r\n\n"), PHP_EOL, $value);
        }
        // remove invalid utf8 chars
        if (preg_match('/[^\x00-\x7F]/S', $value) != 0) {
            $temp = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
            if ($temp !== false) {
                $value = $temp;
            }
        }
        // remove non-printable chars
        do {
            $count = 0;
            $value = preg_replace(array('/%0[0-8bcef]/', '/%1[0-9a-f]/', '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'), '', $value, -1, $count);
        } while ($count);

        switch ($mode) {
            case 'int':
                $value = (int) $value;
                break;
            case 'float':
                $value = (float) $value;
                break;
            case 'nohtml':
                $value = strip_tags((string) $value);
                break;
            case 'escape':
                $value = htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE);
                break;
            case 'string':
                $value = (string) $value;
                break;
            case 'raw':
            default:
                $value = $value;
                break;
        }

        return $value;
    }
    protected function getValue(array $collection, $key, $default, $mode)
    {
        if ($key === null) {
            return $this->cleanValue($collection, $mode);
        }
        return isset($collection[$key]) ? $this->cleanValue($collection[$key], $mode) : $default;
    }
    public function getCookie($key = null, $default = null, $mode = null)
    {
        if (!$this->hasHeader('Cookie')) {
            return $key === null ? [] : null;
        }
        $data = explode(';', $this->getHeader('Cookie'));
        $real = [];
        foreach ($data as $v) {
            $temp = explode('=', $v, 2);
            $real[trim($temp[0])] = $temp[1];
        }
        return $this->getValue($real, $key, $default, $mode);
    }
    public function getQuery($key = null, $default = null, $mode = null)
    {
    }
    public function getPost($key = null, $default = null, $mode = null);
    public function getParam($key = null, $default = null, $mode = null);

    public function isAjax();
    public function isCors();
    public function isSecure();
    public function isSelf();

    /**
     * get the entire request as a string
     * @method __toString
     * @return string     the messsage
     */
    public function __toString()
    {
        $message = '';
        $headers = [];
        foreach ($this->headers as $k => $v)
        {
            $headers[] = $k . ': ' . $v;
        }
        $message .= implode("\r\n", $headers);
        $message .= "\r\n\r\n";
        // TODO: multipart (if files are to be uploaded)
        $message .= $this->getBody(true);
        return $message;
    }

    public function send();

    // TODO: check CSRF?
}


/*
protected function cleanValue($value, $mode = null)
{
    if (is_array($value)) {
        $temp = [];
        foreach ($value as $k => $v) {
            $temp[$k] = $this->cleanValue($v);
        }

        return $temp;
    }
    // remove magic quotes
    if (get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    // normalize newlines
    if (strpos($value, "\r") !== false) {
        $value = str_replace(array("\r\n", "\r", "\r\n\n"), PHP_EOL, $value);
    }
    // remove invalid utf8 chars
    if (preg_match('/[^\x00-\x7F]/S', $value) != 0) {
        $temp = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
        if ($temp !== false) {
            $value = $temp;
        }
    }
    // remove non-printable chars
    do {
        $count = 0;
        $value = preg_replace(array('/%0[0-8bcef]/', '/%1[0-9a-f]/', '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'), '', $value, -1, $count);
    } while ($count);

    switch ($mode) {
        case 'int':
            $value = (int) $value;
            break;
        case 'float':
            $value = (float) $value;
            break;
        case 'nohtml':
            $value = strip_tags((string) $value);
            break;
        case 'escape':
            $value = htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE);
            break;
        case 'string':
            $value = (string) $value;
            break;
        case 'raw':
        default:
            $value = $value;
            break;
    }

    return $value;
}
protected function getValue(array $collection, $key, $default, $mode)
{
    if ($key === null) {
        return $this->cleanValue($collection, $mode);
    }

    return isset($collection[$key]) ? $this->cleanValue($collection[$key], $mode) : $default;
}

public function getProtocolVersion()
{
    return $this->http;
}
public function getBody()
{
    return $this->body !== false ? $this->body : null;
}
public function getHeaders()
{
    return $this->head;
}
public function hasHeader($header)
{
    return isset($this->head[$this->cleanHeaderName($header)]);
}
public function getHeader($header)
{
    return isset($this->head[$this->cleanHeaderName($header)]) ? $this->head[$this->cleanHeaderName($header)] : null;
}
public function getMethod()
{
    return $this->meth;
}

public function getServer($key = null, $default = null, $mode = null)
{
    return $this->getValue($_SERVER, $key, $default, $mode);
}
public function getCookie($key = null, $default = null, $mode = null)
{
    return $this->getValue($_COOKIE, $key, $default, $mode);
}
public function getQuery($key = null, $default = null, $mode = null)
{
    return $this->getValue($_GET, $key, $default, $mode);
}
public function getPost($key = null, $default = null, $mode = null)
{
    $data = strpos((string) $this->getHeader('Content-Type'), 'json') !== false ? @json_decode($this->body, true) : $_POST;

    return $data !== false && $data !== null ? $this->getValue($data, $key, $default, $mode) : null;
}
public function getParam($key = null, $default = null, $mode = null)
{
    $data = [];
    if (strpos((string) $this->getHeader('Content-Type'), 'json') !== false) {
        $data = @json_decode($this->body, true);
    } else {
        @parse_str($this->body, $data);
    }

    return $data !== false && $data !== null ? $this->getValue($data, $key, $default, $mode) : null;
}
public function getRequest($key = null, $default = null, $mode = null)
{
    return $this->getValue($_REQUEST, $key, $default, $mode);
}

public function getFile($key = null)
{
    return $this->getValue($_FILES, $key);
}

public function isAjax()
{
    return ($this->getHeader('X-Requested-With') === 'XMLHttpRequest');
}
public function isCors()
{
    return ($this->hasHeader('Origin') && !$this->hasHeader('X-Requested-With') && (!isset($_SERVER['SERVER_NAME']) || !$_SERVER['SERVER_NAME'] || strpos(parse_url($this->getHeader('Origin'), PHP_URL_HOST), $_SERVER['SERVER_NAME']) === false));
}
public function isSecure()
{
    if (isset($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return true;
    }
    if (!isset($_SERVER) || !isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off') {
        return false;
    }

    return true;
}
public function isSelf()
{
    return (
        (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') ||
        (isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['SERVER_ADDR']) && $_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR'])
    );
}

public function getLanguage($default = 'en')
{
    $acpt = $this->getHeader('Accept-Language') ?: $default;
    $acpt = explode(',', $acpt);
    foreach ($acpt as $k => $v) {
        $v = array_pad(explode(';', $v, 2), 2, 'q=1');
        $v[1] = (float) array_pad(explode('q=', $v[1], 2), 2, '1')[1];
        $v[0] = $v[0]; //explode('-', $v[0], 2)[0];
        $v[2] = $k;
        $acpt[$k] = $v;
    }
    uasort($acpt, function ($a, $b) {
        if ($a[1] > $b[1]) {
            return -1;
        }
        if ($a[1] < $b[1]) {
            return 1;
        }

        return $b[2] > $b[1] ? -1 : 1;
    });

    return $acpt[0][0];
}
public function getResponseFormat($default = 'html')
{
    // parse accept header (uses default instead of 406 header)
    $acpt = $this->extn ? 'application/'.$this->extn : ($this->getHeader('Accept') ?: 'application/'.$default);
    $acpt = explode(',', $acpt);
    foreach ($acpt as $k => $v) {
        $v = array_pad(explode(';', $v, 2), 2, 'q=1');
        $v[1] = (float) array_pad(explode('q=', $v[1], 2), 2, '1')[1];
        $v[0] = explode('+', array_pad(explode('/', $v[0], 2), 2, 'json')[1])[0];
        $v[2] = $k;
        $acpt[$k] = $v;
    }
    uasort($acpt, function ($a, $b) {
        if ($a[1] > $b[1]) {
            return -1;
        }
        if ($a[1] < $b[1]) {
            return 1;
        }

        return $b[2] > $b[1] ? -1 : 1;
    });

    return $acpt[0][0] == '*' ? $default : $acpt[0][0];
}

public function checkCSRF()
{
    // csrf (allow for AJAX & CORS)
    // ajax may not be secure: http://lists.webappsec.org/pipermail/websecurity_lists.webappsec.org/2011-February/007533.html
    if (!$this->isAjax() && !$this->hasHeader('Origin')) {
        if (isset($_SESSION['_csrf_token']) && isset($_POST) && count($_POST) > 0) {
            if (!isset($_POST['_csrf_token']) || $_POST['_csrf_token'] != $_SESSION['_csrf_token']) {
                throw new \Exception('CSRF check fail', 403);
            }
            unset($_POST['_csrf_token']);
        }
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = md5(uniqid(rand(), true));
        }
    }
}
public function validateChecksum($key, $field = 'checksum', $algo = 'sha1')
{
    return base64_encode(hash_hmac($algo, explode('&'.$field, $this->body, 2)[0], $key, true)) === $this->getPost($field);
}
*/
