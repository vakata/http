<?php

namespace vakata\http;

class Request extends Message implements RequestInterface
{
    protected $method = 'GET';
    protected $files = [];
    protected $url = null;
    protected $cacheGet = null;
    protected $cachePost = null;
    protected $cacheCookie = null;
    protected $senderIP = '0';
    protected $senderPort = '0';

    /**
     * Create an instance.
     * @param  string      $method the method for the request
     * @param  string      $url    the URL for the request
     */
    public function __construct($method = 'GET', $url = null)
    {
        $this->setMethod($method);
        $this->setUrl($url);
    }

    /**
     * create a request instance from the current user request
     * @return \vakata\http\Request      the instance
     * @codeCoverageIgnore
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
        if(isset($_SERVER['CONTENT_TYPE'])) {
            $headers['CONTENT_TYPE'] = $_SERVER['CONTENT_TYPE'];
        }
        if(isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['CONTENT_LENGTH'] = $_SERVER['CONTENT_LENGTH'];
        }

        foreach ($headers as $key => $value) {
            if ($req->cleanHeaderName($key) === 'Authorization' && $value === '') {
                continue;
            }
            $req->setHeader($key, $value);
        }
        if (strpos(strtolower($req->getHeader('Content-Type')), 'multipart/') === 0) {
            $req->setBody(http_build_query($_POST));
        } else {
            $req->setBody(file_get_contents('php://input'));
        }

        if (isset($_FILES) && count($_FILES)) {
            foreach (array_keys($_FILES) as $k) {
                $req->addUpload($k, Upload::fromRequest($k));
            }
        }

        // determine sender IP
        $ip = '0';
        // TODO: check if remote_addr is a cloudflare one and only then read the connecting ip
        // https://www.cloudflare.com/ips-v4
        // https://www.cloudflare.com/ips-v6
        if (false && isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        elseif (isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        if (strpos($ip, ',') !== false) {
            $ip = @end(explode(',', $ip));
        }
        $ip = trim($ip);
        if (false === ($ip = filter_var($ip, FILTER_VALIDATE_IP))) {
            $ip = '0';
        }
        $req->setSenderIP($ip);
        $req->setSenderPort((int)$_SERVER['REMOTE_PORT']);

        return $req;
    }
    /**
     * Create an instance from a stringified request.
     * @param  string     $str the stringified request
     * @return \vakata\http\Request          the request instance
     * @codeCoverageIgnore
     */
    public static function fromString($str)
    {
        $req = new self();
        $break = strpos($str, "\r\n\r\n") === false ? "\n" : "\r\n"; // just in case someone breaks RFC 2616
        list($headers, $message) = explode($break . $break, $str, 2);
        $headers = explode($break, preg_replace("(" . $break . "\s+)", " ", $headers));
        if (isset($headers[0]) && strlen($headers[0])) {
            $temp = explode(' ', $headers[0]);
            if (in_array($temp[0], ['GET', 'POST', 'HEAD', 'PATCH', 'PUT', 'OPTIONS', 'TRACE', 'DELETE'])) {
                $req->setMethod($temp[0]);
                $req->setUrl($temp[1]);
                if (isset($temp[2])) {
                    $req->setProtocolVersion(substr($temp[2], 5));
                }
                unset($headers[0]);
                $headers = array_values($headers);
            }
        }
        foreach (array_filter($headers) as $v) {
            $v = explode(':', $v, 2);
            $req->setHeader(trim($v[0]), trim($v[1]));
        }
        if ($req->hasHeader('Host')) {
            $host = explode(':', $req->getHeader('Host'), 2);
            $req->getUrl()->setHost($host[0]);
            if (isset($host[1]) && (int)$host[1]) {
                $req->getUrl()->setPort($host[1]);
            }
        }

        if (strpos($req->getHeader('Content-Type'), 'multipart') !== false) {
            $bndr = trim(explode(' boundary=', $req->getHeader('Content-Type'))[1], '"');
            $parts = explode($break . '--' . $bndr, $break . $message);
            if (count($parts) == 1) {
                $req->setBody($message);
            } else {
                array_pop($parts);
                array_shift($parts);
                $post = [];
                foreach ($parts as $item) {
                    list($head, $body) = explode($break . $break, $item, 2);
                    $head = explode($break, preg_replace("(" . $break . "\s+)", " ", $head));
                    foreach ($head as $h) {
                        if (strpos(strtolower($h), 'content-disposition') === 0) {
                            $cd = explode(';', $h);
                            $name = '';
                            $file = '';
                            foreach ($cd as $p) {
                                if (strpos(trim($p), 'name=') === 0) {
                                    $name = trim(explode('name=', $p)[1], ' "');
                                }
                                if (strpos(trim($p), 'filename=') === 0) {
                                    $file = trim(explode('filename=', $p)[1], ' "');
                                }
                            }
                            if ($file) {
                                $req->addUpload($name, $body, $file);
                            } else {
                                $post[$name] = $body;
                            }
                        }
                    }
                }
                $req->setBody(http_build_query($post));
            }
        } elseif (strlen($message)) {
            $req->setBody($message);
        }
        $req->removeHeader('Content-Length');
        $req->removeHeader('Transfer-Encoding');
        return $req;
    }


    /**
     * get the HTTP verb used (GET / POST / PUT / etc), defaults to `GET`
     * @return string    the verb
     */
    public function getMethod()
    {
        return $this->method;
    }
    /**
     * set the HTTP verb
     * @param  string    $method the verb
     * @return  self
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }
    /**
     * get the port of the sender
     * @return string    the sender's port
     */
    public function getSenderPort()
    {
        return $this->senderPort;
    }
    /**
     * set the port of the sender (useful when you are the sender and want a specific outgoing port when calling send)
     * @param  string|int    $port the sender's port
     * @return  self
     */
    public function setSenderPort($port)
    {
        $this->senderPort = (string)$port;
        return $this;
    }
    /**
     * get the IP address of the sender
     * @return string    the sender's IP
     */
    public function getSenderIP()
    {
        return $this->senderIP;
    }
    /**
     * set the IP address of the sender (useful if you are the sender and want a specific outgoing ip when calling send)
     * @param  string    $ip the sender's IP
     * @return  self
     */
    public function setSenderIP($ip)
    {
        $this->senderIP = $ip;
        return $this;
    }
    /**
     * get the URL instance for this request
     * @return \vakata\http\Url the URL instance
     */
    public function getUrl()
    {
        $this->cacheGet = null;
        return $this->url;
    }
    /**
     * set the URL instance associated with the request
     * @param  \vakata\http\Url|string $url the URL for this request
     * @return  self
     */
    public function setUrl($url)
    {
        $this->url = is_string($url) ? new Url($url) : $url;
        $this->cacheGet = null;
        return $this;
    }
    /**
     * set the message body (either set to a stream resource or a string)
     * @param  mixed  $body the body to use
     * @return self
     */
    public function setBody($body)
    {
        $this->cachePost = null;
        return parent::setBody($body);
    }
    /**
     * Add a header to the message.
     * @param  string    $header the header name
     * @param  string    $value  the header value
     * @return  self
     */
    public function setHeader($header, $value)
    {
        if ($this->cleanHeaderName($header) === 'Cookie') {
            $this->cacheCookie = null;
        }
        return parent::setHeader($header, $value);
    }
    /**
     * add a file to be uploaded (as multipart form data)
     * @param  string  $key     the multipart name
     * @param  UploadInterface|resource|string  $content the file contents
     * @param  string  $name    the file name to submit under
     * @return  self
     */
    public function addUpload($key, $content, $name = null)
    {
        $this->files[$key] = $content instanceof UploadInterface ? $content : new Upload($name, null, $content);
        return $this;
    }
    /**
     * Returns whether there are any files attached to the request.
     * @return boolean      are there are any files attached
     */
    public function hasUploads()
    {
        return count($this->files) > 0;
    }
    /**
     * Does an uploaded file by the specified key exist on this request.
     * @param  string  $key the multipart name
     * @return boolean      does the file exist
     */
    public function hasUpload($key)
    {
        return isset($this->files[$key]);
    }
    /**
     * Get the upload file instance for the specified key.
     * @param  string  $key the multipart name
     * @return \vakata\http\Upload       the file
     */
    public function getUpload($key)
    {
        return isset($this->files[$key]) ? $this->files[$key] : null;
    }
    /**
     * Get the array of files that are about to be uploaded.
     * @return array   an array of Upload objects
     */
    public function getUploads()
    {
        return $this->files;
    }
    /**
     * Remove a file from the request.
     * @param  string     $key the multipart name
     * @return self
     */
    public function removeUpload($key)
    {
        if (isset($this->files[$key])) {
            unset($this->files[$key]);
        }
        return $this;
    }
    /**
     * Clean all files associated with the request.
     * @return self
     */
    public function removeUploads()
    {
        $this->files = [];
        return $this;
    }

    /**
     * Get any authorization details supplied with the request.
     * @return array|null           array of extracted values or null (possible keys are username, password and token)
     */
    public function getAuthorization()
    {
        if (!$this->hasHeader('Authorization')) {
            return null;
        }
        $temp = explode(' ', trim($this->getHeader('Authorization')), 2);
        switch (strtolower($temp[0])) {
            case 'basic':
                $temp[1] = base64_decode($temp[1]);
                $temp[1] = explode(':', $temp[1], 2);
                return ['username' => $temp[1][0], 'password' => $temp[1][1] ?? null];
            case 'token':
            case 'oauth':
            case 'bearer':
                return ['token' => $temp[1] ?? null];
            default:
                return null;
        }
    }
    /**
     * Get any authorization details supplied with the request.
     * @return string           the token (or an empty string)
     */
    public function getAuthorizationToken()
    {
        $temp = $this->getAuthorization();
        return isset($temp['token']) ? $temp['token'] : null;
    }
    /**
     * Get any authorization details supplied with the request.
     * @param  string $token    the token to set
     * @param  string $method   the keyword (defaults to `token`)
     * @return self
     */
    public function setAuthorizationToken($token, $method = 'token')
    {
        return $this->setHeader('Authorization', $method . ' ' . $token);
    }
    /**
     * Get the preffered response language (parses the Accept-Language header if present).
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
        usort($acpt, function ($a, $b) {
            if ($a[1] > $b[1]) {
                return -1;
            }
            if ($a[1] < $b[1]) {
                return 1;
            }
            return $a[2] < $b[2] ? -1 : 1;
        });
        return $acpt[0][0];
    }
    /**
     * Get the prefered response format.
     * @param  string                    $default the default value to return if the Accept header is not present.
     * @return string                    the desired response format
     */
    public function getPreferedResponseFormat($default = 'text/html')
    {
        // parse accept header (uses default instead of 406 header)
        $acpt = $this->getHeader('Accept') ?: $default;
        $acpt = explode(',', $acpt);
        foreach ($acpt as $k => $v) {
            $v = array_pad(explode(';', $v, 2), 2, 'q=1');
            $v[1] = (float) array_pad(explode('q=', $v[1], 2), 2, '1')[1];
            $v[0] = $v[0];
            $v[2] = $k;
            $acpt[$k] = $v;
        }
        usort($acpt, function ($a, $b) {
            if ($a[1] > $b[1]) {
                return -1;
            }
            if ($a[1] < $b[1]) {
                return 1;
            }

            return $a[2] < $b[2] ? -1 : 1;
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
        if (strpos((string)$value, "\r") !== false) {
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
            $value = preg_replace(['/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'], '', $value, -1, $count);
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
    /**
     * Gets a value from a cookie that came with the request
     * @param  string    $key     the cookie name
     * @param  string    $default optional default value to return if the key is not present (default to `null`)
     * @param  string    $mode    optional cleanup of the value, available modes are: int, float, nohtml, escape, string
     * @return string|array             the value (or values)
     */
    public function getCookie($key = null, $default = null, $mode = null)
    {
        if (!$this->hasHeader('Cookie')) {
            return $key === null ? [] : $default;
        }
        if (!$this->cacheCookie) {
            $data = explode(';', $this->getHeader('Cookie'));
            $real = [];
            foreach ($data as $v) {
                $temp = explode('=', $v, 2);
                $real[trim($temp[0])] = urldecode($temp[1]);
            }
            $this->cacheCookie = $real;
        }
        return $this->getValue($this->cacheCookie, $key, $default, $mode);
    }
    /**
     * Get a GET param from the request URL
     * @param  string   $key     the GET param name
     * @param  string   $default optional default value to return if the key is not present (default to `null`)
     * @param  string   $mode    optional cleanup of the value, available modes are: int, float, nohtml, escape, string
     * @return string|array             the value (or values)
     */
    public function getQuery($key = null, $default = null, $mode = null)
    {
        if (!$this->url) {
            return $key === null ? [] : $default;
        }
        if (!$this->cacheGet) {
            $data = [];
            //parse_str($this->url->getQuery(), $data);
            $temp = (string)$this->url->getQuery();
            $temp = strlen($temp) ? explode('&', $temp) : [];
            foreach ($temp as $var) {
                $var = explode('=', $var, 2);
                $name = urldecode($var[0]);
                $value = isset($var[1]) ? urldecode($var[1]) : '';
                $name = explode(']', str_replace(['][', '['], ']', $name));
                $name = count($name) > 1 ? array_slice($name, 0, -1) : $name;

                $tmp = &$data;
                foreach ($name as $k) {
                    if ($k === "") {
                        continue;
                    }
                    if (!isset($tmp[$k])) {
                        $tmp[$k] = [];
                    }
                    $tmp = &$tmp[$k];
                }
                if ($name[count($name) - 1] == '') {
                    $tmp[] = $value;
                } else {
                    $tmp = $value;
                }
            }
            $this->cacheGet = $data;
        }

        return $this->getValue($this->cacheGet, $key, $default, $mode);
    }
    /**
     * Get a param from the request body (if it is in JSON format it will be parsed out as well)
     * @param  string   $key     the param name
     * @param  string   $default optional default value to return if the key is not present (default to `null`)
     * @param  string   $mode    optional cleanup of the value, available modes are: int, float, nohtml, escape, string
     * @return string|array             the value (or values if no key was specified)
     */
    public function getPost($key = null, $default = null, $mode = null)
    {
        if (!$this->cachePost) {
            $data = $this->getBody(true);
            if (!$data) {
                return $key === null ? [] : $default;
            }
            $real = [];
            if ($this->hasHeader('Content-Type') && strpos($this->getHeader('Content-Type'), 'json') !== false) {
                $real = json_decode($data, true);
            } else {
                parse_str($data, $real);
            }
            $this->cachePost = $real;
        }
        return $this->getValue($this->cachePost, $key, $default, $mode);
    }
    /**
     * Determine if this is an AJAX request
     * @return boolean is the request AJAX
     */
    public function isAjax()
    {
        return ($this->getHeader('X-Requested-With') === 'XMLHttpRequest');
    }
    /**
     * Is the request AJAX from another domain
     * @return boolean is this a CORS request
     */
    public function isCors()
    {
        return (
            $this->hasHeader('Origin') &&
            (
                !$this->url->getHost() ||
                strtolower(parse_url($this->getHeader('Origin'), PHP_URL_SCHEME)) !== strtolower($this->url->getScheme()) ||
                strpos(parse_url($this->getHeader('Origin'), PHP_URL_HOST), $this->url->getHost()) === false
            )
        );
    }
    /**
     * get the entire request as a string
     * @return string     the messsage
     * @codeCoverageIgnore
     */
    public function __toString()
    {
        $message = '';
        if ($this->url) {
            $message  = $this->getMethod() . ' ';
            $message .= $this->getUrl()->getPath();
            if (strlen($this->getUrl()->getQuery())) {
                $message .= '?' . $this->getUrl()->getQuery();
            }
            if (strlen($this->getUrl()->getFragment())) {
                $message .= '#' . $this->getUrl()->getFragment();
            }
            $message .= ' HTTP/' . $this->getProtocolVersion();
            $message .= "\r\n";
        }
        $headers = [];
        $bndr = 'multipart-boundary-'.md5(microtime());
        if ($this->hasUploads()) {
            $this->setHeader('Content-Type', 'multipart/form-data; boundary=' . $bndr);
        }
        foreach ($this->headers as $k => $v) {
            $headers[] = $k . ': ' . $v;
        }
        $message .= implode("\r\n", $headers);
        $message .= "\r\n\r\n";
        if ($this->hasUploads()) {
            foreach ($this->getPost() as $k => $v) {
                $message .= '--' . $bndr . "\r\n";
                $message .= 'Content-Disposition: form-data; name="'.$k.'"' . "\r\n";
                $message .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
                $message .= 'Content-Transfer-Encoding: 8bit' . "\r\n\r\n";
                $message .= $v . "\r\n";
            }
            foreach ($this->getUploads() as $key => $file) {
                $message .= '--' . $bndr . "\r\n";
                $message .= 'Content-Disposition: form-data; name="'.$key.'"; filename="'.$file->getName().'"' . "\r\n";
                $message .= 'Content-Transfer-Encoding: binary' . "\r\n\r\n";
                $message .= $file->getBody(true) . "\r\n";
            }
            $message .= '--' . $bndr . '--' . "\r\n\r\n";
        } else {
            $message .= $this->getBody(true);
        }
        return $message;
    }
    /**
     * add a checksum POST param to the request body
     * @param  string      $key   the secret key
     * @param  string      $field the POST key name, defaults to `checksum`
     * @param  string      $algo  the algorythm to use, defaults to `sha1`
     * @return  self
     * @codeCoverageIgnore
     */
    public function addChecksum($key, $field = 'checksum', $algo = 'sha1')
    {
        $body = $this->getBody(true);
        $checksum = base64_encode(hash_hmac($algo, explode('&'.$field, $body, 2)[0], $key, true));
        $this->setBody($body . '&' . $key . '=' . $checksum);
        return $this;
    }
    /**
     * Validate the checksum field of the request
     * @param  string           $key   the secret key
     * @param  string           $field the POST field name, defaults to `checksum`
     * @param  string           $algo  the algorithm to use, defaults to `sha1`
     * @return boolean          is the checksum valid
     * @codeCoverageIgnore
     */
    public function validateChecksum($key, $field = 'checksum', $algo = 'sha1')
    {
        $checksum = base64_encode(hash_hmac($algo, explode('&'.$field, $this->getBody(true), 2)[0], $key, true));
        return $checksum === $this->getPost($field);
    }
    /**
     * Send the request.
     * @param boolean $closeConnection should a "Connection: close" header be added (defaults to true)
     * @return \vakata\http\Response the response.
     * @codeCoverageIgnore
     */
    public function send($closeConnection = true)
    {
        if (!$this->url) {
            throw new \Exception('Cannot send without an URL');
        }

        if ($closeConnection) {
            $this->setHeader('Connection', 'close');
        }

        $lengthAvailable = true;
        if ($this->hasUploads()) {
            foreach ($this->getUploads() as $file) {
                if (!$file->hasSize()) {
                    $lengthAvailable = false;
                    break;
                }
            }
        }

        return $lengthAvailable ? $this->sendAsStream() : $this->sendAsString();
    }
    /**
     * @codeCoverageIgnore
     */
    protected function sendAsString()
    {
        $bndr = 'multipart-boundary-'.md5(microtime());
        if ($this->hasUploads()) {
            $this->setHeader('Content-Type', 'multipart/form-data; boundary=' . $bndr);
        }
        $headers = [];
        foreach ($this->headers as $k => $v) {
            $headers[] = $k . ': ' . $v;
        }
        $context = [
            'http' => [
                'method' => $this->getMethod(),
                'header' => implode("\r\n", $headers),
                'protocol_version' => (float)$this->getProtocolVersion(),
                'ignore_errors' => true
            ]
        ];
        if ($this->getBody() || $this->hasUploads()) {
            $message = '';
            if ($this->hasUploads()) {
                foreach ($this->getPost() as $k => $v) {
                    $message .= '--' . $bndr . "\r\n";
                    $message .= 'Content-Disposition: form-data; name="'.$k.'"' . "\r\n";
                    $message .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
                    $message .= 'Content-Transfer-Encoding: 8bit' . "\r\n\r\n";
                    $message .= $v . "\r\n";
                }
                foreach ($this->getUploads() as $key => $file) {
                    $message .= '--' . $bndr . "\r\n";
                    $message .= 'Content-Disposition: form-data; name="'.$key.'"; filename="'.$file->getName().'"';
                    $message .= "\r\n";
                    $message .= 'Content-Transfer-Encoding: binary' . "\r\n\r\n";
                    $message .= $file->getBody(true) . "\r\n";
                }
                $message .= '--' . $bndr . '--';
            } else {
                $message .= $this->getBody(true);
            }
            $context['http']['content'] = $message;
        }
        $ip = strpos($this->senderIP, ':') !== false ? '['.$this->senderIP.']' : $this->senderIP;
        $context['socket'] = [
            'bindto' => $ip . ':' . $this->senderPort
        ];
        $resp = fopen((string)$this->getUrl(), 'r', false, stream_context_create($context));
        $head = implode("\r\n", stream_get_meta_data($resp)['wrapper_data']);
        $body = stream_get_contents($resp);
        fclose($resp);
        return \vakata\http\Response::fromString($head . "\r\n\r\n" . $body);
    }
    /**
     * @codeCoverageIgnore
     */
    protected function sendAsStream()
    {
        $bndr = 'multipart-boundary-'.md5(microtime());
        if (!$this->hasHeader('Content-Length') && ($this->getBody() || $this->hasUploads())) {
            if ($this->hasUploads()) {
                $this->setHeader('Content-Type', 'multipart/form-data; boundary=' . $bndr);
                $length = 0;
                foreach ($this->getPost() as $k => $v) {
                    $length += strlen($bndr) + 4 + 41 + strlen($k) + 41 + 35 + strlen($v) + 2;
                }
                foreach ($this->getUploads() as $key => $file) {
                    $length += strlen($bndr) + 58 + strlen($key) + strlen($file->getName()) + 37 + $file->getSize() + 2;
                }
                $length += strlen($bndr) + 4;
                $this->setHeader('Content-Length', (string)$length);
            } else {
                $body = $this->getBody(true);
                $this->setHeader('Content-Length', (string)strlen($body));
            }
        }
        $this->setHeader('Host', $this->getUrl()->getHost());
        $headers = [];
        foreach ($this->headers as $k => $v) {
            $headers[] = $k . ': ' . $v;
        }
        $transport = $this->getUrl()->getScheme() === 'https' ? 'tls' : 'tcp';
        $port = $this->getUrl()->getPort() ?: ($transport === 'tls' ? 443 : 80);
        $ip = strpos($this->senderIP, ':') !== false ? '['.$this->senderIP.']' : $this->senderIP;
        $context = [
            'socket' => [
                'bindto' => $ip . ':'. $this->senderPort
            ]
        ];
        $resp = stream_socket_client(
            $transport . "://" . $this->getUrl()->getHost(). ":" . $port,
            $num,
            $str,
            30,
            STREAM_CLIENT_CONNECT,
            stream_context_create($context)
        );

        // request line
        $line  = $this->getMethod() . ' ';
        $line .= $this->getUrl()->getPath();
        if (strlen($this->getUrl()->getQuery())) {
            $line .= '?' . $this->getUrl()->getQuery();
        }
        if (strlen($this->getUrl()->getFragment())) {
            $line .= '#' . $this->getUrl()->getFragment();
        }
        $line .= ' HTTP/' . $this->getProtocolVersion();
        $line .= "\r\n";
        fwrite($resp, $line);
        // headers
        fwrite($resp, implode("\r\n", $headers) . "\r\n\r\n");
        // body
        if ($this->getBody() || $this->hasUploads()) {
            if ($this->hasUploads()) {
                foreach ($this->getPost() as $k => $v) {
                    fwrite($resp, '--' . $bndr . "\r\n");
                    fwrite($resp, 'Content-Disposition: form-data; name="'.$k.'"' . "\r\n");
                    fwrite($resp, 'Content-Type: text/plain; charset=UTF-8' . "\r\n");
                    fwrite($resp, 'Content-Transfer-Encoding: 8bit' . "\r\n\r\n");
                    fwrite($resp, $v . "\r\n");
                }
                foreach ($this->getUploads() as $key => $file) {
                    fwrite($resp, '--' . $bndr . "\r\n");
                    fwrite($resp, 'Content-Disposition: form-data; name="'.$key.'"; filename="'.$file->getName().'"' . "\r\n");
                    fwrite($resp, 'Content-Transfer-Encoding: binary' . "\r\n\r\n");
                    stream_copy_to_stream($file->getBody(), $resp);
                    fwrite($resp, "\r\n");
                }
                fwrite($resp, '--' . $bndr . '--');
                fwrite($resp, str_repeat(' ', 100));
            } else {
                stream_copy_to_stream($this->getBody(), $resp);
            }
        }

        $head = [];
        $body = '';
        $chunked = false;
        while (true) {
            $line = str_replace("\r", "", stream_get_line($resp, PHP_INT_MAX, "\n"));
            if ($line === '') {
                break;
            }
            $head[] = $line;
            if (strpos(strtolower($line), 'transfer-encoding') === 0 && strpos(strtolower($line), 'chunked')) {
                $chunked = true;
            }
        }
        $head = implode("\r\n", $head);
        if (!$chunked) {
            $body = stream_get_contents($resp);
        } else {
            while (true) {
                $length = stream_get_line($resp, PHP_INT_MAX, "\n");
                $length = hexdec(explode(';', $length)[0]);
                if (feof($resp) || $length == 0) {
                    break;
                }
                $body .= stream_get_contents($resp, $length);
            }
        }
        fclose($resp);
        return \vakata\http\Response::fromString($head . "\r\n\r\n" . $body);
    }
}


/*
public function checkCSRF()
{
    // csrf (allow for AJAX & CORS)
    // ajax may not be secure:
    //      http://lists.webappsec.org/pipermail/websecurity_lists.webappsec.org/2011-February/007533.html
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
*/
