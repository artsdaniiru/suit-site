<template>
    <div class="product-card">
        <img :src="image" :alt="name" class="product-image" />
        <div class="product-info">
            <span class="name">{{ name }}</span>
            <span class="english-name">{{ englishName }}</span>
            <span class="price">{{ price }}</span>
        </div>
        <button class="button" @click="updateCount">内容を見る</button>
    </div>
</template>

<script>

import { defineComponent, inject } from 'vue';

export default defineComponent({
    name: 'ProductCard',
    props: {
        name: {
            type: String,
            required: false,
            default: 'クラシックネイビー',
        },
        englishName: {
            type: String,
            required: false,
            default: 'Classic Navy',
        },
        price: {
            type: String,
            required: false,
            default: '¥28,000〜',
        },
        image: {
            type: String,
            required: false,
            default: 'images/suit.webp', // Замените на правильный путь к изображению
        }
    },
    setup() {


        const { cart_count, updateCount } = inject('cart_count')

        return {
            cart_count,
            updateCount
        };
    }
});
</script>

<style lang="scss" scoped>
.product-card {
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 16px;
    width: 270px;
    background: #fff;
    display: flex;
    flex-direction: column;
    gap: 16px;
    text-align: start;

    .product-image {
        width: 100%;
        // border-radius: 8px;
    }

    .product-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
        line-height: 140%;

        .name {
            font-weight: 400;
            font-size: 16px;

            color: #1e1e1e;
        }

        .english-name {
            font-weight: 400;
            font-size: 14px;

            color: #757575;
        }

        .price {
            font-weight: 600;
            font-size: 16px;

            color: #1e1e1e;
        }
    }

    .button {
        width: -webkit-fill-available;
    }
}
</style>
