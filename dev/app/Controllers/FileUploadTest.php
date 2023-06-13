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
        /**
         * @var \CodeIgniter\HTTP\Files\UploadedFile[]
         */
        $files = $this->request->getFiles();
        $data = [];
        
        foreach ($files as $file) {
            $newFileName = $file->getRandomName();
            $newFileNamePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $newFileName;
            if(BURNER_DRIVER == 'OpenSwoole'){
                if ($file->isValid() && ! $file->hasMoved()) {
                    $file->move(WRITEPATH . 'uploads' , $newFileName);
                    $data[$file->getClientName()] = md5_file($newFileNamePath);
                }else{
                    $data[$file->getClientName()] = 'move error';
                }
                continue;
            }else{
                if (!$file->hasMoved()) {
                    rename($file->getTempName(), $newFileNamePath);
                    $data[$file->getClientName()] = md5_file($newFileNamePath);
                }else{
                    $data[$file->getClientName()] = 'move error';
                }
            }
        }

        return $this->respondCreated($data);
    }

    /**
     * psr form-data multiple upload
     */
    public function fileMultipleUpload()
    {
        /**
         * @var \CodeIgniter\HTTP\Files\UploadedFile[]
         */
        $files = $this->request->getFileMultiple('data');
        $data = [];

        foreach ($files as $file) {
            $newFileName = $file->getRandomName();
            $newFileNamePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $newFileName;
            if(BURNER_DRIVER == 'OpenSwoole'){
                if ($file->isValid() && ! $file->hasMoved()) {
                    $file->move(WRITEPATH . 'uploads' , $newFileName);
                    $data[$file->getClientName()] = md5_file($newFileNamePath);
                }else{
                    $data[$file->getClientName()] = 'move error';
                }
                continue;
            }else{
                if (!$file->hasMoved()) {
                    rename($file->getTempName(), $newFileNamePath);
                    $data[$file->getClientName()] = md5_file($newFileNamePath);
                }else{
                    $data[$file->getClientName()] = 'move error';
                }
            }
        }

        return $this->respondCreated($data);
    }

    /**
     * psr form-data
     */
    public function psrFileUpload()
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
     * psr form-data multiple upload
     */
    public function psrFileMultipleUpload()
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
