<template>
    <div class="accepted">
        <h1>ご注文ありがとうございます。</h1>
        <h2>注文番号：{{ order_id }}</h2>
        <span>詳細を記載したメールを送信しました</span>
        <router-link to="/"><button class="button">ホームページへ戻る</button></router-link>
    </div>
</template>

<script>
import { defineComponent } from 'vue';
import { useRoute } from 'vue-router';

export default defineComponent({
    name: 'OrderAccepted',
    setup() {
        const route = useRoute();
        const order_id = "#" + route.params.uid.toString().padStart(5, '0');
        return { order_id };
    },
});
</script>

<style lang="scss" scoped>
.accepted {

    display: flex;
    flex-direction: column;
    margin-left: auto;
    margin-right: auto;
    margin-bottom: 20px;
    text-align: center;

    h1 {
        font-weight: 700;
        font-size: 72px;
        line-height: 120%;
        letter-spacing: -0.03em;
    }

    h2 {
        font-weight: 700;
        font-size: 52px;
        line-height: 120%;
        letter-spacing: -0.03em;
        margin-top: 0;

    }

    span {
        font-size: 32px;
        color: #757575;
        margin-bottom: 20px;
    }

    img {
        width: 270px;
        margin: 0 auto;
    }

}
</style>
