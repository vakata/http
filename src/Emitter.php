<?php

namespace vakata\http;

use RuntimeException;
use Laminas\Diactoros\Response as PSRResponse;

use function ob_get_length;
use function ob_get_level;
use function sprintf;
use function str_replace;
use function ucwords;

class Emitter
{
    /**
     * Checks to see if content has previously been sent.
     *
     * If either headers have been sent or the output buffer contains content,
     * raises an exception.
     *
     * @throws RuntimeException if headers have already been sent.
     * @throws RuntimeException if output is present in the output buffer.
     */
    private function assertNoPreviousOutput()
    {
        if (headers_sent()) {
            throw new RuntimeException('Unable to emit response; headers already sent');
        }

        if (ob_get_level() > 0 && ob_get_length() > 0) {
            throw new RuntimeException('Output has been emitted previously; cannot emit response');
        }
    }

    /**
     * Emit the status line.
     *
     * Emits the status line using the protocol version and status code from
     * the response; if a reason phrase is available, it, too, is emitted.
     *
     * It is important to mention that this method should be called after
     * `emitHeaders()` in order to prevent PHP from changing the status code of
     * the emitted response.
     *
     * @param PSRResponse $response
     *
     * @see \Laminas\Diactoros\Response\SapiEmitterTrait::emitHeaders()
     */
    private function emitStatusLine(PSRResponse $response)
    {
        $reasonPhrase = $response->getReasonPhrase();
        $statusCode   = $response->getStatusCode();

        header(sprintf(
            'HTTP/%s %d%s',
            $response->getProtocolVersion(),
            $statusCode,
            ($reasonPhrase ? ' ' . $reasonPhrase : '')
        ), true, $statusCode);
    }

    /**
     * Emit response headers.
     *
     * Loops through each header, emitting each; if the header value
     * is an array with multiple values, ensures that each is sent
     * in such a way as to create aggregate headers (instead of replace
     * the previous).
     *
     * @param PSRResponse $response
     */
    private function emitHeaders(PSRResponse $response)
    {
        $statusCode = $response->getStatusCode();

        foreach ($response->getHeaders() as $header => $values) {
            $name  = $this->filterHeader($header);
            $first = $name === 'Set-Cookie' ? false : true;
            foreach ($values as $value) {
                header(sprintf(
                    '%s: %s',
                    $name,
                    $value
                ), $first, $statusCode);
                $first = false;
            }
        }
    }

    /**
     * Filter a header name to wordcase
     *
     * @param string $header
     * @return string
     */
    private function filterHeader($header)
    {
        $filtered = str_replace('-', ' ', $header);
        $filtered = ucwords($filtered);
        return str_replace(' ', '-', $filtered);
    }

    /**
     * Emits a response for a PHP SAPI environment.
     *
     * Emits the status line and headers via the header() function, and the
     * body content via the output buffer.
     *
     * @param PSRResponse $response
     */
    public function emit(PSRResponse $response, $maxBufferLength = 8192)
    {
        $this->assertNoPreviousOutput();

        $this->emitHeaders($response);
        $this->emitStatusLine($response);
        
        if (($response instanceof Response) && $response->hasCallback()) {
            call_user_func($response->getCallback());
        } elseif ($response->hasHeader('Accept-Ranges')) {
            $range = $this->parseContentRange($response->getHeaderLine('Content-Range'));
            if (is_array($range) && $range[0] === 'bytes') {
                $this->emitBodyRange($range, $response, $maxBufferLength);
            } else {
                $body = $response->getBody();
                if ($body->isSeekable()) {
                    $body->rewind();
                }
                if (!$body->isReadable()) {
                    echo $body;
                } else {
                    while (!$body->eof()) {
                        echo $body->read($maxBufferLength);
                    }
                }
            }
        } else {
            echo $response->getBody();
        }
    }

    /**
     * Parse content-range header
     * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.16
     *
     * @param string $header
     * @return false|array [unit, first, last, length]; returns false if no
     *     content range or an invalid content range is provided
     */
    private function parseContentRange($header)
    {
        if (preg_match('/(?P<unit>[\w]+)\s+(?P<first>\d+)-(?P<last>\d+)\/(?P<length>\d+|\*)/', $header, $matches)) {
            return [
                $matches['unit'],
                (int) $matches['first'],
                (int) $matches['last'],
                $matches['length'] === '*' ? '*' : (int) $matches['length'],
            ];
        }
        return false;
    }
    /**
     * Emit a range of the message body.
     *
     * @param array $range
     * @param PSRResponse $response
     * @param int $maxBufferLength
     */
    private function emitBodyRange(array $range, PSRResponse $response, $maxBufferLength)
    {
        list($unit, $first, $last, $length) = $range;

        $body = $response->getBody();

        $length = $last - $first + 1;

        if ($body->isSeekable()) {
            $body->seek($first);

            $first = 0;
        }

        if (! $body->isReadable()) {
            echo substr($body->getContents(), $first, $length);
            return;
        }

        $remaining = $length;

        while ($remaining >= $maxBufferLength && ! $body->eof()) {
            $contents   = $body->read($maxBufferLength);
            $remaining -= strlen($contents);

            echo $contents;
        }

        if ($remaining > 0 && ! $body->eof()) {
            echo $body->read($remaining);
        }
    }
}
