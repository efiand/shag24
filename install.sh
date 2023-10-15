#!/bin/bash

if [ ! -d "node_modules" ]; then
	echo "Установка Node-зависимостей..."
	npm i
fi

if [ ! -d "vendor" ]; then
	echo "Установка PHP-зависимостей..."
	composer install
fi

echo "Создание каталога логов..."
mkdir app_html/logs

echo "ГОТОВО!"
