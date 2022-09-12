<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

/**
 * @internal
 */
final class SessionTest extends BaseController
{
    use ResponseTrait;

    /**
     * Session Test (File Handler)
     *
     * @var \CodeIgniter\Session\Session
     */
    private $_session;

    protected $format = 'json';

    public function __construct()
    {
        $this->_session = service('session');
    }

    /**
     * Created Session
     * Success return 200 code
     *
     * @return
     */
    public function createdSession()
    {
        if ($data = $this->request->getPost('text')) {
            $this->_session->set('text', $data);

            return $this->respondCreated([]);
        }

        return $this->failServerError('Post Data Note Found', 400);
    }

    /**
     * Get session text
     * Not found return 404 code
     */
    public function getSessionText()
    {
        $request = \Config\Services::request();
        if ($text = $this->_session->get('text')) {
            return $this->respond(['text' => $text], 200);
        }

        return $this->failNotFound();
    }
}
