<template>

  <div class="product">
    <div class="main">
      <div class="product-img" :class="{ loading: is_loading }">
        <img v-if="!is_loading" :src="data.product_images[show_image_id] == undefined ? '/Image.png' : data.product_images[show_image_id].image_path" :alt="data.product.name" class="product-image" @click="showImage(data.product_images[show_image_id], show_image_id)" />

        <div class="gallery" v-if="data.product_images.length > 1">
          <template v-for="(image, index) in data.product_images" :key="index">
            <img :src="image.image_path" :alt="data.product.name" class="gallery-image" :class="{ selected: show_image_id == index }" @click="show_image_id = index" />
          </template>
        </div>
        <div v-if="show_image" class="modal images-full" @click="show_image = false">
          <img class="close" src="@/assets/icons/close-white.svg" alt="close">
          <img class="prev" src="@/assets/icons/prev-white.svg" alt="prev" @click.stop="showImagePrev">
          <img class="image" @click.stop="showImageNext" :src="show_image_path" alt="">
          <img class="next" src="@/assets/icons/next-white.svg" alt="next" @click.stop="showImageNext">
        </div>
      </div>


      <div class="product-info">
        <div class="product-title">

          <h3 class="product-title" :class="{ loading: is_loading }">{{ data.product.name }}</h3>
          <h3 class="product-title" :class="{ loading: is_loading }">{{ data.product.name_eng }}</h3>
          <span class="price" :class="{ loading: is_loading }">{{ formattedPrice }}</span>
          <div class="setting-inf" v-if="data.product.type == 'suit'">
            <span> 3つのサイズから基本スーツの価格が決定されます。</span>
          </div>
        </div>

        <div class="size-form" v-if="data.product.type == 'suit'">
          <div class="size-cont grid">

            <CustomInput v-model="body_sizes.height" @update:modelValue="is_edited = true" :labelText="'身長'" placeholderText="175cm" type="number" />
            <CustomInput v-model="body_sizes.shoulder_width" @update:modelValue="is_edited = true" :labelText="'肩幅'" placeholderText="40cm" type="number" />
            <CustomInput v-model="body_sizes.waist_size" @update:modelValue="is_edited = true" :labelText="'ウェストサイズ'" placeholderText="70cm" type="number" />
          </div>

          <div class="size-cont">
            <div class="size-box">
              <CustomSelect :values="options.cloth" v-model="selectedOptions.cloth" @update:modelValue="is_edited = true" :labelText="'生地の種類'" />
            </div>
            <div class="size-box">
              <CustomSelect :values="options.color" v-model="selectedOptions.color" @update:modelValue="is_edited = true" :labelText="'生地の色'" />
            </div>
          </div>
          <div class="size-cont">
            <div class="size-box">

              <CustomSelect :values="options.lining" v-model="selectedOptions.lining" @update:modelValue="is_edited = true" :labelText="'裏地の種類'" />
            </div>
            <div class="size-box">
              <CustomSelect :values="options.button" v-model="selectedOptions.button" @update:modelValue="is_edited = true" :labelText="'ボタンの種類'" />
            </div>
          </div>
        </div>
        <div class="size-form" v-else>
          <CustomSelect :values="sizes_o" v-model="selectedSizeId" @update:modelValue="is_edited = true" :labelText="'サイズ'" :notSelect="true" />
        </div>

        <button v-if="!in_cart" class="button" @click="addToCartLocal">カートに追加</button>
        <div class="edit-btn" v-else>
          <button class="button edit" @click="updateCartLocal" :disabled="!is_edited">変更</button>
          <button class="button danger" @click="deleteFromCartLocal">削除</button>
          <button class="button edit" @click="goTo('/cart')">カートへ</button>
        </div>

      </div>

    </div>

    <div class="description" v-if="data.product.description != ''">
      <h2>商品説明</h2>
      <div v-html="data.product.description"></div>
    </div>
  </div>
</template>

<CustomNotification v-model="showNotIf" message="Это уведомление!" />

<script>
import axios from "axios";
import { defineComponent, computed, ref, onBeforeMount, watch, inject } from "vue";
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'vue-toast-notification';

export default defineComponent({
  name: "ProductCard",
  setup() {

    const toast = useToast();
    //users thing

    const { user } = inject('auth');
    const { addToCart, deleteFromCart, updateCartItem, cart } = inject('cart');
    const in_cart = computed(() => {
      if (!cart.value) return false;
      return cart.value.some(item => item.id === uid);
    });

    const showNotIf = ref(false);

    const is_loading = ref(true);

    const router = useRouter();
    const route = useRoute();
    const uid = route.params.uid;

    const data = ref({
      product: {
        name: '商品名',
        type: 'suit',
        name_eng: 'Product name',
        price: 28000,
      },
      sizes: [
        {
          id: null,
          height_min: 0,
          height_max: 0,
          shoulder_width_min: 0,
          shoulder_width_max: 0,
          waist_size_min: 0,
          waist_size_max: 0

        }
      ],
      options: [],
      product_images: [],

    });

    const is_edited = ref(false);



    const show_image = ref(false);
    const show_image_path = ref('/image.png');
    const show_image_id = ref(0);

    function showImage(img, index) {

      show_image_path.value = img.image_path
      show_image_id.value = index
      show_image.value = true;
    }

    // Следим за изменениями modelValue
    watch(() => show_image.value, (newValue) => {

      if (newValue) {
        document.body.classList.add('no-scroll');
      } else {
        document.body.classList.remove('no-scroll');
      }

    });


    function showImageNext() {
      if ((show_image_id.value + 1) != data.value.product_images.length) {
        show_image_id.value = show_image_id.value + 1;
      } else {
        show_image_id.value = 0;
      }

      show_image_path.value = data.value.product_images[show_image_id.value].image_path;
    }

    function showImagePrev() {
      if ((show_image_id.value - 1) != -1) {
        show_image_id.value = show_image_id.value - 1;
      } else {
        show_image_id.value = data.value.product_images.length - 1;
      }

      show_image_path.value = data.value.product_images[show_image_id.value].image_path;
    }

    // Метод для получения товара
    const fetchProduct = async () => {
      is_loading.value = true;
      try {
        const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/product.php?product_id=' + uid, {
          withCredentials: true
        });

        console.log(response);


        if (response.data.status == "success") {

          data.value = response.data.data;
          if (data.value.product.type == 'suit') {
            fetchAllOptions()
          } else {
            setTimeout(() => {
              setSizeNotSuit();
            }, 10);

          }

          if (data.value.product.active == 0 || data.value.product.active == '0') {
            router.push('/404');
          }

          is_loading.value = false;
        } else {
          console.error("Ошибка при получении товара:", response.data.status);
          router.push('/404');
        }

      } catch (error) {
        console.error("Ошибка при получении товара:", error);
        router.push('/404');
      }
    };

    //options
    const options = ref({});
    const product_options = ref([]);

    const fetchAllOptions = async () => {
      try {


        let url = process.env.VUE_APP_BACKEND_URL + '/backend/options.php?action=list_all_options&splitByType=true&itemsPerPage=1000';
        const response = await axios.get(url, {
          withCredentials: true
        });


        // Убедимся, что товары приходят в поле `products`
        if (response.data.status == 'success') {

          let product_options_d = [];
          Object.keys(data.value.options).forEach(key => {
            let id = data.value.options[key].id;
            product_options_d.push(id);
          })

          product_options.value = product_options_d;


          let options_edit = {};
          Object.keys(response.data.options).forEach(key => {
            options_edit[key] = {};
            response.data.options[key].forEach(val => {

              let id = val.id

              let price = val.price;
              if (product_options_d.includes(id)) {
                price = 0;
              }

              options_edit[key][id] = val.name + '<strong>' + `+¥${Number(price).toLocaleString('ja-JP')}` + '</strong>';
            })
          })
          options.value = options_edit;

          selectedOptions.value = {
            cloth: setOptions('cloth'),
            color: setOptions('color'),
            lining: setOptions('lining'),
            button: setOptions('button'),
          }

        } else {
          console.error("Ожидался массив опций, но получено что-то другое:", response.data);
        }
      } catch (error) {
        console.error("Ошибка при получении опций:", error);
      }
    };

    //sizes
    const sizes_o = computed(
      () => {
        let sizes = {};
        data.value.sizes.forEach(val => {
          sizes[val.id] = val.name;
        })

        return sizes;

      }
    );

    function optionsInCart() {

      let options = {};
      Object.keys(selectedOptions.value).forEach(key => {
        options[key] = {
          id: selectedOptions.value[key],
          free: product_options.value.includes(selectedOptions.value[key]) ? true : false,
        };
      })
      return options;
    }

    function createCartItem() {
      let cart_item = {
        id: data.value.product.id,
      };
      if (data.value.product.type == 'suit') {
        cart_item['options'] = optionsInCart();


        cart_item['body_sizes'] = body_sizes.value;
        cart_item['size'] = priceBasedOnSize.value.id;
        cart_item['price'] = priceBasedOnSize.value.price;
      } else {
        cart_item['size'] = selectedSizeId.value;
        cart_item['price'] = data.value.sizes[0].price;
      }

      return cart_item
    }

    function addToCartLocal() {


      if (data.value.product.type == 'suit' && (body_sizes.value.height == 0 || body_sizes.value.shoulder_width == 0 || body_sizes.value.waist_size == 0)) {
        toast.error('ボディ寸法が入力されていません');
      } else {
        toast.success('商品はカートに追加しました', {
          position: 'bottom-right', // Можно изменить на 'bottom-left', 'bottom-right', 'top-center', и т.д.
          duration: 2000, // Время отображения уведомления в миллисекундах
        });


        addToCart(createCartItem());
      }



    }
    function updateCartLocal() {
      toast.success('商品はカートに変更しました', {
        position: 'bottom-right',
        duration: 2000,
      });
      updateCartItem(data.value.product.id, createCartItem());

    }

    function deleteFromCartLocal() {
      toast.error('商品はカートから削除しました', {
        position: 'bottom-right',
        duration: 2000,
      });
      deleteFromCart(data.value.product.id);
    }

    function setOptions(index) {
      if (in_cart.value) {
        let cart_item = cart.value.find(item => item.id == uid);

        if (cart_item.options != undefined) {
          console.log(cart_item.options[index].id);

          return cart_item.options[index].id;
          // return "";
        }
      } else {
        return "";
      }
    }


    const selectedOptions = ref({
      cloth: '',
      color: '',
      lining: '',
      button: '',
    })
    const selectedSizeId = ref(null);


    function setSizeNotSuit() {
      let cart_item = cart.value.find(item => item.id == uid);

      if (cart_item?.size != undefined) {
        selectedSizeId.value = cart_item.size;
      }
    }

    function setBodySize(index) {
      if (user.value[index] != undefined) {
        let size = user.value[index];
        if (in_cart.value) {
          let cart_item = cart.value.find(item => item.id == uid);
          if (cart_item.body_sizes != undefined && cart_item.body_sizes[index] != undefined) {
            size = cart_item.body_sizes[index];
          }
        }

        return size;
      } else {
        let size = "";
        if (in_cart.value) {
          let cart_item = cart.value.find(item => item.id == uid);
          if (cart_item.body_sizes != undefined && cart_item.body_sizes[index] != undefined) {
            size = cart_item.body_sizes[index];
          }
        }
        return size;
      }

    }

    const body_sizes = ref({
      height: setBodySize('height'),
      shoulder_width: setBodySize('shoulder_width'),
      waist_size: setBodySize('waist_size')
    });



    const priceBasedOnSize = computed(() => {

      const sortedSizes = [...data.value.sizes].sort((a, b) => Number(a.height_min) - Number(b.height_min));
      // Функция для определения подходящего размера для каждого параметра тела
      function findSizeForParameter(parameter, minKey, maxKey) {
        for (let i = 0; i < sortedSizes.length; i++) {
          const size = sortedSizes[i];
          const min = Number(size[minKey]);
          const max = Number(size[maxKey]);

          // Если параметр меньше минимального размера, возвращаем минимальный размер
          if (parameter < min) {
            return sortedSizes[0];
          }
          // Если параметр находится в пределах текущего размера, возвращаем этот размер
          if (parameter >= min && parameter <= max) {
            return size;
          }
        }
        // Если параметр больше максимального, возвращаем максимальный размер
        return sortedSizes[sortedSizes.length - 1];
      }

      if (data.value.product.type == 'suit') {
        const { height, shoulder_width, waist_size } = body_sizes.value;

        // Сортируем размеры по высоте для упрощения проверки по порядку




        // Находим размеры для каждого параметра тела
        const heightSize = findSizeForParameter(height, 'height_min', 'height_max');
        const shoulderSize = findSizeForParameter(shoulder_width, 'shoulder_width_min', 'shoulder_width_max');
        const waistSize = findSizeForParameter(waist_size, 'waist_size_min', 'waist_size_max');

        // Определяем максимальный из найденных размеров
        const finalSize = [heightSize, shoulderSize, waistSize].reduce((max, size) => {
          return Number(size.height_min) > Number(max.height_min) ? size : max;
        }, sortedSizes[0]);

        finalSize.price = Number(finalSize.price);

        return finalSize;
      } else {
        return data.value.sizes[0];
      }

    });

    // Используем `watch` для обновления `selectedSizeId` при изменении `priceBasedOnSize`
    watch(priceBasedOnSize, (newValue) => {
      selectedSizeId.value = newValue.id;
    });



    // Вычисляемое свойство для форматирования цены
    const formattedPrice = computed(
      () => {
        let price = 10000;
        if (data.value.sizes[0] != undefined) {

          price = data.value.sizes[0].price;

          data.value.sizes.forEach(val => {
            if (Number(val.price) < price) {
              price = val.price;
            }
          })

          if (data.value.product.type == 'suit') {
            price = priceBasedOnSize.value.price;
          }



        }

        return `¥${Number(price).toLocaleString('ja-JP')}` + (data.value.product.type == 'suit' ? '〜' : '');
      }
    );


    onBeforeMount(() => {
      fetchProduct();
    });

    function goTo(url) {
      router.push(url);
    }

    return {
      formattedPrice,
      data,
      is_loading,
      options,
      show_image_id,
      show_image,
      show_image_path,
      showImage,
      showImageNext,
      showImagePrev,
      sizes_o,
      body_sizes,
      selectedSizeId,
      selectedOptions,
      addToCartLocal,
      in_cart,
      deleteFromCartLocal,
      updateCartLocal,
      showNotIf,
      setSizeNotSuit,
      goTo,
      is_edited
    };
  },
});
</script>

<style lang="scss" scoped>
.product {
  padding: 64px;
  gap: 64px;

  @include is-mobile() {
    padding: 24px;
    gap: 24px;
  }

  .main {
    display: flex;
    justify-content: space-between;
    gap: 64px;

    flex-direction: unset;

    @include is-mobile() {
      flex-direction: column;
      justify-content: unset;
      gap: 24px;
    }

    .product-img {
      width: 50%;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      gap: 12px;

      @include is-mobile() {
        width: -webkit-fill-available;
      }

      .product-image {
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
      }

      .gallery {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;

        .gallery-image {
          width: 90px;
          height: 90px;
          position: relative;
          cursor: pointer;
          border-radius: 5px;
          object-fit: cover;
          opacity: 0.7;

          &.selected {
            opacity: 1;
          }
        }
      }

    }

    .product-info {
      width: 50%;
      display: flex;
      flex-direction: column;
      gap: 24px;

      @include is-mobile() {
        width: -webkit-fill-available;
      }

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

      .size-form {

        display: flex;
        flex-direction: column;
        gap: 24px;
        width: -webkit-fill-available;


        .size-cont {
          display: flex;
          gap: 24px;

          @include is-mobile() {
            flex-direction: column;
          }

          .size-box {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: 8px;
          }

          input {
            min-width: auto;
            width: 120px;
          }
        }
      }

      .edit-btn {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        width: -webkit-fill-available;

        .button {
          width: -webkit-fill-available;

          &:last-of-type {
            grid-column: span 2;

          }
        }




      }
    }

    .add-content {
      border: 1px solid #d9d9d9;
      border-radius: 16px;
      padding: 12px;
    }
  }





  // стили кастомного чек бокса
  .form-check {
    position: relative;
    padding-left: 35px;
    margin-bottom: 20px;
    cursor: pointer;
    font-size: 16px;
    user-select: none;
  }

  .form-check-input {
    position: absolute;
    padding: 12px;
    left: 0;
    top: 0;
    min-width: auto;
    height: 20px;
    width: 20px;
    cursor: pointer;
    appearance: none;
    background-color: #fff;
    border: 2px solid #2c2c2c;
    border-radius: 4px;
    transition: background-color 0.2s ease, border-color 0.2s ease;
  }

  .form-check-input:checked {
    background-color: #2c2c2c;
    border-color: #2c2c2c;
  }

  .form-check-input:checked::before {
    content: "";
    position: absolute;
    left: 9px;
    top: 3px;
    width: 6px;
    height: 12px;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
  }

  .form-check-label {
    margin-left: 10px;
    cursor: pointer;
  }

  .form-check-input:focus {
    outline: none;
    // box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
  }
}

.size-cont.grid {
  display: grid !important;
  grid-template-columns: 1fr 1fr 1fr;

  @include is-mobile() {
    grid-template-columns: 1fr 1fr;
    grid-template-rows: auto auto;


    >* {
      @for $i from 1 through 3 {
        &:nth-child(3n - #{$i - 1}) {
          @if $i ==3 {
            grid-column: 1 / -1; // Третий элемент занимает всю строку
          }

          @else {
            grid-column: span 1; // Первые два элемента делят строку
          }
        }
      }
    }
  }

  .size-box {
    width: 100%;

    input {
      width: 100%;
    }
  }
}

// чтобы инпуты не разьебывало которые нужно вводить текст
.size-cont input {
  width: auto;
  min-width: none;
}

.add-content {
  border: 1px solid #d9d9d9;
  border-radius: 12px;
  padding: 40px 20px 40px 10px;
  position: relative;
  display: flex;
  justify-content: space-between;



  &::before {
    content: url(../assets/icons/plus.svg);
    display: block;
    position: absolute;
    top: -16px;
    left: calc(50% - 16px);
    padding: 0 8px;
    background-color: #fff;

  }

  .img-box {
    width: 170px;
    height: 170px;
    border-radius: 50%;
    border: 1px solid black;
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

  }

  .add-card {
    text-align: center;
    width: max-content;
    position: relative;

    .card-box {

      p {
        font-weight: 600;
        font-size: 24px;
        margin: 10px;

      }
    }

    .form-check {
      position: absolute;
      top: -25px;
      right: 0;
    }

    .card-icq {
      position: absolute;
      top: calc(50% - 16px);
      left: -50px;

      &::before {
        content: url(../assets/icons/plus.svg);
        display: block;
        position: absolute;
        top: calc(50% - 16px);
        left: -16px;
      }
    }

    .opacity {
      opacity: 50%;
    }


  }

  .buy {

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 15px;

    .price {
      font-weight: 700;
      font-size: 48px;
    }

    .button {
      width: 100%;
    }
  }




}


.modal.images-full {
  z-index: 110;
  cursor: pointer;

  .close {
    position: absolute;
    top: 5%;
    right: 5%;
  }

  .prev,
  .next {
    position: absolute;
    top: 50%;
  }

  .prev {
    left: 5%;
  }

  .next {
    right: 5%;
  }

  .image {
    margin: auto;
    max-height: 80%;
    max-width: 80%;
  }
}

.loading {
  background: #eee;
  background: linear-gradient(110deg, #ececec 8%, #f5f5f5 18%, #ececec 33%);
  background-size: 200% 100%;
  color: transparent;
  width: fit-content;
  animation: 1.5s shine linear infinite;
  border-radius: 8px;
}

@keyframes shine {
  to {
    background-position-x: -200%;
  }
}
</style>
