<?php
namespace vakata\http;

/**
 * An abstract class representing an HTTP message (either a request or a response)
 */
abstract class Message implements MessageInterface
{
    protected $protocol = '1.1';
    protected $headers = [];
    protected $body = null;

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

    /**
     * get the current HTTP version
     * @method getProtocolVersion
     * @return string             the protocol version
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }
    /**
     * set the HTTP version to use
     * @method setProtocolVersion
     * @param  string             $version the HTTP version to use
     * @return  self
     */
    public function setProtocolVersion($version)
    {
        $this->protocol = $version;
        return $this;
    }

    /**
     * Retrieve all set headers.
     * @method getHeaders
     * @return array     all headers of the message
     */
    public function getHeaders()
    {
        return $this->headers;
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
        $this->headers[$this->cleanHeaderName($header)] = $value;
        return $this;
    }
    /**
     * Is a specific header set on the message.
     * @method hasHeader
     * @param  string    $header the header name
     * @return boolean
     */
    public function hasHeader($header)
    {
        return isset($this->headers[$this->cleanHeaderName($header)]);
    }
    /**
     * Retieve a header value by name.
     * @method getHeader
     * @param  string    $header the header name
     * @return string            the header value
     */
    public function getHeader($header)
    {
        return isset($this->headers[$this->cleanHeaderName($header)]) ?
            $this->headers[$this->cleanHeaderName($header)] :
            null;
    }
    /**
     * Remove a header from the message by name.
     * @method removeHeader
     * @param  string       $header the header name
     * @return self
     */
    public function removeHeader($header)
    {
        unset($this->headers[$this->cleanHeaderName($header)]);
        return $this;
    }
    /**
     * Remove all headers from the message.
     * @method removeHeaders
     * @return self
     */
    public function removeHeaders()
    {
        $this->headers = [];
        return $this;
    }

    /**
     * get the message body (as a stream resource or as a string)
     * @method getBody
     * @param  boolean $asString should the content be returned as a string (defaults to `false`)
     * @return mixed  the body
     */
    public function getBody($asString = false)
    {
        if (!$this->body) {
            return $asString ? '' : null;
        }
        @rewind($this->body);
        $body = $asString ? stream_get_contents($this->body) : $this->body;
        @rewind($this->body);
        return $body;
    }
    /**
     * set the message body (either set to a stream resource or a string)
     * @method setBody
     * @param  mixed  $body the body to use
     * @return self
     */
    public function setBody($body)
    {
        if (is_string($body)) {
            $this->body = fopen('php://temp', 'r+');
            fwrite($this->body, $body);
            rewind($this->body);
        } else {
            $this->body = $body;
        }
        return $this;
    }

    abstract public function __toString();
}
