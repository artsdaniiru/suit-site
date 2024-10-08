<template>
    <div>
        <div class="hero">
            <div class="cover">
                <p>すべての縫い目に完璧を。<br>
                    理想の男性にふさわしい理想のスーツ</p>
            </div>

        </div>

        <section class="services">
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
        </section>

        <section class="products">
            <h3>人気な商品</h3>
            <div class="product-list">
                <ProductCard>
                </ProductCard>
                <ProductCard :name="'エレガンスグレー'" :price="'¥25,000〜'">
                </ProductCard>
                <ProductCard :name="'ミッドナイトブラック'" :price="'¥30,000〜'">
                </ProductCard>
            </div>
        </section>

        <section class="products">
            <h3>新品</h3>
            <div class="product-list">
                <ProductCard>
                </ProductCard>
                <ProductCard :name="'エレガンスグレー'" :price="'¥25,000〜'">
                </ProductCard>
                <ProductCard :name="'ミッドナイトブラック'" :price="'¥30,000〜'">
                </ProductCard>
            </div>
        </section>

        <section class="contact">
            <h3>連絡</h3>
            <form>
                <div class="form-elem">
                    <label for="name">名前</label>
                    <input id="name" type="text" placeholder="名前を入力してください">
                </div>
                <div class="form-elem">
                    <label for="email">メール</label>
                    <input id="email" type="email" placeholder="メールアドレスを入力してください">
                </div>
                <div class="form-elem">
                    <label for="phone">電話番号</label>
                    <input id="phone" type="tel" placeholder="電話番号を入力してください">
                </div>
                <div class="form-elem">
                    <label for="message">メッセージ</label>
                    <textarea id="message" placeholder="メッセージを入力してください"></textarea>
                </div>
                <button class="button" type="submit">送信</button>
            </form>
        </section>
    </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'HomeView',
    setup() {

        return {
        };
    }
});
</script>

<style lang="scss" scoped>
section {
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

.contact {

    form {
        display: flex;
        flex-direction: column;
        gap: 24px;
        border: 1px solid #d9d9d9;
        border-radius: 8px;
        padding: 24px;

        .form-elem {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
    }
}
</style>
