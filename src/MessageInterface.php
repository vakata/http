<?php

namespace vakata\http;

interface MessageInterface
{
    public function getProtocolVersion();
    public function getBody();
    public function getHeaders();
    public function hasHeader($header);
    public function getHeader($header);
}
