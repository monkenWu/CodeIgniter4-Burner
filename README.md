# CodeIgniter4-Burner

<p align="center">
  <a href="https://ciburner.com/">
    <img src="https://i.imgur.com/YI4RqdP.png" alt="logo" width="200" />
  </a>
</p>

Burner is an out-of-the-box library for CodeIgniter4 that supports [RoadRunner](https://roadrunner.dev/), [Workerman](https://github.com/walkor/workerman), and [OpenSwoole](https://openswoole.com/) high-performance web servers.  All you need to do is open a few php extensions to dramatically speed up your CodeIgniter4 applications, allowing them to handle higher loads and more connections at the same time.

[English Document](https://ciburner.com//en/introduction)

[正體中文簡介](README_zh-TW.md)

## Install

### Prerequisites
1. CodeIgniter Framework 4.3.0^
2. Composer
3. PHP8^

### Composer Install

Use "Composer" to download the library and its dependencies to the project

You can install the appropriate Driver according to your preference.

Each Driver has its own development rules to be aware of, as well as some proprietary server commands, which you can find in their Git repository README files.

* [OpenSwoole Driver](https://github.com/monkenWu/CodeIgniter4-Burner-OpenSwoole)

  ```
  composer require monken/codeigniter4-burner-openswoole:^1.0@beta
  ```
* [RoadRunner Driver](https://github.com/monkenWu/CodeIgniter4-Burner-RoadRunner)

  ```
  composer require monken/codeigniter4-burner-roadrunner:^1.0@beta
  ```

* [Workerman Driver](https://github.com/monkenWu/CodeIgniter4-Burner-Workerman)

  ```
  composer require monken/codeigniter4-burner-workerman:^1.0@beta
  ```

After installing the driver, initialize the server files using the built-in commands in the library

```
php spark burner:init [RoadRunner, Workerman, OpenSwoole]
```

## Run
Run the command in the root directory of your project:

```
php spark burner:start
```
