<?php

declare(strict_types=1);

namespace Zegnat\Http;

use Psr\Http\Message\StreamInterface;

final class StreamReader
{
    private $stream = null;

    public function __construct(StreamInterface $stream)
    {
        if ($stream->isSeekable() === false
           || $stream->isReadable() === false
        ) {
            throw new \RuntimeException();
        }
        $this->stream = $stream;
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
            $seek = min($this->stream->tell(), 4096);
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
