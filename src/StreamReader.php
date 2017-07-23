<?php

declare(strict_types=1);

namespace Zegnat\Http;

use Psr\Http\Message\StreamInterface;

final class StreamReader
{
    private $stream = null;
    private $buffer = 4096;

    public function __construct(StreamInterface $stream)
    {
        if ($stream->isSeekable() === false
            || $stream->isReadable() === false
        ) {
            throw new \RuntimeException();
        }
        $this->stream = $stream;
    }
    
    public function inStream(string $string): int
    {
        $length = mb_strlen($string, '8bit');
        $buffer = max($this->buffer, mb_strlen())
        $this->stream->seek(0, SEEK_SET);
        while (!$this->stream->eof()) {
            $data = $this->stream->read($this->buffer);
            $found = mb_strpos($data, $string, 0, '8bit');
            if ($found !== false) {
                return $found;
            }
        }
        return -1;
    }

    public function tail(int $lines): string
    {
        $output = '';
        $chunk = '';
        $this->stream->seek(-1, SEEK_END);
        if ($this->stream->read(1) !== "\n") {
            $lines -= 1;
        }
        while ($this->stream->tell() > 0 && $lines >= 0) {
            $seek = min($this->stream->tell(), $this->buffer);
            $this->stream->seek(-$seek, SEEK_CUR);
            $chunk = $this->stream->read($seek);
            $output = $chunk . $output;
            $this->stream->seek(-mb_strlen($chunk, '8bit'), SEEK_CUR);
            $lines -= substr_count($chunk, "\n");
        }
        while ($lines++ < 0) {
            $output = substr($output, strpos($output, "\n") + 1);
        }
        return $output;
    }
}
