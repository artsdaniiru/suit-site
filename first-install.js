const fs = require('fs');
const path = require('path');



// Данные для записи в PHP файл
const envContent = `VUE_APP_BACKEND_URL=http://localhost:8181/public
DB_HOST=localhost
DB_USER=user
DB_PASS=pass
DB_NAME=name`;

// Путь к PHP файлу
const envFilePath = path.join(__dirname, '', '.env');
// Запись данных в PHP файл
fs.writeFile(envFilePath, envContent, (err) => {
    if (err) {
        console.error('Ошибка при записи в env файл:', err);
    } else {
        console.log('env файл успешно обновлен.');
    }
});

// Путь к PHP файлу
const envFilePathProd = path.join(__dirname, '', '.env.production');
// Запись данных в PHP файл
fs.writeFile(envFilePathProd, envContent, (err) => {
    if (err) {
        console.error('Ошибка при записи в env файл:', err);
    } else {
        console.log('env файл успешно обновлен.');
    }
});