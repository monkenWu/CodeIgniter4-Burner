<?php

namespace Monken\CIBurner\Bridge;

use Monken\CIBurner\Bridge\UploadedFile as ReplaceUploadedFile;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class UploadedFileBridge
{
    private static $_instance;

    /**
     * PSR-7 UploadedFile Object Array
     */
    private array $_psrFiles = [];

    public function __construct(
        array $files = [],
        bool $isBurner = false,
        bool $burnerHandle = false
    ) {
        if ($isBurner) {
            if ($burnerHandle) {
                $this->handleFile($files);
            } else {
                $this->_psrFiles = &$files;
            }
        } else {
            $this->handleFile();
        }
    }

    /**
     * get Psr7 Uploaded Files
     *
     * @return \Psr\Http\Message\UploadedFileInterface[]
     */
    public static function getPsr7UploadedFiles(
        array $files = [],
        bool $isBurner = false
    ): array {
        if (! (self::$_instance instanceof UploadedFileBridge)) {
            if ($isBurner) {
                $needNewInstance = false;
                $check       = reset($files);
                if (is_array($check)) {
                    $needNewInstance = true;
                    $multiCheck  = reset($check);
                    if ($multiCheck instanceof \Psr\Http\Message\UploadedFileInterface) {
                        $needNewInstance = false;
                    }
                }
                self::$_instance = new UploadedFileBridge(
                    $files,
                    $isBurner,
                    // is need new file instance ?
                    $needNewInstance
                );
            } else {
                self::$_instance = new UploadedFileBridge();
            }
        }

        return self::$_instance->_psrFiles;
    }

    public static function reset(): void
    {
        self::$_instance = null;
    }

    private function handleFile(?array $files = null)
    {
        if (null === $files) {
            $files = $this->fixFilesArray($_FILES);
        }

        foreach ($files as $name => $file) {
            $this->_psrFiles[$name] = $this->createFileObject($file);
        }
    }

    /**
     * Reformats the odd $_FILES array into something much more like
     * we would expect, with each object having its own array.
     *
     * Thanks to Jack Sleight on the PHP Manual page for the basis
     * of this method.
     *
     * @see http://php.net/manual/en/reserved.variables.files.php#118294
     */
    private function fixFilesArray(array $data): array
    {
        $output = [];

        foreach ($data as $name => $array) {
            foreach ($array as $field => $value) {
                $pointer = &$output[$name];

                if (! is_array($value)) {
                    $pointer[$field] = $value;

                    continue;
                }

                $stack    = [&$pointer];
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveArrayIterator($value),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($iterator as $key => $val) {
                    array_splice($stack, $iterator->getDepth() + 1);
                    $pointer = &$stack[count($stack) - 1];
                    $pointer = &$pointer[$key];
                    $stack[] = &$pointer;
                    if (! $iterator->hasChildren()) {
                        $pointer[$field] = $val;
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Given a file array, will create UploadedFile instances. Will
     * loop over an array and create objects for each.
     *
     * @return array|ReplaceUploadedFile
     */
    protected function createFileObject(array $array)
    {
        if (! isset($array['name'])) {
            $output = [];

            foreach ($array as $key => $values) {
                if (! is_array($values)) {
                    continue;
                }
                $output[$key] = $this->createFileObject($values);
            }

            return $output;
        }

        return new ReplaceUploadedFile(
            $array['tmp_name'] ?? null,
            $array['name'] ?? null,
            $array['type'] ?? null,
            $array['size'] ?? null,
            $array['error'] ?? null
        );
    }
}
