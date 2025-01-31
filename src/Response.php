<?php

namespace vakata\http;

use Closure;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Response as PSRResponse;

class Response extends PSRResponse
{
    protected ?Closure $callback = null;

    public function __construct(int $status = 200, ?string $body = null, array $headers = [])
    {
        if ($body !== null) {
            $temp = (new Stream('php://temp', 'wb+'));
            $temp->write($body);
        } else {
            $temp = 'php://memory';
        }
        parent::__construct($temp, $status, $headers);
    }
    public function hasCache(): bool
    {
        return $this->hasHeader('Cache-Control') ||
            $this->hasHeader('Expires') ||
            $this->hasHeader('Last-Modified') ||
            $this->hasHeader('ETag');
    }
    /**
     * Make the response cacheable.
     * @param  int|string     $expires when should the request expire - either a timestamp or strtotime expression
     * @return static
     */
    public function cacheUntil(int|string $expires): static
    {
        if (!is_int($expires)) {
            $expires = strtotime($expires);
        }
        return $this
            ->withHeader('Pragma', 'public')
            ->withHeader('Cache-Control', 'maxage='.($expires - time()))
            ->withHeader('Expires', gmdate('D, d M Y H:i:s', $expires ?: null).' GMT');
    }
    /**
     * Prevent caching
     *
     * @return static
     */
    public function noCache(): static
    {
        return $this
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->withHeader('Expires', gmdate('D, d M Y H:i:s', 0).' GMT');
    }
    public function setBody(string $body): static
    {
        $temp = (new Stream('php://temp', 'wb+'));
        $temp->write($body);
        return $this->withBody($temp);
    }
    /**
     * Set a cookie
     * @param  string    $name  the cookie name
     * @param  string    $value the cookie value
     * @param  string    $extra optional extra params for the cookie (semicolon delimited)
     * @return  static
     */
    public function withCookie(string $name, string $value, string $extra = ''): static
    {
        return $this->withAddedHeader('Set-Cookie', $name . '=' . urlencode($value) . '; ' . $extra);
    }
    /**
     * Expires an existing cookie
     * @param  string    $name the cookie name
     * @param  string    $extra optional extra params for the cookie (semicolon delimited)
     * @return self
     */
    public function expireCookie($name, $extra = '')
    {
        $extra = implode('; ', array_filter([ $extra, 'Expires=' . date('r', 0) ]));
        return $this->withCookie($name, 'deleted', $extra);
    }
    public function setContentTypeByExtension(string $extension): static
    {
        switch (strtolower($extension)) {
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
                $type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
                break;
            case "doc":
                $type = "application/msword";
                break;
            case "xlsx":
                $type = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
                break;
            case "xls":
                $type = "application/vnd.ms-excel";
                break;
            case "ppt":
                $type = "application/vnd.ms-powerpoint";
                break;
            case "pptx":
                $type = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
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
                $type = "image/jpeg";
                break;
            case "html":
            case "php":
            case "htm":
                $type = "text/html; charset=UTF-8";
                break;
            default:
                $type = "application/binary";
                break;
        }
        return $this->withHeader('Content-Type', $type);
    }

    public function withCallback(?callable $callback = null): static
    {
        return $this->setBody('')->setCallback($callback);
    }
    protected function setCallback(?callable $callback = null): static
    {
        $this->callback = $callback ? Closure::fromCallable($callback) : null;
        return $this;
    }
    public function hasCallback(): bool
    {
        return $this->callback !== null;
    }
    public function getCallback(): ?callable
    {
        return $this->callback;
    }
}
