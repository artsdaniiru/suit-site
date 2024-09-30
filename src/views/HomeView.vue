<template>
    <div>
        <section class="hero">
            <h1>dfjghdkjfgjkd</h1>
            <p>理想の男性にふさわしい理想のスーツ</p>
        </section>

        <section class="services">
            <h2>当社のサービスvbvxbxcv</h2>
            <p>
                当サイトでは、豊富な紳士スーツのラインナップをご用意しております。お客様はご自身のサイズを入力するだけで、
                ピッタリのスーツを簡単にご注文いただけます。
            </p>
            <div class="strengths">
                <div class="strength">
                    <i class="icon">👔</i>
                    <h3>個別対応</h3>
                    <p>お客様一人ひとりの体型に合わせた、特別なオーダーメイドスーツを作り上げます。</p>
                </div>
                <div class="strength">
                    <i class="icon">🧵</i>
                    <h3>高品質</h3>
                    <p>最高品質の素材を使用し、熟練された職人が作る高級スーツをご提供します。</p>
                </div>
                <div class="strength">
                    <i class="icon">🛒</i>
                    <h3>簡単な注文プロセス</h3>
                    <p>お気に入りのスーツを選び、サイズを入力するだけで簡単にご注文いただけます。</p>
                </div>
            </div>
        </section>

        <section class="products">
            <h2>人気な商品</h2>
            <div class="product-list">
                <div class="product">
                    <img src="path/to/navy-suit.jpg" alt="クラシックネイビー">
                    <h3>クラシックネイビー</h3>
                    <p>¥28,000〜</p>
                    <button>内容を見る</button>
                </div>
                <div class="product">
                    <img src="path/to/gray-suit.jpg" alt="エレガンスグレー">
                    <h3>エレガンスグレー</h3>
                    <p>¥25,000〜</p>
                    <button>内容を見る</button>
                </div>
                <div class="product">
                    <img src="path/to/black-suit.jpg" alt="ミッドナイトブラック">
                    <h3>ミッドナイトブラック</h3>
                    <p>¥30,000〜</p>
                    <button>内容を見る</button>
                </div>
            </div>
        </section>

        <section class="testimonials">
            <h2>お客様の声</h2>
            <div class="testimonial-list">
                <div class="testimonial">
                    <h3>期待以上の仕上がり</h3>
                    <p>初めてオーダーメイドのスーツを注文しましたが、期待以上の仕上がりに感動しました。</p>
                    <p>田中さん | 東京都</p>
                </div>
                <div class="testimonial">
                    <h3>素晴らしい品質とサービス</h3>
                    <p>生地の優雅な質感や高品質な仕上がり、着心地も抜群です。</p>
                    <p>鈴木さん | 大阪府</p>
                </div>
                <div class="testimonial">
                    <h3>自分のスタイルにぴったり</h3>
                    <p>自分のスタイルにぴったりのスーツを見つけられて大満足です。</p>
                    <p>佐藤さん | 横浜市</p>
                </div>
            </div>
        </section>

        <section class="contact">
            <h2>連絡</h2>
            <form>
                <label for="name">名前</label>
                <input id="name" type="text" placeholder="名前を入力してください">

                <label for="email">メール</label>
                <input id="email" type="email" placeholder="メールアドレスを入力してください">

                <label for="phone">電話番号</label>
                <input id="phone" type="tel" placeholder="電話番号を入力してください">

                <label for="message">メッセージ</label>
                <textarea id="message" placeholder="メッセージを入力してください"></textarea>

                <button type="submit">送信</button>
            </form>
        </section>
    </div>
</template>

<script setup>
import { defineComponent } from 'vue';

defineComponent({});
</script>

<style scoped>
.hero {
    /* background-image: url('path/to/hero-image.jpg'); */
    background: #42b983;
    background-size: cover;
    background-position: center;
    color: #fff;
    text-align: center;
    padding: 100px 20px;
}

.services,
.products,
.testimonials,
.contact {
    padding: 40px 20px;
    text-align: center;
}

.strengths {
    display: flex;
    justify-content: space-around;
    margin-top: 20px;
}

.strength {
    max-width: 250px;
}

.icon {
    font-size: 40px;
}

.product-list {
    display: flex;
    justify-content: space-around;
}

.product {
    max-width: 250px;
}

.testimonial-list {
    display: flex;
    justify-content: space-around;
}

form {
    display: grid;
    gap: 10px;
    max-width: 500px;
    margin: 0 auto;
}

button {
    background-color: #42b983;
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
}

button:hover {
    background-color: #3a9f6b;
}
</style>
