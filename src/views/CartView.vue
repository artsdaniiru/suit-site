<template>
  <h2 style="text-align: left">カート</h2>

  <div class="product-list">
    <div class="product-cart">
      <div class="product-info">
        <div class="product-img">
          <img :src="image" :alt="name" class="product-image" />
        </div>
        <div class="price-info">
          <div class="price-title">
            <h3 class="product-title">{{ name }}</h3>
            <p class="product-title">{{ englishName }}</p>
            <div class="price-size">
              <div class="size-price">
                <span class="font-weight">身長</span>
                <span>150cm</span>
              </div>
              <div class="size-price">
                <span class="font-weight">肩幅</span>
                <span>40cm</span>
              </div>
              <div class="size-price">
                <span class="font-weight">ウェストサイズ</span>
                <span>70cm</span>
              </div>
              <span class="final-price font-weight">30,000￥</span>
            </div>
          </div>
          <div class="option-price">
            <div class="option-wrap">
              <span class="font-weight">生地の種類</span>
              <span
                >コットン (綿) <span class="font-weight">+1000￥</span></span
              >
            </div>
            <div class="option-wrap">
              <span class="font-weight">生地の色</span>
              <span>赤 (あか) <span class="font-weight">+1000￥</span></span>
            </div>
            <div class="option-wrap">
              <span class="font-weight">裏地の種類</span>
              <span>サテン裏地 <span class="font-weight">+1000￥</span></span>
            </div>
            <div class="option-wrap">
              <span class="font-weight">ボタンの種類</span>
              <span
                >プラスチックボタン
                <span class="font-weight">+1000￥</span></span
              >
            </div>
          </div>
        </div>
      </div>
      <div class="product-final-price">
        <div class="delete-box"><a class="product-delete">Delete</a></div>
        <div class="final-price">
          <span>会計</span>
          <span>100,000￥</span>
        </div>
      </div>
    </div>
    <div class="product-cart">
      <div class="product-info">
        <div class="product-img">
          <img :src="image" :alt="name" class="product-image" />
        </div>
        <div class="price-info">
          <div class="price-title">
            <h3 class="product-title">{{ name }}</h3>
            <p class="product-title">{{ englishName }}</p>
            <div class="price-size">
              <div class="size-price">
                <span class="font-weight">身長</span>
                <span>150cm</span>
              </div>
              <div class="size-price">
                <span class="font-weight">肩幅</span>
                <span>40cm</span>
              </div>
              <div class="size-price">
                <span class="font-weight">ウェストサイズ</span>
                <span>70cm</span>
              </div>
              <span class="final-price font-weight">30,000￥</span>
            </div>
          </div>
          <div class="option-price">
            <div class="option-wrap">
              <span class="font-weight">生地の種類</span>
              <span
                >コットン (綿) <span class="font-weight">+1000￥</span></span
              >
            </div>
            <div class="option-wrap">
              <span class="font-weight">生地の色</span>
              <span>赤 (あか) <span class="font-weight">+1000￥</span></span>
            </div>
            <div class="option-wrap">
              <span class="font-weight">裏地の種類</span>
              <span>サテン裏地 <span class="font-weight">+1000￥</span></span>
            </div>
            <div class="option-wrap">
              <span class="font-weight">ボタンの種類</span>
              <span
                >プラスチックボタン
                <span class="font-weight">+1000￥</span></span
              >
            </div>
          </div>
        </div>
      </div>
      <div class="product-final-price">
        <div class="delete-box"><a class="product-delete">Delete</a></div>
        <div class="final-price">
          <span>会計</span>
          <span>100,000￥</span>
        </div>
      </div>
    </div>
    <div class="product-cart">
      <div class="product-info">
        <div class="product-img">
          <img :src="image" :alt="name" class="product-image" />
        </div>
        <div class="price-info">
          <div class="price-title">
            <h3 class="product-title">{{ name }}</h3>
            <p class="product-title">{{ englishName }}</p>
            <div class="price-size">
              <div class="size-price">
                <span class="font-weight">身長</span>
                <span>150cm</span>
              </div>
              <div class="size-price">
                <span class="font-weight">肩幅</span>
                <span>40cm</span>
              </div>
              <div class="size-price">
                <span class="font-weight">ウェストサイズ</span>
                <span>70cm</span>
              </div>
              <span class="final-price font-weight">30,000￥</span>
            </div>
          </div>
          <div class="option-price">
            <div class="option-wrap">
              <span class="font-weight">生地の種類</span>
              <span
                >コットン (綿) <span class="font-weight">+1000￥</span></span
              >
            </div>
            <div class="option-wrap">
              <span class="font-weight">生地の色</span>
              <span>赤 (あか) <span class="font-weight">+1000￥</span></span>
            </div>
            <div class="option-wrap">
              <span class="font-weight">裏地の種類</span>
              <span>サテン裏地 <span class="font-weight">+1000￥</span></span>
            </div>
            <div class="option-wrap">
              <span class="font-weight">ボタンの種類</span>
              <span
                >プラスチックボタン
                <span class="font-weight">+1000￥</span></span
              >
            </div>
          </div>
        </div>
      </div>
      <div class="product-final-price">
        <div class="delete-box"><a class="product-delete">Delete</a></div>
        <div class="final-price">
          <span>会計</span>
          <span>100,000￥</span>
        </div>
      </div>
    </div>
  </div>

  <div class="checkout-box">
    <div class="list-pricebox">
      <div class="list-price">
        <span>会計</span>
        <span>100,000￥</span>
      </div>
      <div class="list-btn">
        <router-link to="/catalog"><span>戻る</span></router-link>
        <button class="button" @click="goToAboutPage">チェックアウト</button>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, computed } from "vue";

export default defineComponent({
  methods: {
    goToAboutPage() {
      this.$router.push("../checkout");
    },
  },
  name: "ProductCard",
  props: {
    name: {
      type: String,
      default: "クラシックネイビー",
    },
    englishName: {
      type: String,
      default: "Classic Navy",
    },
    price: {
      type: Number,
      default: 28000,
    },
    image: {
      type: String,
      default: "images/suit.webp",
    },
    width: {
      type: String,
      default: "-webkit-fill-available",
    },
  },
  setup(props) {
    // Вычисляемое свойство для форматирования цены
    const formattedPrice = computed(
      () => `¥${props.price.toLocaleString("ja-JP")}〜`
    );

    return {
      formattedPrice,
    };
  },
});
</script>

<style lang="scss" scoped>
.font-weight {
  font-weight: 700;
  font-size: 16px;
}

h3 {
  font-weight: 600;
  font-size: 24px;
  margin: 0;
}
.product-title {
  margin: 0;
}

.product-cart {
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  margin: 24px 0;

  .product-info {
    display: flex;
    gap: 24px;
    margin: 24px 0 24px 24px;

    .price-title {
      margin-bottom: 16px;
    }

    .price-size {
      display: flex;
      gap: 12px;

      .size-price {
        width: max-content;
        display: flex;
        gap: 5px;
        align-items: baseline;
      }
    }
    .option-wrap {
      display: flex;
      justify-content: space-between;
    }
  }
  .product-final-price {
    display: flex;
    flex-direction: column;
    justify-content: space-between;

    .final-price {
      display: flex;
      gap: 16px;
      margin: 10px 24px;
      span {
        font-weight: 600;
        font-size: 24px;
      }
    }

    .delete-box {
      text-align: right;
      margin: 24px 24px 0 24px;

      .product-delete {
        width: min-content;
        font-weight: 400;
        color: #ec221f;
        position: relative;
        &::before {
          content: url(../assets/icons/delete.svg);
          display: block;
          position: absolute;
          right: 50px;
        }
      }
    }
  }
}

.checkout-box {
  width: 100%;
  display: flex;
  justify-content: right;

  .list-price,
  .list-btn {
    display: flex;
    gap: 16px;
    align-items: center;
    margin-bottom: 24px;
    justify-content: space-between;

    span {
      font-weight: 600;
      font-size: 24px;
    }

    a {
      span {
        font-weight: 400;
        font-size: 16px;
      }

      text-decoration: none;
      color: #1e1e1e;
      padding: 0px 8px 0px 8px;
      align-items: center;
      display: flex;
      padding: 10px;
      &:hover,
      &.router-link-exact-active {
        background: #f5f5f5;
        border-radius: 8px;
        padding: 10px;
      }
    }
  }
}

.product-image {
  width: 100%;
}
.product-img {
  width: 30%;
}
</style>
