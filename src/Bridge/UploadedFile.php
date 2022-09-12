<?php

namespace Monken\CIBurner\Bridge;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use Exception;
use InvalidArgumentException;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadedFile implements UploadedFileInterface
{
    private $path;
    private $clientFilename;
    private $clientMediaType;
    private $size;

    /**
     * PHP uploaderror code
     */
    private int $error;

    /**
     * Whether the file has been moved already or not.
     */
    protected bool $hasMoved = false;

    protected $stream;

    /**
     * Accepts the file information as would be filled in from the $_FILES array.
     *
     * @param string $path     The temporary location of the uploaded file.
     * @param string $filename The client-provided filename.
     * @param string $mimeType The type of file as provided by PHP
     * @param int    $size     The size of the file, in bytes
     * @param int    $error    The error constant of the upload (one of PHP's UPLOADERRXXX constants)
     */
    public function __construct(string $path, ?string $filename = null, ?string $mimeType = null, ?int $size = null, ?int $error = null)
    {
        $this->path            = $path;
        $this->clientFilename  = $filename;
        $this->clientMediaType = $mimeType;
        $this->size            = $size;
        $this->error           = $error;

        //parent::__construct($path, false);
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * @throws RuntimeException in cases when no stream is available or can be
     *                          created.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     */
    public function getStream(): StreamInterface
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw HTTPException::forInvalidFile();
        }

        if ($this->hasMoved) {
            throw HTTPException::forAlreadyMoved();
        }

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        $this->stream = new Stream($this->path);

        return $this->stream;
    }

    /**
     * Move the uploaded file to a new location.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     *
     * @param string $targetPath Path to which to move the uploaded file.
     *
     * @throws InvalidArgumentException if the $targetPath specified is invalid.
     * @throws RuntimeException         on any error during the move operation, or on
     *                                  the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        $this->setPath($targetPath); //set the target path

        if ($this->hasMoved) {
            throw HTTPException::forAlreadyMoved();
        }

        if ($this->error !== UPLOAD_ERR_OK) {
            throw HTTPException::forInvalidFile();
        }

        try {
            rename($this->path, $targetPath);
        } catch (Exception $e) {
            $error   = error_get_last();
            $message = isset($error['message']) ? strip_tags($error['message']) : '';

            throw HTTPException::forMoveFailed(basename($this->path), $targetPath, $message);
        }

        @chmod($targetPath, 0777 & ~umask());

        $this->hasMoved = true;

        return true;
    }

    /**
     * create file target path if
     * the set path does not exist
     *
     * @param string $path
     *
     * @return string The path set or created.
     */
    protected function setPath(string $targetPath): string
    {
        $path = dirname($targetPath);
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
            //create the index.html file
            if (! is_file($path . 'index.html')) {
                $file = fopen($path . 'index.html', 'x+b');
                fclose($file);
            }
        }

        return $path;
    }

    /**
     * Retrieve the file size.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * @return string|null The filename sent by the client or null if none
     *                     was provided.
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * @return string|null The media type sent by the client or null if none
     *                     was provided.
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }
}
