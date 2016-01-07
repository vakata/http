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
    public function saveAs($dest);
}
