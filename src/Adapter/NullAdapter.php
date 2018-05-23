<?php

namespace League\Flysystem\Adapter;

use League\Flysystem\Adapter\Polyfill\StreamedCopyTrait;
use League\Flysystem\Adapter\Polyfill\StreamedTrait;
use League\Flysystem\Config;

class NullAdapter extends AbstractAdapter
{
    use StreamedTrait;
    use StreamedCopyTrait;

    /**
     * Check whether a file is present.
     *
     * @param string $path
     *
     * @return bool
     */
    public function has($path)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function write($path, $contents, Config $config)
    {
        $type = 'file';
        $result = compact('contents', 'type', 'path');

        if ($visibility = $config->get('visibility')) {
            $result['visibility'] = $visibility;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function update($path, $contents, Config $config)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function read($path)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function rename($source, $destination)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function delete(string $path): void
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function listContents(string $path, bool $recursive = false): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getMetadata(string $path): array
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getSize(string $path): array
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getMimetype(string $path): array
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTimestamp(string $path): array
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getVisibility(string $path): array
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function setVisibility(string $path, string $visibility): array
    {
        return compact('visibility');
    }

    /**
     * @inheritdoc
     */
    public function createDir(string $path, Config $config): array
    {
        return ['path' => $dirname, 'type' => 'dir'];
    }

    /**
     * @inheritdoc
     */
    public function deleteDir(string $dirname): void
    {
        return false;
    }
}
