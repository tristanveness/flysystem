<?php

namespace League\Flysystem\Adapter\Polyfill;

use League\Flysystem\Config;
use League\Flysystem\FilesystemOperationFailedException;
use League\Flysystem\Util;

trait StreamedWritingTrait
{
    /**
     * Stream fallback delegator.
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config
     * @param string   $fallback
     *
     * @return array fallback result
     *
     * @throws FilesystemOperationFailedException
     */
    protected function stream(string $path, $resource, Config $config, $fallback): array
    {
        Util::rewindStream($resource);

        return call_user_func([$this, $fallback], $path, stream_get_contents($resource), $config);
    }

    /**
     * Write using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config
     *
     * @return array file metadata
     *
     * @throws FilesystemOperationFailedException
     */
    public function writeStream(string $path, $resource, Config $config): array
    {
        return $this->stream($path, $resource, $config, 'write');
    }

    /**
     * Update a file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config Config object or visibility setting
     *
     * @return array file metadata
     *
     * @throws FilesystemOperationFailedException
     */
    public function updateStream(string $path, $resource, Config $config): array
    {
        return $this->stream($path, $resource, $config, 'update');
    }

    abstract public function write(string $path, string $contents, Config $config): array;

    abstract public function update(string $path, string $contents, Config $config): array;
}
