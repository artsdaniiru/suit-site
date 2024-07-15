const fs = require('fs');
const path = require('path');
const dotenv = require('dotenv');

// Загрузка переменных окружения из .env файла
dotenv.config();

// Путь к PHP файлу
const phpFilePath = path.join(__dirname, 'public/backend', 'config.php');

// Данные для записи в PHP файл
const phpContent = `<?php
define('API_BASE_URL', '${process.env.VUE_APP_API_BASE_URL}');
define('CUSTOM_VALUE', '${process.env.VUE_APP_CUSTOM_VALUE}');
?>`;

// Запись данных в PHP файл
fs.writeFile(phpFilePath, phpContent, (err) => {
    if (err) {
        console.error('Ошибка при записи в PHP файл:', err);
    } else {
        console.log('PHP файл успешно обновлен.');
    }
});