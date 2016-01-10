<?php
namespace vakata\http;
/**
 * A class representing uploaded files in an HTML multipart request.
 */
class Upload implements UploadInterface
{
    protected $name = '';
    protected $body = null;
    protected $path = null;
    protected $size = null;

    /**
     * Create an instance
     * @method __construct
     * @param  string      $name the file name (optional - defaults to an empty string)
     * @param  string      $path the path to the file (if it is a file on the filesystem)
     * @param  stream|string      $body the contents of the file - optional (either a stream resource or string)
     */
    public function __construct($name = '', $path = null, $body = null)
    {
        if ($name) {
            $this->setName($name);
        }
        if ($path) {
            $this->setPath($name);
        }
        if ($body) {
            $this->setBody($body);
        }
    }
    /**
     * Create an instance from the current client request
     * @method fromRequest
     * @param  string      $key the key in the $_FILES array
     * @return Upload           the instance
     */
    public static function fromRequest($key)
    {
        if (!isset($_FILES) || !isset($_FILES[$key])) {
            throw new \Exception('Uploaded file not found');
        }
        $upload = new self($_FILES[$key]['name'], $_FILES[$key]['tmp_name']);
        return $upload;
    }
    /**
     * Returns whether the upload has a defined size.
     * @method hasSize
     * @return boolean  whether the upload has a defined size
     */
    public function hasSize()
    {
        return $this->size !== null;
    }
    /**
     * Get the size of the file.
     * @method getSize
     * @return string  the size in bytes
     */
    public function getSize()
    {
        return (int)$this->size;
    }
    /**
     * Set the file size.
     * @method setSize
     * @param  string  $size the size
     * @return  self
     */
    public function setSize($size)
    {
        $this->size = (int)$size;
        return $this;
    }
    /**
     * Get the name of the file.
     * @method getName
     * @return string  the name
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Set the file name.
     * @method setName
     * @param  string  $name the name
     * @return  self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    /**
     * Get the file path (if available)
     * @method getPath
     * @return string  the path
     */
    public function getPath()
    {
        return $this->path;
    }
    /**
     * Set the file path (which also updates the body of the upload)
     * @method setPath
     * @param  string  $path the path
     * @return  self
     */
    public function setPath($path)
    {
        $this->path = $path;
        try {
            $temp = fopen($this->path, 'r');
            if (!$temp) {
                throw new \Exception('Can not open path');
            }
            $size = @filesize($this->path);
            if ($size !== false) {
                $this->setSize($size);
            }
            if ($this->name === '') {
                $this->name = basename($path);
            }
            $this->body = $temp;
        } catch (\Exception $e) {
            $this->size = null;
            $this->path = null;
            $this->body = null;
        }
        return $this;
    }
    /**
     * Get the file body (as a string or as a stream resource)
     * @method getBody
     * @param  boolean $asString should a string be returned (defaults to `false`)
     * @return stream|string            the body of the file
     */
    public function getBody($asString = false)
    {
        if (!$this->body) {
            return $asString ? '' : null;
        }
        $body = $asString ? stream_get_contents($this->body) : $this->body;
        rewind($this->body);
        return $body;
    }
    /**
     * Set the upload file body (either set to a stream resource or a string).
     * @method setBody
     * @param  stream|string  $body the body to use
     * @return self
     */
    public function setBody($body)
    {
        if (is_string($body)) {
            $this->path = null;
            $this->body = fopen('php://temp', 'r+');
            $size = fwrite($this->body, $body);
            $this->setSize($size);
            rewind($this->body);
        }
        else {
            $this->size = null;
            $this->path = null;
            $this->body = $body;
        }
        return $this;
    }
    /**
     * Save the file to a location.
     * @method saveAs
     * @param  string $dest the destination (a file system path)
     * @return bool       was the file saved
     */
    public function saveAs($dest)
    {
        if ($this->body) {
            try {
                $out = fopen($dest, 'w');
                stream_copy_to_stream($this->body, $out);
                fclose($out);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        if ($this->path) {
            if (is_uploaded_file($this->path)) {
                return move_uploaded_file($this->path, $dest);
            }
            $temp = file_get_contents($this->path);
            return $temp !== false && file_put_contents($dest, $temp) > 0;
        }
        return false;
    }
}
