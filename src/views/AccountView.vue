<template>

  <div class="account">

    <h2>マイページ</h2>

    <div class="tabs">
      <span class="tab" :class="{ active: tab == 'info' }" @click="switchTab('info')">個人情報</span>
      <span class="tab" :class="{ active: tab == 'orders' }" @click="switchTab('orders')">オーダー歴史</span>
    </div>

    <div class="account-view" v-if="tab == 'info'">
      <div>
        <div class="account-inf">
          <h2>個人情報</h2>
          <div class="main">
            <CustomInput v-model="userValue.name" :labelText="'氏名'" placeholderText="例）山田 太郎" type="text" />
            <CustomInput v-model="userValue.email" :labelText="'メール'" placeholderText="例）サイトーズ@mail.jp" type="text" />
          </div>
          <div class="sizes">
            <CustomInput v-model="body_sizes.height" :labelText="'身長'" placeholderText="175cm" type="number" />
            <CustomInput v-model="body_sizes.shoulder_width" :labelText="'肩幅'" placeholderText="40cm" type="number" />
            <CustomInput v-model="body_sizes.waist_size" :labelText="'ウェストサイズ'" placeholderText="70cm" type="number" />
          </div>

          <button class="button" @click="saveUserData">保存</button>

        </div>

        <div class="password-change">
          <h2>パスワード変更</h2>

          <CustomInput v-model="password.current" :labelText="'現在のパスワード'" type="password" />
          <CustomInput v-model="password.new" :labelText="'新しいパスワード'" type="password" />
          <CustomInput v-model="password.confirm" :labelText="'新しいパスワード（確認）'" type="password" />

          <button class="button" @click="updatePassword">保存</button>
        </div>

      </div>


      <div>
        <div class="payment-methods">
          <h2>お支払方法</h2>
          <div class="card-wrap">
            <p>クレジットカード</p>

            <div class="card-choice-wrap" v-for="item in user.payment_methods" :key="item">
              <img src="@/assets/icons/card.svg" alt="card">
              <label>{{ item.card_number.replace(/\D/g, "").replace(/(.{4})/g, "$1 ").trim() }}</label>
              <img src="@/assets/icons/delete.svg" alt="delete" @click.stop="openDelete('payment', item.id)">
              <img src="@/assets/icons/pencil.svg" alt="edit" @click.stop="openEditPaymentModal(item)">
            </div>
            <a class="add-card" @click="openAddPaymentModal">
              クレジットカードまたはデビットカードを追加する
            </a>
          </div>

        </div>
        <div class="account-address">
          <h2>配達</h2>

          <div class="address-card" v-for="item in user.addresses" :key="item">
            <h3>{{ item.name }}</h3>
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
      </div>


    </div>

    <div class="account-orders" v-if="tab == 'orders'">
      <div class="order-list">
        <div class="order-elem" v-for="order in orders" :key="order">
          <img :src="order.cart[0]?.image_path != undefined ? order.cart[0]?.image_path : 'Image.png'" alt="img">
          <div class="order-info">
            <h3>オーダー番号: {{ "#" + order.id.toString().padStart(5, '0') }}</h3>
            <span><strong>合計: </strong>{{ priceFormatter(order.total_price) }}</span>
          </div>
          <div class="order-status">
            <span>配達中</span>
            <button class="button">詳細</button>
          </div>
        </div>
      </div>
    </div>

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

</template>
<!-- eslint-disable -->
<script>
import axios from "axios";
import { defineComponent, ref, inject, watch, onMounted, computed } from "vue";
import { useToast } from "vue-toast-notification";
import { useRouter } from 'vue-router'

export default defineComponent({
  name: "AccountView",
  setup() {

    const router = useRouter();
    // Set initial tab based on route or defaultTab prop
    const tab = ref('info');

    // Method to switch tabs and navigate
    const switchTab = (selectedTab) => {
      tab.value = selectedTab;
    };

    const toast = useToast();
    const { user, isUserLoggedIn } = inject("auth");

    if (!isUserLoggedIn.value) router.push('/');

    const userValue = ref({
      name: user.value.name,
      email: user.value.email,
      password: user.value.password,
    });


    // Размеры тела
    const body_sizes = ref({
      height: user.value.height,
      shoulder_width: user.value.shoulder_width,
      waist_size: user.value.waist_size,
    });


    const saveUserData = async () => {
      try {
        const response = await axios.post(
          `${process.env.VUE_APP_BACKEND_URL}/backend/client.php?action=edit_client`,
          {
            name: userValue.value.name,
            email: userValue.value.email,
            height: body_sizes.value.height,
            shoulder_width: body_sizes.value.shoulder_width,
            waist_size: body_sizes.value.waist_size,
          },
          { withCredentials: true }
        );
        console.log(response);
        if (response.data.status == 'success') {
          toast.success("保存されました");
        } else {
          console.error('Ошибка при сохранении пользователя:', response.data.message);
        }


      } catch (error) {
        console.error('Ошибка при сохранении пользователя:', error);
      }

    }

    const password = ref({
      current: "",
      new: "",
      confirm: "",
    })

    function checkPasswordsEqual() {
      if (password.value.new !== password.value.confirm) {
        return false;
      }
      return true;
    }

    const updatePassword = async () => {
      if (checkPasswordsEqual()) {
        try {
          const response = await axios.post(
            `${process.env.VUE_APP_BACKEND_URL}/backend/client.php?action=edit_password`,
            {
              password: password.value.confirm,
            },
            { withCredentials: true }
          );
          console.log(response);
          if (response.data.status == 'success') {
            toast.success("保存されました");
          } else {
            console.error('Ошибка при сохранении пользователя:', response.data.message);
          }


        } catch (error) {
          console.error('Ошибка при сохранении пользователя:', error);
        }
      } else {
        toast.error("パスワードが同じではないです");
      }

    }

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
        alert("すべてのフィールドを正しく入力してください"); // "Пожалуйста, заполните все поля."
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
        alert("カード番号を正しく入力してください"); // "Пожалуйста, введите корректный номер карты."
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
      } catch (error) {
        console.error("Ошибка при удалении метода оплаты:", error);
      }
    };

    const orders = ref([]);

    // Метод для получения товара
    const fetchOrders = async () => {
      try {
        const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/client.php?action=get_client_orders', {
          withCredentials: true
        });

        console.log(response);


        if (response.data.status == "success") {
          orders.value = response.data.data;
        } else {
          console.error("Ошибка при получении товара:", response.data.status);
        }

      } catch (error) {
        console.error("Ошибка при получении товара:", error);
      }
    };

    function priceFormatter(price) {
      return `¥${Number(price).toLocaleString('ja-JP')}`
    }

    onMounted(() => {
      fetchOrders();
    })

    return {
      user,
      userValue,
      toast,
      body_sizes,
      currentAddress,
      showAddressModal,
      isEditing,
      currentPaymentMethod,
      showPaymentModal,
      isEditingPayment,
      deleteModalFlag,
      deleteType,
      deleteId,
      openDelete,
      openAddAddressModal,
      openEditAddressModal,
      closeAddressModal,
      saveAddressHandler,
      saveAddress,
      openAddPaymentModal,
      openEditPaymentModal,
      closePaymentModal,
      savePaymentMethod,
      deleteAction,

      tab,
      switchTab,
      orders,

      priceFormatter,


      saveUserData,
      password,
      updatePassword

    };
  },
});
</script>


<style lang="scss" scoped>
.account {
  @include respond-to('md') {
    padding: 24px;
  }
}

h2 {
  font-weight: 600;
  font-size: 24px;
  text-align: left;
}

.tabs {
  display: flex;

  .tab {
    font-weight: 400;
    font-size: 16px;
    line-height: 140%;
    color: #767676;

    padding: 4px 12px;
    border-bottom: 1px solid #b2b2b2;
    cursor: pointer;

    margin-bottom: 48px;

    &.active {
      color: #303030;
      border-bottom: 1px solid #303030;
    }
  }
}

.account-view {
  display: grid;
  grid-template-columns: 50% 1fr;

  gap: 20px;
  margin-bottom: 64px;

  @include respond-to('md') {
    grid-template-columns: 1fr;
  }



  .account-inf {
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    width: -webkit-fill-available;
    height: min-content;


    .main,
    .sizes {
      display: flex;
      gap: 24px;

      flex-direction: column;
    }

    .sizes {
      @include respond-to('md') {
        display: grid;
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
    }



  }

  .password-change {
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    width: -webkit-fill-available;
    height: min-content;
    margin-top: 20px;

  }

  .payment-methods {

    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 24px;

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

  .account-address {

    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 24px;
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 20px;

    .address-card {
      border: 1px solid #d9d9d9;
      border-radius: 8px;
      position: relative;
      box-sizing: border-box;
      width: -webkit-fill-available;
      padding: 32px;

      @include respond-to('md') {
        padding: 24px;

        p {
          width: -webkit-fill-available;
          word-break: break-all;

        }
      }

      h3 {
        margin-top: 0px;
        margin-bottom: 12px;
        font-size: 18px;
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
      width: -webkit-fill-available;
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

}


.account-orders {
  border-radius: 8px;
  padding: 24px;
  width: -webkit-fill-available;

  .order-list {
    display: flex;
    flex-direction: column;
    gap: 20px;

    .order-elem {
      border: 1px solid #d9d9d9;
      border-radius: 8px;
      display: flex;
      gap: 24px;
      padding: 24px;

      h3 {
        margin-top: 0px;
      }

      img {
        width: 100px;
        height: 100px;
        object-fit: cover;
      }

      .order-info {
        display: flex;
        flex-direction: column;
        justify-content: space-between;

      }

      .order-status {
        margin-left: auto;
        margin-right: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;

        span {
          font-size: 16px;
          font-weight: 600;
          padding: 4px 8px;
          color: #f5f5f5;
          background: #2c2c2c;
          border-radius: 8px;
          text-align: center;
        }
      }
    }
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