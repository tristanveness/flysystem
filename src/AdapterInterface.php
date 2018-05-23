<?php

namespace League\Flysystem;

interface AdapterInterface
{
    /**
     * @const  VISIBILITY_PUBLIC  public visibility
     */
    const VISIBILITY_PUBLIC = 'public';

    /**
     * @const  VISIBILITY_PRIVATE  private visibility
     */
    const VISIBILITY_PRIVATE = 'private';

    /**
     * Write a new file.
     *
     * @param string $destination
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array file meta data
     *
     * @throws FilesystemOperationFailedException
     */
    public function write(string $destination, string $contents, Config $config): array;

    /**
     * Write a new file using a stream.
     *
     * @param string   $destination
     * @param resource $resource
     * @param Config   $config Config object
     *
     * @return array file meta data
     *
     * @throws FilesystemOperationFailedException
     */
    public function writeStream(string $destination, $resource, Config $config): array;

    /**
     * Update a file.
     *
     * @param string $destination
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array false on failure file meta data on success
     *
     * @throws FilesystemOperationFailedException
     */
    public function update(string $destination, string $contents, Config $config): array;

    /**
     * Update a file using a stream.
     *
     * @param string   $destination
     * @param resource $resource
     * @param Config   $config Config object
     *
     * @return array file meta data
     *
     * @throws FilesystemOperationFailedException
     */
    public function updateStream(string $destination, $resource, Config $config): array;

    /**
     * Rename a file.
     *
     * @param $source
     * @param $destination
     *
     * @throws FilesystemOperationFailedException
     */
    public function rename(string $source, string $destination): void;

    /**
     * Copy a file.
     *
     * @param string $source
     * @param string $destination
     *
     * @throws FilesystemOperationFailedException
     */
    public function copy(string $source, string $destination): void;

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @throws FilesystemOperationFailedException
     */
    public function delete(string $path): void;

    /**
     * Delete a directory.
     *
     * @param string $path
     *
     * @throws FilesystemOperationFailedException
     */
    public function deleteDir(string $path): void;

    /**
     * Create a directory.
     *
     * @param string $path
     * @param Config $config
     *
     * @return array
     *
     * @throws FilesystemOperationFailedException
     */
    public function createDir(string $path, Config $config): array;

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array file meta data
     *
     * @throws FilesystemOperationFailedException
     */
    public function setVisibility(string $path, string $visibility): array;

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function has(string $path);

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array
     *
     * @throws FilesystemOperationFailedException
     */
    public function read(string $path): array;

    /**
     * Read a file as a stream.
     *
     * @param string $source
     *
     * @return array
     *
     * @throws FilesystemOperationFailedException
     */
    public function readStream(string $source): array;

    /**
     * List contents of a directory.
     *
     * @param string $path
     * @param bool   $recursive
     *
     * @return array
     *
     * @throws FilesystemOperationFailedException
     */
    public function listContents(string $path, bool $recursive): array;

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array
     *
     * @throws FilesystemOperationFailedException
     */
    public function getMetadata(string $path): array;

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array
     *
     * @throws FilesystemOperationFailedException
     */
    public function getSize(string $path): array;

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array
     *
     * @throws FilesystemOperationFailedException
     */
    public function getMimetype(string $path): array;

    /**
     * Get the last modified time of a file as a timestamp.
     *
     * @param string $path
     *
     * @return array
     *
     * @throws FilesystemOperationFailedException
     */
    public function getTimestamp(string $path): array;

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array
     *
     * @throws FilesystemOperationFailedException
     */
    public function getVisibility(string $path): array;
}
