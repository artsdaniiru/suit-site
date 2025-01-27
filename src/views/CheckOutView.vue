<template>

  <div class="checkout-view">

    <h2>チェックアウト</h2>

    <div class="checkout-content">
      <h2>配達</h2>
      <div class="address-wrap">

        <div class="address-card" v-for="item in user.addresses" :key="item" :class="{ selected: selectedAddress === item.id }" @click="selectAddress(item.id)">
          <span class="icon-selected" v-if="selectedAddress === item.id"></span>
          <h2>{{ item.name }}</h2>
          <p>{{ item.address }}</p>
          <div class="tel">
            <span>電話番号: </span>
            <span>{{ item.phone }}</span>
          </div>
          <span class="icon-close" @click.stop="openDelete('address', item.id)"></span>
          <span class="icon-pencil" @click.stop="openEditAddressModal(item)"></span>
        </div>

        <div class="add-card" @click="openAddAddressModal">
          <span class="icon-plus"></span>
          <p>新しいお届け先住所を追加する</p>
        </div>

      </div>

      <div class="payment-methods">
        <h2>お支払方法</h2>
        <div class="card-wrap">
          <p>クレジットカード</p>

          <div class="card-choice-wrap" v-for="item in user.payment_methods" :key="item" :class="{ selected: selectedPayment === item.id }" @click="selectPayment(item.id)">
            <div class="radio-btn"></div>
            <img src="@/assets/icons/card.svg" alt="card">
            <label>{{ item.card_number.replace(/\D/g, "").replace(/(.{4})/g, "$1 ").trim() }}</label>
            <img src="@/assets/icons/delete.svg" alt="delete" @click.stop="openDelete('payment', item.id)">
            <img src="@/assets/icons/pencil.svg" alt="edit" @click.stop="openEditPaymentModal(item)">
          </div>
          <a class="add-card" @click="openAddPaymentModal">
            クレジットカードまたはデビットカードを追加する
          </a>
        </div>
        <div class="card-wrap">
          <p>その他</p>
          <div class="pay-choice-wrap" :class="{ selected: selectedPayment === 'CASH' }" @click="selectPayment('CASH')">
            <div class="radio-btn"></div>
            <label>現金</label>
          </div>

          <div class="pay-choice-wrap" :class="{ selected: selectedPayment === 'KONBINI' }" @click="selectPayment('KONBINI')">
            <div class="radio-btn"></div>
            <label>コンビニ払い</label>
          </div>

        </div>

      </div>

      <button :class="['button', { loading: lock_send_order }]" @click="saveAction" style="width: 100%;" :disabled="lock_send_order">
        <span v-if="!lock_send_order">注文を確定する</span>
        <span v-else>送信中...</span>
      </button>

    </div>
    <CustomModal v-model="showAddressModal" :title="isEditing ? '配達変更' : '配達追加'">
      <div class="modal-content">
        <CustomInput v-model="currentAddress.name" labelText="名前" placeholderText="名前入力" />
        <CustomInput v-model="currentAddress.address" labelText="住所" placeholderText="住所入力" />
        <CustomInput v-model="currentAddress.phone" labelText="電話番号" placeholderText="電話番号入力" />
      </div>
      <div class="modal-actions">
        <button class="button button-plain" @click="closeModal">戻る</button>
        <button class="button" @click="saveAddress">保存</button>
      </div>
    </CustomModal>

    <!-- Payment Modal -->
    <CustomModal v-model="showPaymentModal" :title="isEditingPayment ? '支払い方法の編集' : '支払い方法の追加'">
      <div class="modal-content">
        <CustomInput v-model="currentPaymentMethod.card_number" labelText="カード番号" placeholderText="1234 5678 9012 3456" type="credit-card" />

        <div class="double">
          <CustomInput labelText="Exp date(2024年01月⇒2401)" placeholderText="2401" type="number" />
          <CustomInput labelText="CVV" placeholderText="023" type="number" />
        </div>

      </div>
      <div class="modal-actions">
        <button class="button button-plain" @click="closePaymentModal">戻る</button>
        <button class="button" @click="savePaymentMethod">保存</button>
      </div>
    </CustomModal>

    <CustomModal class="delete" v-model="deleteModalFlag" :title="deleteType == 'address' ? '配達削除' : '支払い方法削除'">
      <div class="delete-container">
        <button class="button danger" @click="deleteAction">削除</button>
        <button class="button" @click="deleteModalFlag = false;">戻る</button>
      </div>
    </CustomModal>
  </div>
</template>
<!-- eslint-disable -->
<script>
import { defineComponent, inject, onBeforeMount, ref, computed, onMounted } from "vue";
import axios from "axios";
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toast-notification';


export default defineComponent({
  name: "CheckOutViewView",
  setup() {

    const toast = useToast();

    const { user } = inject("auth");
    const { cart } = inject("cart");

    const router = useRouter();
    const lock_send_order = ref(false);

    const deleteModalFlag = ref(false);
    const deleteType = ref('');
    const deleteId = ref('');

    function deleteAction() {
      switch (deleteType.value) {
        case 'address':
          deleteAddress(deleteId.value);
          break;
        case 'payment':
          deletePaymentMethod(deleteId.value);
          break;

        default:
          break;
      }
      deleteModalFlag.value = false;
    }

    function openDelete(type, id) {
      deleteType.value = type;
      deleteId.value = id;
      deleteModalFlag.value = true;
    }

    const selectedAddress = ref(user.value.addresses[0]?.id != undefined ? user.value.addresses[0].id : null); // Индекс выбранного адреса
    const selectedPayment = ref(user.value.payment_methods[0]?.id != undefined ? user.value.payment_methods[0].id : 'CASH'); // Выбранный метод оплаты



    // Для AddressModal
    const showAddressModal = ref(false);
    const isEditing = ref(false);
    const currentAddress = ref({ name: "", address: "", phone: "" });

    const openAddAddressModal = () => {
      currentAddress.value = { name: "", address: "", phone: "" };
      isEditing.value = false;
      showAddressModal.value = true;
    };

    const openEditAddressModal = (address) => {
      currentAddress.value = { ...address };
      isEditing.value = true;
      showAddressModal.value = true;
    };

    const closeAddressModal = () => {
      showAddressModal.value = false;
    };

    const saveAddressHandler = (savedAddress) => {
      if (isEditing.value) {
        const index = user.value.addresses.findIndex((addr) => addr.id === savedAddress.id);
        if (index !== -1) user.value.addresses[index] = savedAddress;
      } else {
        user.value.addresses.push(savedAddress);
      }
      showAddressModal.value = false;
    };

    const deleteAddress = async (id) => {
      try {
        const response = await axios.get(
          `${process.env.VUE_APP_BACKEND_URL}/backend/client.php?action=delete_address&address_id=${id}`,
          { withCredentials: true }
        );
        console.log('here del', response);
        user.value.addresses = user.value.addresses.filter((address) => address.id !== id);
      } catch (error) {
        console.error("Ошибка при удалении адреса:", error);
      }
    };


    // Сохранение адреса
    const saveAddress = async () => {
      if (!currentAddress.value.name.trim() || !currentAddress.value.address.trim() || !currentAddress.value.phone.trim()) {
        toast.error("すべてのフィールドを正しく入力してください"); // "Пожалуйста, заполните все поля."
        return;
      }
      try {
        if (isEditing.value) {
          // Редактирование существующего адреса
          const response = await axios.post(
            `${process.env.VUE_APP_BACKEND_URL}/backend/client.php?action=edit_address&address_id=${currentAddress.value.id}`,
            {
              name: currentAddress.value.name,
              address: currentAddress.value.address,
              phone: currentAddress.value.phone
            },
            { withCredentials: true }
          );
          console.log(response);
          // Обновить локальные данные
          const index = user.value.addresses.findIndex(
            (method) => method.id === currentAddress.value.id
          );
          if (index !== -1) {
            user.value.addresses[index] = currentAddress.value;
          }

        } else {
          // Добавление нового адреса
          const response = await axios.post(
            `${process.env.VUE_APP_BACKEND_URL}/backend/client.php?action=add_address`,
            {
              address_name: currentAddress.value.name,
              address: currentAddress.value.address,
              phone: currentAddress.value.phone
            },
            { withCredentials: true }
          );
          console.log(response);
          user.value.addresses.push({ ...currentAddress.value, id: response.data.id });
        }

      } catch (error) {
        console.error('Ошибка при сохранении адреса:', error);
      }
      finally {
        showAddressModal.value = false;
      }
    };

    // 支払方法
    const showPaymentModal = ref(false);
    const isEditingPayment = ref(false);
    const currentPaymentMethod = ref({ card_number: "" });

    const openAddPaymentModal = () => {
      currentPaymentMethod.value = { card_number: "" };
      isEditingPayment.value = false;
      showPaymentModal.value = true;
    };

    const openEditPaymentModal = (paymentMethod) => {
      currentPaymentMethod.value = { ...paymentMethod };
      isEditingPayment.value = true;
      showPaymentModal.value = true;
    };

    const closePaymentModal = () => {
      showPaymentModal.value = false;
    };

    const savePaymentMethod = async () => {

      if (!currentPaymentMethod.value.card_number.trim()) {
        toast.error("カード番号を正しく入力してください"); // "Пожалуйста, введите корректный номер карты."
        return;
      }
      try {
        if (isEditingPayment.value) {
          // Редактирование существующего метода оплаты
          const response = await axios.post(
            `${process.env.VUE_APP_BACKEND_URL}/backend/client.php?action=edit_payment_method&payment_method_id=${currentPaymentMethod.value.id}`,
            {
              card_number: currentPaymentMethod.value.card_number,
            },
            { withCredentials: true }
          );
          console.log(response);
          // Обновить локальные данные
          const index = user.value.payment_methods.findIndex(
            (method) => method.id === currentPaymentMethod.value.id
          );
          if (index !== -1) {
            user.value.payment_methods[index] = currentPaymentMethod.value;
          }
        } else {
          // Добавление нового метода оплаты
          const response = await axios.post(
            `${process.env.VUE_APP_BACKEND_URL}/backend/client.php?action=add_payment_method`,
            {
              card_number: currentPaymentMethod.value.card_number,
            },
            { withCredentials: true }
          );
          console.log(response);
          user.value.payment_methods.push({
            ...currentPaymentMethod.value,
            id: response.data.id,
          });
          selectedPayment.value = response.data.id;
        }
      } catch (error) {
        console.error("Ошибка при сохранении метода оплаты:", error);
      } finally {
        showPaymentModal.value = false;
      }
    };

    const deletePaymentMethod = async (id) => {
      try {
        await axios.post(
          `${process.env.VUE_APP_BACKEND_URL}/backend/client.php?action=delete_payment_method&payment_method_id=${id}`,
          {},
          { withCredentials: true }
        );
        user.value.payment_methods = user.value.payment_methods.filter((method) => method.id !== id);

        selectedPayment.value = user.value.payment_methods[0]?.id != undefined ? user.value.payment_methods[0].id : 'CASH';
      } catch (error) {
        console.error("Ошибка при удалении метода оплаты:", error);
      }
    };


    if (cart.value.length === 0) router.push('/cart');

    const selectAddress = (index) => {
      selectedAddress.value = index;
    };

    const selectPayment = (payment) => {
      selectedPayment.value = payment;
    };



    const saveAction = async () => {
      let order_id = null;
      if (selectedAddress.value == null || selectedPayment.value == null) {
        toast.error("支払い方法または住所がありません");
      } else {

        try {
          if (lock_send_order.value) return;
          lock_send_order.value = true;

          const response = await axios.post(
            process.env.VUE_APP_BACKEND_URL + '/backend/orders.php?action=create_order',
            {
              cart: cart.value,
              address_id: selectedAddress.value,
              payment_method_id: selectedPayment.value,
            },
            { withCredentials: true }
          );

          if (response.data.status !== "success") {
            console.error("Ошибка при сохранении заказа2:", response.data.message);
            return;
          } else {
            console.log(response.data);
            cart.value = [];
            order_id = response.data.order_id;
          }
        } catch (error) {
          console.error("Ошибка при сохранении заказа1:", error);
        } finally {
          lock_send_order.value = false;
          router.push('/accepted/' + order_id);
        }
      }
    };

    return {
      user,
      cart,
      saveAction,
      lock_send_order,
      selectedAddress,
      selectedPayment,
      selectAddress,
      selectPayment,
      showAddressModal,
      isEditing,
      currentAddress,
      openAddAddressModal,
      openEditAddressModal,
      closeAddressModal,
      saveAddressHandler,
      deleteAddress,
      saveAddress,
      showPaymentModal,
      isEditingPayment,
      currentPaymentMethod,
      openAddPaymentModal,
      openEditPaymentModal,
      closePaymentModal,
      savePaymentMethod,
      deletePaymentMethod,
      deleteModalFlag,
      deleteType,
      deleteAction,
      openDelete,
      deleteId
    };
  },
});
</script>

<style lang="scss" scoped>
.checkout-view {
  @include is-mobile() {
    padding: 24px;
  }
}

h2 {
  font-weight: 600;
  font-size: 24px;
  text-align: left;
}

.checkout-content {
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  padding: 24px;

  @include is-mobile() {
    border: none;
    padding: 0px;
  }
}


.address-wrap {
  display: grid;
  grid-template-columns: 1fr 1fr;

  gap: 20px;

  @include is-mobile() {
    grid-template-columns: 1fr;
  }


  .address-card {
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    position: relative;
    padding: 32px;
    box-sizing: border-box;
    width: 100%;

    cursor: pointer;

    &.selected {
      border: 1px solid #1e1e1e;
    }

    .icon-selected {
      width: min-content;
      position: absolute;
      top: 10px;
      left: 10px;
      cursor: pointer;

      &::before {
        content: url(../assets/icons/check-cycle.svg);
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

    .icon-close {
      width: min-content;
      position: absolute;
      top: 0;
      right: 0;
      top: 10px;
      right: 10px;
      cursor: pointer;

      &::before {
        content: url(../assets/icons/delete.svg);
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

.payment-methods {


  .radio-btn {
    width: 16px;
    height: 16px;
    border: 2px solid #d9d9d9;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
    position: relative;

    &.selected {
      background-color: #000;
    }
  }

  .card-choice-wrap,
  .pay-choice-wrap {
    display: flex;
    align-items: center;
    cursor: pointer;
    margin-bottom: 12px;
    gap: 5px;

    &.selected .radio-btn {
      background-color: #000;
      /* Цвет выбранного элемента */
    }

    img {
      width: 20px;
    }

    label {
      cursor: pointer;
    }

  }

  .add-card {
    padding-left: 30px;
    position: relative;
    cursor: pointer;

    &::before {
      content: url(../assets/icons/plus.svg);
      display: block;
      position: absolute;
      transform: scale(0.5);
      bottom: -10px;
    }

  }
}

.button {
  position: relative;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  font-size: 16px;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;

  &.loading {
    cursor: not-allowed;
    background-color: #ccc;

    &:after {
      content: '';
      position: absolute;
      border: 3px solid transparent;
      border-top: 3px solid white;
      border-radius: 50%;
      width: 16px;
      height: 16px;
      animation: spin 1s linear infinite;
    }
  }
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

.modal-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 20px;

  .double {
    display: flex;
    gap: 16px;
  }
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 20px 0;
}

.modal.delete {
  z-index: 110;

  .delete-container {
    margin-top: 20px;
    display: flex;
    flex-direction: row-reverse;
    gap: 12px;
  }
}
</style>