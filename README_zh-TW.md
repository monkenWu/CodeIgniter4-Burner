# Codeigniter4-Burner

Burner 是一款專屬於 CodeIgniter4 的開箱即用的程式庫，它支援 [RoadRunner](https://roadrunner.dev/) , [Workerman](https://github.com/walkor/workerman) 與 , [OpenSwoole](https://openswoole.com/) 高效能網頁伺服器。你只需要開啟一些 php 擴充套件，即可大幅度地加速你的 CodeIgniter4 應用程式，使其能承受更高的負載並同時處理更多的連線。

## 安裝

### 需求
1. CodeIgniter Framework 4.3.0 以上
2. Composer
3. PHP8^

### Composer 安裝

於專案根目錄下，使用 Composer 下載程式庫與其所需之依賴。

```
composer require monken/codeigniter4-burner
```

你可以依據你的喜好安裝對應的伺服器驅動。

每個驅動程式都有相應的開發規則，以及一些專屬的伺服器指令，你可以在它們的 Git 儲存庫 README 檔案中閱讀它們。

* [OpenSwoole 驅動](https://github.com/monkenWu/CodeIgniter4-Burner-OpenSwoole)

  ```
  composer require monken/codeigniter4-burner-openswoole
  ```
* [RoadRunner 驅動](https://github.com/monkenWu/CodeIgniter4-Burner-RoadRunner)

  ```
  composer require monken/codeigniter4-burner-roadrunner
  ```

* [Workerman 驅動](https://github.com/monkenWu/CodeIgniter4-Burner-Workerman)

  ```
  composer require monken/codeigniter4-burner-workerman
  ```

安裝好驅動後，使用程式庫提供的內建指令初始化伺服器與其所需的檔案。

```
php spark burner:init [RoadRunner, Workerman, OpenSwoole]
```

## 執行伺服器

在專案根目錄中使用指令執行伺服器：

```
php spark burner:start
```
## 開發建議

### 使用 Codeigniter4 Request 與 Response 物件

Codeigniter4 並沒有實作完整的 [HTTP message 介面](https://www.php-fig.org/psr/psr-7/)，所以這個程式庫著重於 `PSR-7 介面` 與 `Codeigniter4 HTTP 介面` 的同步。

基於上述原因，在開發上，你應該使用 Codeigniter4 所提供的 `$this->request` 或是使用全域函數 `\Config\Services::('request')` 取得正確的 request 物件；使用 `$this->response` 或是 `\Config\Services::('response')` 取得正確的 response 物件。

請注意，在建構給予使用者的響應時，不論是 `header` 或 `set-cookies` 應該避免使用 PHP 內建的方法進行設定。而是使用 [Codeigniter4 響應物件](https://codeigniter.tw/user_guide/outgoing/response.html) 提供的 `setHeader()` 與 `setCookie()` 進行設定。 

### 以 return 結束控制器邏輯

在 Controller 中，盡量使用 return 結束程式邏輯，不論是視圖的響應或是 API 響應，減少使用 `echo` 輸出內容可以避免很多錯誤，就像這個樣子。

```php
<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Home extends BaseController
{
	use ResponseTrait;

	public function index()
	{
		//Don't use :
		//echo view('welcome_message');
		return view('welcome_message');
	}

	/**
	 * send header
	 */
	public function sendHeader()
	{
		$this->response->setHeader("X-Set-Auth-Token",uniqid());
		return $this->respond(["status"=>true]);
	}

}
```

### 使用內建 Session 程式庫

我們只針對 Codeigniter4 內建 [Session 程式庫](https://codeigniter.tw/user_guide/libraries/sessions.html) 進行支援，並不保證使用 `session_start()` 與 `$_SESSION` 是否能照常運作。所以，你應該避免使用 PHP 內建的 Session 方法，而是以 Codeigniter4 框架內建的程式庫為主。

### 在只有一個 Worker 的環境中開發與除錯

因為 RoadRunner、OpenSwoole 與 Workerman 與其他伺服器軟體（Nginx、Apache）有著根本上的不同，每個 Codeigniter4 將會以 Worker 的形式持久化於記憶體中，HTTP 的請求會重複利用到這些 Worker 進行處裡。所以，我們最好在只有單個 Worker 的情況下開發軟體並測試是否穩定，以證明在多個 Woker 的實際環境中能正常運作。 

### 資料庫連線

我們只針對 Codeigniter4 內建 [Database 程式庫](https://codeigniter.tw/user_guide/database/index.html) 進行支援，並不保證 PHP 內建的方法是否能照常運作。所以，你應該避免使用內建的 PHP 資料庫連線方法，而是以 Codeigniter4 框架內建的程式庫為主。

預設的情況下，在 Worker 中的 DB 連線是持久的，並會在連線失效時自動重新連線。所有進入 Worker 的 Request 都使用同一個 DB 連線實體。如果你不想要這個預設設定，希望每個進入 Worker 的 Request 都使用重新連線的 DB 連線實體。你可以在專案根目錄下的 `.env` 檔案加入以下設定。

```env
CIROAD_DB_AUTOCLOSE = true
```

## 全域方法

我們提供了一些全域方法，幫助你更加順利地開發你的專案。

### 處裡檔案上傳

因為 RoadRunner 與 Workerman Worker 無法傳遞正確的 `$_FILES` 內容，所以 Codeingiter4 的 [檔案上傳類別](https://codeigniter.tw/user_guide/libraries/uploaded_files.html) 將無法正確運作。對此，我們提供了符合 PSR-7 規範的檔案上傳類別，讓你可以正確地在 RoadRunner 中處理檔案上傳。就算你將專案切換到了其他伺服器環境（spark serve、Apache、Nginx）運作，這個類別依舊可以正常使用，並且不需要修改任何程式碼。

你可以在控制器（或任何地方），以 `SDPMlab\Ci4Roadrunner\UploadedFileBridge::getPsr7UploadedFiles()` 取得使用者上傳的檔案。這個方法將回傳以 Uploaded File 物件組成的陣列。此物件可用的方法與 [PSR-7 Uploaded File Interface](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface) 中規範的一樣。

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

### 處理錯誤拋出

如果你在開發環境中碰到了一些需要確認的變數、或物件內容，無論在程式的何處，你都可以使用全域函數 `dump()` 來將錯誤拋出到終端機上。
