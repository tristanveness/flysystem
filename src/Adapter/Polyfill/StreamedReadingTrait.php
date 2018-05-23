<?php

namespace League\Flysystem\Adapter\Polyfill;
use League\Flysystem\FilesystemOperationFailedException;

/**
 * A helper for adapters that only handle strings to provide read streams.
 */
trait StreamedReadingTrait
{
    /**
     * Reads a file as a stream.
     *
     * @param string $path
     *
     * @return array
     *
     * @throws FilesystemOperationFailedException
     *
     */
    public function readStream(string $path): array
    {
        $data = $this->read($path);
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $data['contents']);
        rewind($stream);
        $data['stream'] = $stream;
        unset($data['contents']);

        return $data;
    }

    abstract public function read(string $path): array;
}
