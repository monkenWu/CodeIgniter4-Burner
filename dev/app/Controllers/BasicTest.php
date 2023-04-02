<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

/**
 * @internal
 */
final class BasicTest extends BaseController
{
    use ResponseTrait;

    protected $format = 'json';

    /**
     * load view
     */
    public function loadView()
    {
        return view('welcome_message');
    }

    /**
     * echo text(test php output)
     */
    public function echoText()
    {
        echo 'testText';
    }

    /**
     * test query($_GET)
     */
    public function urlqyery()
    {
        $text1 = $this->request->getGet('texts')[0];
        $text2 = $this->request->getGet('texts')[1];
        $text3 = $this->request->getGet('text3');

        return md5($text1 . $text2 . $text3);
    }

    /**
     * test x-www-form-urlencoded($_POST)
     */
    public function formparams()
    {
        $text1 = $this->request->getPost('texts')[0];
        $text2 = $this->request->getPost('texts')[1];
        $text3 = $this->request->getPost('text3');

        return md5($text1 . $text2 . $text3);
    }

    /**
     * x-www-form-urlencoded and query mix test
     */
    public function formparamsandquery()
    {
        $text1 = $this->request->getGet('text1');
        $text2 = $this->request->getPost('text2');

        return md5($text1 . $text2);
    }

    /**
     * read header
     */
    public function readHeader()
    {
        $token = $this->request->header('X-Auth-Token')->getValueLine();

        return $this->respond(['X-Auth-Token' => $token]);
    }

    /**
     * send header
     */
    public function sendHeader()
    {
        $this->response->setHeader('X-Set-Auth-Token', uniqid());

        return $this->respond(['status' => true]);
    }

    /**
     * i18n test
     *
     * @return void
     */
    public function i18n()
    {
        echo lang('Burner.negotiate');
    }

    /**
     * test cookie
     */
    public function cookieCreate()
    {
        $text1 = $this->request->getGet('text1');
        $text2 = $this->request->getGet('text2');
        $text3 = $this->request->getGet('text3');

        $this->response->setCookie('text1', $text1, 3600);
        $this->response->setCookie('text2', $text2, 3600);
        $this->response->setCookie('text3', $text3, 3600);

        return $this->respond(['status' => true]);
    }

    /**
     * csrf test
     */
    public function csrfCreate()
    {
        $token = csrf_token();
        $hash  = csrf_hash();
        $body = <<< HTML
            <form action="/basicTest/csrfVerify" method="post">
                <input name="{$token}" value='{$hash}' />
                <input type="text" name="text1" />
                <input type="submit" value="submit" />
            </form>
        HTML;
        return $body;
    }

    /**
     * csrf verify
     */
    public function csrfVerify()
    {
        $text = $this->request->getPost('text1');
        return $this->respond($text, 200);
    }

}
