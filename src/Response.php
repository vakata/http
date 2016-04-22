<?php

namespace vakata\http;

/**
 * A class representing an HTTP response.
 */
class Response extends Message implements ResponseInterface
{
    protected $code = 200;
    protected $reason = 'OK';
    public static $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];

    /**
     * Create an instance.
     * @method __construct
     * @param  integer      $status the status code to use
     */
    public function __construct($status = 200)
    {
        $this->setStatusCode($status);
    }

    /**
     * Create an instance from a stream resource.
     * @method fromStream
     * @param  stream   $stream a stream resource
     * @param  string   $name optional name to serve the file with
     * @return \vakata\http\Response         the response instance
     * @codeCoverageIgnore
     */
    public static function fromStream($stream, $name = null)
    {
        if (!$name) {
            $meta = stream_get_meta_data($stream);
            if (!$meta) {
                throw new \Exception('Invalid stream');
            }
            $name = basename($meta['uri']);
        }
        if ($name) {
            $extension = substr($name, strrpos($name, '.') + 1);
            if ($extension) {
                $this->setContentTypeByExtension($extension);
            }
            $disposition = in_array(strtolower($extension), ['txt','png','jpg','gif','jpeg','html','htm','mp3','mp4']) ?
                'inline' :
                'attachment';
            $this->setHeader(
                'Content-Disposition',
                (
                    $disposition.'; '.
                    'filename="'.preg_replace('([^a-z0-9.-]+)i', '_', $name).'"; '.
                    'filename*=UTF-8\'\''.rawurlencode($name)
                )
            );
        }
        $this->setBody($stream);
    }
    /**
     * Create an instance from a file.
     * @method fromFile
     * @param  string   $file a path to a file
     * @param  string   $name optional name to serve the file with
     * @param  string   $hash optional string to use as ETag
     * @param  string   $cached optional strtotime expression used for caching validity
     * @return \vakata\http\Response         the response instance
     * @codeCoverageIgnore
     */
    public static function fromFile($file, $name = null, $hash = null, $cached = null)
    {
        $res = new self();
        $name = $name ?: basename($file);
        $size = filesize($file);
        if ($name) {
            $extension = substr($name, strrpos($name, '.') + 1);
            if ($extension) {
                $res->setContentTypeByExtension($extension);
            }
            $res->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($file)).' GMT');
            if ($hash !== null) {
                $res->setHeader('ETag', $hash);
            }
            if ($cached !== null) {
                $res->cacheUntil($cached);
            }
            $disposition = in_array(strtolower($extension), ['txt','png','jpg','gif','jpeg','html','htm','mp3','mp4']) ?
                'inline' :
                'attachment';
            $res->setHeader(
                'Content-Disposition',
                (
                    $disposition.'; '.
                    'filename="'.preg_replace('([^a-z0-9.-]+)i', '_', $name).'"; '.
                    'filename*=UTF-8\'\''.rawurlencode($name).'; '.
                    'size='.$size
                )
            );
            $res->setHeader('Content-Length', $size);
        }
        $res->setBody(fopen($file, 'r'));
        return $res;
    }
    /**
     * Create an instance from an input string.
     * @method fromString
     * @param  string     $str the stringified response
     * @return \vakata\http\Response          the response instance
     * @codeCoverageIgnore
     */
    public static function fromString($str)
    {
        $res = new self();
        $str = str_replace(["\r\n", "\n"], ["\n", "\r\n"], $str);
        list($headers, $message) = explode("\r\n\r\n", (string)$str, 2);
        $headers = explode("\r\n", preg_replace("(\r\n\s+)", " ", $headers));
        if (isset($headers[0]) && substr($headers[0], 0, 5) === 'HTTP/') {
            $temp = explode(' ', substr($headers[0], 5));
            $res->setProtocolVersion($temp[0]);
            $res->setStatusCode((int)$temp[1]);
            unset($headers[0]);
            $headers = array_values($headers);
        }
        foreach (array_filter($headers) as $v) {
            $v = explode(':', $v, 2);
            $res->setHeader(trim($v[0]), trim($v[1]));
        }
        $res->setBody($message);
        $res->removeHeader('Content-Length');
        $res->removeHeader('Transfer-Encoding');
        return $res;
    }

    /**
     * Add a header to the message.
     * @method setHeader
     * @param  string    $header the header name
     * @param  string    $value  the header value
     * @return  self
     */
    public function setHeader($header, $value)
    {
        $header = $this->cleanHeaderName($header);
        $this->headers[$header] = $value;
        if ($header === 'Status') {
            $this->code = (int)trim($value);
        }
        return $this;
    }

    /**
     * get the currently set status code
     * @method getStatusCode
     * @return integer        the status code
     */
    public function getStatusCode()
    {
        return $this->code;
    }
    /**
     * sets the status code
     * @method setStatusCode
     * @param  integer        $code the new status code
     * @param  string         $reason optional reason, if not set the default will be used
     * @return  self
     */
    public function setStatusCode($code, $reason = '')
    {
        if (!$reason && isset(self::$statusTexts[$code])) {
            $reason = self::$statusTexts[$code];
        }
        $this->code = $code;
        $this->reason = $reason;
        $this->setHeader('Status', $code . ' ' . $reason);
        return $this;
    }
    /**
     * Set the Content-Type header by using a file extension.
     * @method setContentTypeByExtension
     * @param  string                    $type the extension
     * @return  self
     * @codeCoverageIgnore
     */
    public function setContentTypeByExtension($type)
    {
        switch (strtolower($type)) {
            case "txt":
            case "text":
                $type = "text/plain; charset=UTF-8";
                break;
            case "xml":
            case "xsl":
                $type = "text/xml; charset=UTF-8";
                break;
            case "json":
                $type = "application/json; charset=UTF-8";
                break;
            case "pdf":
                $type = "application/pdf";
                break;
            case "exe":
                $type = "application/octet-stream";
                break;
            case "zip":
                $type = "application/zip";
                break;
            case "docx":
            case "doc":
                $type = "application/msword";
                break;
            case "xlsx":
            case "xls":
                $type = "application/vnd.ms-excel";
                break;
            case "ppt":
                $type = "application/vnd.ms-powerpoint";
                break;
            case "gif":
                $type = "image/gif";
                break;
            case "png":
                $type = "image/png";
                break;
            case "mp3":
                $type = "audio/mpeg";
                break;
            case "mp4":
                $type = "video/mpeg";
                break;
            case "jpeg":
            case "jpg":
                $type = "image/jpg";
                break;
            case "html":
            case "php":
            case "htm":
                $type = "text/html; charset=UTF-8";
                break;
            default:
                return;
        }
        $this->setHeader('Content-Type', $type);
        return $this;
    }
    /**
     * Make the response cacheable.
     * @method cacheUntil
     * @param  int|string     $expires when should the request expire - either a timestamp or strtotime expression
     * @return self
     */
    public function cacheUntil($expires)
    {
        if (!is_int($expires)) {
            $expires = strtotime($expires);
        }
        $this->setHeader('Pragma', 'public');
        $this->setHeader('Cache-Control', 'maxage='.($expires - time()));
        $this->setHeader('Expires', gmdate('D, d M Y H:i:s', $expires).' GMT');
        return $this;
    }
    /**
     * Enable CORS
     * @method enableCors
     * @param  string     $origin  the host to allow CORS for, defaults to `'*'`
     * @param  string     $creds   are credentials allowed, defaults to `false`
     * @param  integer    $age     the max age, defaults to `3600`
     * @param  array      $methods allowed methods, defaults to all
     * @param  array      $headers allowed headers, defaults to `['Authorization']`
     * @return self
     */
    public function enableCors($origin = '*', $creds = false, $age = 3600, array $methods = null, array $headers = null)
    {
        if ($methods === null) {
            $methods = ['GET','POST','PUT','PATCH','HEAD','DELETE'];
        }
        if ($headers === null) {
            $headers = ['Authorization'];
        }
        $this->setHeader('Access-Control-Allow-Origin', $origin);
        $this->setHeader('Access-Control-Max-Age', $age);
        $this->setHeader('Access-Control-Allow-Methods', implode(',', $methods));
        $this->setHeader('Access-Control-Allow-Headers', implode(', ', $headers));
        $this->setHeader('Access-Control-Allow-Credentials', $creds ? 'true' : 'false');

        return $this;
    }
    /**
     * get the entire response as a string
     * @method __toString
     * @return string     the messsage
     * @codeCoverageIgnore
     */
    public function __toString()
    {
        $message = 'HTTP/' . $this->getProtocolVersion() . ' ' . $this->code . ' ' . $this->reason . "\r\n";
        $headers = [];
        foreach ($this->headers as $k => $v) {
            $headers[] = $k . ': ' . $v;
        }
        $message .= implode("\r\n", $headers);
        $message .= "\r\n\r\n";
        $message .= $this->getBody(true);
        return $message;
    }

    /**
     * Send the response to the client.
     * @method send
     * @param  RequestInterface|null $req  optional request object that triggered this response
     * @return self
     * @codeCoverageIgnore
     */
    public function send(RequestInterface $req = null)
    {
        $seekBeg = 0;
        $seekEnd = -1;

        // modify response according to request
        if ($req) {
            // process cached response (not modified)
            if ($req->hasHeader('If-Modified-Since') && $this->hasHeader('Last-Modified')) {
                $cached = strtotime($req->getHeader('If-Modified-Since'));
                $current = strtotime($this->getHeader('Last-Modified'));
                if ($cached === $current) {
                    $this->setStatusCode(304);
                }
            }
            // process cached response (ETag)
            if ($req->hasHeader('If-None-Match') && $this->hasHeader('ETag')) {
                if ($req->getHeader('If-None-Match') === $this->getHeader('ETag')) {
                    $this->setStatusCode(304);
                }
            }
            // process chunks
            if ($req->hasHeader('Range') && $this->hasHeader('Content-Length')) {
                $size = (int)$this->getHeader('Content-Length');
                $range = $req->getHeader('Range');
                $this->setHeader('Accept-Ranges', 'bytes');
                try {
                    if (!preg_match('@^bytes=\d*-\d*(,\d*-\d*)*$@', $range)) {
                        throw new \Exception('Invalid range');
                    }
                    $range = current(explode(',', substr($range, 6)));
                    list($seekBeg, $seekEnd) = explode('-', $range, 2);
                    $seekBeg = max((int)$seekBeg, 0);
                    $seekEnd = !(int)$seekEnd ? ($size - 1) : min((int)$seekEnd, ($size - 1));
                    if ($seekBeg > $seekEnd) {
                        throw new \Exception('Invalid range');
                    }
                    $this->setStatusCode(206);
                    $this->setHeader('Content-Range', 'bytes '.$seekBeg.'-'.$seekEnd.'/'.$size);
                    $seekEnd = ($seekEnd - $seekBeg);
                } catch (\Exception $e) {
                    $this->setStatusCode(416);
                    $this->setHeader('Content-Range', 'bytes * /'.$size);
                    $this->body = null;
                }
            }
        }
        if (!headers_sent()) {
            if ($this->getHeader('Location') &&
                $this->code !== 201 &&
                $this->code !== 202 &&
                floor($this->code / 100) !== 3
            ) {
                $this->setStatusCode(302);
            }
            http_response_code($this->code);
            foreach ($this->getHeaders() as $k => $v) {
                header($k . ': ' . $v);
            }
        }
        if ($this->body && (!in_array($this->getStatusCode(), [204,304,416])) && (!$req || $req->getMethod() !== 'HEAD')) {
            $out = fopen('php://output', 'w');
            stream_copy_to_stream($this->body, $out, $seekEnd, $seekBeg);
            fclose($out);
        }
        return $this;
    }
}
