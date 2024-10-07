<template>
    <div class="suit-card">
        <img :src="image" :alt="name" class="suit-image" />
        <div class="suit-info">
            <h3>{{ name }}</h3>
            <span class="english-name">{{ englishName }}</span>
            <p class="price">{{ price }}</p>
        </div>
        <button class="details-button">内容を見る</button>
    </div>
</template>

<script setup>
import { defineComponent } from 'vue';

defineComponent({
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
            default: '../assets/suit-image.png', // Замените на правильный путь к изображению
        }
    }
});
</script>

<style lang="scss" scoped>
.suit-card {
    border: 1px solid #ddd;
    padding: 16px;
    border-radius: 8px;
    width: 300px;
    text-align: center;
    background-color: #fff;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);

    .suit-image {
        width: 100%;
        border-radius: 8px;
        margin-bottom: 16px;
    }

    .suit-info {
        margin-bottom: 16px;

        h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .english-name {
            font-size: 14px;
            color: #666;
        }

        .price {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 8px 0 0;
        }
    }

    .details-button {
        background-color: #000;
        color: #fff;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;

        &:hover {
            background-color: #333;
        }
    }
}
</style>
