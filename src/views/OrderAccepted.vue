<template>
    <div class="accepted">
        <h1>ご注文ありがとうございます。</h1>
        <span>詳細を記載したメールを送信しました</span>
        <router-link to="/"><button class="button">ホームページへ戻る</button></router-link>
    </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'OrderAccepted'
});
</script>

<style lang="scss" scoped>
.accepted {

    display: flex;
    flex-direction: column;
    margin: auto 20px;
    text-align: center;

    h1 {
        font-weight: 700;
        font-size: 72px;
        line-height: 120%;
        letter-spacing: -0.03em;
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
