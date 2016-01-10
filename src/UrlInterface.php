<?php

namespace vakata\http;

interface UrlInterface
{
    public function getScheme();
    public function setScheme($scheme);
    public function getHost();
    public function setHost($host);
    public function getPort();
    public function setPort($port);
    public function getUser();
    public function setUser($user);
    public function getPass();
    public function setPass($pass);
    public function getPath($ext = true);
    public function setPath($path);
    public function getQuery();
    public function setQuery($query);
    public function getFragment();
    public function setFragment($fragment);
    public function getExtension($default = null);
    public function getSegments();
    public function getSegment($i, $ext = true);
    public function __toString();

    public function linkFrom($url, $forceAbsolute = true);
    public function linkTo($url, $forceAbsolute = true);
}
