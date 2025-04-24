<?php

namespace vakata\http;

use Laminas\Diactoros\Uri as LaminasUri;

class Uri extends LaminasUri
{
    protected string $basePath;
    protected string $realPath;
    protected array $segments;

    public function __construct(string $uri = '', ?string $base = null)
    {
        parent::__construct($uri);
        $this->setBasePath($base);
    }

    public function setBasePath(?string $base = null): static
    {
        $base = $base ?: (isset($_SERVER['PHP_SELF']) ? dirname($_SERVER['PHP_SELF']) : '/');
        if ($base === '.') {
            $base = '/';
        }
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
        $base = trim(implode('/', array_map('rawurldecode', array_filter(
            explode('/', trim($this->basePath, '/')),
            function ($v) {
                return $v !== '';
            }
        ))), '/');
        $real = trim(implode('/', $this->segments), '/');
        $this->segments['base'] = '/' . $base;
        $this->segments['path'] = '/' . $real;
        return $this;
    }

    public function getBasePath(bool $decoded = false): string
    {
        return $decoded ? $this->segments['base'] : $this->basePath;
    }
    public function getRealPath(bool $decoded = false): string
    {
        return $decoded ? $this->segments['path'] : $this->realPath;
    }
    public function getSegment(mixed $index, string $default = ''): string
    {
        if (is_numeric($index) && (int)$index < 0) {
            $index = count($this->segments) - 2 + $index; // -2 to avoid "base" and "path"
        }
        return $this->segments[$index] ?? $default;
    }
    public function linkTo(string $path = '', array $params = [], bool $absolute = false): string
    {
        $orig = $path;
        if (strpos($path, '?') !== false) {
            list($path, $query) = explode('?', $path, 2);
            $params = array_merge(Request::fixedQueryParams($query), $params);
        }
        // parse_url breaks utf8 sometimes - encode it in advance
        $path = preg_replace_callback('(\pL)ui', function ($m) { return rawurlencode($m[0]); }, $path);
        $data = parse_url($path . (count($params) ? '?' . http_build_query($params) : ''));
        if (!isset($data['host']) && !isset($data['path'])) {
            return $orig;
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
        $host = isset($data['host']) ?
            $data['host'] . (isset($data['port']) ? ':' . $data['port'] : '') :
            $curr['host'] . (isset($curr['port']) ? ':' . $curr['port'] : '');
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
    public function self(bool $absolute = false): string
    {
        return $this->linkTo($this->realPath . ($this->getQuery() ? '?' . $this->getQuery() : ''), [], $absolute);
    }

    public function get(string $path = '', array $params = [], bool $absolute = false): string
    {
        return $this->linkTo($path, $params, $absolute);
    }
    public function __invoke(string $path = '', array $params = [], bool $absolute = false): string
    {
        return $this->linkTo($path, $params, $absolute);
    }
}
