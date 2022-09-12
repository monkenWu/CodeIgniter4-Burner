#  Test Case

This is the Codeigniter4-RoadRunner test case project, it is built by Codeigniter4, and has loaded the Codeigniter4-Roadrunner library class inside the upper directory included in the `src` in.

You can run the test right after you modified the Codeigniter4-Roadrunner project files, verify if the functions are complete; Or write some related program logic in this project to assist your development.

## Test Range

This test case takes the actual sent CURL Request as test approach, because what Codeigniter4-Roadrunner provide is the synchronization on HTTP Request and Response objects of Roadrunner-Worker and Codeigniter4 (Since Codeigniter4 doesn't implement PSR-7 interface standard). In other words, we just have to verify if the server workes as what we wanted under the actual HTTP connection.

1. BasicTest：Test HTTP `GET`、`POST`、`query`、`form-data`, and the `php echo` output command, and if `header` can process normally and give us outputs.
2. FileUploadTest：Test if file upload class can work correctly and move files.
3. RestTest：Test if Codeigniter4 RESTful library can work properly and can parse every verbs
4. SessionTest：Test if the Session mode, triggered by the file system can work properly.

## Requirements

We recommend you to use the latest PHPUnit. While we're writing scripts, the version we're running at is version `9.5.19`. You might need to use Composer to download the library your project needed back to your develop environment.

```
composer install
```

Next, you must initialize the environment that Roadrunner needed.

```
php spark ciroad:init
```

Finally, please confirm if the directory has these three files including `rr` (if you are developing under Windows, you will see `rr.exe`), `.rr.yaml`, `psr-worker.php`.

## Run Tests

Before running tests, please open `.rr.yaml` file first, and ensure this configuration file has these settings:

```yaml
rpc:
  listen: tcp://127.0.0.1:6001

server:
  command: "php psr-worker.php"

http:
  address: "0.0.0.0:8080"
  static:
    dir: "./public"
    forbid: [".htaccess", ".php"]
  pool:
    num_workers: 1  
```

Since Roadrunner-Worker lasts inside RAMs, HTTP requests will reuse Workers to process. Hence, we need to test the stability under the environment with only one worker to prove that it can work properly under several workers.

Next, you have to open a terminal and cd to the root directory, type the commands below to run the Roadrunner server:

```
./rr serve -d
```

Finally, open another new terminal and cd to the test project, type the commands below to run tests:

```
./vendor/bin/phpunit
```

If you're running tests under Windows CMD, your command should be like this:

```
vendor\bin\phpunit
```
