<?php

namespace vakata\http;

interface ResponseInterface extends MessageInterface
{
    public function getStatusCode();
    public function setStatusCode($code);
    public function send(RequestInterface $req = null);
}
