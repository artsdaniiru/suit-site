<template>
  <!-- Тут не нужен еще один див! Стили от product приоритетные чем у router-view см. стили этого файла↓ -->
  <div class="product">
    <div class="product-img">
      <img :src="image" :alt="name" class="product-image" />
    </div>

    <div class="product-setting">
      <div class="product-info">
        <div class="product-title">
          <h3 class="product-title">{{ name }}</h3>
          <h3 class="product-title">{{ englishName }}</h3>
          <span class="price">{{ formattedPrice }}</span>
          <div class="setting-inf">
            <span> 3つのサイズから基本スーツの価格が決定されます。</span>
          </div>

        </div>
        <form @submit.prevent="submitForm">
          <label for="size">Размер:</label>
          <select v-model="selectedSize" id="size">
            <option disabled value="">Выберите размер</option>
            <option v-for="size in sizes" :key="size" :value="size">
              {{ size }}
            </option>
          </select>

          <button type="submit">Отправить</button>
        </form>
        <div class="size-form">

        </div>




      </div>
    </div>


  </div>


</template>

<script>
import { defineComponent, computed } from 'vue';

export default defineComponent({
  name: 'ProductView',
  props: {
    name: {
      type: String,
      default: 'クラシックネイビー',
    },
    englishName: {
      type: String,
      default: 'Classic Navy',
    },
    price: {
      type: Number,
      default: 28000,
    },
    image: {
      type: String,
      default: 'images/suit.webp',
    },
    width: {
      type: String,
      default: '-webkit-fill-available',
    }
  },
  setup(props) {
    // Вычисляемое свойство для форматирования цены
    const formattedPrice = computed(() => `¥${props.price.toLocaleString('ja-JP')}〜`);

    return {
      formattedPrice,
    };
  }
});
</script>

<style lang="scss" scoped>
.product {
  display: flex;
  justify-content: space-between;
  gap: 64px;
  padding: 64px;
  flex-direction: unset; //эта тема отменяет направление от router-view

  .product-img {
    width: 50%;


    .product-image {
      width: 100%;
    }
  }

  .product-setting {
    width: 50%;

    .product-title {

      h3 {
        margin-top: 0;
        margin-bottom: 5px;
      }

      .price {
        font-weight: 700;
        font-size: 48px;
      }

      .setting-inf {
        border: 1px solid #d9d9d9;
        border-radius: 12px;
        padding: 10px 40px;
        position: relative;

        &::before {
          content: url("../assets/icons/info.svg");
          display: block;
          position: absolute;
          top: 12px;
          left: 10px;
          width: 16px;
          height: 16px;
        }
      }
    }
  }
}
</style>