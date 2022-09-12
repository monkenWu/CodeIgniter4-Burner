<?php

namespace Monken\CIBurner\Test;

use CodeIgniter\Test\CIUnitTestCase;
use Monken\CIBurner\Bridge\UploadedFile;
use Monken\CIBurner\Bridge\UploadedFileBridge;

/**
 * @internal
 */
final class UploadedFileBridgeTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_FILES = [];
        UploadedFileBridge::reset();
    }

    public function testUploadedFileBridge()
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => 0,
            ],
        ];

        $files = UploadedFileBridge::getPsr7UploadedFiles();

        $this->assertCount(1, $files);

        $file = array_shift($files);
        $this->assertInstanceOf(UploadedFile::class, $file);

        $this->assertSame('someFile.txt', $file->getClientFilename());
        $this->assertSame(124, $file->getSize());
    }

    public function testGetFileMultiple()
    {
        $_FILES = [
            'userfile' => [
                'name' => [
                    'someFile.txt',
                    'someFile2.txt',
                ],
                'type' => [
                    'text/plain',
                    'text/plain',
                ],
                'size' => [
                    '124',
                    '125',
                ],
                'tmp_name' => [
                    '/tmp/myTempFile.txt',
                    '/tmp/myTempFile2.txt',
                ],
                'error' => [
                    0,
                    0,
                ],
            ],
        ];

        $gotit = UploadedFileBridge::getPsr7UploadedFiles()['userfile'];
        $this->assertSame(124, $gotit[0]->getSize());
        $this->assertSame(125, $gotit[1]->getSize());
    }
}
