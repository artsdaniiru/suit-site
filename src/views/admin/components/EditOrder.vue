<template>

    <div class="edit-form">

        <div class="grid-selection">
            <div class="side">
                <CustomSelect :values="status_field_names" v-model="data.order.status" labelText="状態：" :labelPosition="'side'" width="130px" :notSelect="true" />
                <span class="label">個人情報</span>
                <div class="client">

                    <span class="name">{{ data.order.client_name }}</span>
                    <span><strong>メールアドレス：</strong>{{ data.order.email }}</span>
                    <span><strong>電話番号：</strong>{{ data.order.phone }}</span>
                    <span><strong>身長：</strong>{{ data.order.height }}cm</span>
                    <span><strong>肩幅：</strong>{{ data.order.shoulder_width }}cm</span>
                    <span><strong>ウェストサイズ：</strong>{{ data.order.waist_size }}cm</span>
                    <div class="payment-method">
                        <img class="card" src="@/assets/icons/card.svg" alt="card">
                        <span class="number">{{ String(data.order.card_number).replace(/\D/g, "").replace(/(.{4})/g, "$1 ").trim() }}</span>
                    </div>
                </div>
            </div>

            <div class="side">
                <span class="label">配達</span>
                <div class="address">
                    <span class="name">{{ data.order.client_name }}</span>
                    <span class="address-full">{{ data.order.address }}</span>
                    <span class="phone"><strong>電話番号：</strong> {{ data.order.phone }}</span>
                    <img class="edit" src="@/assets/icons/pencil.svg" alt="edit" @click="openEditAddressModal">
                </div>
            </div>
        </div>
        <div class="order">
            <span class="label">注文内容</span>
            <div class="order-item" v-for="(item, key) in data.products" :key="item">
                <img class="image" :src="item.product.image_path" alt="product">
                <div class="info">
                    <span class="name">{{ item.product.name }}</span>
                    <div class="elem">
                        <span class="name">サイズ</span>
                        <span class="value">{{ item.size.name }} <strong v-if="item.product.type == 'suit'">{{ priceFormatter(item.size.price) }}</strong></span>
                    </div>
                    <div class="elem" v-for="option in item.options" :key="option">
                        <span class="name">{{ options_types[option.type] }}</span>
                        <span class="value">{{ option.name }} <strong>+{{ priceFormatter(option.price) }}</strong></span>
                    </div>
                    <span class="price">{{ priceFormatter(item.product.price) }}¥</span>
                </div>

                <!-- Edit and Delete icons -->
                <img class="edit" src="@/assets/icons/pencil.svg" alt="edit" @click="editProduct(key)">
                <img class="delete" src="@/assets/icons/delete-admin.svg" alt="delete" @click="deleteProduct(key)">
            </div>
        </div>
        <!-- Total Price -->
        <div class="total-price">
            <span>合計金額: </span><strong>{{ priceFormatter(totalOrderPrice) }}¥</strong>
        </div>

        <div class="actions">
            <div class="dates">
                <!-- <div class="date">
                    <span>登録日：</span>
                    <span>{{ data.client.date_of_registration }}</span>
                </div> -->

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
    <CustomModal class="edit-product" v-model="editProductModalFlag" :title="'商品編集'" :in_modal="true">
        <div class="edit-product-modal">
            <div class="form">
                <!-- Product Image -->
                <div class="product-image">
                    <img src="/Image.png" alt="product">
                </div>

                <!-- Product Details Form -->
                <div class="product-details">
                    <CustomInput v-model="productEditData.size" labelText="サイズ" placeholderText="Enter size" />
                    <CustomSelect :values="{ S: 'Small', M: 'Medium', L: 'Large' }" v-model="productEditData.size" :labelText="'サイズ選択'" />
                    <CustomSelect :values="{ wool: 'Wool', cotton: 'Cotton', silk: 'Silk' }" v-model="productEditData.material" :labelText="'生地の種類'" />
                    <CustomSelect :values="{ red: 'Red', blue: 'Blue', black: 'Black' }" v-model="productEditData.color" :labelText="'色選択'" />
                    <CustomSelect :values="{ round: 'Round', square: 'Square', diamond: 'Diamond' }" v-model="productEditData.buttonType" :labelText="'ボタンの種類'" />
                </div>
            </div>


            <!-- Actions -->
            <div class="modal-actions">
                <button class="button" @click="saveProductEdit">保存</button>
                <button class="button button-plain" @click="editProductModalFlag = false">キャンセル</button>
            </div>
        </div>
    </CustomModal>
    <CustomModal v-model="editAddressModalFlag" :title="'住所編集'" :in_modal="true">
        <div class="edit-address-modal">
            <CustomInput v-model="addressEditData.name" labelText="名前" placeholderText="Enter name" />
            <CustomInput v-model="addressEditData.address" labelText="住所" placeholderText="Enter address" />
            <CustomInput v-model="addressEditData.phone" labelText="電話番号" placeholderText="Enter phone number" />

            <!-- Modal Actions -->
            <div class="modal-actions">
                <button class="button" @click="saveAddressEdit">保存</button>
                <button class="button button-plain" @click="editAddressModalFlag = false">キャンセル</button>
            </div>
        </div>
    </CustomModal>


</template>
<script>
import CustomModal from "@/components/CustomModal.vue";
import axios from "axios";
import { defineComponent, ref, onMounted, watch, computed } from "vue";

export default defineComponent({
    name: "EditOrder",
    components: {
        CustomModal,
    },
    props: {
        order_id: {
            type: Number,
            required: true
        }
    },
    setup(props, { emit }) {
        //main data
        const data_original = ref({ order: [], products: [] });
        const data = ref({ order: [], products: [] });

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


        const edit_product_id = ref(null);

        const productEditData = ref({
            size: '',
            material: '',
            color: '',
            buttonType: ''
        });

        function editProduct(id) {
            edit_product_id.value = id;
            editProductModalFlag.value = true;
            // Load product details here if needed, for now, example defaults are set.
            productEditData.value = {
                size: 'M', // Example default value
                material: 'cotton',
                color: 'blue',
                buttonType: 'round'
            };
        }

        function saveProductEdit() {
            // Logic to save the edited product data
            console.log("Product Edited:", productEditData.value);
            editProductModalFlag.value = false; // Close modal after saving
        }

        const deleteProduct = (index) => {
            data.value.products.splice(index, 1); // Remove the product at the specified index
        };

        const totalOrderPrice = computed(() => {
            return data.value.products.reduce((total, item) => total + (Number(item.product.price) || 0), 0);
        });


        const deleteModalFlag = ref(false);
        const editProductModalFlag = ref(false);

        // Следим за изменениями modelValue
        watch([deleteModalFlag.value, editProductModalFlag.value], () => {
            document.body.classList.add('no-scroll');
        });

        // Метод для удаления заказа
        const deleteAction = async () => {
            try {
                const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/admin/orders.php?action=delete_order&order_id=' + props.order_id, {
                    withCredentials: true
                });

                if (response.data.status == "success") {
                    emit("orderDelete");
                } else {
                    console.error("Ошибка при удалении заказа:", response.data.status);
                }

            } catch (error) {
                console.error("Ошибка при удалении заказа:", error);
            }
        };

        // Метод для получения заказа
        const fetchAction = async () => {
            try {
                const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/admin/orders.php?action=get_order&order_id=' + props.order_id, {
                    withCredentials: true
                });
                console.log(response.data);

                if (response.data.status == "success") {

                    let raw_data = response.data.data;

                    data.value = raw_data;
                    data_original.value = JSON.parse(JSON.stringify(raw_data));

                } else {
                    console.error("Ошибка при получении заказа:", response.data.status);
                }

            } catch (error) {
                console.error("Ошибка при получении заказа:", error);
            }
        };

        const saveAction = async () => {
            try {


                const response = await axios.post(
                    process.env.VUE_APP_BACKEND_URL + '/backend/admin/orders.php?action=edit_order&order_id=' + props.order_id,
                    {
                        data: data.value,
                        data_original: data_original.value
                    },
                    { withCredentials: true }
                );


                if (response.data.status !== "success") {
                    console.error("Ошибка при сохранении заказа:", response.data.message);
                    return;
                } else {
                    setTimeout(() => {
                        emit("orderUpdate");
                        fetchAction();
                    }, 200);

                }
            } catch (error) {
                console.error("Ошибка при сохранении заказа:", error);
            }
        };


        const editAddressModalFlag = ref(false); // Controls the visibility of the address edit modal
        const addressEditData = ref({
            name: '',
            address: '',
            phone: ''
        });

        // Method to open the address edit modal
        const openEditAddressModal = () => {
            // Populate `addressEditData` with the current address information
            addressEditData.value = {
                name: data.value.order.client_name,
                address: data.value.order.address,
                phone: data.value.order.phone
            };
            editAddressModalFlag.value = true;
        };

        // Method to save the edited address
        const saveAddressEdit = () => {
            // Update the address in `data` with the edited values
            data.value.order.client_name = addressEditData.value.name;
            data.value.order.address = addressEditData.value.address;
            data.value.order.phone = addressEditData.value.phone;

            editAddressModalFlag.value = false; // Close the modal
        };


        onMounted(() => {
            fetchAction();
        });

        function priceFormatter(price) {
            return `¥${Number(price).toLocaleString('ja-JP')}`
        }

        const options_types = ref({
            cloth: '生地',
            color: '生地の色',
            lining: '裏地',
            button: 'ボタン',
        });

        const status_field_names = ref({
            confirmed: '確定済(confirmed)',
            processing: '処理中(processing)',
            shipped: '発送済(shipped)',
            in_transit: '配送中(in_transit)',
            delivered: '配達済(delivered)'
        });

        return {
            data,
            client_headers,
            areDataEqual,
            saveAction,
            deleteAction,
            deleteModalFlag,
            editProductModalFlag,
            editProduct,
            edit_product_id,
            productEditData,
            saveProductEdit,
            deleteProduct,
            totalOrderPrice,
            // Address editing
            editAddressModalFlag,   // Controls the visibility of the address edit modal
            openEditAddressModal,   // Opens the address edit modal with current data
            addressEditData,        // Holds the data for editing the address
            saveAddressEdit,         // Saves the changes made to the address

            priceFormatter,
            options_types,
            status_field_names
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

    .order {
        padding-left: 10px;
        padding-right: 10px;
        gap: 24px;
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

            .payment-method {
                display: flex;
                gap: 8px;
                position: relative;
                font-weight: 400;
                align-items: center;

                .card {
                    width: 16px;
                }
            }
        }



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

    .order {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding-left: 10px;
        padding-right: 10px;

        .order-item {
            display: flex;
            gap: 24px;
            border: 1px solid #d9d9d9;
            border-radius: 8px;
            padding: 12px;
            position: relative;
            font-weight: 400;
            font-size: 12px;
            line-height: 140%;

            .image {
                width: 70px;
                height: 70px;
                object-fit: cover;
                border-radius: 5px;
            }

            .info {
                display: flex;
                flex-direction: column;
                gap: 4px;
                width: 220px;

                .name {
                    font-weight: 600;
                    font-size: 16px;
                    line-height: 120%;
                }

                .elem {
                    display: flex;
                    justify-content: space-between;

                    .name {
                        font-weight: 600;
                        font-size: 12px;
                        line-height: 140%;
                    }

                    .value {
                        font-weight: 400;
                        font-size: 12px;
                        line-height: 140%;
                    }
                }

                .price {
                    font-weight: 600;
                    font-size: 16px;
                    line-height: 120%;
                    margin-top: 8px; // Adjust margin to position under options
                }
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

    .total-price {
        display: flex;
        justify-content: flex-end;
        font-size: 18px;
        font-weight: 600;
        padding-right: 10px;
        margin-top: 16px;
        align-items: baseline;
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

.edit-product-modal {
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 20px;

    .form {
        display: flex;
        flex-direction: column;
        gap: 24px;


        .product-image {
            display: flex;

            img {
                width: 100px;
                height: 100px;
                border-radius: 5px;
                object-fit: cover;
            }
        }

        .product-details {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
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