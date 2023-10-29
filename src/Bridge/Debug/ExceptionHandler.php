<?php

namespace Monken\CIBurner\Bridge\Debug;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Debug\BaseExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Paths;
use Throwable;

final class ExceptionHandler extends BaseExceptionHandler implements ExceptionHandlerInterface
{
    use ResponseTrait;

    /**
     * ResponseTrait needs this.
     */
    private ?RequestInterface $request = null;

    /**
     * ResponseTrait needs this.
     */
    private ?ResponseInterface $response = null;

    /**
     * Determines the correct way to display the error.
     */
    public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode
    ): void {
        // ResponseTrait needs these properties.
        $this->request  = $request;
        $this->response = $response;

        if ($request instanceof IncomingRequest) {
            try {
                $response->setStatusCode($statusCode);
            } catch (HTTPException $e) {
                // Workaround for invalid HTTP status code.
                $statusCode = 500;
                $response->setStatusCode($statusCode);
            }

            if (strpos($request->getHeaderLine('accept'), 'text/html') === false) {
                $data = (ENVIRONMENT === 'development' || ENVIRONMENT === 'testing')
                    ? $this->collectVars($exception, $statusCode)
                    : '';
                $response->setBody($data);
                $response->setStatusCode($statusCode);
                return;
            }
        }

        // Determine possible directories of error views
        $addPath = ($request instanceof IncomingRequest ? 'html' : 'cli') . DIRECTORY_SEPARATOR;
        $path    = $this->viewPath . $addPath;
        $altPath = rtrim((new Paths())->viewDirectory, '\\/ ')
            . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . $addPath;

        // Determine the views
        $view    = $this->determineView($exception, $path);
        $altView = $this->determineView($exception, $altPath);

        // Check if the view exists
        $viewFile = null;
        if (is_file($path . $view)) {
            $viewFile = $path . $view;
        } elseif (is_file($altPath . $altView)) {
            $viewFile = $altPath . $altView;
        }

        http_response_code($statusCode);
        $viewString = (function () use ($exception, $statusCode, $viewFile): string {
            $vars = $this->collectVars($exception, $statusCode);
            extract($vars, EXTR_SKIP);
            // CLI error views output to STDERR/STDOUT, so ob_start() does not work.
            ob_start();
            include $viewFile;
            return ob_get_clean();
        })();

        $response->setBody($viewString);
        $response->setStatusCode($statusCode);
    }

    /**
     * Determines the view to display based on the exception thrown,
     * whether an HTTP or CLI request, etc.
     *
     * @return string The filename of the view file to use
     */
    protected function determineView(Throwable $exception, string $templatePath): string
    {
        // Production environments should have a custom exception file.
        $view = 'production.php';

        if (str_ireplace(['off', 'none', 'no', 'false', 'null'], '', ini_get('display_errors')) !== '') {
            $view = 'error_exception.php';
        }

        // 404 Errors
        if ($exception instanceof PageNotFoundException) {
            return 'error_404.php';
        }

        $templatePath = rtrim($templatePath, '\\/ ') . DIRECTORY_SEPARATOR;

        // Allow for custom views based upon the status code
        if (is_file($templatePath . 'error_' . $exception->getCode() . '.php')) {
            return 'error_' . $exception->getCode() . '.php';
        }

        return $view;
    }
}
