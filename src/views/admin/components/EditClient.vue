<template>
    <div class="edit-form">
        <div class="grid-selection">
            <div class="side">
                <div class="client">
                    <span class="label">個人情報</span>
                    <div class="elems">
                        <template v-for="(item, key) in client_headers" :key="item">
                            <CustomInput v-model="data.client[key]" :labelText="item" :placeholderText="item" />
                        </template>
                    </div>
                </div>
                <div class="addresses">
                    <span class="label">配達</span>
                    <div class="address" v-for="(item, index) in data.client_addresses" :key="item">
                        <img class="delete" src="@/assets/icons/delete-admin.svg" alt="close" @click="deleteAddress(index)">
                        <span class="name">{{ item.name }}</span>
                        <span class="address-full">{{ item.address }}</span>
                        <span class="phone"><strong>電話番号：</strong> {{ item.phone }}</span>
                        <img class="edit" src="@/assets/icons/pencil.svg" alt="edit" @click="openEditAddressModal(index)">
                    </div>
                    <div class="add-address-button" @click="openAddAddressModal">
                        <span class="plus-sign">+</span>
                        <span class="add-text">新しいお届け先住所を追加する</span>
                    </div>
                </div>
            </div>
            <div class="side">
                <div class="payment-methods" v-if="data.client_payment_methods.length != 0">
                    <span class="label">お支払方法</span>
                    <div class="payment-method" v-for="(item, index) in data.client_payment_methods" :key="item">
                        <img class="card" src="@/assets/icons/card.svg" alt="card">
                        <span class="number">{{ item.card_number }}</span>
                        <img class="delete" src="@/assets/icons/delete-admin.svg" alt="delete" @click="deletePaymentMethod(index)">
                    </div>
                </div>
                <div class="orders" v-if="data.client_orders.length != 0">
                    <span class="label">注文</span>
                    <div class="order" v-for="item in data.client_orders" :key="item" @click="openOrder(item.id)">
                        <span class="name">{{ "#" + item.id.toString().padStart(5, '0') }}</span>
                        <span class="address-full">{{ item.client_address }}</span>
                        <span class="phone">電話番号: {{ item.client_phone }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="actions">
            <div class="dates">
                <div class="date">
                    <span>登録日：</span>
                    <span>{{ data.client.date_of_registration }}</span>
                </div>
            </div>
            <div class="buttons">
                <button class="button danger" @click="deleteModalFlag = true">削除</button>
                <button class="button" :disabled="areDataEqual" @click="saveAction">保存</button>
            </div>
        </div>
    </div>
    <CustomModal class="delete" v-model="deleteModalFlag" :title="'商品削除'" :in_modal="true">
        <div class="delete-container">
            <button class="button danger" @click="deleteAction">削除</button>
            <button class="button" @click="deleteModalFlag = false;">戻る</button>
        </div>
    </CustomModal>
    <CustomModal v-model="closeFlagOrder" :title="'注文内容変更'" :in_modal="true">
        <EditOrder :order_id="order_id" />
    </CustomModal>
    <CustomModal v-model="addressModalFlag" :title="editingIndex !== null ? '配達変更' : '配達追加'" :in_modal="true">
        <div class="modal-content">
            <CustomInput v-model="addressModalData.name" labelText="名前" placeholderText="名前入力" />
            <CustomInput v-model="addressModalData.address" labelText="住所" placeholderText="住所入力" />
            <CustomInput v-model="addressModalData.phone" labelText="電話番号" placeholderText="電話番号入力" />
        </div>
        <div class="modal-actions">
            <button class="button button-plain" @click="addressModalFlag = false">戻る</button>
            <button class="button" @click="saveAddress">保存</button>
        </div>
    </CustomModal>


</template>

<script>
import axios from "axios";
import { defineComponent, ref, onMounted, watch } from "vue";
import EditOrder from "./EditOrder.vue";

export default defineComponent({
    name: "EditClient",
    components: {
        EditOrder
    },
    props: {
        client_id: {
            type: Number,
            required: true
        }
    },
    setup(props, { emit }) {
        const data_original = ref({ client: [], client_addresses: [], client_payment_methods: [], client_orders: [] });
        const data = ref({ client: [], client_addresses: [], client_payment_methods: [], client_orders: [] });

        const deepEqual = (obj1, obj2) => {
            return JSON.stringify(obj1) === JSON.stringify(obj2);
        };

        const areDataEqual = ref(true);
        watch(data, () => {
            areDataEqual.value = deepEqual(data.value, data_original.value);
        }, { deep: true });

        const client_headers = ref({
            name: '名前',
            login: "ログイン",
            email: "メールアドレス",
            height: "身長",
            shoulder_width: "肩幅",
            waist_size: "ウェストサイズ",
        });

        const deleteModalFlag = ref(false);
        const closeFlagOrder = ref(false);
        const order_id = ref(null);

        watch([deleteModalFlag.value, closeFlagOrder.value], () => {
            document.body.classList.add('no-scroll');
        });

        function openOrder(id) {
            order_id.value = id;
            closeFlagOrder.value = true;
        }

        const addressModalFlag = ref(false); // Controls the modal visibility
        const addressModalData = ref({ name: '', address: '', phone: '' }); // Stores address data for the modal
        const editingIndex = ref(null); // Keeps track of whether we're editing or adding

        // Method to open the modal for editing
        const openEditAddressModal = (index) => {
            addressModalData.value = { ...data.value.client_addresses[index] };
            editingIndex.value = index;
            addressModalFlag.value = true;
        };

        // Method to open the modal for adding a new address
        const openAddAddressModal = () => {
            addressModalData.value = { name: '', address: '', phone: '' };
            editingIndex.value = null;
            addressModalFlag.value = true;
        };

        // Method to save the address (either editing an existing one or adding a new one)
        const saveAddress = () => {
            if (editingIndex.value !== null) {
                // Update existing address
                data.value.client_addresses[editingIndex.value] = { ...addressModalData.value };
            } else {
                // Add new address
                data.value.client_addresses.push({ ...addressModalData.value });
            }
            addressModalFlag.value = false; // Close the modal
        };
        const deleteAddress = (index) => {
            data.value.client_addresses.splice(index, 1);
        };

        const deletePaymentMethod = (index) => {
            data.value.client_payment_methods.splice(index, 1);
        };

        const deleteAction = async () => {
            try {
                const response = await axios.get(
                    process.env.VUE_APP_BACKEND_URL + '/backend/admin/clients.php?action=delete_client&client_id=' + props.client_id,
                    { withCredentials: true }
                );

                if (response.data.status == "success") {
                    emit("clientDelete");
                } else {
                    console.error("Ошибка при удалении клиента:", response.data.status);
                }
            } catch (error) {
                console.error("Ошибка при удалении клиента:", error);
            }
        };

        const fetchAction = async () => {
            try {
                const response = await axios.get(
                    process.env.VUE_APP_BACKEND_URL + '/backend/admin/clients.php?action=get_client&client_id=' + props.client_id,
                    { withCredentials: true }
                );

                if (response.data.status == "success") {
                    let raw_data = response.data.data;
                    data.value = raw_data;
                    data_original.value = JSON.parse(JSON.stringify(raw_data));
                } else {
                    console.error("Ошибка при получении клиента:", response.data.status);
                }
            } catch (error) {
                console.error("Ошибка при получении клиента:", error);
            }
        };

        const saveAction = async () => {
            try {
                const response = await axios.post(
                    process.env.VUE_APP_BACKEND_URL + '/backend/admin/clients.php?action=edit_client',
                    {
                        data: data.value,
                        data_original: data_original.value
                    },
                    { withCredentials: true }
                );

                if (response.data.status !== "success") {
                    console.error("Ошибка при сохранении клиента:", response.data.message);
                    return;
                } else {
                    setTimeout(() => {
                        emit("clientUpdate");
                        fetchAction();
                    }, 200);
                }
            } catch (error) {
                console.error("Ошибка при сохранении клиента:", error);
            }
        };

        onMounted(() => {
            fetchAction();
        });

        return {
            data,
            client_headers,
            areDataEqual,
            saveAction,
            deleteAction,
            deleteModalFlag,
            closeFlagOrder,
            order_id,
            openOrder,
            addressModalFlag,
            addressModalData,
            editingIndex,
            openEditAddressModal,
            openAddAddressModal,
            saveAddress,
            deleteAddress,
            deletePaymentMethod
        };
    }
});
</script>

<style lang="scss" scoped>
.edit-form {
    display: flex;
    flex-direction: column;
    gap: 24px;

    .label {
        font-weight: 600;
        font-size: 18px;
        line-height: 120%;
        letter-spacing: -0.02em;
    }

    .grid-selection {
        padding-left: 10px;
        padding-right: 10px;
        width: 1100px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;

        .side {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .client {
            display: flex;
            flex-direction: column;
            gap: 12px;

            .elems {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
        }

        .addresses {
            display: flex;
            flex-direction: column;
            gap: 12px;

            .address {
                display: flex;
                flex-direction: column;
                gap: 4px;
                border: 1px solid #d9d9d9;
                border-radius: 8px;
                padding: 12px;
                position: relative;
                font-weight: 400;
                font-size: 12px;
                line-height: 140%;

                .name {
                    font-weight: 600;
                    font-size: 16px;
                    line-height: 120%;
                }

                .delete {
                    position: absolute;
                    top: 12px;
                    right: 12px;
                    width: 16px;
                    height: 16px;
                    cursor: pointer;
                }

                .edit {
                    position: absolute;
                    bottom: 12px;
                    right: 12px;
                    width: 16px;
                    height: 16px;
                    cursor: pointer;
                }
            }

            .add-address-button {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 20px;
                border: 2px dashed #d9d9d9;
                border-radius: 8px;
                color: #a0a0a0;
                font-weight: 500;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s ease;
                text-align: center;
                gap: 8px; // Space between the plus sign and text

                &:hover {
                    background-color: #f5f5f5;
                }

                .plus-sign {
                    font-size: 24px;
                    line-height: 1;
                }

                .add-text {
                    font-size: 14px;
                    line-height: 1.2;
                }
            }
        }

        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 12px;

            .payment-method {
                display: flex;
                gap: 8px;
                border: 1px solid #d9d9d9;
                border-radius: 8px;
                padding: 12px;
                position: relative;
                font-weight: 400;
                font-size: 16px;
                line-height: 140%;
                align-items: center;

                .card {
                    width: 16px;
                }

                .delete {
                    margin-left: auto;
                }

                .delete {
                    width: 16px;
                    height: 16px;
                    cursor: pointer;
                }
            }
        }

        .orders {
            display: flex;
            flex-direction: column;
            gap: 12px;

            .order {
                display: flex;
                flex-direction: column;
                gap: 4px;
                border: 1px solid #d9d9d9;
                border-radius: 8px;
                padding: 12px;
                position: relative;
                font-weight: 400;
                font-size: 12px;
                line-height: 140%;
                cursor: pointer;
                transition: background 0.3s ease;

                &:hover {
                    background: #f5f5f5;
                }

                .name {
                    font-weight: 600;
                    font-size: 16px;
                    line-height: 120%;
                }

                .delete {
                    position: absolute;
                    top: 12px;
                    right: 12px;
                    width: 16px;
                    height: 16px;
                    cursor: pointer;
                }

                .edit {
                    position: absolute;
                    bottom: 12px;
                    right: 12px;
                    width: 16px;
                    height: 16px;
                    cursor: pointer;
                }
            }
        }
    }

    .actions {
        display: flex;
        gap: 12px;
        justify-content: space-between;
        width: 100%;

        .dates {
            display: flex;
            flex-direction: column;
            color: #ccc;
            font-size: 14px;

            .date {
                align-items: center;
                display: flex;
                gap: 8px;
            }
        }

        .buttons {
            display: flex;
            gap: 12px;
        }
    }
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

.modal-content {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 20px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 20px 0;
}
</style>