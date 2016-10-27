<?php
namespace vakata\http;

/**
 * A class representing an URL
 */
class Url implements UrlInterface
{
    protected $data = [];
    /**
     * Create an instance.
     * @param  string      $url the URL to parse
     */
    public function __construct($url)
    {
        $this->data = parse_url($url);
        if ($this->data === false) {
            throw new \Exception('Invalid input string');
        }

        $port = isset($_SERVER) && isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 80;
        $scheme = isset($_SERVER) && isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
        $this->data = array_merge([
            'scheme' => $scheme,
            'host' => isset($_SERVER) && isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost',
            'port' => ($scheme === 'http' && $port !== 80) || ($scheme === 'https' && $port !== 443) ? $port : null,
            'user' => null,
            'pass' => null,
            'path' => '/',
            'query' => null,
            'fragment' => null
        ], $this->data);
    }
    /**
     * Create an instance from the current request.
     * @return vakata\http\Url  the URL instance of the current request
     * @codeCoverageIgnore
     */
    public static function fromRequest()
    {
        $port = (int)$_SERVER['SERVER_PORT'];
        $scheme = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
        $url = $scheme . '://' . $_SERVER['SERVER_NAME'];
        if (($scheme === 'http' && $port !== 80) || ($scheme === 'https' && $port !== 443)) {
            $url .= ':' . $port;
        }
        $url .= '/' . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        if (strlen($_SERVER['QUERY_STRING'])) {
            $url .= '?' . $_SERVER['QUERY_STRING'];
        }
        return new self($url);
    }
    /**
     * Get the URL scheme (http, https, etc), defaults to `"http"`.
     * @return string the scheme
     */
    public function getScheme()
    {
        return $this->scheme;
    }
    /**
     * Set the scheme.
     * @param  string    $scheme the new scheme
     * @return  self
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }
    /**
     * Get the host part of the URL (for example - google.com), defaults to `"localhost"`.
     * @return string the host
     */
    public function getHost()
    {
        return $this->host;
    }
    /**
     * Set the host part of the URL.
     * @param  string  $host the new host
     * @return  self
     */
    public function setHost($host)
    {
        $this->host = trim($host, '/');
        return $this;
    }
    /**
     * Get the port, if a standart port is used this will return `null`.
     * @return string|null the port
     */
    public function getPort()
    {
        return $this->port;
    }
    /**
     * Set the port of the URL.
     * @param  string|int $port the new port
     * @return  self
     */
    public function setPort($port)
    {
        $this->port = (string)$port;
        return $this;
    }
    /**
     * Get the user part of the URL (if supplied in the form scheme://user:pass@domain.tld/).
     * @return string|null the username
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * Set the user part of the URL.
     * @param  string  $user the new user
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
    /**
     * Get the password part of the URL (if supplied in the form scheme://user:pass@domain.tld/).
     * @return string|null the password
     */
    public function getPass()
    {
        return $this->pass;
    }
    /**
     * Set the password part of the URL.
     * @param  string  $pass the new password
     * @return  self
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
        return $this;
    }
    /**
     * Returns the path part of the URL.
     * @param  boolean $ext should the extension (for example .html) be returned (if any), defaults to `true`
     * @return string   the path
     */
    public function getPath($ext = true)
    {
        return $ext ? $this->path : preg_replace('(\.[^/.]+$)', '', $this->path);
    }
    /**
     * Set the path part of the URL.
     * @param  string  $path the new path
     * @return  self
     */
    public function setPath($path)
    {
        $this->path = '/' . trim($path, '/');
        return $this;
    }
    /**
     * get the extension part of the URL (like: html, gif, jpg)
     * @param  string    $default the default to use if the URL does not have an extension (optional)
     * @return string|null             the extenstion
     */
    public function getExtension($default = null)
    {
        return strpos($this->getSegment(-1), '.') !== false ?
            preg_replace('(.*\.)', '', $this->getSegment(-1)) :
            $default;
    }
    /**
     * get the query part of the URL (after the question mark)
     * @return string the query
     */
    public function getQuery()
    {
        return $this->query;
    }
    /**
     * Set the query part og the URL.
     * @param  string   $query the new query
     * @return  self
     */
    public function setQuery($query)
    {
        $this->query = ltrim($query, '?');
        return $this;
    }
    /**
     * Get the fragment part of the URL (after the hash sign).
     * @return string   the fragment
     */
    public function getFragment()
    {
        return $this->fragment;
    }
    /**
     * Set the fragment part of the URL
     * @param  string      $fragment the new fragment
     * @return  self
     */
    public function setFragment($fragment)
    {
        $this->fragment = trim($fragment, '#');
        return $this;
    }
    /**
     * get the path part of the URL as an array (the path string exploded by `/`)
     * @return array   the path segments
     */
    public function getSegments()
    {
        return array_values(
            array_filter(
                explode('/', $this->path),
                function ($var) {
                    return $var !== '';
                }
            )
        );
    }
    /**
     * get a specific segment from the path part of the URL
     * @param  integer  $i  the index of the segment (can be negative too)
     * @param  boolean $ext should the extension be included (if the segment is the last one), defaults to `true`
     * @return string|null  the segment (or null if the index is invalid)
     */
    public function getSegment($i, $ext = true)
    {
        $segs = $this->getSegments();
        $i = (int) $i;
        if ($i < 0) {
            $i = count($segs) + $i;
        }
        if (!isset($segs[$i])) {
            return null;
        }
        return $ext ? $segs[$i] : preg_replace('(\.[^/.]+$)', '', $segs[$i]);
    }
    /**
     * get the entire URL back as a string
     * @return string     the URL
     */
    public function __toString()
    {
        $url = $this->scheme . '://';
        if ($this->user && $this->pass) {
            $url .= $this->user . ':' . $this->pass . '@';
        }
        $url .= $this->host;
        if ($this->port) {
            $url .= ':' . $this->port;
        }
        $url .= $this->path;
        if ($this->query) {
            $url .= '?' . $this->query;
        }
        if ($this->fragment) {
            $url .= '#' . $this->fragment;
        }
        return $url;
    }
    public function __get($k)
    {
        return isset($this->data[$k]) ? $this->data[$k] : null;
    }
    public function __set($k, $v)
    {
        if (array_key_exists($k, $this->data)) {
            $this->data[$k] = $v;
        }
    }
    /**
     * get a link from the current URL to another one
     * @param  UrlInterface|string $url the URL to link to
     * @param  boolean $forceAbsolute   should an absolute path be used, defaults to `true`)
     * @return string  the link
     */
    public function linkTo($url, $forceAbsolute = true)
    {
        if (is_string($url)) {
            $url = new Url($url);
        }

        $str = (string)$url;
        if ($this->getScheme() !== $url->getScheme()) {
            return $str;
        }
        $str = preg_replace('(^[^/]+//)', '', $str);
        if ($this->getHost() !== $url->getHost() || $this->getPort() !== $url->getPort()) {
            return '//' . $str;
        }
        $str = preg_replace('(^[^/]+)', '', $str);
        if ($this->getPath() !== $url->getPath()) {
            if ($forceAbsolute) {
                return $str;
            }
            $cnt = 0;
            $tseg = $this->getSegments();
            $useg = $url->getSegments();
            foreach ($tseg as $k => $v) {
                if (!isset($useg[$k]) || $useg[$k] !== $v) {
                    break;
                }
                $cnt ++;
            }
            $str = './' . str_repeat('../', count($useg) - $cnt) . implode('/', array_slice($useg, $cnt));
            if ($url->getQuery()) {
                $str .= '?' . $url->getQuery();
            }
            if ($url->getFragment()) {
                $str .= '#' . $url->getFragment();
            }
            return $str;
        }
        $str = preg_replace('(^[^?]+)', '', $str);
        if ($this->getQuery() !== $url->getQuery() || $url->getFragment() === null) {
            return $str;
        }
        return '#' . $url->getFragment();
    }
    /**
     * get a link to the current URL from another one
     * @param  UrlInterface|string $url the URL to link from
     * @param  boolean  $forceAbsolute  should an absolute path be used, defaults to `true`
     * @return string                   the link
     */
    public function linkFrom($url, $forceAbsolute = true)
    {
        if (is_string($url)) {
            $url = new Url($url);
        }
        return $url->linkTo($this, $forceAbsolute);
    }
    /**
     * append new segments to the current path
     * @param  array|string $segments   the URL segments to append
     * @return self
     */
    public function append($segments)
    {
        if (!is_array($segments)) {
            $segments = explode('/', $segments);
        }
        $segments = array_values(array_filter($segments));
        $path = trim($this->getPath(), '/') . '/' . implode('/', $segments);
        $path = preg_replace('(/+)', '/', $path);
        return $this->setPath($path);
    }
    /**
     * get a parameter from the URL
     * @param  string   $key     the name of the parameter
     * @param  mixed    $default the default value to return if the parameter is not found (defaults to `null`)
     * @return mixed             the value of the parameter (or the default value)
     */
    public function getParam($key, $default = null)
    {
        $data = [];
        parse_str($this->getQuery(), $data);
        return isset($data[$key]) ? $data[$key] : $default;
    }
    /**
     * set a query param on the URL
     * @param  string   $key   the param name
     * @param  mixed    $value the param value
     */
    public function setParam($key, $value)
    {
        $data = [];
        parse_str($this->getQuery(), $data);
        $data[$key] = $value;
        return $this->setQuery(http_build_query($data));
    }
}
