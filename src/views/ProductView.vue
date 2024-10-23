<template>

  <div class="product">
    <div class="main">
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

          <div class="size-form">
            <form @submit.prevent="submitForm">

              <div class="size-cont grid">

                <div class="size-box1">

                  <input v-model="inputValue" id="textInput" type="text" placeholder="肩幅cm" />
                </div>
                <div class="size-box1">

                  <input v-model="inputValue" id="textInput" type="text" placeholder="肩幅cm" />
                </div>
                <div class="size-box1">

                  <input v-model="inputValue" id="textInput" type="text" placeholder="肩幅cm" />
                </div>

                <!-- <div class="size-box">
                  <CustomInput style="width: 152px;;" v-model="inputValue" :labelText="'身長'" placeholderText="身長cm" />
                </div>
                <div class="size-box">
                  <label for="textInput">肩幅</label>
                  <input v-model="inputValue" id="textInput" type="text" placeholder="肩幅cm" />
                </div>
                <div class="size-box">
                  <label for="textInput">ウェストサイズ</label>
                  <input v-model="inputValue" id="textInput" type="text" placeholder="ウェストサイズcm" />
                </div> -->
              </div>
              
              <div class="size-cont">
                <div class="size-box">
                  <CustomSelect :values="['コットン (綿)', 'コットン (綿)2', 'コットン (綿)3', 'コットン (綿)4']" v-model="selectedSize" :labelText="'生地の種類'" />
                </div>
                <div class="size-box">
                  <CustomSelect :values="['赤 (あか)', '赤 (あか)2', '赤 (あか)3', '赤 (あか)4']" v-model="selectedSize" :labelText="'生地の色'" />
                </div>
              </div>
              <div class="size-cont">
                <div class="size-box">

                  <CustomSelect :values="['サテン裏地', 'サテン裏地2', 'サテン裏地3', 'サテン裏地4']" v-model="selectedSize" :labelText="'裏地の種類'" />
                </div>
                <div class="size-box">
                  <CustomSelect :values="['プラスチックボタン', 'プラスチックボタン2', 'プラスチックボタン3', 'プラスチックボタン4']" v-model="selectedSize" :labelText="'ボタンの種類'" />
                </div>
              </div>
              <button class="button">カートに追加</button>
            </form>
          </div>
        </div>
      </div>
    </div>


    <div class="add-product">
      <h2>追加の商品</h2>

      <div class="add-content">

        <div class="add-card">


          <div class="img-box">
            <!-- <img :src="image" :alt="name" class="product-image" /> -->
          </div>
        </div>

      </div>



      <div class="form-check">
        <input type="checkbox" class="form-check-input" id="customCheck" v-model="isChecked" />
      </div>
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
.product {
  padding: 64px;
  gap: 64px;

  .main {
    display: flex;
    justify-content: space-between;
    gap: 64px;

    flex-direction: unset;

    .product-img {
      width: 50%;

      .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
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

      .size-form {
        display: flex;
        margin-top: 20px;

        form {
          display: flex;
          flex-direction: column;
          gap: 24px;
          width: -webkit-fill-available;
        }

        .size-cont {
          display: flex;
          gap: 24px;

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

.size-cont.grid{
  display: grid !important;
  grid-template-columns: 1fr 1fr 1fr;

  .size-box{
    width: 100%;

    input{
      width: 100%;
    }
  }
}
// чтобы инпуты не разьебывало которые нужно вводить текст
.size-cont input{
  width: auto;
  min-width: none;
}

.add-content{
  border: 1px solid #d9d9d9;
  border-radius: 12px;
  padding: 10px 40px;
  position: relative;


  &::before{
    content: url(../assets/icons/plus.svg);
    display: block;
    position: absolute;
    top: -16px;
    left: calc(50% - 16px);
    padding: 0 8px;
    background-color: #fff;

  }
  .img-box{
    width: 170px;
    height: 170px;
    border-radius: 50%;
    background: url(../assets/images/main.webp);
    border: 1px solid black;


      img{
        width: 100%;
      }
    }

}




</style>
