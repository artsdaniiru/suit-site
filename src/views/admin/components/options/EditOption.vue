<template>

    <div class="product-edit">

        <div class="product-main">
            <div class="product-main-head">
                <div class="type">
                    <label>タイプ：</label>
                    <span>{{ types[data.type] }}</span>
                </div>
            </div>
            <template v-for="(item, key) in product_headers" :key="item">
                <CustomInput v-model="data[key]" :labelText="item" :placeholderText="item" />
            </template>
        </div>

        <div class="actions">
            <div class="dates">
                <div class="date">
                    <span>作成日：</span>
                    <span>{{ data.date_of_creation }}</span>
                </div>
                <div class="date">
                    <span>変更日：</span>
                    <span>{{ data.date_of_change }}</span>
                </div>

            </div>
            <div class="buttons">
                <button class="button danger">削除</button>
                <button class="button" :disabled="areDataEqual" @click="saveProduct">保存</button>
            </div>
        </div>
    </div>
</template>
<script>
import axios from "axios";
import { defineComponent, ref, onMounted, watch } from "vue";

export default defineComponent({
    name: "EditOption",
    props: {
        option_id: {
            type: Number,
            required: true
        },
        types: {
            type: Object,
            required: true
        }
    },
    setup(props, { emit }) {
        //main data
        const data_original = ref({});
        const data = ref({});

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


        const product_headers = ref({
            name: '名前',
            price: '値段',
            stock: "在庫数量"
        });



        // Метод для получения товара
        const fetchProduct = async () => {
            try {
                const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/admin/products.php?action=get_option&option_id=' + props.option_id, {
                    withCredentials: true
                });
                if (response.data.status == "success") {

                    let raw_data = response.data.option;


                    data.value = raw_data;
                    data_original.value = JSON.parse(JSON.stringify(raw_data));
                } else {
                    console.error("Ошибка при получении товара:", response.data.status);
                }

            } catch (error) {
                console.error("Ошибка при получении товара:", error);
            }
        };

        const saveProduct = async () => {
            try {


                const productResponse = await axios.post(
                    process.env.VUE_APP_BACKEND_URL + '/backend/admin/products.php?action=edit_product&product_id=' + data.value.product.id,
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
                        fetchProduct();
                    }, 200);

                }
            } catch (error) {
                console.error("Ошибка при сохранении продукта:", error);
            }
        };



        onMounted(() => {
            fetchProduct();
        });

        return {
            data,
            product_headers,
            areDataEqual,
            saveProduct,
        };
    }
});
</script>

<style lang="scss" scoped>
.product-edit {
    display: flex;
    flex-direction: column;
    gap: 24px;


    .product-main {
        display: flex;
        flex-direction: column;
        gap: 12px;

        &-head {
            display: flex;
            gap: 12px;

            .type {
                display: flex;
                gap: 8px;
                align-items: center;
                font-size: 18px;
                width: max-content;

                span {
                    padding: 4px 8px;
                    color: #f5f5f5;
                    background: #2c2c2c;

                    border-radius: 8px;
                    width: max-content;

                }

                label {
                    width: max-content;
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
</style>

<style lang="scss">
.ql-toolbar {
    border-radius: 8px 8px 0px 0px;
    border: 1px solid #d9d9d9;
}

.ql-container {
    border-radius: 0px 0px 8px 8px;
    border: 1px solid #d9d9d9;
}
</style>