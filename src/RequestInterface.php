<?php

namespace vakata\http;

interface RequestInterface extends MessageInterface
{
    public function getMethod();
    public function setMethod($method);

    public function getUrl();
    public function setUrl($url);

    public function hasUpload($key);
    public function getUpload($key);
    public function hasUploads();
    public function getUploads();
    public function addUpload($key, $content, $name);
    public function removeUpload($key);
    public function removeUploads();

    public function getSenderPort();
    public function setSenderPort($port);
    public function getSenderIP();
    public function setSenderIP($ip);

    // shortcuts
    public function getAuthorization();
    public function getAuthorizationToken();
    public function setAuthorizationToken($token);
    public function getPreferedResponseFormat($default = null);
    public function getPreferedResponseLanguage($default = null);
    public function getCookie($key = null, $default = null, $mode = null);
    public function getQuery($key = null, $default = null, $mode = null);
    public function getPost($key = null, $default = null, $mode = null);

    public function isAjax();
    public function isCors();

    public function send();
}
