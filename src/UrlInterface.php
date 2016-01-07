<?php

namespace vakata\http;

interface UrlInterface
{
    public function scheme();
    public function host();
    public function port();
    public function user();
    public function pass();
    public function path($ext = true);
    public function extension($default = null);
    public function query();
    public function fragment();
    public function segments();
    public function segment($i, $ext = true);
    public function __toString();

    public function linkFrom($url, $forceAbsolute = true);
    public function linkTo($url, $forceAbsolute = true);
}
