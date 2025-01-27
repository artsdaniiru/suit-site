<template>
    <div class="product-card" :style="{ width: width }">
        <img :src="product.image_path == undefined ? '/Image.png' : product.image_path" :alt="product.name" class="product-image" />
        <div class="product-info">
            <span class="name">{{ product.name }}</span>
            <span class="english-name">{{ product.name_eng }}</span>
            <span class="price">{{ formattedPrice }}</span>
        </div>
        <button class="button" @click="goToProduct">内容を見る</button>

    </div>
</template>

<script>
import { defineComponent, computed, ref } from 'vue';
import { useRouter } from 'vue-router'

export default defineComponent({
    name: 'ProductCard',
    props: {
        item: {
            type: Object,
            required: false,
            default: () => ({
                name: '商品名',
                type: 'suit',
                name_eng: 'Product name',
                min_price: 28000,
                image_path: '/Image.png',
            })
        },
        width: {
            type: String,
            required: false,
            default: '-webkit-fill-available',
        }
    },
    setup(props) {

        const router = useRouter();

        const product = ref(props.item);

        // Вычисляемое свойство для форматирования цены
        const formattedPrice = computed(() => `¥${Number(product.value.min_price).toLocaleString('ja-JP')}` + (product.value.type == 'suit' ? '〜' : ''));

        function goToProduct() {
            router.push('/product/' + product.value.id);
        }
        return {
            formattedPrice, // Возвращаем форматированную цену
            product,
            goToProduct,
        };
    }
});
</script>

<style lang="scss" scoped>
.product-card {
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 16px;
    width: -webkit-fill-available;
    background: #fff;
    display: flex;
    flex-direction: column;
    gap: 16px;
    text-align: start;
    height: 450px;

    @include is-mobile() {
        height: unset;
    }

    .product-image {
        width: 100%;
        border-radius: 8px;

        object-fit: cover;
        aspect-ratio: 1 / 1;
    }

    .product-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
        line-height: 140%;
        max-width: 250px;

        .name {
            font-weight: 400;
            font-size: 16px;

            color: #1e1e1e;
            max-height: 48px; //TODO: запилить нормально
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .english-name {
            font-weight: 400;
            font-size: 14px;
            max-height: 24px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;

            color: #757575;
        }

        .price {
            font-weight: 600;
            font-size: 16px;

            color: #1e1e1e;
        }
    }

    .button {
        margin-bottom: 0px;
        margin-top: auto;
        width: -webkit-fill-available;
    }
}
</style>