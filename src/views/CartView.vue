<template>
  <div class="empty-cart" v-if="computedCart.length == 0">
    <h1>カートに何もありません</h1>
    <img src="@/assets/images/sorry.png" alt="">
    <span>商品はカートに追加してください</span>
    <router-link to="/catalog"><button class="button">カタログへ</button></router-link>
  </div>

  <template v-else>
    <h2 style="text-align: left">カート</h2>


    <div class="product-list">

      <template v-if="!is_loading">
        <div class="product-cart" v-for="item in computedCart" :key="item">
          <div class="product-info">

            <img :src="items[item.id].image_path" :alt="name" class="product-image" @click="goToProductPage(item.id)" />

            <div class="price-info">
              <div class="price-title">
                <h3 class="product-title" @click="goToProductPage(item.id)">{{ items[item.id].name }}</h3>
                <p class="product-title">{{ englishName }}</p>
                <div class="price-size">
                  <template v-if="items[item.id].type == 'suit'">
                    <div class="size-price">
                      <span class="font-weight">身長</span>
                      <span>{{ item.body_sizes.height }}cm</span>
                    </div>
                    <div class="size-price">
                      <span class="font-weight">肩幅</span>
                      <span>{{ item.body_sizes.shoulder_width }}cm</span>
                    </div>
                    <div class="size-price">
                      <span class="font-weight">ウェストサイズ</span>
                      <span>{{ item.body_sizes.waist_size }}cm</span>
                    </div>
                  </template>
                  <div v-else class="size-price">
                    <span class="font-weight">サイズ</span>
                    <span>{{ items[item.id].sizes[item.size].name }}</span>
                  </div>

                  <span class="final-price font-weight">{{ formattedPrice(items[item.id].sizes[item.size].price) }}</span>
                </div>
              </div>
              <div class="option-price" v-if="items[item.id].type == 'suit'">
                <div class="option-wrap" v-if="item.options.cloth.id != ''">
                  <span class="font-weight">生地の種類</span>
                  <span>{{ options[item.options.cloth.id].name }} <span class="font-weight">+{{ formattedPrice(optionPrice(item.options.cloth)) }}</span></span>
                </div>
                <div class="option-wrap" v-if="item.options.color.id != ''">
                  <span class="font-weight">生地の色</span>
                  <span>{{ options[item.options.color.id].name }} <span class="font-weight">+{{ formattedPrice(optionPrice(item.options.color)) }}</span></span>
                </div>
                <div class="option-wrap" v-if="item.options.lining.id != ''">
                  <span class="font-weight">裏地の種類</span>
                  <span>{{ options[item.options.lining.id].name }} <span class="font-weight">+{{ formattedPrice(optionPrice(item.options.lining)) }}</span></span>
                </div>
                <div class="option-wrap" v-if="item.options.button.id != ''">
                  <span class="font-weight">ボタンの種類</span>
                  <span>{{ options[item.options.button.id].name }} <span class="font-weight">+{{ formattedPrice(optionPrice(item.options.button)) }}</span></span>
                </div>
              </div>
            </div>
          </div>
          <div class="product-final-price">
            <div class="delete-box" @click="deleteFromCartLocal(item.id)"><span class="product-delete">Delete</span></div>
            <div class="change-box" @click="deleteFromCart(item.id)"><span class="product-change">Order Change</span></div>
            <div class="final-price">
              <span>金額</span>
              <span>{{ formattedPrice(item.totalPrice) }}</span>
            </div>
          </div>
        </div>
      </template>

    </div>

    <div class="checkout-box">
      <div class="list-pricebox">
        <div class="list-price">
          <span>会計</span>
          <span>{{ formattedPrice(totalPrice) }}</span>
        </div>
        <div class="list-btn">
          <router-link to="/catalog"><span>戻る</span></router-link>
          <button class="button" @click="goToAboutPage">チェックアウト</button>
        </div>
      </div>
    </div>
  </template>
</template>

<script>
import { defineComponent, inject, onBeforeMount, ref, computed } from "vue";
import axios from "axios";
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toast-notification';


export default defineComponent({
  name: "CartView",
  setup() {

    const toast = useToast();

    const router = useRouter();
    const items = ref({});
    const { cart, deleteFromCart } = inject("cart");
    const is_loading = ref(false);
    const options = ref({});

    // Преобразование размеров
    function fix_sizes_ids(sizes) {
      let n_sizes = {};
      sizes.forEach((val) => {
        n_sizes[val.id] = val;
      });
      return n_sizes;
    }

    // Форматирование цены
    function formattedPrice(price) {
      return `¥${Number(price).toLocaleString("ja-JP")}`;
    }

    // Цена опции
    function optionPrice(option) {
      if (option.free) {
        return 0;
      } else {
        if (option.id == "") return 0;
        return options.value[option.id]?.price || 0;
      }
    }

    // Рассчитываем итоговую цену для каждого товара
    const computedCart = computed(() => {
      return cart.value.map((item) => {
        const basePrice = items.value[item.id]?.sizes[item.size]?.price || 0;
        const optionPriceSum = Object.values(item.options || {}).reduce(
          (total, option) => Number(total) + Number(optionPrice(option)),
          0
        );
        const totalPrice = Number(basePrice) + Number(optionPriceSum);
        return {
          ...item,
          totalPrice,
        };
      });
    });

    // Рассчитываем общую цену корзины
    const totalPrice = computed(() => {
      return computedCart.value.reduce((sum, item) => sum + item.totalPrice, 0);
    });

    // Получение товаров
    const fetchProducts = async () => {
      is_loading.value = true;
      try {
        const p_ids = cart.value.map((val) => val.id);
        const url = `${process.env.VUE_APP_BACKEND_URL}/backend/products.php?itemsPerPage=1000&ids=${p_ids.join()}`;
        const response = await axios.get(url, { withCredentials: true });

        if (Array.isArray(response.data.products)) {
          let items_obj = {};
          response.data.products.forEach((product) => {
            items_obj[product.id] = {
              id: product.id,
              ...product,
              sizes: fix_sizes_ids(product.sizes),
            };
          });
          items.value = items_obj;
        } else {
          console.error("Ошибка: ожидался массив товаров");
        }
      } catch (error) {
        console.error("Ошибка при получении товаров:", error);
      } finally {
        is_loading.value = false;
      }
    };

    // Получение опций
    const fetchAllOptions = async () => {
      try {
        const url = `${process.env.VUE_APP_BACKEND_URL}/backend/options.php?action=list_all_options&itemsPerPage=1000`;
        const response = await axios.get(url, { withCredentials: true });

        if (response.data.status === "success") {
          let options_edit = {};
          response.data.options.forEach((val) => {
            options_edit[val.id] = val;
          });
          options.value = options_edit;
        } else {
          console.error("Ошибка: ожидался массив опций");
        }
      } catch (error) {
        console.error("Ошибка при получении опций:", error);
      }
    };

    // Переход на страницу оформления
    const goToAboutPage = () => {
      router.push("/checkout");
    };
    const goToProductPage = (id) => {
      router.push("/product/" + id);
    };

    onBeforeMount(() => {
      fetchAllOptions();
      fetchProducts();
    });


    function deleteFromCartLocal(id) {
      deleteFromCart(id);
      toast.error('商品はカートから削除しました', {
        position: 'bottom-right',
        duration: 2000,
      });
    }

    return {
      items,
      cart,
      is_loading,
      options,
      formattedPrice,
      optionPrice,
      computedCart,
      totalPrice,
      goToAboutPage,
      goToProductPage,
      deleteFromCartLocal
    };
  },
});
</script>

<style lang="scss" scoped>
.empty-cart {
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


.font-weight {
  font-weight: 700;
  font-size: 16px;
}

h3 {
  font-weight: 600;
  font-size: 24px;
  margin: 0;
  cursor: pointer;
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




    .product-image {
      width: 160px;
      aspect-ratio: 1 / 1;
      object-fit: cover;
      border-radius: 5px;
      cursor: pointer;
    }


    .price-title {
      margin-bottom: 16px;
    }

    .price-size {
      display: flex;
      gap: 12px;
      align-items: baseline;

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
        font-size: 18px;
      }
    }

    .delete-box {
      text-align: right;
      margin: 24px 24px 0 24px;
      cursor: pointer;

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

    .change-box {
      text-align: right;
      margin: -80px 24px 0 24px;
      ;
      cursor: pointer;


      .product-change {
        width: min-content;
        font-weight: 400;
        position: relative;

        &::before {
          content: url(../assets/icons/refresh.svg);
          display: block;
          position: absolute;
          right: 110px;
          width: 20px;

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
</style>