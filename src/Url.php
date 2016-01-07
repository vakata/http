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
     * @method __construct
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
     * @method fromRequest
     * @return vakata\http\Url  the URL instance of the current request
     */
    public static function fromRequest()
    {
        $port = (int)$_SERVER['SERVER_PORT'];
        $scheme = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
        $url = $scheme . '://' . $_SERVER['SERVER_NAME'];
        if (($scheme === 'http' && $port !== 80) || ($scheme === 'https' && $port !== 443)) {
            $url .= ':' . $port;
        }
        $url .= '/' . trim($_SERVER['REQUEST_URI'], '/');
        if (strlen($_SERVER['QUERY_STRING'])) {
            $url .= '?' . $_SERVER['QUERY_STRING'];
        }
        return new self($url);
    }
    /**
     * get the URL scheme (http, https, etc), defaults to `"http"`
     * @method scheme
     * @return string the scheme
     */
    public function scheme()
    {
        return $this->scheme;
    }
    /**
     * get the host part of the URL (for example - google.com), defaults to `"localhost"`
     * @method host
     * @return string the host
     */
    public function host()
    {
        return $this->host;
    }
    /**
     * get the port, if a standart port is used this will return `null`
     * @method port
     * @return string|null the port
     */
    public function port()
    {
        return $this->port;
    }
    /**
     * get the user part of the URL (if supplied in the form scheme://user:pass@domain.tld/)
     * @method user
     * @return string|null the username
     */
    public function user()
    {
        return $this->user;
    }
    /**
     * get the password part of the URL (if supplied in the form scheme://user:pass@domain.tld/)
     * @method pass
     * @return string|null the password
     */
    public function pass()
    {
        return $this->pass;
    }
    /**
     * returns the path part of the URL
     * @method path
     * @param  boolean $ext should the extension (for example .html) be returned (if any), defaults to `true`
     * @return [type]       [description]
     */
    public function path($ext = true)
    {
        return $ext ? $this->path : preg_replace('(\.[^/.]+$)', '', $this->path);
    }
    /**
     * get the extension part of the URL (like: html, gif, jpg)
     * @method extension
     * @param  string    $default the default to use if the URL does not have an extension (optional)
     * @return string|null             the extenstion
     */
    public function extension($default = null)
    {
        return strpos($this->segment(-1), '.') !== false ? preg_replace('(.*\.)', '', $this->segment(-1)) : $default;
    }
    /**
     * get the query part of the URL (after the question mark)
     * @method query
     * @return string the query
     */
    public function query()
    {
        return $this->query;
    }
    /**
     * get the fragment part of the URL (after the hash sign)
     * @method fragment
     * @return string   the fragment
     */
    public function fragment()
    {
        return $this->fragment;
    }
    /**
     * get the path part of the URL as an array (the path string exploded by `/`)
     * @method segments
     * @return array   the path segments
     */
    public function segments()
    {
        return array_values(array_filter(explode('/', $this->path), function ($var) { return $var !== ''; }));
    }
    /**
     * get a specific segment from the path part of the URL
     * @method segment
     * @param  integer  $i  the index of the segment (can be negative too)
     * @param  boolean $ext should the extension be included (if the segment is the last one), defaults to `true`
     * @return string|null  the segment (or null if the index is invalid)
     */
    public function segment($i, $ext = true)
    {
        $segs = $this->segments();
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
     * @method __toString
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
    /**
     * get a link from the current URL to another one
     * @method linkTo
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
        if ($this->scheme !== $url->scheme()) {
            return $str;
        }
        $str = preg_replace('(^[^/]+//)', '', $str);
        if ($this->host !== $url->host() || $this->port !== $url->port()) {
            return '//' . $str;
        }
        $str = preg_replace('(^[^/]+)', '', $str);
        if ($this->path !== $url->path()) {
            if ($forceAbsolute) {
                return $str;
            }
            $cnt = 0;
            $tseg = $this->segments();
            $useg = $url->segments();
            foreach ($tseg as $k => $v) {
                if (!isset($useg[$k]) || $useg[$k] !== $v) {
                    break;
                }
                $cnt ++;
            }
            $str = './' . str_repeat('../', count($useg) - $cnt) . implode('/', array_slice($useg, $cnt));
            if ($url->query) {
                $str .= '?' . $url->query;
            }
            if ($url->fragment) {
                $str .= '#' . $url->fragment;
            }
            return $str;
        }
        $str = preg_replace('(^[^?]+)', '', $str);
        if ($this->query !== $url->query() || $url->fragment === null) {
            return $str;
        }
        return '#' . $url->fragment;
    }
    /**
     * get a link to the current URL from another one
     * @method linkFrom
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
}
