<?php

namespace vakata\http;

interface RequestInterface extends MessageInterface
{
    public function getMethod();

    public function getServer($key = null, $default = null, $mode = null);
    public function getCookie($key = null, $default = null, $mode = null);
    public function getQuery($key = null, $default = null, $mode = null);
    public function getPost($key = null, $default = null, $mode = null);
    public function getParam($key = null, $default = null, $mode = null);
    public function getRequest($key = null, $default = null, $mode = null);

    public function getFile($key = null);

    public function isAjax();
    public function isCors();
    public function isSecure();
    public function isSelf();

    public function getResponseFormat();
    public function getAuthorization();
}
