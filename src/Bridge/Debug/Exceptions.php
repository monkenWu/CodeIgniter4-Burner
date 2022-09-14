<?php

namespace Monken\CIBurner\Bridge\Debug;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Config\Services;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use Config\Paths;
use ErrorException;
use Monken\CIBurner\Bridge\ResponseBridge;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Exceptions manager
 */
class Exceptions
{
    use ResponseTrait;

    /**
     * Nesting level of the output buffering mechanism
     */
    public int $ob_level;

    /**
     * The path to the directory containing the
     * cli and html error view directories.
     */
    protected string $viewPath;

    /**
     * Config for debug exceptions.
     *
     * @var \Config\Exceptions
     */
    protected $config;

    /**
     * The incoming request.
     *
     * @var IncomingRequest
     */
    protected $request;

    /**
     * The outgoing response.
     *
     * @var Response
     */
    protected $response;

    /**
     * Roadrunner Request
     */
    protected ServerRequestInterface $rRequest;

    /**
     * Roadrunner Client
     *
     * @var \Spiral\RoadRunner\PSR7Client
     */
    protected $client;

    // --------------------------------------------------------------------
    /**
     * Constructor.
     *
     * @param \Config\Exceptions $config
     * @param IncomingRequest    $request
     * @param Response           $response
     */
    public function __construct(
        ServerRequestInterface $rRequest
    ) {
        $this->config   = new \Config\Exceptions();
        $this->ob_level = ob_get_level();
        $this->viewPath = rtrim($this->config->errorViewPath, '/ ') . '/';
        $this->request  = Services::request();
        $this->response = Services::response();
        $this->rRequest = &$rRequest;
    }

    public function exceptionHandler($exception)
    {
        // @codeCoverageIgnoreStart
        $codes      = $this->determineCodes($exception);
        $statusCode = $codes[0];

        // Log it
        if ($this->config->log === true && ! in_array($statusCode, $this->config->ignoreCodes, true)) {
            log_message('critical', $exception->getMessage() . "\n{trace}", [
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        if (! is_cli()) {
            $this->response->setStatusCode($statusCode);
            $header = "HTTP/{$this->request->getProtocolVersion()} {$this->response->getStatusCode()} {$this->response->getReasonPhrase()}";
            header($header, true, $statusCode);
            if (strpos($this->rRequest->getHeaderLine('accept'), 'text/html') === false) {
                $msg = $this->collectVars($exception, $statusCode);
                if (ENVIRONMENT === 'development') {
                    $this->response->setBody(json_encode($msg));
                }
                $this->response->setStatusCode($statusCode);

                return new ResponseBridge($this->response->send(), $this->rRequest);
            }
        }

        return $this->render($exception, $statusCode);

        // @codeCoverageIgnoreEnd
    }

    /**
     * Even in PHP7, some errors make it through to the errorHandler, so
     * convert these to Exceptions and let the exception handler log it and
     * display it.
     *
     * This seems to be primarily when a user triggers it with trigger_error().
     *
     * @throws ErrorException
     */
    public function errorHandler(int $severity, string $message, ?string $file = null, ?int $line = null)
    {
        if (! (\error_reporting() & $severity)) {
            return;
        }

        // Convert it to an exception and pass it along.
        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    // --------------------------------------------------------------------

    /**
     * Determines the view to display based on the exception thrown,
     * whether an HTTP or CLI request, etc.
     *
     * @return string The path and filename of the view file to use
     */
    protected function determineView(Throwable $exception, string $template_path): string
    {
        // Production environments should have a custom exception file.
        $view          = 'production.php';
        $template_path = rtrim($template_path, '/ ') . '/';

        if (str_ireplace(['off', 'none', 'no', 'false', 'null'], '', ini_get('display_errors'))) {
            $view = 'error_exception.php';
        }

        // 404 Errors
        if ($exception instanceof PageNotFoundException) {
            return 'error_404.php';
        }

        // Allow for custom views based upon the status code
        if (is_file($template_path . 'error_' . $exception->getCode() . '.php')) {
            return 'error_' . $exception->getCode() . '.php';
        }

        return $view;
    }

    // --------------------------------------------------------------------

    /**
     * Given an exception and status code will display the error to the client.
     */
    protected function render(Throwable $exception, int $statusCode)
    {
        // Determine directory with views
        $path = $this->viewPath;
        if (empty($path)) {
            $paths = new Paths();
            $path  = $paths->viewDirectory . '/errors/';
        }

        $path = is_cli() ? $path . 'cli/' : $path . 'html/';

        // Determine the vew
        $view = $this->determineView($exception, $path);

        // Prepare the vars
        $vars = $this->collectVars($exception, $statusCode);
        extract($vars);

        // Render it
        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_clean();
        }
        ob_start();
        include $path . $view;
        $buffer = ob_get_contents();
        ob_end_clean();
        $this->response->setBody($buffer);

        return new ResponseBridge($this->response, $this->rRequest);
    }

    // --------------------------------------------------------------------

    /**
     * Gathers the variables that will be made available to the view.
     */
    protected function collectVars(Throwable $exception, int $statusCode): array
    {
        return [
            'title'   => get_class($exception),
            'type'    => get_class($exception),
            'code'    => $statusCode,
            'message' => $exception->getMessage() ?? '(null)',
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $exception->getTrace(),
        ];
    }

    /**
     * Determines the HTTP status code and the exit status code for this request.
     */
    protected function determineCodes(Throwable $exception): array
    {
        $statusCode = abs($exception->getCode());

        if ($statusCode < 100 || $statusCode > 599) {
            $exitStatus = $statusCode + EXIT__AUTO_MIN; // 9 is EXIT__AUTO_MIN
            if ($exitStatus > EXIT__AUTO_MAX) { // 125 is EXIT__AUTO_MAX
                $exitStatus = EXIT_ERROR; // EXIT_ERROR
            }
            $statusCode = 500;
        } else {
            $exitStatus = 1; // EXIT_ERROR
        }

        return [
            $statusCode ?? 500,
            $exitStatus,
        ];
    }

    // --------------------------------------------------------------------
    // --------------------------------------------------------------------
    // Display Methods
    // --------------------------------------------------------------------

    /**
     * Clean Path
     *
     * This makes nicer looking paths for the error output.
     */
    public static function cleanPath(string $file): string
    {
        switch (true) {
            case strpos($file, APPPATH) === 0:
                $file = 'APPPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(APPPATH));
                break;

            case strpos($file, SYSTEMPATH) === 0:
                $file = 'SYSTEMPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(SYSTEMPATH));
                break;

            case strpos($file, FCPATH) === 0:
                $file = 'FCPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(FCPATH));
                break;

            case defined('VENDORPATH') && strpos($file, VENDORPATH) === 0:
                $file = 'VENDORPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(VENDORPATH));
                break;
        }

        return $file;
    }

    // --------------------------------------------------------------------

    /**
     * Describes memory usage in real-world units. Intended for use
     * with memory_get_usage, etc.
     *
     * @param $bytes
     */
    public static function describeMemory(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . 'B';
        }
        if ($bytes < 1_048_576) {
            return round($bytes / 1024, 2) . 'KB';
        }

        return round($bytes / 1_048_576, 2) . 'MB';
    }

    // --------------------------------------------------------------------

    /**
     * Creates a syntax-highlighted version of a PHP file.
     *
     * @return bool|string
     */
    public static function highlightFile(string $file, int $lineNumber, int $lines = 15)
    {
        if (empty($file) || ! is_readable($file)) {
            return false;
        }

        // Set our highlight colors:
        if (function_exists('ini_set')) {
            ini_set('highlight.comment', '#767a7e; font-style: italic');
            ini_set('highlight.default', '#c7c7c7');
            ini_set('highlight.html', '#06B');
            ini_set('highlight.keyword', '#f1ce61;');
            ini_set('highlight.string', '#869d6a');
        }

        try {
            $source = file_get_contents($file);
        } catch (Throwable $e) {
            return false;
        }

        $source = str_replace(["\r\n", "\r"], "\n", $source);
        $source = explode("\n", highlight_string($source, true));
        $source = str_replace('<br />', "\n", $source[1]);

        $source = explode("\n", str_replace("\r\n", "\n", $source));

        // Get just the part to show
        $start = $lineNumber - (int) round($lines / 2);
        $start = $start < 0 ? 0 : $start;

        // Get just the lines we need to display, while keeping line numbers...
        $source = array_splice($source, $start, $lines, true);

        // Used to format the line number in the source
        $format = '% ' . strlen(sprintf('%s', $start + $lines)) . 'd';

        $out = '';
        // Because the highlighting may have an uneven number
        // of open and close span tags on one line, we need
        // to ensure we can close them all to get the lines
        // showing correctly.
        $spans = 1;

        foreach ($source as $n => $row) {
            $spans += substr_count($row, '<span') - substr_count($row, '</span');
            $row = str_replace(["\r", "\n"], ['', ''], $row);

            if (($n + $start + 1) === $lineNumber) {
                preg_match_all('#<[^>]+>#', $row, $tags);
                $out .= sprintf(
                    "<span class='line highlight'><span class='number'>{$format}</span> %s\n</span>%s",
                    $n + $start + 1,
                    strip_tags($row),
                    implode('', $tags[0])
                );
            } else {
                $out .= sprintf('<span class="line"><span class="number">' . $format . '</span> %s', $n + $start + 1, $row) . "\n";
            }
        }

        if ($spans > 0) {
            $out .= str_repeat('</span>', $spans);
        }

        return '<pre><code>' . $out . '</code></pre>';
    }

    // --------------------------------------------------------------------
}
