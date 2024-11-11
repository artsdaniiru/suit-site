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
                    <div class="address" v-for="item in data.client_addresses" :key="item">
                        <img class="delete" src="@/assets/icons/delete-admin.svg" alt="close">
                        <span class="name">{{ item.name }}</span>
                        <span class="address-full">{{ item.address }}</span>
                        <span class="phone"><strong>電話番号：</strong> {{ item.phone }}</span>
                        <img class="edit" src="@/assets/icons/pencil.svg" alt="edit">
                    </div>
                </div>
            </div>

            <div class="side">
                <div class="payment-methods" v-if="data.client_payment_methods.length != 0">
                    <span class="label">お支払方法</span>
                    <div class="payment-method" v-for="item in data.client_payment_methods" :key="item">
                        <img class="card" src="@/assets/icons/card.svg" alt="card">
                        <span class="number">{{ item.card_number }}</span>
                        <img class="delete" src="@/assets/icons/delete-admin.svg" alt="delete">
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
        //main data
        const data_original = ref({ client: [], client_addresses: [], client_payment_methods: [], client_orders: [] });
        const data = ref({ client: [], client_addresses: [], client_payment_methods: [], client_orders: [] });

        // Функция для глубокого сравнения двух объектов или массивов
        const deepEqual = (obj1, obj2) => {
            return JSON.stringify(obj1) === JSON.stringify(obj2);
        };

        // computed для сравнения data и data_original
        const areDataEqual = ref(true);

        // Watch для глубокого отслеживания изменений
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

        // Следим за изменениями modelValue
        watch([deleteModalFlag.value, closeFlagOrder.value], () => {
            document.body.classList.add('no-scroll');
        });


        function openOrder(id) {
            order_id.value = id;
            closeFlagOrder.value = true;
        }


        // Метод для удаления товара
        const deleteAction = async () => {
            try {
                const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/admin/products.php?action=delete_product&client_id=' + data.value.product.id, {
                    withCredentials: true
                });

                if (response.data.status == "success") {
                    emit("productDelete");
                } else {
                    console.error("Ошибка при удалении товара:", response.data.status);
                }

            } catch (error) {
                console.error("Ошибка при удалении товара:", error);
            }
        };

        // Метод для получения товара
        const fetchAction = async () => {
            try {
                const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/admin/clients.php?action=get_client&client_id=' + props.client_id, {
                    withCredentials: true
                });
                console.log(response.data);

                if (response.data.status == "success") {

                    let raw_data = response.data.data;

                    data.value = raw_data;
                    data_original.value = JSON.parse(JSON.stringify(raw_data));

                } else {
                    console.error("Ошибка при получении товара:", response.data.status);
                }

            } catch (error) {
                console.error("Ошибка при получении товара:", error);
            }
        };

        const saveAction = async () => {
            try {


                const productResponse = await axios.post(
                    process.env.VUE_APP_BACKEND_URL + '/backend/admin/products.php?action=edit_product&client_id=' + data.value.product.id,
                    {
                        data: data.value,
                        data_original: data_original.value
                    },
                    { withCredentials: true }
                );


                if (productResponse.data.status !== "success") {
                    console.error("Ошибка при сохранении продукта:", productResponse.data.message);
                    return;
                } else {
                    setTimeout(() => {
                        emit("productUpdate");
                        fetchAction();
                    }, 200);

                }
            } catch (error) {
                console.error("Ошибка при сохранении продукта:", error);
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
            openOrder
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
</style>