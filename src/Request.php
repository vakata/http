<?php

namespace vakata\http;

class Request extends Message implements RequestInterface
{
    protected $method = 'GET';
    protected $files = [];
    protected $url = null;

    /**
     * Create an instance.
     * @method __construct
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
     * @method fromRequest
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

        foreach ($headers as $key => $value) {
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

        return $req;
    }
    /**
     * Create an instance from a stringified request.
     * @method fromString
     * @param  string     $str the stringified request
     * @return \vakata\http\Request          the request instance
     * @codeCoverageIgnore
     */
    public static function fromString($str)
    {
        $req = new self();
        $str = str_replace(["\r\n", "\n"], ["\n", "\r\n"], $str);
        list($headers, $message) = explode("\r\n\r\n", $str, 2);
        $headers = explode("\r\n", preg_replace("(\r\n\s+)", " ", $headers));
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
            $parts = explode("\r\n" . '--' . $bndr, "\r\n" . $message);
            array_pop($parts);
            array_shift($parts);
            $post = [];
            foreach ($parts as $item) {
                $item = str_replace(["\r\n", "\n"], ["\n", "\r\n"], $item);
                list($head, $body) = explode("\r\n\r\n", $item, 2);
                $head = explode("\r\n", preg_replace("(\r\n\s+)", " ", $head));
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
        } elseif (strlen($message)) {
            $req->setBody($message);
        }
        $req->removeHeader('Content-Length');
        $req->removeHeader('Transfer-Encoding');
        return $req;
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
     * @method addUpload
     * @param  string  $key     the multipart name
     * @param  UploadInterface|stream|string  $content the file contents
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
     * @method hasUploads
     * @return boolean      are there are any files attached
     */
    public function hasUploads()
    {
        return count($this->files) > 0;
    }
    /**
     * Does an uploaded file by the specified key exist on this request.
     * @method hasUpload
     * @param  string  $key the multipart name
     * @return boolean      does the file exist
     */
    public function hasUpload($key)
    {
        return isset($this->files[$key]);
    }
    /**
     * Get the upload file instance for the specified key.
     * @method getUpload
     * @param  string  $key the multipart name
     * @return \vakata\http\Upload       the file
     */
    public function getUpload($key)
    {
        return isset($this->files[$key]) ? $this->files[$key] : null;
    }
    /**
     * Get the array of files that are about to be uploaded.
     * @method getUploads
     * @return array   an array of Upload objects
     */
    public function getUploads()
    {
        return $this->files;
    }
    /**
     * Remove a file from the request.
     * @method removeUpload
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
     * @method removeUploads
     * @return self
     */
    public function removeUploads()
    {
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
        $temp = explode(' ', trim($this->getHeader('Authorization')), 2);
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
            $value = preg_replace(['/%0[0-8bcef]/', '/%1[0-9a-f]/', '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'], '', $value, -1, $count);
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
     * @method getCookie
     * @param  string    $key     the cookie name
     * @param  string    $default optional default value to return if the key is not present (default to `null`)
     * @param  string    $mode    optional cleanup of the value, available modes are: int, float, nohtml, escape, string
     * @return string             the value
     */
    public function getCookie($key = null, $default = null, $mode = null)
    {
        if (!$this->hasHeader('Cookie')) {
            return $key === null ? [] : $default;
        }
        $data = explode(';', $this->getHeader('Cookie'));
        $real = [];
        foreach ($data as $v) {
            $temp = explode('=', $v, 2);
            $real[trim($temp[0])] = $temp[1];
        }
        return $this->getValue($real, $key, $default, $mode);
    }
    /**
     * Get a GET param from the request URL
     * @method getQuery
     * @param  string   $key     the GET param name
     * @param  string   $default optional default value to return if the key is not present (default to `null`)
     * @param  string   $mode    optional cleanup of the value, available modes are: int, float, nohtml, escape, string
     * @return string             the value
     */
    public function getQuery($key = null, $default = null, $mode = null)
    {
        if (!$this->url) {
            return $key === null ? [] : $default;
        }
        $data = [];
        $temp = explode('&', $this->url->getQuery());
        foreach ($temp as $var) {
            $var = explode('=', $var, 2);
            $data[urldecode($var[0])] = isset($var[1]) ? urldecode($var[1]) : '';
        }
        //parse_str($this->url->getQuery(), $data);
        return $this->getValue($data, $key, $default, $mode);
    }
    /**
     * Get a param from the request body (if it is in JSON format it will be parsed out as well)
     * @method getPost
     * @param  string   $key     the param name
     * @param  string   $default optional default value to return if the key is not present (default to `null`)
     * @param  string   $mode    optional cleanup of the value, available modes are: int, float, nohtml, escape, string
     * @return string             the value
     */
    public function getPost($key = null, $default = null, $mode = null)
    {
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
        return $this->getValue($real, $key, $default, $mode);
    }
    /**
     * Determine if this is an AJAX request
     * @method isAjax
     * @return boolean is the request AJAX
     */
    public function isAjax()
    {
        return ($this->getHeader('X-Requested-With') === 'XMLHttpRequest');
    }
    /**
     * Is the request AJAX from another domain
     * @method isCors
     * @return boolean is this a CORS request
     */
    public function isCors()
    {
        return (
            $this->hasHeader('Origin') &&
            !$this->hasHeader('X-Requested-With') &&
            (
                !$this->url->getHost() ||
                strpos(parse_url($this->getHeader('Origin'), PHP_URL_HOST), $this->url->getHost()) === false
            )
        );
    }
    /**
     * get the entire request as a string
     * @method __toString
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
     * @method addChecksum
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
     * @method validateChecksum
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
     * @method send
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
                $this->setHeader('Content-Length', $length);
            } else {
                $body = $this->getBody(true);
                $this->setHeader('Content-Length', strlen($body));
            }
        }
        $this->setHeader('Host', $this->getUrl()->getHost());
        $headers = [];
        foreach ($this->headers as $k => $v) {
            $headers[] = $k . ': ' . $v;
        }
        $transport = $this->getUrl()->getScheme() === 'https' ? 'tls' : 'tcp';
        $port = $this->getUrl()->getPort() ?: ($transport === 'tls' ? 443 : 80);
        $resp = stream_socket_client($transport . "://" . $this->getUrl()->getHost(). ":" . $port, $num, $str, 30);

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
