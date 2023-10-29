<?php

namespace Monken\CIBurner\Bridge\Override;

use CodeIgniter\Debug\Toolbar as CiToolbar;
use Config\Services;

class Toolbar extends CiToolbar
{
    public function respond()
    {
        if (ENVIRONMENT === 'testing') {
            return;
        }

        $request = Services::request();

        // If the request contains '?debugbar then we're
        // simply returning the loading script
        if ($request->getGet('debugbar') !== null) {
            ob_start();
            include $this->config->viewsPath . 'toolbarloader.js';
            $output = ob_get_clean();
            $output = str_replace('{url}', rtrim(site_url(), '/'), $output);
            return $this->getResponse($output, 200, 'application/javascript');
        }

        // Otherwise, if it includes ?debugbar_time, then
        // we should return the entire debugbar.
        if ($request->getGet('debugbar_time')) {
            helper('security');

            // Negotiate the content-type to format the output
            $format = $request->negotiate('media', ['text/html', 'application/json', 'application/xml']);
            $format = explode('/', $format)[1];

            $filename = sanitize_filename('debugbar_' . $request->getGet('debugbar_time'));
            $filename = WRITEPATH . 'debugbar/' . $filename . '.json';

            if (is_file($filename)) {
                // Show the toolbar if it exists
                $contents = $this->format(file_get_contents($filename), $format);
                return $this->getResponse($contents, 200, $format);
            }

            // Filename not found
            return $this->getResponse('', 404, 'text/html');
        }
    }

    private function getResponse(string $body, int $code, string $contentType)
    {
        $response = Services::response();
        $response->setBody($body)->setStatusCode($code)->setHeader('Content-Type', $contentType);

        return $response;
    }

}
