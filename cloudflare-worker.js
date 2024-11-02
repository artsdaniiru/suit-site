addEventListener('fetch', event => {
    event.respondWith(handleRequest(event.request))
})

async function handleRequest(request) {
    const url = new URL(request.url)

    // Опции для перенаправляемого запроса
    const init = {
        method: request.method,
        headers: new Headers(request.headers),
        body: request.method !== 'GET' && request.method !== 'HEAD' ? request.clone().body : null
    };

    // Проверяем, является ли запрос загрузкой файла и корректируем заголовок Content-Type
    if (request.headers.get("content-type") && request.headers.get("content-type").includes("multipart/form-data")) {
        init.headers.set("content-type", request.headers.get("content-type"));
    }

    // Проверка пути и перенаправление
    if (url.pathname === '/') {
        return fetch(`https://${url.host}/~k23b${url.search}`, init)
    } else {
        return fetch(`https://${url.host}/~k23b${url.pathname}${url.search}`, init)
    }
}