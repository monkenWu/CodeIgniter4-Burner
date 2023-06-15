<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

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

        $data['mixForm'] = $this->request->getPost('mixForm');

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

        $data['mixForm'] = $this->request->getPost('mixForm');

        return $this->respondCreated($data);
    }

}
