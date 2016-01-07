<?php

namespace vakata\http;

interface RequestInterface extends MessageInterface
{
    public function getMethod();
    public function setMethod($method);

    public function getUrl();
    public function setUrl($url);

    public function getFile($key);
    public function getFiles();
    public function addFile($key, $content, $name);
    public function removeFile($key);

    // shortcuts
    public function getAuthorization();
    public function getResponseFormat($default = null);
    public function getCookie($key = null, $default = null, $mode = null);
    public function getQuery($key = null, $default = null, $mode = null);
    public function getPost($key = null, $default = null, $mode = null);
    public function getParam($key = null, $default = null, $mode = null);

    public function isAjax();
    public function isCors();
    public function isSecure();
    public function isSelf();

    public function send();
}
