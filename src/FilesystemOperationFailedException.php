<?php

namespace League\Flysystem;

use Throwable;

class FilesystemOperationFailedException extends Exception
{
    public function __construct(string $operation, string $reason, Throwable $previous = null)
    {
        parent::__construct("Operation League\Flysystem\Fileststem::{$operation} failed. Reason: {$reason}", 0, $previous);
    }

    public static function write(string $reason, Throwable $previous = null): FilesystemOperationFailedException
    {
        return new static('write', $reason, $previous);
    }

    public static function writeStream(string $reason, Throwable $previous = null): FilesystemOperationFailedException
    {
        return new static('stream', $reason, $previous);
    }

    public static function copy(string $reason, Throwable $previous = null): FilesystemOperationFailedException
    {
        return new static('copy', $reason, $previous);
    }

    public static function deleteDir(string $reason, Throwable $previous = null): FilesystemOperationFailedException
    {
        return new static('deleteDir', $reason, $previous);
    }

    public static function createDir(string $reason, Throwable $previous = null): FilesystemOperationFailedException
    {
        return new static('createDir', $reason, $previous);
    }

    public static function rename(string $reason, Throwable $previous = null): FilesystemOperationFailedException
    {
        return new static('rename', $reason, $previous);
    }

    public static function read(string $reason, Throwable $previous = null): FilesystemOperationFailedException
    {
        return new static('read', $reason, $previous);
    }

    public static function update(string $reason, Throwable $previous = null): FilesystemOperationFailedException
    {
        return new static('read', $reason, $previous);
    }

    public static function delete(string $reason, Throwable $previous = null): FilesystemOperationFailedException
    {
        return new static('delete', $reason, $previous);
    }

    public static function setVisibility(string $reason, Throwable $previous = null): FilesystemOperationFailedException
    {
        return new static('setVisibility', $reason, $previous);
    }
}