# suit-site

Install [node.js](https://nodejs.org/).

```
npm install -g @vue/cli
```

## Project setup
```
npm install && node first-install.js
```

### Setup database and backend API usl

.env for dev
.env.production for production

#### .env-file example
```
VUE_APP_BACKEND_URL=http://localhost:8181/public
DB_HOST=localhost
DB_USER=user
DB_PASS=pass
DB_NAME=name
VPS_HOST=host
VPS_USERNAME=user
SSH_PASSWORD=pass
REMOTE_DIR=dirname
```
### Compiles and hot-reloads for development
```
npm run serve
```

### Compiles and minifies for production
```
npm run build
```

### Upload to VPS-server
```
node uploader.js
```

### Lints and fixes files
```
npm run lint
```

