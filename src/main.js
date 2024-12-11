// import './assets/main.scss'
import clickOutSide from './directives/clickoutside'


import 'vue-toast-notification/dist/theme-bootstrap.css';

import {
    createApp
} from 'vue'
import App from './App.vue'
import router from './router'

import Components from './components/Components'

const app = createApp(App)
Components.forEach((component) => {
    app.component(component.name, component)
})

app.use(router)
app.directive('click-out-side', clickOutSide)
app.mount('#app')