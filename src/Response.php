<?php

namespace vakata\http;

class Response extends Message implements ResponseInterface
{
    protected $code = 200;
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

    public static function fromFile($file)
    {
        // TODO: parse the file name / file size / create the body stream / etc
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
     * @return  self
     */
    public function setStatusCode($code)
    {
        $this->code = $code;
        $this->setHeader('Status', self::$statusTexts[$code]);
        return $this;
    }

    /**
     * get the entire response as a string
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
        $message .= $this->getBody(true);
        return $message;
    }

    /**
     * send the response to the client
     * @method send
     * @return  self
     */
    public function send(RequestInterface $req = null)
    {
        // TODO: parse stuff from $req as needed (last modified / chunks / etc)

        if (!headers_sent()) {
            http_response_code($this->code);
            foreach ($this->getHeaders() as $k => $v) {
                header($k . ': ' . $v);
            }
        }
        if ($this->body) {
            $out = fopen('php://output');
            stream_copy_to_stream($this->body, $out);
            fclose($out);
        }
    }
}

/*
protected $http = '1.1';
protected $code = 200;
protected $head = [];
protected $body = null;
protected $gzip = true;

protected $filters = [];

public function __construct()
{
    ob_start();
}

protected function processBody()
{
    if ($this->body === null) {
        $this->body = ob_get_clean();
    }
    while (ob_get_level() && ob_end_clean());

    // above headers, so that filters can send headers
    if ($this->body !== null && strlen($this->body)) {
        $type = explode(';', $this->getHeader('Content-Type'))[0];
        foreach ($this->filters as $filter) {
            $this->body = call_user_func($filter, $this->body, $type);
        }
    }
}

public function enableCors(RequestInterface $req, array $methods = null)
{
    if (!$req->isCors()) {
        return;
    }
    if (!$methods) {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'HEAD', 'DELETE'];
    }
    $this->setHeader('Access-Control-Allow-Origin', $req->getHeader('Origin'));
    $headers = [];
    if ($req->hasHeader('Access-Control-Request-Headers')) {
        $headers = array_map('trim', array_filter(explode(',', $req->getHeader('Access-Control-Request-Headers'))));
    }
    $headers[] = 'Authorization';
    $this->setHeader('Access-Control-Allow-Headers', implode(', ', $headers));
    if ($req->getMethod() === 'OPTIONS') {
        $this->setHeader('Access-Control-Max-Age', '3600');
        $this->setHeader('Access-Control-Allow-Methods', implode(', ', $methods));
    }
}

public function file(\vakata\file\FileInterface $file, $file_name = null, $chunks = false, $head_only = false)
{
    $extension = $file_name ? substr($file_name, strrpos($file_name, '.') + 1) : $file->extension;
    $file_name = $file_name ?: $file->name;
    $location = $file->location;
    $file_beg = 0;
    $file_end = $file->size ? $file->size : null;

    $this->setGzip(false);
    $this->body = null;
    $this->head = [];

    $expires = 60 * 60 * 24 * 30; // 1 месец
    if ($file->modified) {
        $this->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $file->modified).' GMT');
    }
    if ($file->hash) {
        $this->setHeader('Etag', $file->hash);
    }
    $this->setHeader('Pragma', 'public');
    $this->setHeader('Cache-Control', 'maxage='.$expires);
    $this->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + $expires).' GMT');

    $modified = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : getenv('HTTP_IF_MODIFIED_SINCE');
    $hash = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : getenv('HTTP_IF_NONE_MATCH');

    // ако клиента има кеширано копие пускаме 304 и не качваме брояча за downloaded
    $cached = false;
    if (
        ($file->modified && $modified && @strtotime($modified) == $file->modified) ||
        ($file->hash && $hash && $hash == $file->hash)
    ) {
        $this->setStatusCode(304);
        $cached = true;
    } else {
        // ако получаваме заявка за чънкове (resume/chunk поддръжка чрез HTTP_RANGE)
        // но само ако имаме размера
        if ($chunks && $file->size && $location && isset($_SERVER['HTTP_RANGE'])) {
            $this->setHeader('Accept-Ranges', 'bytes');
            if (!preg_match('@^bytes=\d*-\d*(,\d*-\d*)*$@', $_SERVER['HTTP_RANGE'])) {
                $this->setStatusCode(416);
                $this->setHeader('Content-Range', 'bytes * /'.$file->size);
                $location = null;
            } else {
                $range = current(explode(',', substr($_SERVER['HTTP_RANGE'], 6)));
                list($seek_beg, $seek_end) = explode('-', $range, 2);
                $seek_beg = max((int) $seek_beg, 0);
                $seek_end = !(int) $seek_end ? ((int) $file->size - 1) : min((int) $seek_end, ((int) $file->size - 1));
                if ($seek_beg > $seek_end) {
                    $this->setStatusCode(416);
                    $this->setHeader('Content-Range', 'bytes * /'.$file->size);
                    $location = null;
                } else {
                    $this->setStatusCode(206);
                    $this->setHeader('Content-Range', 'bytes '.$seek_beg.'-'.$seek_end.'/'.$file->size);
                    $file_beg = $seek_beg;
                    $file_end = ($seek_end - $seek_beg);
                }
            }
        } else {
            $chunks = false;
            $this->setStatusCode(200);
        }
    }
    $this->setContentType($extension);
    if (!$this->hasHeader('Content-Type')) {
        $this->setHeader('Content-Type', 'application/octet-stream');
    }
    $this->setHeader('Content-Disposition', (!$chunks && in_array(strtolower($extension), array('txt', 'png', 'jpg', 'gif', 'jpeg', 'html', 'htm', 'mp3', 'mp4')) ? 'inline' : 'attachment').'; filename="'.preg_replace('([^a-z0-9.-]+)i', '_', $file_name).'"; filename*=UTF-8\'\''.rawurlencode($file_name).''.($file->size ? '; size='.$file->size : ''));
    if ($file_end) {
        $this->setHeader('Content-Length', $file_end);
    }

    session_write_close();
    while (ob_get_level() && ob_end_clean());

    if (!headers_sent()) {
        http_response_code($this->code);
        foreach ($this->head as $key => $header) {
            header($key.': '.$header);
        }
    }

    if (!$cached && !$head_only) {
        if ($location && strpos($location, 'http') !== 0 && ($fp = @fopen($location, 'rb'))) {
            set_time_limit(0);
            ob_implicit_flush(true);
            @ob_end_flush();

            fseek($fp, $file_beg);
            $chunk = 1024 * 8;
            $read = 0;
            while (!feof($fp) && $read <= $file_end) {
                echo fread($fp, $chunk);
                $read += $chunk;
                if ($file_end - $read < $chunk) {
                    $chunk = $file_end - $read;
                }
                if (!$chunk) {
                    break;
                }
            }
            @fclose($fp);
        } else {
            echo $file->content();
        }
    }
    @ob_end_flush();
    $this->body = null;
    $this->head = [];
}

public function send()
{
    if (!$this->hasHeader('Content-Type')) {
        $this->setContentType('html');
    }
    $this->processBody();

    if (!headers_sent()) {
        http_response_code($this->code);
        foreach ($this->head as $key => $header) {
            header($key.': '.$header);
        }
    }
    if ($this->body !== null && strlen($this->body)) {
        if ($this->gzip && !(bool) @ini_get('zlib.output_compression') && extension_loaded('zlib')) {
            ob_start('ob_gzhandler');
        }
        echo $this->body;
    }
    $this->body = null;
    $this->head = [];
    @ob_end_flush();
}

public function __sleep()
{
    $this->processBody();

    return ['http','code','head','body','gzip'];
}
*/
