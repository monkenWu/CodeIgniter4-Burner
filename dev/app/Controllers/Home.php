<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $cacheData = $this->request->getGet('setCache');
        if ($cacheData !== null) {
            cache()->save('cacheDataBurner', $cacheData, 0);
        }
        print_r(cache('cacheDataBurner'));

        return view('welcome_message');
    }
}
