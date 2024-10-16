addEventListener('fetch', event => {
    event.respondWith(handleRequest(event.request))
})

async function handleRequest(request) {
    const url = new URL(request.url)

    // Клонируем исходный запрос, чтобы сохранить все его свойства (метод, заголовки, тело и т.д.)
    const init = {
        method: request.method,
        headers: request.headers,
        body: request.method !== 'GET' && request.method !== 'HEAD' ? await request.clone().arrayBuffer() : null, // Клонируем тело запроса, если оно есть
        redirect: 'manual' // Перенаправления обрабатываем вручную
    };

    // Если запрос к корню сайта (example.com), перенаправляем на папку пользователя
    if (url.pathname === '/') {
        return fetch(`https://${url.host}/~k23b${url.search}`, init)
    } else {
        return fetch(`https://${url.host}/~k23b${url.pathname}${url.search}`, init)
    }
}