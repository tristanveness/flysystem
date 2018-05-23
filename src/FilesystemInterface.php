<?php

namespace League\Flysystem;

use InvalidArgumentException;

interface FilesystemInterface
{
    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function has(string $path): bool;

    /**
     * Read a file.
     *
     * @param string $path The path to the file.
     *
     * @return string the file contents
     *
     * @throws FilesystemOperationFailedException
     * @throws FileNotFoundException
     */
    public function read(string $path): string;

    /**
     * Retrieves a read-stream for a path.
     *
     * @param string $path The path to the file.
     *
     * @return resource the file resource
     *
     * @throws FilesystemOperationFailedException
     * @throws FileNotFoundException
     */
    public function readStream(string $path);

    /**
     * List contents of a directory.
     *
     * @param string $directory The directory to list.
     * @param bool   $recursive Whether to list recursively.
     *
     * @return array A list of file metadata.
     *
     * @throws FilesystemOperationFailedException
     */
    public function listContents(string $directory, $recursive = false): array;

    /**
     * Get a file's metadata.
     *
     * @param string $path The path to the file.
     *
     * @return array The file metadata
     *
     * @throws FilesystemOperationFailedException
     * @throws FileNotFoundException
     */
    public function getMetadata(string $path): array;

    /**
     * Get a file's size.
     *
     * @param string $path The path to the file.
     *
     * @return int the file size
     *
     * @throws FilesystemOperationFailedException
     * @throws FileNotFoundException
     */
    public function getSize(string $path): int;

    /**
     * Get a file's mime-type.
     *
     * @param string $path The path to the file.
     *
     * @return string the file mime-type
     *
     * @throws FileNotFoundException
     * @throws FilesystemOperationFailedException
     */
    public function getMimetype(string $path): string;

    /**
     * Get a file's timestamp.
     *
     * @param string $path The path to the file.
     *
     * @return int The timestamp or false on failure.
     *
     * @throws FileNotFoundException
     * @throws FilesystemOperationFailedException
     */
    public function getTimestamp(string $path): int;

    /**
     * Get a file's visibility.
     *
     * @param string $path The path to the file.
     *
     * @return string the visibility (public|private)
     *
     * @throws FileNotFoundException
     * @throws FilesystemOperationFailedException
     */
    public function getVisibility(string $path): string;

    /**
     * Write a new file.
     *
     * @param string $path     The path of the new file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @throws FileExistsException
     * @throws FilesystemOperationFailedException
     */
    public function write(string $path, string $contents, array $config = []): void;

    /**
     * Write a new file using a stream.
     *
     * @param string   $path     The path of the new file.
     * @param resource $resource The file handle.
     * @param array    $config   An optional configuration array.
     *
     * @throws FilesystemOperationFailedException
     * @throws FileExistsException
     */
    public function writeStream(string $path, $resource, array $config = []): void;

    /**
     * Update an existing file.
     *
     * @param string $path     The path of the existing file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @throws FilesystemOperationFailedException
     * @throws FileExistsException
     */
    public function update(string $path, string $contents, array $config = []): void;

    /**
     * Update an existing file using a stream.
     *
     * @param string   $path     The path of the existing file.
     * @param resource $resource The file handle.
     * @param array    $config   An optional configuration array.
     *
     *
     * @throws FilesystemOperationFailedException
     * @throws FileExistsException
     */
    public function updateStream(string $path, $resource, array $config = []): void;

    /**
     * Rename a file.
     *
     * @param string $source
     * @param string $destination
     * @return void True on success, false on failure.
     *
     * @throws FilesystemOperationFailedException
     * @throws FileNotFoundException
     * @throws FileExistsException
     */
    public function rename(string $source, string $destination): void;

    /**
     * Copy a file.
     *
     * @param string $source      Path to the existing file.
     * @param string $destination The new path of the file.
     *
     * @throws FileExistsException   Thrown if $newpath exists.
     * @throws FileNotFoundException Thrown if $path does not exist.
     * @throws FilesystemOperationFailedException
     */
    public function copy(string $source, string $destination): void;

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @throws FileNotFoundException
     * @throws FilesystemOperationFailedException
     */
    public function delete(string $path): void;

    /**
     * Delete a directory.
     *
     * @param string $path
     *
     * @throws FilesystemOperationFailedException
     * @throws RootViolationException
     */
    public function deleteDir(string $path): void;

    /**
     * Create a directory.
     *
     * @param string $path   The name of the new directory.
     * @param array  $config An optional configuration array.
     *
     * @throws FilesystemOperationFailedException
     */
    public function createDir(string $path, array $config = []): void;

    /**
     * Set the visibility for a file.
     *
     * @param string $path       The path to the file.
     * @param string $visibility One of 'public' or 'private'.
     *
     * @throws FileNotFoundException
     * @throws FilesystemOperationFailedException
     */
    public function setVisibility(string $path, string $visibility): void;

    /**
     * Create a file or update if exists.
     *
     * @param string $path     The path to the file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @throws FilesystemOperationFailedException
     */
    public function put(string $path, string $contents, array $config = []): void;

    /**
     * Create a file or update if exists.
     *
     * @param string   $path     The path to the file.
     * @param resource $resource The file handle.
     * @param array    $config   An optional configuration array.
     *
     * @throws InvalidArgumentException Thrown if $resource is not a resource.
     * @throws FilesystemOperationFailedException
     */
    public function putStream(string $path, $resource, array $config = []): void;

    /**
     * Read and delete a file.
     *
     * @param string $path The path to the file.
     *
     * @return string the file contents
     *
     * @throws FilesystemOperationFailedException
     * @throws FileNotFoundException
     */
    public function readAndDelete(string $path): string;
}
