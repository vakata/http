<?php

namespace vakata\http;

interface MessageInterface
{
    public function getProtocolVersion();
    public function setProtocolVersion($version);

    public function getHeader($header);
    public function getHeaders();
    public function hasHeader($header);
    public function setHeader($header, $value);
    public function removeHeader($header);
    public function removeHeaders();

    public function getBody($asString = false);
    public function setBody($body);

    public function __toString();
}
