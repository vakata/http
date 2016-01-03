<?php

namespace vakata\http;

interface UrlInterface
{
    public function current($withQuery = true);
    public function segments();
    public function segment($i, $stripExtension = false);
    public function extension($default = null);
    public function request($ext = true);
    public function root();
    public function base();
    public function server();
    public function domain();
    public function get($req = '', array $params = null);
}
