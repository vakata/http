<?php
namespace vakata\http;

interface UploadInterface
{
    public function getName();
    public function setName($name);
    public function getPath();
    public function setPath($path);
    public function getBody($asString = false);
    public function setBody($body);
    public function hasSize();
    public function getSize();
    public function setSize($size);
    public function saveAs($dest);
    public function appendTo($dest);
}
