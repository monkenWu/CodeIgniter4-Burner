# 系統測試

這是 Codeigniter4-Burner 的測試案例專案，它以 Codeigniter4 建置，並在 `app/config/Autoload.php` 中，載入了上層目錄的 `src` 中包含的 Codeigniter4-Burner 程式庫類別。

你可以在修改 Codeigniter4-Burner 專案檔案後立即運作這個測試，驗證功能是否完備；或是在這個專案中撰寫相關的程式邏輯，輔助你的開發。

## 測試範圍

本測試案例專案以發送實際的 CURL Request 為測試方法，因為 Codeigniter4-Burner 提供的是 Roadrunner-Worker 或 Workerman-Worker 與 Codeigniter4 在 HTTP Request 與 Response 物件上的同步（因為 Codeigniter4 並沒有實作 PSR-7 介面規範）。也就是說，我們只需要驗證在實際的 HTTP 連線下，伺服器是否依照我們所想的方式工作。

1. BasicTest：測試 HTTP `GET`、`POST`、`query`、`form-data`，與畫面輸出、 `php echo` 指令，以及 `header` 是否能夠正常處理與輸出。
2. FileUploadTest：測試檔案上傳類別是否正確運作與移動檔案。
3. RestTest：測試 Codeigniter4 RESTful 程式庫是否能夠正確運作與解析各式動詞。
4. SessionTest：測試檔案系統驅動的 Session 模式是否能夠正常運作。

## 要求

建議使用最新版本的 PHPUnit。在撰寫本文時，我們正在運作的是版本 `9.5.24` 。你可能需要先使用 Composer 將專案所需的程式庫下載回你的開發環境。

```
composer install
```

接著，你必須初始化 Burner 伺服器所需要的環境。

```
php spark burner:init [RoadRunner Or Workerman]
```

最後，如果你使用了 RoadRunner Driver 請確定目錄下包含 `.rr.yaml` 這個檔案；如果你使用了 Workerman Driver 請確定 `app/Config` 是否包含 `Workerman.php` 這個檔案。不論和種 Driver，都必須擁有 `app/Config/Burner.php` 這個檔案。

## 執行測試

### RoadRunner 驅動器

在運作測試前，請先打開 `.rr.yaml` 檔案，並確保這個設定檔案具有以下設定：

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

在運作測試前，請先打開 `app/Config/Workerman.php` 檔案，並確保這個設定檔案具有以下設定：

```php
public $workerCount = 1;
public $listeningPort = 8080;
public $ssl = false;
public $staticForbid = ['htaccess', 'php'];
```

### 開始測試

因為 Roadrunner-Worker 與 Workerman-Worker 都會持久化於記憶體中，HTTP 的請求會重複利用到這些 Worker 進行處裡。所以我們必須要在只有單個 Worker 的情況下測試是否穩定，以證明在多個 Woker 的實際環境中能正常運作。 

接著，你得先打開一個終端機，移動到測試專案的根目錄下，輸入以下指令運作起 Burner 伺服器：

```
php spark burner:start
```

最後，再打開一個新的終端機，移動到測試專案下，輸入以下指令運作測試：

```
./vendor/bin/phpunit
```

如果你以 Windows 的 CMD 運作測試的話，你的指令會是這樣：

```
vendor\bin\phpunit
```