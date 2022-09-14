#  Test Case

This is the Codeigniter4-Burner test case project, it is built by Codeigniter4, and has loaded the Codeigniter4-Burner library class inside the upper directory included in the `src` in.

You can run the test right after you modified the Codeigniter4-Burner project files, verify if the functions are complete; Or write some related program logic in this project to assist your development.

## Test Range

This test case takes the actual sent CURL Request as test approach, because what Codeigniter4-Burner provide is the synchronization on HTTP Request and Response objects of Roadrunner-Worker and Workerman-Worker and Codeigniter4 (Since Codeigniter4 doesn't implement PSR-7 interface standard). In other words, we just have to verify if the server workes as what we wanted under the actual HTTP connection.

1. BasicTest：Test HTTP `GET`、`POST`、`query`、`form-data`, and the `php echo` output command, and if `header` can process normally and give us outputs.
2. FileUploadTest：Test if file upload class can work correctly and move files.
3. RestTest：Test if Codeigniter4 RESTful library can work properly and can parse every verbs
4. SessionTest：Test if the Session mode, triggered by the file system can work properly.

## Requirements

We recommend you to use the latest PHPUnit. While we're writing scripts, the version we're running at is version `9.5.24`. You might need to use Composer to download the library your project needed back to your develop environment.

```
composer install
```

Next, you must initialize the environment that Burner needed.

```
php spark burner:init [RoadRunner Or Workerman]
```

Finally, if you are using the RoadRunner Driver make sure the directory contains the `.rr.yaml` file; if you are using the Workerman Driver make sure that `app/Config` contains the `Workerman.php` file. Both Drivers must have the `app/Config/Burner.php` file.

## Run Tests

### RoadRunner 驅動器

Before running tests, please open `.rr.yaml` file first, and ensure this configuration file has these settings:

```yaml
rpc:
  listen: tcp://127.0.0.1:6001

server:
  command: "php Worker.php"

http:
  address: "0.0.0.0:8080"
  static:
    dir: "/app/dev/public"
    forbid: [".htaccess", ".php"]
  pool:
    num_workers: 1
```

### Workerman 驅動器

Before running tests, please open `app/Config/Workerman.php` file first, and ensure this configuration file has these settings:

```php
public $workerCount = 1;
public $listeningPort = 8080;
public $ssl = false;
public $staticForbid = ['htaccess', 'php'];
```

### Start!

Since Roadrunner-Worker lasts and Workerman-Worker inside RAMs, HTTP requests will reuse Workers to process. Hence, we need to test the stability under the environment with only one worker to prove that it can work properly under several workers.

Next, you have to open a terminal and cd to the root directory, type the commands below to run the Burner server:

```
php spark burner:start
```

Finally, open another new terminal and cd to the test project, type the commands below to run tests:

```
./vendor/bin/phpunit
```

If you're running tests under Windows CMD, your command should be like this:

```
vendor\bin\phpunit
```
