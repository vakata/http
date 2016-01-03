<?php

namespace vakata\http;

class Response implements ResponseInterface
{
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

    protected function cleanHeaderName($name)
    {
        if (strncmp($name, 'HTTP_', 5) === 0) {
            $name = substr($name, 5);
        }
        $name = str_replace('_', ' ', strtolower($name));
        $name = str_replace('-', ' ', strtolower($name));
        $name = str_replace(' ', '-', ucwords($name));

        return $name;
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

    public function getBody()
    {
        return $this->body;
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

    public function getProtocolVersion()
    {
        return $this->http;
    }
    public function setProtocolVersion($version)
    {
        $this->http = $version;
    }

    public function getStatusCode()
    {
        return $this->code;
    }
    public function setStatusCode($code)
    {
        $this->code = $code;
    }

    public function getGzip()
    {
        return $this->gzip;
    }
    public function setGzip($gzip)
    {
        $this->gzip = $gzip;
    }

    public function setContentType($type)
    {
        switch (mb_strtolower($type)) {
            case 'txt'  :
            case 'text' :
                $type = 'text/plain; charset=UTF-8';
                break;
            case 'xml'  :
            case 'xsl'  :
                $type = 'text/xml; charset=UTF-8';
                break;
            case 'json' :
                $type = 'application/json; charset=UTF-8';
                break;
            case 'pdf'  :
                $type = 'application/pdf';
                break;
            case 'exe'  :
                $type = 'application/octet-stream';
                break;
            case 'zip'  :
                $type = 'application/zip';
                break;
            case 'docx' :
            case 'doc'  :
                $type = 'application/msword';
                break;
            case 'xlsx' :
            case 'xls'  :
                $type = 'application/vnd.ms-excel';
                break;
            case 'ppt'  :
                $type = 'application/vnd.ms-powerpoint';
                break;
            case 'gif'  :
                $type = 'image/gif';
                break;
            case 'png'  :
                $type = 'image/png';
                break;
            case 'mp3'  :
                $type = 'audio/mpeg';
                break;
            case 'mp4'  :
                $type = 'video/mpeg';
                break;
            case 'jpeg' :
            case 'jpg'  :
                $type = 'image/jpg';
                break;
            case 'html' :
            case 'php'  :
            case 'htm'  :
                $type = 'text/html; charset=UTF-8';
                break;
            default     :
                return;
        }
        $this->setHeader('Content-Type', $type);
    }
    public function setHeader($header, $value)
    {
        $this->head[$this->cleanHeaderName($header)] = $value;
        if ($this->cleanHeaderName($header) === 'Location') {
            $this->setStatusCode(302);
        }
    }
    public function removeHeader($header)
    {
        unset($this->head[$this->cleanHeaderName($header)]);
    }
    public function removeHeaders()
    {
        $this->head = [];
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function addFilter(callable $f)
    {
        $this->filters[] = $f;
    }

    public function redirectUrl($url)
    {
        $this->setHeader('Location', $url);
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
                    $this->setHeader('Content-Range', 'bytes */'.$file->size);
                    $location = null;
                } else {
                    $range = current(explode(',', substr($_SERVER['HTTP_RANGE'], 6)));
                    list($seek_beg, $seek_end) = explode('-', $range, 2);
                    $seek_beg = max((int) $seek_beg, 0);
                    $seek_end = !(int) $seek_end ? ((int) $file->size - 1) : min((int) $seek_end, ((int) $file->size - 1));
                    if ($seek_beg > $seek_end) {
                        $this->setStatusCode(416);
                        $this->setHeader('Content-Range', 'bytes */'.$file->size);
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
}
