<template>
    <div class="page-404">
        <h1>Error 404</h1>
        <img src="@/assets/images/sorry.png" alt="">
        <span>お探しのページが見つかりませんでした</span>
        <router-link to="/"><button class="button">ホームページへ戻る</button></router-link>
    </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'NotFound'
});
</script>

<style lang="scss" scoped>
.page-404 {
    display: flex;
    flex-direction: column;
    margin: 20px;
    text-align: center;

    h1 {
        font-size: 50px;
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
