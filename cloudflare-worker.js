addEventListener('fetch', event => {
    event.respondWith(handleRequest(event.request))
})

async function handleRequest(request) {
    const url = new URL(request.url)

    // Если запрос к корню сайта (example.com), перенаправляем на папку пользователя
    if (url.pathname === '/') {
        return fetch(`https://${url.host}/~k23b`)
    } else {
        return fetch(`https://${url.host}/~k23b/${url.pathname}`)
    }
}