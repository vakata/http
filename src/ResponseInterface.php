<?php

namespace vakata\http;

interface ResponseInterface extends MessageInterface
{
    public function setProtocolVersion($version);

    public function getStatusCode();
    public function setStatusCode($code);

    public function setHeader($header, $value);
    public function removeHeader($header);
    public function removeHeaders();

    public function setBody($body);
    public function send();
}
