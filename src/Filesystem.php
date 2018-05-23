<?php

namespace League\Flysystem;

use InvalidArgumentException;
use League\Flysystem\Adapter\CanOverwriteFiles;
use League\Flysystem\Plugin\PluggableTrait;
use League\Flysystem\Util\ContentListingFormatter;

/**
 * @method array getWithMetadata(string $path, array $metadata)
 * @method bool  forceCopy(string $path, string $newpath)
 * @method bool  forceRename(string $path, string $newpath)
 * @method array listFiles(string $path = '', boolean $recursive = false)
 * @method array listPaths(string $path = '', boolean $recursive = false)
 * @method array listWith(array $keys = [], $directory = '', $recursive = false)
 */
class Filesystem implements FilesystemInterface
{
    use PluggableTrait;
    use ConfigAwareTrait;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Constructor.
     *
     * @param AdapterInterface $adapter
     * @param Config|array     $config
     */
    public function __construct(AdapterInterface $adapter, $config = null)
    {
        $this->adapter = $adapter;
        $this->setConfig($config);
    }

    /**
     * Get the Adapter.
     *
     * @return AdapterInterface adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    public function has(string $path): bool
    {
        $path = Util::normalizePath($path);

        return $path === '' ? false : (bool) $this->getAdapter()->has($path);
    }

    public function write(string $path, string $contents, array $config = []): void
    {
        $config = $this->prepareConfig($config);
        $path = Util::normalizePath($path);
        $this->assertAbsent($path);
        $this->getAdapter()->write($path, $contents, $config);
    }

    public function writeStream(string $path, $resource, array $config = []): void
    {
        if ( ! is_resource($resource)) {
            throw new InvalidArgumentException(__METHOD__ . ' expects argument #2 to be a valid resource.');
        }

        $path = Util::normalizePath($path);
        $this->assertAbsent($path);
        $config = $this->prepareConfig($config);

        Util::rewindStream($resource);

        return (bool) $this->getAdapter()->writeStream($path, $resource, $config);
    }

    public function put(string $path, string $contents, array $config = []): void
    {
        $path = Util::normalizePath($path);
        $config = $this->prepareConfig($config);

        if ( ! $this->getAdapter() instanceof CanOverwriteFiles && $this->has($path)) {
            return (bool) $this->getAdapter()->update($path, $contents, $config);
        }

        return (bool) $this->getAdapter()->write($path, $contents, $config);
    }

    public function putStream(string $path, $resource, array $config = []): void
    {
        if ( ! is_resource($resource)) {
            throw new InvalidArgumentException(__METHOD__ . ' expects argument #2 to be a valid resource.');
        }

        $path = Util::normalizePath($path);
        $config = $this->prepareConfig($config);
        Util::rewindStream($resource);

        if ( ! $this->getAdapter() instanceof CanOverwriteFiles &&$this->has($path)) {
            return (bool) $this->getAdapter()->updateStream($path, $resource, $config);
        }

        return (bool) $this->getAdapter()->writeStream($path, $resource, $config);
    }

    public function readAndDelete(string $path): string
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);
        $contents = $this->read($path);

        if ($contents === false) {
            return false;
        }

        $this->delete($path);

        return $contents;
    }

    public function update(string $path, string $contents, array $config = []): void
    {
        $path = Util::normalizePath($path);
        $config = $this->prepareConfig($config);
        $this->assertPresent($path);
        $this->getAdapter()->update($path, $contents, $config);
    }

    public function updateStream(string $path, $resource, array $config = []): void
    {
        if ( ! is_resource($resource)) {
            throw new InvalidArgumentException(__METHOD__ . ' expects argument #2 to be a valid resource.');
        }

        $path = Util::normalizePath($path);
        $config = $this->prepareConfig($config);
        $this->assertPresent($path);
        Util::rewindStream($resource);
        $this->getAdapter()->updateStream($path, $resource, $config);
    }

    public function read(string $path): string
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);

        \var_dump($this->getAdapter()->read($path));
        die();

        return $this->getAdapter()->read($path)['contents'];
    }

    public function readStream(string $path)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);

        return $this->getAdapter()->readStream($path)['stream'];
    }

    public function rename(string $source, string $destination): void
    {
        $source = Util::normalizePath($source);
        $destination = Util::normalizePath($destination);
        $this->assertPresent($source);
        $this->assertAbsent($destination);
        $this->getAdapter()->rename($source, $destination);
    }

    public function copy(string $source, string $destination): void
    {
        $source = Util::normalizePath($source);
        $destination = Util::normalizePath($destination);
        $this->assertPresent($source);
        $this->assertAbsent($destination);
        $this->getAdapter()->copy($source, $destination);
    }

    public function delete(string $path): void
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);
        $this->getAdapter()->delete($path);
    }

    public function deleteDir(string $path): void
    {
        if ($path = Util::normalizePath($path) === '') {
            throw new RootViolationException('Root directories can not be deleted.');
        }

        $this->getAdapter()->deleteDir($path);
    }

    public function createDir(string $path, array $config = []): void
    {
        $this->getAdapter()->createDir(
            Util::normalizePath($path),
            $this->prepareConfig($config)
        );
    }

    public function listContents(string $directory, $recursive = false): array
    {
        $directory = Util::normalizePath($directory);

        return (new ContentListingFormatter($directory, $recursive))
            ->formatListing($this->getAdapter()->listContents($directory, $recursive));
    }

    public function getMimetype(string $path): string
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);

        if (( ! $object = $this->getAdapter()->getMimetype($path)) || ! array_key_exists('mimetype', $object)) {
            return false;
        }

        return $object['mimetype'];
    }

    public function getTimestamp(string $path): int
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);

        if (( ! $object = $this->getAdapter()->getTimestamp($path)) || ! array_key_exists('timestamp', $object)) {
            return false;
        }

        return $object['timestamp'];
    }

    public function getVisibility(string $path): string
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);

        if (( ! $object = $this->getAdapter()->getVisibility($path)) || ! array_key_exists('visibility', $object)) {
            return false;
        }

        return $object['visibility'];
    }

    public function getSize(string $path): int
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);

        return $this->getAdapter()->getSize($path)['size'];
    }

    public function setVisibility(string $path, string $visibility): void
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);
        $this->getAdapter()->setVisibility($path, $visibility);
    }

    public function getMetadata(string $path): array
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);

        return $this->getAdapter()->getMetadata($path);
    }

    /**
     * Assert a file is present.
     *
     * @param string $path path to file
     *
     * @throws FileNotFoundException
     */
    private function assertPresent($path): void
    {
        if ($this->config->get('disable_asserts', false) === false && ! $this->has($path)) {
            throw new FileNotFoundException($path);
        }
    }

    /**
     * Assert a file is absent.
     *
     * @param string $path path to file
     *
     * @throws FileExistsException
     */
    private function assertAbsent($path): void
    {
        if ($this->config->get('disable_asserts', false) === false && $this->has($path)) {
            throw new FileExistsException($path);
        }
    }
}
