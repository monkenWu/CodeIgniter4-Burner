# Codeigniter4-Burner

<p align="center">
  <a href="https://ciburner.com/">
    <img src="https://i.imgur.com/YI4RqdP.png" alt="logo" width="200" />
  </a>
</p>

Burner 是一款專屬於 CodeIgniter4 的開箱即用的程式庫，它支援 [RoadRunner](https://roadrunner.dev/) , [Workerman](https://github.com/walkor/workerman) 與 , [OpenSwoole](https://openswoole.com/) 高效能網頁伺服器。你只需要開啟一些 php 擴充套件，即可大幅度地加速你的 CodeIgniter4 應用程式，使其能承受更高的負載並同時處理更多的連線。

[正體中文文件](https://ciburner.com/zh_TW/introduction)

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
