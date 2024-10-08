<?php

namespace Algolia\AlgoliaSearch\Http\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Provides a buffer stream that can be written to to fill a buffer, and read
 * from to remove bytes from the buffer.
 *
 * This stream returns a "hwm" metadata value that tells upstream consumers
 * what the configured high water mark of the stream is, or the maximum
 * preferred size of the buffer.
 *
 * @internal
 */
class BufferStream implements StreamInterface
{
    private $hwm;

    private $buffer = '';

    /**
     * @param int $hwm High water mark, representing the preferred maximum
     *                 buffer size. If the size of the buffer exceeds the high
     *                 water mark, then calls to write will continue to succeed
     *                 but will return false to inform writers to slow down
     *                 until the buffer has been drained by reading from it.
     */
    public function __construct($hwm = 16384)
    {
        $this->hwm = $hwm;
    }

    public function __toString(): string
    {
        return $this->getContents();
    }

    public function getContents(): string
    {
        $buffer = $this->buffer;
        $this->buffer = '';

        return $buffer;
    }

    public function close(): void
    {
        $this->buffer = '';
    }

    public function detach(): void
    {
        $this->close();
    }

    public function getSize(): int
    {
        return mb_strlen($this->buffer);
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new \RuntimeException('Cannot seek a BufferStream');
    }

    public function eof(): bool
    {
        return 0 === mb_strlen($this->buffer);
    }

    public function tell(): int
    {
        throw new \RuntimeException('Cannot determine the position of a BufferStream');
    }

    /**
     * Reads data from the buffer.
     *
     * @param mixed $length
     */
    public function read($length): string
    {
        $currentLength = mb_strlen($this->buffer);

        if ($length >= $currentLength) {
            // No need to slice the buffer because we don't have enough data.
            $result = $this->buffer;
            $this->buffer = '';
        } else {
            // Slice up the result to provide a subset of the buffer.
            $result = mb_substr($this->buffer, 0, $length);
            $this->buffer = mb_substr($this->buffer, $length);
        }

        return $result;
    }

    /**
     * Writes data to the buffer.
     *
     * @param mixed $string
     */
    public function write($string): int
    {
        $this->buffer .= $string;

        // TODO: What should happen here?
        if (mb_strlen($this->buffer) >= $this->hwm) {
            return false;
        }

        return mb_strlen($string);
    }

    public function getMetadata($key = null)
    {
        if ('hwm' === $key) {
            return $this->hwm;
        }

        return $key ? null : [];
    }
}
