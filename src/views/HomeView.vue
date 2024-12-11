<template>
    <div>
        <div class="hero">
            <div class="cover">
                <p>すべての縫い目に完璧を。<br>
                    理想の男性にふさわしい理想のスーツ</p>
            </div>

        </div>

        <div class="section-box services">
            <h2>当社のサービス</h2>
            <p>
                当サイトでは、豊富な紳士スーツのラインナップをご用意しております。お客様はご自身のサイズを入力するだけで、
                ピッタリのスーツを簡単にご注文いただけます。
            </p>
            <h3>当社の強み</h3>
            <span class="subtitle">なぜ私たちが選ばれるのか？</span>
            <div class="strengths">
                <div class="strength">
                    <img src="../assets/icons/user.svg" alt="user">
                    <div class="info">
                        <h3>個別対応</h3>
                        <span class="subtitle">お客様一人ひとりの体型に合わせた、特別なオーダーメイドスーツを作り上げます。</span>
                    </div>

                </div>
                <div class="strength">
                    <img src="../assets/icons/award.svg" alt="user">
                    <div class="info">
                        <h3>高品質</h3>
                        <span class="subtitle">最高品質の素材を使用し、熟練された職人が作る高級スーツをご提供します。</span>
                    </div>
                </div>
                <div class="strength">
                    <img src="../assets/icons/cart.svg" alt="user">
                    <div class="info">
                        <h3>簡単な注文プロセス</h3>
                        <span class="subtitle">お気に入りのスーツを選び、サイズを入力するだけで簡単にご注文いただけます。</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-box products">
            <h3>人気な商品</h3>
            <Carousel :items-to-show="3" :wrap-around="true">
                <Slide v-for="item in popular" :key="item">
                    <ProductCard :item="item" :width="'270px'" />

                </Slide>
                <template #addons>
                    <Navigation />
                </template>
            </Carousel>
        </div>

        <div class="section-box products">
            <h3>新品</h3>
            <Carousel :items-to-show="3" :wrap-around="true">
                <Slide v-for="item in new_items" :key="item">
                    <ProductCard :item="item" :width="'270px'" />

                </Slide>
                <template #addons>
                    <Navigation />
                </template>
            </Carousel>
        </div>


        <div class="section-box contact">
            <h3>連絡</h3>
            <ContactForm />
        </div>
    </div>
</template>
<script>
import { defineComponent, onMounted, ref } from 'vue';
import axios from "axios";

import { Carousel, Slide, Navigation } from 'vue3-carousel'
import 'vue3-carousel/dist/carousel.css'

import ContactForm from '../components/ContactForm.vue';
//ref https://ismail9k.github.io/vue3-carousel/

export default defineComponent({
    name: 'HomeView',
    components: {
        Carousel,
        Slide,
        Navigation,
        ContactForm
    },
    setup() {

        const popular = ref(null)
        const new_items = ref(null)


        const fetchProducts = async (type) => {

            let filter = type == 'popular' ? '&is_popular=1' : '&is_new=1';

            let url = process.env.VUE_APP_BACKEND_URL + '/backend/products.php?itemsPerPage=10' + filter;


            const response = await axios.get(url, {
                withCredentials: true
            });

            console.log(response);


            // Убедимся, что товары приходят в поле `products`
            if (Array.isArray(response.data.products)) {
                // Преобразуем данные (например, конвертируем цену в число)
                if (type == 'popular') {
                    popular.value = response.data.products.map(product => ({
                        ...product,
                        min_price: Number(product.min_price), // Преобразуем строку в число
                    }));
                } else {
                    new_items.value = response.data.products.map(product => ({
                        ...product,
                        min_price: Number(product.min_price), // Преобразуем строку в число
                    }));
                }
            } else {
                console.error("Ожидался массив товаров, но получено что-то другое:", response.data);
            }


        }

        // Загружаем товары при монтировании компонента
        onMounted(() => {
            fetchProducts('popular');
            fetchProducts();
        });


        return {
            popular,
            new_items,
        };
    }
});
</script>

<style lang="scss" scoped>
.section-box {
    padding: 10px 64px 10px 64px;
}

.hero {
    background-image: url('../assets/images/main.webp');
    background-size: cover;
    background-position: center;
    color: #fff;
    text-align: center;
    height: 495px;
    position: relative;

    .cover {
        position: absolute;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;

        p {
            margin: auto;
            font-weight: 700;
            font-size: 36px;
            text-align: center;
            color: #fff;
        }
    }
}

.services {


    h3 {
        margin-bottom: 5px;
    }

    p {
        font-weight: 400;
        font-size: 20px;
        line-height: 120%;
        text-align: center;
        color: #000;
        text-align: center;
    }


    .strengths {
        display: flex;
        justify-content: space-around;
        margin-top: 20px;
        gap: 64px;

        .strength {
            display: flex;
            gap: 24px;
            align-items: start;

            img {
                width: 32px;
            }

            h3 {
                margin-top: 0px;
            }

            .subtitle {
                font-size: 16px !important;
                line-height: 140% !important;
            }
        }
    }


}

.product-list {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
}
</style>
