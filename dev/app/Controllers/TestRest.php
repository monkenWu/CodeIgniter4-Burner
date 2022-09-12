<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class TestRest extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        return $this->respond([
            'status' => true,
            'msg'    => 'index method successful.',
        ]);
    }

    public function show($id = null)
    {
        return $this->respond([
            'status' => true,
            'id'     => $id,
            'msg'    => 'show method successful.',
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        if ($data === null) {
            return $this->failValidationErrors('data not found', 400);
        }

        return $this->respondCreated([
            'status' => true,
            'data'   => $data,
            'msg'    => 'create method successful.',
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        return $this->respond([
            'status' => true,
            'id'     => $id,
            'data'   => $data,
            'msg'    => 'update method successful.',
        ]);
    }

    public function new()
    {
        return 'newView';
    }

    public function edit($id = null)
    {
        return $id . 'editView';
    }

    public function delete($id = null)
    {
        return $this->respondDeleted([
            'status' => true,
            'id'     => $id,
            'msg'    => 'delede method successful.',
        ]);
    }
}
