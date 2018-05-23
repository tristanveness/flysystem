<?php

namespace League\Flysystem\Adapter;

use DirectoryIterator;
use FilesystemIterator;
use finfo as Finfo;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Exception;
use League\Flysystem\FilesystemOperationFailedException;
use League\Flysystem\NotSupportedException;
use League\Flysystem\UnreadableFileException;
use League\Flysystem\Util;
use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class Local extends AbstractAdapter
{
    /**
     * @var int
     */
    const SKIP_LINKS = 0001;

    /**
     * @var int
     */
    const DISALLOW_LINKS = 0002;

    /**
     * @var array
     */
    protected static $permissions = [
        'file' => [
            'public' => 0644,
            'private' => 0600,
        ],
        'dir' => [
            'public' => 0755,
            'private' => 0700,
        ]
    ];

    /**
     * @var string
     */
    protected $pathSeparator = DIRECTORY_SEPARATOR;

    /**
     * @var array
     */
    protected $permissionMap;

    /**
     * @var int
     */
    protected $writeFlags;
    /**
     * @var int
     */
    private $linkHandling;

    /**
     * Constructor.
     *
     * @param string $root
     * @param int    $writeFlags
     * @param int    $linkHandling
     * @param array  $permissions
     *
     * @throws LogicException
     */
    public function __construct($root, $writeFlags = LOCK_EX, $linkHandling = self::DISALLOW_LINKS, array $permissions = [])
    {
        $root = is_link($root) ? realpath($root) : $root;
        $this->permissionMap = array_replace_recursive(static::$permissions, $permissions);
        $this->ensureDirectory($root);

        if ( ! is_dir($root) || ! is_readable($root)) {
            throw new LogicException('The root path ' . $root . ' is not readable.');
        }

        $this->setPathPrefix($root);
        $this->writeFlags = $writeFlags;
        $this->linkHandling = $linkHandling;
    }

    /**
     * Ensure a directory exists.
     *
     * @param string $path root directory path
     *
     * @return void
     *
     * @throws Exception in case the root directory can not be created
     */
    protected function ensureDirectory($path)
    {
        if ( ! is_dir($path)) {
            $umask = umask(0);
            @mkdir($path, $this->permissionMap['dir']['public'], true);
            umask($umask);

            if ( ! is_dir($path)) {
                throw new Exception(sprintf('Impossible to create the root directory "%s".', $path));
            }
        }
    }

    public function has(string $path)
    {
        $location = $this->applyPathPrefix($path);

        return file_exists($location);
    }

    public function write(string $path, string $contents, Config $config): array
    {
        $location = $this->applyPathPrefix($path);
        $this->ensureDirectory(dirname($location));

        if (($size = file_put_contents($location, $contents, $this->writeFlags)) === false) {
            throw FilesystemOperationFailedException::write("Could not write to file at location {$location}.");
        }

        $type = 'file';
        $result = compact('contents', 'type', 'size', 'path');

        if ($visibility = $config->get('visibility')) {
            $result['visibility'] = $visibility;
            $this->setVisibility($path, $visibility);
        }

        return $result;
    }

    public function writeStream(string $path, $resource, Config $config): array
    {
        $location = $this->applyPathPrefix($path);
        $this->ensureDirectory(dirname($location));
        $stream = fopen($location, 'w+b');

        if ( ! $stream) {
            throw FilesystemOperationFailedException::writeStream("Could not open stream at location {$location}.");
        }

        stream_copy_to_stream($resource, $stream);

        if ( ! fclose($stream)) {
            throw FilesystemOperationFailedException::writeStream("Could not close stream for file at location {$location}.");
        }

        $type = 'file';
        $result = compact('type', 'path');

        if ($visibility = $config->get('visibility')) {
            $this->setVisibility($path, $visibility);
            $result['visibility'] = $visibility;
        }

        return $result;
    }

    public function readStream(string $path): array
    {
        $location = $this->applyPathPrefix($path);
        $stream = fopen($location, 'rb');

        return ['type' => 'file', 'path' => $path, 'stream' => $stream];
    }

    public function updateStream(string $path, $resource, Config $config): array
    {
        return $this->writeStream($path, $resource, $config);
    }

    public function update(string $path, string $contents, Config $config): array
    {
        $type = 'file';
        $location = $this->applyPathPrefix($path);
        $size = file_put_contents($location, $contents, $this->writeFlags);

        if ($size === false) {
            throw FilesystemOperationFailedException::update("Could not update file at location {$location}.");
        }

        return compact('type', 'path', 'size', 'contents');
    }

    public function read(string $path): array
    {
        $location = $this->applyPathPrefix($path);
        $contents = file_get_contents($location);

        if ($contents === false) {
            throw FilesystemOperationFailedException::read("Could not read file at location {$location}.");
        }

        return ['type' => 'file', 'path' => $path, 'contents' => $contents];
    }

    public function rename(string $source, string $destination): void
    {
        $location = $this->applyPathPrefix($source);
        $newLocation = $this->applyPathPrefix($destination);
        $this->ensureDirectory(Util::dirname($newLocation));

        if ( ! rename($location, $newLocation)) {
            throw FilesystemOperationFailedException::rename("Could not rename {$source} to {$destination}.");
        }
    }

    public function copy(string $source, string $destination): void
    {
        $location = $this->applyPathPrefix($source);
        $newLocation = $this->applyPathPrefix($destination);
        $this->ensureDirectory(dirname($newLocation));

        if ( ! copy($location, $newLocation)) {
            throw FilesystemOperationFailedException::copy("Unable to copy {$source} to {$destination}.");
        }
    }

    public function delete(string $path): void
    {
        $location = $this->applyPathPrefix($path);

        if ( ! @unlink($location)) {
            throw FilesystemOperationFailedException::delete("Could not delete file at location {$location}.");
        }
    }

    public function listContents(string $path, bool $recursive = false): array
    {
        $result = [];
        $location = $this->applyPathPrefix($path);

        if ( ! is_dir($location)) {
            return [];
        }

        $iterator = $recursive ? $this->getRecursiveDirectoryIterator($location) : new DirectoryIterator($location);

        foreach ($iterator as $file) {
            if ($recursive === false && $file->isDot()) continue;
            $result[] = $this->normalizeFileInfo($file);
        }

        return array_filter($result);
    }

    public function getMetadata(string $path): array
    {
        $location = $this->applyPathPrefix($path);

        return $this->normalizeFileInfo(new SplFileInfo($location));
    }

    public function getSize(string $path): array
    {
        return $this->getMetadata($path);
    }

    public function getMimetype(string $path): array
    {
        $location = $this->applyPathPrefix($path);
        $finfo = new Finfo(FILEINFO_MIME_TYPE);
        $mimetype = $finfo->file($location);

        if (in_array($mimetype, ['application/octet-stream', 'inode/x-empty'])) {
            $mimetype = Util\MimeType::detectByFilename($location);
        }

        return ['path' => $path, 'type' => 'file', 'mimetype' => $mimetype];
    }

    public function getTimestamp(string $path): array
    {
        return $this->getMetadata($path);
    }

    public function getVisibility(string $path): array
    {
        $location = $this->applyPathPrefix($path);
        clearstatcache(false, $location);
        $permissions = octdec(substr(sprintf('%o', fileperms($location)), -4));
        $visibility = $permissions & 0044 ? AdapterInterface::VISIBILITY_PUBLIC : AdapterInterface::VISIBILITY_PRIVATE;

        return compact('path', 'visibility');
    }

    public function setVisibility(string $path, string $visibility): array
    {
        $location = $this->applyPathPrefix($path);
        $type = is_dir($location) ? 'dir' : 'file';

        if (chmod($location, $this->permissionMap[$type][$visibility]) === false) {
            throw FilesystemOperationFailedException::setVisibility("Unable to chmod file at location {$location}.");
        }

        return compact('path', 'visibility');
    }

    public function createDir(string $path, Config $config): array
    {
        $location = $this->applyPathPrefix($path);
        $umask = umask(0);
        $visibility = $config->get('visibility', 'public');

        try {
            if ( ! is_dir($location) && ! mkdir($location, $this->permissionMap['dir'][$visibility], true)) {
                throw FilesystemOperationFailedException::createDir("Unable to create directory at location {$location}.");
            }

            return ['path' => $path, 'type' => 'dir'];
        } finally {
            umask($umask);
        }
    }

    public function deleteDir(string $dirname): void
    {
        $location = $this->applyPathPrefix($dirname);

        if ( ! is_dir($location)) {
            throw FilesystemOperationFailedException::deleteDir("Location '{$location}' is not a directory.");
        }

        $contents = $this->getRecursiveDirectoryIterator($location, RecursiveIteratorIterator::CHILD_FIRST);

        /** @var SplFileInfo $file */
        foreach ($contents as $file) {
            $this->deleteFileInfoObject($file);
        }

        if ( ! rmdir($location)) {
            throw FilesystemOperationFailedException::deleteDir("Unable to delete directory '{$location}'.");
        }
    }

    /**
     * @param SplFileInfo $file
     */
    protected function deleteFileInfoObject(SplFileInfo $file)
    {
        if ( ! $file->isReadable()) {
            throw UnreadableFileException::forFileInfo($file);
        }

        switch ($file->getType()) {
            case 'dir':
                rmdir($file->getRealPath());
                break;
            case 'link':
                unlink($file->getPathname());
                break;
            default:
                unlink($file->getRealPath());
        }
    }

    /**
     * Normalize the file info.
     *
     * @param SplFileInfo $file
     *
     * @return array|void
     *
     * @throws NotSupportedException
     */
    protected function normalizeFileInfo(SplFileInfo $file)
    {
        if ( ! $file->isLink()) {
            return $this->mapFileInfo($file);
        }

        if ($this->linkHandling & self::DISALLOW_LINKS) {
            throw NotSupportedException::forLink($file);
        }
    }

    /**
     * Get the normalized path from a SplFileInfo object.
     *
     * @param SplFileInfo $file
     *
     * @return string
     */
    protected function getFilePath(SplFileInfo $file)
    {
        $location = $file->getPathname();
        $path = $this->removePathPrefix($location);

        return trim(str_replace('\\', '/', $path), '/');
    }

    /**
     * @param string $path
     * @param int    $mode
     *
     * @return RecursiveIteratorIterator
     */
    protected function getRecursiveDirectoryIterator($path, $mode = RecursiveIteratorIterator::SELF_FIRST)
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            $mode
        );
    }

    /**
     * @param SplFileInfo $file
     *
     * @return array
     */
    protected function mapFileInfo(SplFileInfo $file)
    {
        $normalized = [
            'type' => $file->getType(),
            'path' => $this->getFilePath($file),
        ];

        $normalized['timestamp'] = $file->getMTime();

        if ($normalized['type'] === 'file') {
            $normalized['size'] = $file->getSize();
        }

        return $normalized;
    }

    /**
     * @param SplFileInfo $file
     *
     * @throws UnreadableFileException
     */
    protected function guardAgainstUnreadableFileInfo(SplFileInfo $file)
    {
        if ( ! $file->isReadable()) {
            throw UnreadableFileException::forFileInfo($file);
        }
    }
}
