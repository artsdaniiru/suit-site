<template>
    <div class="test-api">
        <h2>Test API Requests</h2>
        <form @submit.prevent="sendRequest">
            <div>
                <label for="url">Request URL:</label>
                <input type="text" v-model="url" placeholder="/backend/test.php?action=login" required />
            </div>

            <div>
                <label for="method">HTTP Method:</label>
                <select v-model="method" required>
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                    <option value="PUT">PUT</option>
                    <option value="DELETE">DELETE</option>
                </select>
            </div>

            <div v-if="method !== 'GET'">
                <label for="body">Request Body (JSON):</label>
                <textarea v-model="body" placeholder='{"key": "value"}'></textarea>
            </div>

            <button type="submit">Send Request</button>
        </form>

        <div class="response" v-if="response">
            <h3>Response</h3>
            <button @click="toggleHighlight" outlined>
                {{ highlightEnabled ? 'Отключить' : 'Включить' }} раскраску
            </button>
            <pre v-html="highlightEnabled ? highlightedJson : formattedJson"></pre>
        </div>
    </div>
</template>

<script>
import { ref, defineComponent } from 'vue';
import axios from 'axios';

export default defineComponent({
    name: "TestApiView",
    setup() {
        const url = ref('');
        const method = ref('GET');
        const body = ref('');
        const response = ref(null);
        const formattedJson = ref('');
        const highlightedJson = ref('');
        const highlightEnabled = ref(true);

        // Функция для раскраски JSON
        const syntaxHighlight = (json) => {
            if (typeof json != 'string') {
                json = JSON.stringify(json, undefined, 2);
            }
            json = json
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            return json.replace(
                /("(\\u[\da-fA-F]{4}|\\[^u]|[^\\"])*")(?=\s*:)|("(\\u[\da-fA-F]{4}|\\[^u]|[^\\"])*")|(:)|\b(true|false|null)\b|(-?\d+(\.\d+)?([eE][+-]?\d+)?)/g,
                function (match, key, _, strValue, __, colon, boolOrNull, number) {
                    if (key) {
                        return `<span class="key">${key}</span>`; // Ключи (строки до двоеточия)
                    } else if (strValue) {
                        return `<span class="string">${strValue}</span>`; // Строки (значения)
                    } else if (colon) {
                        return `<span class="colon">${colon}</span>`; // Двоеточие
                    } else if (boolOrNull) {
                        return `<span class="boolean">${boolOrNull}</span>`; // Логические значения и null
                    } else if (number) {
                        return `<span class="number">${number}</span>`; // Числа
                    }
                    return match;
                }
            );
        };

        const sendRequest = async () => {
            try {
                const url_f = process.env.VUE_APP_BACKEND_URL + '/backend/'+url.value;
        
                console.log(url_f);
                

                const config = {
                    method: method.value,
                    url: url_f,
                    withCredentials: true, // Разрешаем отправку куки сессии
                };

                // Если метод не GET, добавляем тело запроса
                if (method.value !== 'GET' && body.value) {
                    config.data = JSON.parse(body.value);
                }

                const res = await axios(config);
                response.value = res.data;

                // Форматирование JSON
                formattedJson.value = JSON.stringify(res.data, null, 2);
                highlightedJson.value = syntaxHighlight(res.data);
            } catch (error) {
                const errorMsg = error.response ? error.response.data : error.message;
                formattedJson.value = JSON.stringify(errorMsg, null, 2);
                highlightedJson.value = syntaxHighlight(errorMsg);
                console.error('Error:', error);
            }
        };

        const toggleHighlight = () => {
            highlightEnabled.value = !highlightEnabled.value;
        };

        return {
            url,
            method,
            body,
            response,
            formattedJson,
            highlightedJson,
            sendRequest,
            toggleHighlight,
            highlightEnabled,
        };
    }
});
</script>

<style lang="scss">
.test-api {

    .response {
        margin-left: auto;
        margin-right: auto;
    }

    /* Стили для формы */
    form {
        display: flex;
        flex-direction: column;
        max-width: 400px;
        margin: 0 auto;
    }

    label {
        margin-top: 10px;
    }

    textarea {
        height: 100px;
    }

    button {
        margin-top: 20px;
    }

    /* Стили для JSON отображения */
    pre {
        margin: 0;
        white-space: pre-wrap;
        word-wrap: break-word;
        font-family: monospace;
        font-size: 14px;
        max-height: 400px;
        overflow-y: auto;
        text-align: start;
    }


    .response {

        /* Стили для раскраски JSON */
        .string {
            color: green !important;
        }

        .number {
            color: darkorange !important;
        }

        .boolean {
            color: blue !important;
        }

        .null {
            color: magenta !important;
        }

        .key {
            color: red !important;
        }
    }
}
</style>
