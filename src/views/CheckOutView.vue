<template>

  <div class="checkout-view">

    <h2>チェックアウト</h2>

    <div class="checkout-content">


      <h2>配達</h2>
      <div class="address-wrap">

        <div class="address-card" v-for="item in user.addresses" :key="item">
          <h2>{{ item.name }}</h2>
          <p>{{ item.address }}</p>
          <div class="tel">
            <span>電話番号: </span>
            <span>{{ item.phone }}</span>
          </div>
          <span class="icon-close"></span>
          <span class="icon-pencil"></span>
        </div>

        <div class="add-card">
          <span class="icon-plus"></span>
          <p>新しいお届け先住所を追加する</p>
        </div>

      </div>

      <div class="order-wrap">
        <h2>お支払方法</h2>
        <div class="card-wrap">
          <p>クレジットカード</p>

          <div class="card-choice-wrap" v-for="(item, index) in user.payment_methods" :key="item">
            <input class="radioBtn" type="radio" :id="'payment-' + index" :value="item" v-model="selectedPayment" name="payment-method" />
            <label :for="'payment-' + index">{{ item.card_number }}</label>
          </div> <a class="add-card">
            クレジットカードまたはデビットカードを追加する
          </a>
        </div>
        <div class="card-wrap">
          <p>その他</p>
          <div class="pay-choice-wrap">
            <input class="radioBtn" type="radio" id="cash" value="cash" v-model="paymentMethod" name="other-payment" />
            <label for="cash">現金</label>
          </div>
          <div class="pay-choice-wrap">
            <input class="radioBtn" type="radio" id="convenience" value="convenience" v-model="paymentMethod" name="other-payment" />
            <label for="convenience">コンビニ払い</label>
          </div>

        </div>

      </div>

      <button style="width: 100%;" class="button" @click="saveAction">注文を確定する</button>

    </div>


  </div>



</template>
<script>
import { defineComponent, inject/* , onBeforeMount, ref, computed */ } from "vue";
import axios from "axios";
// import { useRouter } from 'vue-router'
// import { useToast } from 'vue-toast-notification';


export default defineComponent({
  name: "CartView",
  setup() {

    const { user } = inject("auth");
    const { cart } = inject('cart');




    const saveAction = async () => {
      try {
        const response = await axios.post(
          process.env.VUE_APP_BACKEND_URL + '/backend/orders.php?action=create_order',
          {
            cart: cart.value,
            address_id: user.value.addresses[0].id,
            payment_method_id: user.value.payment_methods[0].id
          },
          { withCredentials: true }
        );

        if (response.data.status !== "success") {
          console.error("Ошибка при сохранении заказа2:", response.data.message);
          return;
        } else {
          console.log(response.data);
        }
      } catch (error) {
        console.error("Ошибка при сохранении заказа1:", error);
      }
    };
    return {
      user,
      saveAction
    };
  },
});
</script>

<style lang="scss" scoped>
h2 {
  font-weight: 600;
  font-size: 24px;
  text-align: left;
}

.checkout-content {
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  padding: 24px;
}


.address-wrap {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;




  .address-card {
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    position: relative;
    padding: 32px;
    box-sizing: border-box;
    width: 100%;



    .icon-close {
      width: min-content;
      position: absolute;
      top: 0;
      right: 0;
      top: 10px;
      right: 10px;
      cursor: pointer;

      &::before {
        content: url(../assets/icons/close.svg);
        display: block;

      }
    }

    .icon-pencil {
      width: min-content;
      position: absolute;
      bottom: 10px;
      right: 10px;
      cursor: pointer;

      &::before {
        content: url(../assets/icons/pencil.svg);
        display: block;

      }
    }
  }


  .add-card {
    position: relative;
    border: 2px dashed #d9d9d9;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100%;
    padding: 32px;
    box-sizing: border-box;
    cursor: pointer;


    .icon-plus {
      width: min-content;
      font-weight: 400;
      opacity: 50%;

      &::before {
        content: url(../assets/icons/plus.svg);
        display: block;
      }
    }

    p {
      opacity: 50%;
    }
  }


}

.order-wrap {


  .pay-choice-wrap {

    display: flex;
    align-items: baseline;
    cursor: pointer;

    .radioBtn {
      position: relative;
      display: block;
      height: min-content;
      margin-bottom: 24px;
      cursor: pointer;
    }
  }

  .card-choice-wrap {
    display: flex;
    align-items: baseline;
    gap: 50px;
    cursor: pointer;

    .radioBtn {
      position: relative;
      display: block;
      height: min-content;
      margin-bottom: 24px;
      cursor: pointer;

      &::before {
        content: url(../assets/icons/visa.svg);
        display: block;
        position: absolute;
        left: 20px;
        bottom: -4px;
        height: 21px;
      }
    }
  }

  .add-card {
    padding-left: 30px;
    position: relative;

    &::before {
      content: url(../assets/icons/plus.svg);
      display: block;
      position: absolute;
      transform: scale(0.5);
      bottom: -10px;
    }

  }
}
</style>