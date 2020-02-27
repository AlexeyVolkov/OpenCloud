# OpenCloud

## Overview

-   [x] авторизация (каждый пользователь видит свои данные)
-   [ ] создание папок и подпапок
-   [x] загрузка файлов
-   [x] переименование / удаление файлов
-   [x] скачивание файлов
-   [ ] генерация уникальной публичной ссылки на файл

## Time spent

20 hours (24.02.2020 - 27.02.2020)

## Live website

[https://volkov.best/projects/napopravku/](https://volkov.best/projects/napopravku/)

## Features

-   Secure for Server side
-   100% Accessible

## Main Work Flow

1. Login as (admin/admin) or (test/test).
1. Upload file(s).
1. Download or Remove one.

## Security

1. Passwords are hashed.
1. Files have hashed names like `a384f074e91f07073cd1d71108c0de06`.
1. This hash generates on uploading.
1. Database table `files` remembers hashed names and real ones.
1. Downloading combines hash and real name:

```php
header("Content-disposition: attachment;filename=$file['real_name']");
readfile($hashed__path);
```

## Optimization

1. Files` dublicates are catch by matching file names hashes. So database stores a few links to one physical file.
1. Each new file type stores in `extensions`. It will help in future filtering and security functions.

## Files

-   DB dump - `open_cloud.sql`
