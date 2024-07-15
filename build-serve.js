const fs = require('fs');
const path = require('path');
const dotenv = require('dotenv');

// Загрузка переменных окружения из .env файла
dotenv.config();

// Путь к PHP файлу
const phpFilePath = path.join(__dirname, 'public/backend', 'config.php');

// Данные для записи в PHP файл
const phpContent = `<?php
define('DB_HOST', '${process.env.DB_HOST}');
define('DB_USER', '${process.env.DB_USER}');
define('DB_PASS', '${process.env.DB_PASS}');
define('DB_NAME', '${process.env.DB_NAME}');`;

// Запись данных в PHP файл
fs.writeFile(phpFilePath, phpContent, (err) => {
    if (err) {
        console.error('Ошибка при записи в PHP файл:', err);
    } else {
        console.log('PHP файл успешно обновлен.');
    }
});