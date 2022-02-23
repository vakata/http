<?php

namespace vakata\http;

use Laminas\Diactoros\Uri as LaminasUri;

class Uri extends LaminasUri
{
    protected $basePath;
    protected $realPath;
    protected $segments;

    public function __construct($uri = '', string $base = null)
    {
        parent::__construct($uri);
        $this->setBasePath($base);
    }

    public function setBasePath(string $base = null)
    {
        $base = $base ?: (isset($_SERVER['PHP_SELF']) ? dirname($_SERVER['PHP_SELF']) : '/');
        $this->basePath = str_replace('//', '/', '/' . trim(str_replace('\\', '/', $base), '/') . '/');
        $this->realPath = $this->getPath();
        $hasTrailingSlash = strlen($this->realPath) && substr($this->realPath, -1) === '/';
        $this->realPath = explode('?', $this->realPath, 2)[0];
        $this->realPath = str_replace('//', '/', '/' . trim($this->realPath, '/') . '/');
        if (strpos($this->realPath, $this->basePath) === 0) {
            $this->realPath = substr($this->realPath, strlen($this->basePath)) ?: '';
        }
        $this->realPath = rtrim($this->realPath, '/') . ($hasTrailingSlash ? '/' : '');
        $this->segments = array_map('rawurldecode', array_filter(
            explode('/', trim($this->realPath, '/')),
            function ($v) {
                return $v !== '';
            }
        ));
        $this->segments['base'] = $this->basePath;
        $this->segments['path'] = trim($this->realPath, '/');
        return $this;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }
    public function getRealPath()
    {
        return $this->realPath;
    }
    public function getSegment($index, $default = null)
    {
        if (is_numeric($index) && (int)$index < 0) {
            $index = count($this->segments) - 2 + $index; // -2 to avoid "base" and "path"
        }
        return $this->segments[$index] ?? $default;
    }
    public function linkTo(string $path = '', array $params = [], bool $absolute = false)
    {
        if (strpos($path, '?') !== false) {
            list($path, $query) = explode('?', $path, 2);
            $params = array_merge(Request::fixedQueryParams($query), $params);
        }
        $data = parse_url($path . (count($params) ? '?' . http_build_query($params) : ''));
        if (!isset($data['host']) && !isset($data['path'])) {
            throw new \Exception('Invalid destination');
        }
        if (!isset($data['path']) || !strlen($data['path'])) {
            $data['path'] = isset($data['host']) ? '/' : $this->getBasePath();
        }
        if (substr($data['path'], 0, 1) !== '/') {
            $data['path'] = $this->basePath . $data['path'];
        }
        $data['path'] = implode('/', array_map(
            'rawurldecode',
            explode('/', $data['path'])
        ));
        $curr = parse_url((string)$this);
        unset($curr['query']);
        unset($curr['fragment']);
        $data = array_merge($curr, $data);
        $host = $data['host'] . (isset($data['port']) ? ':' . $data['port'] : '');
        $data['path'] = implode('/', array_map('rawurlencode', explode('/', $data['path'])));
        $path = $data['path'] . (isset($data['query']) ? '?' . $data['query'] : '');
        $frag = isset($data['fragment']) ? '#' . $data['fragment'] : '';

        if ($absolute || (isset($data['scheme']) && $data['scheme'] !== ($curr['scheme'] ?? null))) {
            return (isset($data['scheme']) ? $data['scheme'] . ':' : '') . '//' . $host . $path . $frag;
        }
        if ((isset($data['host']) && $data['host'] !== $curr['host']) ||
            (isset($data['port']) && $data['port'] !== $curr['port'])
        ) {
            return '//' . $host . $path . $frag;
        }
        return $path . $frag;
    }
    public function self(bool $absolute = false)
    {
        return $this->linkTo($this->realPath . ($this->getQuery() ? '?' . $this->getQuery() : ''), [], $absolute);
    }

    public function get(string $path = '', array $params = [], int $relative = 0)
    {
        return $this->linkTo($path, $params, $relative > 0);
    }
    public function __invoke(string $path = '', array $params = [], int $relative = 0)
    {
        return $this->linkTo($path, $params, $relative > 0);
    }
}
