# shag24

Программный код публичной и административной части сайтов:
* https://khara.ru
* https://khara24.ru
* https://kharasho.ru
* https://rusnlp.ru
* https://shag24.ru
* https://shag24fest.ru
* https://shag24online.ru
* https://shag24sochi.ru
* https://videnin24.ru


## Технические характеристики

* PHP 7.3+
* MariaDB 10.3+
* Twig 3+
* composer
* Less
* ES6+
* Gulp 4+, webpack 4+
* Stylelint, eslint, htmlhint, editorconfig
* Svelte 3+, tinymce, rollup - фронтенд-часть админки


## Установка зависимостей

```sh
bash install.sh
```

Этот скрипт выполняет:
* При отсутствии PHP-зависимостей они будут установлены (предварительно надо установить `composer` на сервер и добавить его в `path`) в каталог `vendor` (он должен лежать на одном уровне с каталогами публичной части и приложений).
* При отсутствии зависимостей для node (сборка фронтенд-части) они будут установлены в каталог `node_modules`.


## Запуск в режиме livereload для конкретного сайта

Пример запуска: `npm start -- --khara`


## Запуск админзоны

В соседнем терминале командой `npm run admin`


# Сборка продуктовых версий

Командой `npm run build` собирается сначала основная часть (каталог `public_html`), потом админка (каталог `public_admin`). На сервере соотношение каталогов необходимо сохранять.
