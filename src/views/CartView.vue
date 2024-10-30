<template>
<h2 style="text-align: left;">カート</h2>

<div class="product-cart">

  <div class="product-info">
      <div class="product-img">
        <img :src="image" :alt="name" class="product-image" />
      </div>
      <div class="price-info">
        <div class="price-title">
          <h3  class="product-title">{{ name }}</h3>
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
            <span>コットン (綿) <span class="font-weight">+1000￥</span></span>
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
            <span>プラスチックボタン <span class="font-weight">+1000￥</span></span>
          </div>

        </div>
      </div>
  </div>
  <div class="product-final-price">
    <a class="product-delete">Delete</a>
    <h2 class="final-price">会計  100,000￥</h2>
  </div>

</div>

</template>

<script>
import { defineComponent, computed } from "vue";

export default defineComponent({
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
.font-weight{
  font-weight: 700;
  font-size: 16px;
}

h3{
  font-weight: 600;
  font-size: 24px;
  margin: 0;
}
.product-title{
  margin: 0;
}

.product-cart{
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  padding: 24px;
  display: flex;
  justify-content: space-between;


  .product-info{
    display: flex;
    gap: 24px;

    .price-title{
      margin-bottom: 16px;

      .size-price{
        align-content: baseline;
      }
    }

    .price-size{
      display: flex;
      gap: 12px;
    }
    .option-wrap{
      display: flex;
      justify-content: space-between;
    }
  }
  .product-final-price{
    display: flex;
    flex-direction: column;
    justify-content: space-between;

    .product-delete{
      font-weight: 400;
      text-align: right;
      color: #ec221f;
      position: relative;
      &::before{
        content: url(../assets/icons/delete.svg);
        display: block;
        position: absolute;
        right: 50px;
      }
    }

    h2{
      width: max-content;
    }
  }


}






.product-image{
  width: 100%;
}
.product-img{
  width: 30%;
}
</style>
