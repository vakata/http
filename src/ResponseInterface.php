<?php

namespace vakata\http;

interface ResponseInterface extends MessageInterface
{
    public function getStatusCode();
    public function setStatusCode($code);
    public function setContentTypeByExtension($type);
    public function cacheUntil($expires);
    public function enableCors($origin = '*', $creds = false, $age = 3600, array $methods = null, array $headers = null);
    public function setCookie($name, $value, $extra = '');
    public function getCookie($name, $default = null);
    public function removeCookie($name);
    public function expireCookie($name, $extra = '');
    public function send(RequestInterface $req = null);
}
