# CodeIgniter4-Burner

Burner is an out-of-the-box library for CodeIgniter4 that supports both [RoadRunner](https://roadrunner.dev/) and [Workerman](https://github.com/walkor/workerman) high-performance web servers. All you need to do is open a few php extensions to dramatically speed up your CodeIgniter4 applications, allowing them to handle higher loads and more connections at the same time.

[正體中文說明書](README_zh-TW.md)

## Install

### Prerequisites
1. CodeIgniter Framework 4.2.0^
2. Composer
3. PHP8^
3. Enable `php-curl` extension
4. Enable `php-zip` extension
5. Enable `php-sockets` extension
6. Enable `php-pcntl` extension
7. Enable `php-posix` extension
8. If you use `Workerman` driver. We recommend you to install [php-event](https://www.php.net/manual/en/book.event.php) extension

### Composer Install
Use "Composer" to download the library and its dependencies to the project

```
composer require monken/codeigniter4-burner
```

Initialize Server files using built-in commands in the library

```
php spark burner:init [RoadRunner Or Workerman]
```

## Run
Run the command in the root directory of your project:

```
php spark burner:start
```

## RoadRunner Server Settings

The server settings are all in the project root directory ".rr.yaml". The default file will look like this:

```yaml
version: "2.7"

rpc:
  listen: tcp://127.0.0.1:6001

server:
  command: "php Worker.php"
  # env:
  #   XDEBUG_SESSION: 1

http:
  address: "0.0.0.0:8080"
  static:
    dir: "/app/dev/public"
    forbid: [".htaccess", ".php"]
  pool:
    num_workers: 1
    # max_jobs: 64
    # debug: true

# reload:
#   interval: 1s
#   patterns: [ ".php" ]
#   services:
#     http:
#       recursive: true
#       ignore: [ "vendor" ]
#       patterns: [ ".php", ".go", ".dmd" ]
#       dirs: [ "/app/dev" ]
```

You can create your configuration file according to the [Roadrunner document](https://roadrunner.dev/docs/intro-config).

## Workerman Server Settings

The server settings are all in the `app/Config` directory `Workerman.php`. The default file will look like this:


```php
class Workerman extends BaseConfig
{
    /**
     * Public static files location path.
     *
     * @var string
     */
    public $staticDir = '/app/dev/public';

    /**
     * Public access to files with these filename-extension is prohibited.
     *
     * @var array
     */
    public $staticForbid = ['htaccess', 'php'];

    /** hide **/
}
```

You can create your configuration file according to the [Workerman document](https://www.workerman.net/doc/workerman/worker/count.html).

## Development Suggestions

### Automatic reload

In the default circumstance of RoadRunner and Workerman, you must restart the server everytime after you revised any PHP files so that your revision will effective.
It seems not that friendly during development.

#### RoadRunner

You can revise your `.rr.yaml` configuration file, add the settings below and start the development mode with `-d`.
RoadRunner Server will detect if the PHP files were revised or not, automatically, and reload the Worker instantly.

```yaml
reload:
  interval: 1s
  patterns: [ ".php" ]
  services:
    http:
      recursive: true
      ignore: [ "vendor" ]
      patterns: [ ".php", ".go", ".md" ]
      dirs: [ "." ]
```

#### Workerman 

You can modify your `app/Config/Workerman.php` configuration file, add the following settings and restart the server.

```php
public $autoReload = true;
```

> The `reload` function is very resource-intensive, please do not activate the option in the formal environment.

### Using Codeigniter4 Request and Response object

Codeigniter4 does not implement the complete [HTTP message interface](https://www.php-fig.org/psr/psr-7/), hence this library focuses on the synchronize of `PSR-7 interface` and `Codeigniter4 HTTP interface`.

Base on the reasons above, You should use `$this->request`, provided by Codeigniter4, or the global function `/Config/Services::('request')` to fetch the correct request object; Use `$this->response` or `/Config/Services::('response')` to fetch the correct response object.

Please be noticed, while constructing response for the users during developing, you should prevent using PHP built-in methods to conduct `header` or `set-cookies` settings. Using the `setHeader()` and `setCookie()`, provided by the [Codeigniter4 Response Object](https://codeigniter.com/user_guide/outgoing/response.html), to conduct setting.

### Use return to stop controller logic

Inside the Controller, try using return to stop the controller logic. No matter the response of view or API, reduce the `echo` output usage can avoid lets of errors, just like ths:

```php
<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Home extends BaseController
{
  use ResponseTrait;

  public function index()
  {
    // Don't use:
    // echo view('welcome_message');
    return view('welcome_message');
  }

  /**
   * send header
   */
   public function sendHeader()
   {
     $this->response->setHeader("X-Set-Auth-Token", uniqid());
     return $this->respond(["status"=>true]);
   }

}
```

### Use the built-in Session library

We only focus on supporting the Codeigniter4 built-in [Session library](https://codeigniter.com/user_guide/libraries/sessions.html), and do not guarantee if using `session_start()` and `$_SEEEION` can work as normal. So, you should avoid using the PHP built-in Session method, change to the Codeigniter4 framework built-in library.

### Developing and debugging in a environment with only one Worker

Since the RoadRunner and Workerman has fundamentally difference with other server software(i.e. Nginx, Apache), every Codeigniter4 will persist inside RAMs as the form of Worker, HTTP requests will reuse these Workers to process. Hence, we have better develop and test stability under the circumstance with only one Worker to prove it can also work properly under serveral Workers in the formal environment.

#### RoadRunner

You can reference the `.rr.yaml` settings below to lower the amount of Worker to the minimum:

```yaml
http:
  address: "0.0.0.0:8080"
  static:
    dir: "./public"
    forbid: [".htaccess", ".php"]
  pool:
    num_workers: 1
    # max_jobs: 64
    # debug: true
```

#### Workerman

You can reference the `app/Config/Workerman.php` settings below to lower the amount of Worker to the minimum:

```php
public $workerCount = 1;
```

### Database Connection

We only focus on supporting the Codeigniter4 built-in [Database Library](https://codeigniter.com/user_guide/database/index.html), hence we do not guarantee if using the PHP
built-in method should work as normal. Therefore, you should avoid using the PHP built-in database connection method but
pick the Codeigniter4 framework built-in library.

Under the default situation, DB of the Worker should be lasting, and will try to reconnect once the connection is failed.
Every Request that goes into Worker is using a same DB connection instance. If you don't want this default setting but expecting
every Request to use the reconnect DB connection instance. You can add the configuration down below into the `.env`  file under the root directory.

```env
CIROAD_DB_AUTOCLOSE = true
```

# Global Methods

We offer some Global methods to help you develop your projects more smoothly.

### Dealing with the file uploading

Since the RoadRunner and Workerman Worker can not transfer the correct `$_FILES` context, the Codeigniter4 file upload class will not be able to work properly. To solve this, we offered a file upload class corresponding the PSR-7 standard for you to deal with file uploading correctly within RoadRunner. Even if you switched your project to another server environment(i.e. spark serve, Apache, Nginx), this class can still work properly, and doesn't need any code modification.

You can fetch the uploaded files by means of `SDPMlab\Ci4Roadrunner\UploadedFileBridge::getPsr7UploadedFiles()` in the controller (or any other places). This method will return an array, consist of Uploaded File objects. The available methods of this object is identical as the regulation of [PSR-7 Uploaded File Interface](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface).

```php
<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use SDPMlab\Ci4Roadrunner\UploadedFileBridge;

class FileUploadTest extends BaseController
{
    use ResponseTrait;

    protected $format = "json";

    /**
     * form-data 
     */
    public function fileUpload()
    {
        $files = UploadedFileBridge::getPsr7UploadedFiles();
        $data = [];
        foreach ($files as $file) {
            $fileNameArr = explode('.', $file->getClientFilename());
            $fileEx = array_pop($fileNameArr);
            $newFileName = uniqid(rand()) . "." . $fileEx;
            $newFilePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $newFileName;
            $file->moveTo($newFilePath);
            $data[$file->getClientFilename()] = md5_file($newFilePath);
        }
        return $this->respondCreated($data);
    }

    /**
     * form-data multiple upload
     */
    public function fileMultipleUpload()
    {
        $files = UploadedFileBridge::getPsr7UploadedFiles()["data"];
        $data = [];
        foreach ($files as $file) {
            $fileNameArr = explode('.', $file->getClientFilename());
            $fileEx = array_pop($fileNameArr);
            $newFileName = uniqid(rand()) . "." . $fileEx;
            $newFilePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $newFileName;
            $file->moveTo($newFilePath);
            $data[$file->getClientFilename()] = md5_file($newFilePath);
        }
        return $this->respondCreated($data);
    }
}
```

### Dealing with thrown errors

If you encountered some variables or object content that needed to be confirmed in `-d` development mode, you can use the global function `dump()` to throw errors onto the terminal no matter where the program is.
