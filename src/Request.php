<?php

namespace vakata\http;

use Laminas\Diactoros\Uri as LaminasUri;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;

class Request extends ServerRequest
{
    protected $certificateNumber;
    protected $certificateData;
    /**
     * Create an instance from globals
     *
     * @param array $server
     * @param array $query
     * @param array $body
     * @param array $cookies
     * @param array $files
     * @return Request
     */
    public static function fromGlobals(
        array $server = null,
        array $query = null,
        array $body = null,
        array $cookies = null,
        array $files = null
    ) {
        $server  = \Laminas\Diactoros\normalizeServer($server ?: $_SERVER);
        $files   = \Laminas\Diactoros\normalizeUploadedFiles($files ?: $_FILES);
        $headers = [];
        foreach ($server as $key => $value) {
            if (strpos($key, 'REDIRECT_') === 0) {
                $key = substr($key, 9);
                if (array_key_exists($key, $server)) {
                    continue;
                }
            }
            if (is_string($value) && strlen($value) && strpos($key, 'HTTP_') === 0) {
                $name = strtr(strtolower(substr($key, 5)), '_', '-');
                $headers[$name] = $value;
                continue;
            }
            if (is_string($value) && strlen($value) && strpos($key, 'CONTENT_') === 0) {
                $name = 'content-' . strtolower(substr($key, 8));
                $headers[$name] = $value;
                continue;
            }
        }

        $method  = \Laminas\Diactoros\marshalMethodFromSapi($server);
        $uri     = \Laminas\Diactoros\marshalUriFromSapi($server, $headers);

        if (null === $cookies && array_key_exists('cookie', $headers)) {
            $cookies = self::parseCookieHeader($headers['cookie']);
        }
        

        if ($body === null) {
            $body = [];
            if (isset($headers['content-type']) && strpos($headers['content-type'], 'json') !== false) {
                $body = json_decode($body, true);
                if ($body === null) {
                    $body = [];
                }
            } else {
                $body = static::fixedQueryParams(file_get_contents('php://input'));
            }
        }

        return new static(
            $server,
            $files,
            $uri,
            $method,
            'php://input',
            $headers,
            $cookies ?: $_COOKIE,
            $query ?: static::fixedQueryParams($uri->getQuery()),
            $body ?: (count($_POST) ? $_POST : json_decode(file_get_contents('php://input'), true)),
            \Laminas\Diactoros\marshalProtocolVersionFromSapi($server),
            $server['SSL_CLIENT_M_SERIAL'] ?? null,
            $server['SSL_CLIENT_CERT'] ?? null
        );
    }
    public static function fromString(string $str) : Request
    {
        $method = 'GET';
        $version = '1.1';
        $uri = '/';
        $headers = [];
        $files = [];
        $body = '';

        $break = strpos($str, "\r\n\r\n") === false ? "\n" : "\r\n"; // just in case someone breaks RFC 2616

        list($headers, $message) = array_pad(explode($break . $break, $str, 2), 2, '');
        $headers = explode($break, preg_replace("(" . $break . "\s+)", " ", $headers));
        if (isset($headers[0]) && strlen($headers[0])) {
            $temp = explode(' ', $headers[0]);
            if (in_array($temp[0], ['GET', 'POST', 'HEAD', 'PATCH', 'PUT', 'OPTIONS', 'TRACE', 'DELETE'])) {
                $method = $temp[0];
                $uri = $temp[1];
                if (isset($temp[2])) {
                    $version = substr($temp[2], 5);
                }
                unset($headers[0]);
                $headers = array_values($headers);
            }
        }
        $temp = array_filter($headers);
        $headers = [];
        foreach ($temp as $v) {
            $v = explode(':', $v, 2);
            $name = trim($v[0]);
            $name = str_replace('_', ' ', strtolower($name));
            $name = str_replace('-', ' ', strtolower($name));
            $name = str_replace(' ', '-', ucwords($name));
            $headers[$name] = trim($v[1]);
        }
        if (isset($headers['Host'])) {
            $uri = $headers['Host'] . $uri;
        } else {
            $uri = 'localhost' . $uri;
        }
        if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'multipart') !== false) {
            $bndr = trim(explode(' boundary=', $headers['Content-Type'])[1], '"');
            $parts = explode($break . '--' . $bndr, $break . $message);
            if (count($parts) == 1) {
                $body = $message;
            } else {
                array_pop($parts);
                array_shift($parts);
                $post = [];
                $fres = [];
                foreach ($parts as $k => $item) {
                    list($head, $pbody) = explode($break . $break, $item, 2);
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
                                // create resource manually
                                $fres[$k] = fopen('php://temp', 'wb+');
                                fwrite($fres[$k], $pbody);
                                rewind($fres[$k]);
                                $files[$name] = new UploadedFile(
                                    $fres[$k],
                                    strlen($pbody),
                                    UPLOAD_ERR_OK,
                                    $file
                                );
                            } else {
                                $post[$name] = $pbody;
                            }
                        }
                    }
                }
                $body = http_build_query($post);
            }
        } elseif (strlen($message)) {
            $body = $message;
        }
        if (strpos($uri, '://') === false) {
            $uri = 'http://' . $uri;
        }

        if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'json') !== false) {
            $params = json_decode($body, true);
        } else {
            $params = static::fixedQueryParams($body);
        }
        $temp = (new Stream('php://temp', 'wb+'));
        $temp->write($body);
        $uri = new LaminasUri($uri);
        return new static(
            [],
            \Laminas\Diactoros\normalizeUploadedFiles($files),
            $uri,
            $method,
            $temp,
            $headers,
            isset($headers['Cookie']) ? self::parseCookieHeader($headers['Cookie']) : [],
            static::fixedQueryParams($uri->getQuery()),
            $params ?? [],
            $version
        );
    }
    public static function fixedQueryParams($query)
    {
        $data = [];
        $temp = strlen($query) ? explode('&', $query) : [];
        foreach ($temp as $var) {
            $var   = explode('=', $var, 2);
            $name  = urldecode($var[0]);
            $value = isset($var[1]) ? urldecode($var[1]) : '';
            $name  = explode(']', str_replace(['][', '['], ']', $name));
            $name  = count($name) > 1 ? array_slice($name, 0, -1) : $name;

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
                if (!is_array($tmp)) {
                    $tmp = [];
                }
                $tmp[] = $value;
            } else {
                $tmp = $value;
            }
        }
        return $data;
    }
    private static function parseCookieHeader($cookieHeader)
    {
        preg_match_all('(
            (?:^\\n?[ \t]*|;[ ])
            (?P<name>[!#$%&\'*+-.0-9A-Z^_`a-z|~]+)
            =
            (?P<DQUOTE>"?)
                (?P<value>[\x21\x23-\x2b\x2d-\x3a\x3c-\x5b\x5d-\x7e]*)
            (?P=DQUOTE)
            (?=\\n?[ \t]*$|;[ ])
        )x', $cookieHeader, $matches, PREG_SET_ORDER);

        $cookies = [];

        if (is_array($matches)) {
            foreach ($matches as $match) {
                $cookies[$match['name']] = urldecode($match['value']);
            }
        }

        return $cookies;
    }
    public function __construct(
        array $serverParams = [],
        array $uploadedFiles = [],
        $uri = null,
        $method = null,
        $body = 'php://input',
        array $headers = [],
        array $cookies = [],
        array $queryParams = [],
        $parsedBody = null,
        $protocol = '1.1',
        string $certificateNumber = null,
        string $certificateData = null
    ) {
        $uri = new Uri((string)$uri);
        parent::__construct(
            $serverParams,
            $uploadedFiles,
            $uri,
            $method,
            $body,
            $headers,
            $cookies,
            $queryParams,
            $parsedBody,
            $protocol
        );
        $this->certificateNumber = $certificateNumber ? strtoupper(ltrim(trim($certificateNumber), '0')) : null;
        $this->certificateData = $certificateData;
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
        } while ((int)$count > 0);

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
     * @param  mixed     $default optional default value to return if the key is not present (default to `null`)
     * @param  string    $mode    optional cleanup of the value, available modes are: int, float, nohtml, escape, string
     * @return mixed             the value (or values)
     */
    public function getCookie($key = null, $default = null, $mode = null)
    {
        return $this->getValue($this->getCookieParams(), $key, $default, $mode);
    }
    /**
     * Get a GET param from the request URL
     * @param  string   $key     the GET param name
     * @param  mixed    $default optional default value to return if the key is not present (default to `null`)
     * @param  string   $mode    optional cleanup of the value, available modes are: int, float, nohtml, escape, string
     * @return mixed             the value (or values)
     */
    public function getQuery($key = null, $default = null, $mode = null)
    {
        return $this->getValue($this->getQueryParams(), $key, $default, $mode);
    }
    /**
     * Get a param from the request body (if it is in JSON format it will be parsed out as well)
     * @param  string   $key     the param name
     * @param  mixed    $default optional default value to return if the key is not present (default to `null`)
     * @param  string   $mode    optional cleanup of the value, available modes are: int, float, nohtml, escape, string
     * @return mixed             the value (or values if no key was specified)
     */
    public function getPost($key = null, $default = null, $mode = null)
    {
        $body = $this->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }
        return $this->getValue($body, $key, $default, $mode);
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
        $temp = explode(' ', trim($this->getHeaderLine('Authorization')), 2);
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
     * Get the Uri object
     * @return Uri
     */
    public function getUrl()
    {
        return $this->getUri();
    }
    /**
     * Determine if this is an AJAX request
     * @return boolean is the request AJAX
     */
    public function isAjax()
    {
        return ($this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest');
    }
    /**
     * Determine if this is an CORS request
     * @return boolean is the request CORS
     */
    public function isCors()
    {
        if (!$this->hasHeader('Origin')) {
            return false;
        }
        $origin = parse_url($this->getHeaderLine('Origin'));
        $host   = $this->getUri()->getHost();
        $scheme = $this->getUri()->getScheme();
        return (
            !$host ||
            strtolower($origin['scheme']?? '') !== strtolower($scheme) ||
            strpos(strtolower($origin['host'] ?? ''), strtolower($host)) === false
        );
    }
    /**
     * Get the prefered response languages (parses the Accept-Language header if present).
     * @param  bool    $shortNames should values like "en-US", be truncated to "en", defaults to true
     * @return array   array of ordered lowercase language codes
     */
    public function getPreferredResponseLanguages(bool $shortNames = true) : array
    {
        $acpt = $this->getHeaderLine('Accept-Language') ?: '*';
        $acpt = explode(',', $acpt);
        foreach ($acpt as $k => $v) {
            $v = array_pad(explode(';', $v, 2), 2, 'q=1');
            $v[1] = (float) array_pad(explode('q=', $v[1], 2), 2, '1')[1];
            $v[0] = $shortNames ? explode('-', $v[0], 2)[0] : $v[0];
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
        $acpt = array_map(function ($v) {
            return strtolower($v[0]);
        }, $acpt);
        $acpt = array_filter($acpt, function ($v) {
            return $v !== '*';
        });
        return array_unique($acpt);
    }
    /**
     * Get the preffered response language (parses the Accept-Language header if present).
     * @param  string       $default the default code to return if the header is not found
     * @param  array|null   $allowed an optional list of lowercase language codes to intersect with, defaults to null
     * @return string       the prefered language code
     */
    public function getPreferredResponseLanguage(string $default = 'en', array $allowed = null) : string
    {
        $acpt = $this->getPreferredResponseLanguages(true);
        foreach ($acpt as $lang) {
            if ($allowed === null) {
                return $lang;
            }
            if (in_array($lang, $allowed)) {
                return $lang;
            }
        }
        return $default;
    }
    /**
     * Get the prefered response formats.
     * @param  string                    $default the default value to return if the Accept header is not present.
     * @return string[]                  the desired response formats
     */
    public function getPreferredResponseFormats($default = 'text/html')
    {
        // parse accept header (uses default instead of 406 header)
        $acpt = $this->getHeaderLine('Accept') ?: $default;
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
        $acpt = array_map(function ($v) {
            return strtolower($v[0]);
        }, $acpt);
        $acpt = array_filter($acpt, function ($v) {
            return $v !== '*/*';
        });
        return array_unique($acpt);
    }
    /**
     * Get the preffered response language (parses the Accept-Language header if present).
     * @param  string       $default the default code to return if the header is not found
     * @param  array|null   $allowed an optional list of lowercase language codes to intersect with, defaults to null
     * @return string       the prefered language code
     */
    public function getPreferredResponseFormat(string $default = 'text/html', array $allowed = null) : string
    {
        // parse accept header (uses default instead of 406 header)
        $acpt = $this->getPreferredResponseFormats();
        foreach ($acpt as $format) {
            if ($allowed === null) {
                return $format;
            }
            if (in_array($format, $allowed)) {
                return $format;
            }
        }
        return $default;
    }
    public function hasCertificate()
    {
        return $this->certificateNumber !== null;
    }
    public function getCertificateNumber()
    {
        return $this->certificateNumber;
    }
    public function getCertificate()
    {
        return $this->certificateData;
    }
    public function withCertificate(string $number, string $data = null)
    {
        $ret = clone $this;
        $ret->certificateNumber = strtoupper(ltrim(trim($number), '0'));
        $ret->certificateData = $data;
        return $ret;
    }
}
