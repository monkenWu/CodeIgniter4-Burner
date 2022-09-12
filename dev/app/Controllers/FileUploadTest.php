<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use Monken\CIBurner\Bridge\UploadedFileBridge;

/**
 * @internal
 */
final class FileUploadTest extends BaseController
{
    use ResponseTrait;

    protected $format = 'json';

    /**
     * form-data
     */
    public function fileUpload()
    {
        $files = UploadedFileBridge::getPsr7UploadedFiles();
        $data  = [];

        foreach ($files as $file) {
            $fileNameArr = explode('.', $file->getClientFilename());
            $fileEx      = array_pop($fileNameArr);
            $newFileName = uniqid(mt_rand()) . '.' . $fileEx;
            $newFilePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $newFileName;
            $file->moveTo($newFilePath);
            $data[$file->getClientFilename()] = md5_file($newFilePath);
        }

        return $this->respondCreated($data);
    }

    /**
     * form-data multiple upload
     */
    public function fileMultipleUpload()
    {
        $files = UploadedFileBridge::getPsr7UploadedFiles()['data'];
        $data  = [];

        foreach ($files as $file) {
            $fileNameArr = explode('.', $file->getClientFilename());
            $fileEx      = array_pop($fileNameArr);
            $newFileName = uniqid(mt_rand()) . '.' . $fileEx;
            $newFilePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $newFileName;
            $file->moveTo($newFilePath);
            $data[$file->getClientFilename()] = md5_file($newFilePath);
        }

        return $this->respondCreated($data);
    }
}
