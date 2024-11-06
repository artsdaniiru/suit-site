<template>

    <div class="product-edit">
        <div class="grid-selection">
            <div class="side">
                <div class="product-main">
                    <template v-for="(item, key) in product_headers" :key="item">
                        <CustomInput v-model="data.product[key]" :labelText="item" :placeholderText="item" />
                    </template>
                </div>
            </div>

        </div>
        <div class="actions">
            <div class="dates">
                <div class="date">
                    <span>作成日：</span>
                    <span>{{ data.product.date_of_creation }}</span>
                </div>
                <div class="date">
                    <span>変更日：</span>
                    <span>{{ data.product.date_of_change }}</span>
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
        options: {
            type: Object,
            required: true
        }
    },
    setup(props, { emit }) {
        //main data
        const data_original = ref({});
        const data = ref({ });

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
            price:'値段'
        });

     
        // Метод для получения товара
        const fetchProduct = async () => {
            try {
                const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/product.php?option_id=' + props.option_id, {
                    withCredentials: true
                });
                if (response.data.status == "success") {

                    let raw_data = response.data.data;

                    data.value = raw_data;
                    data_original.value = JSON.parse(JSON.stringify(raw_data));
                    selected_options.value = getSelectedOptions();
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

            .description {
                display: flex;
                flex-direction: column;

                label {
                    margin-bottom: 8px;
                }

                textarea {
                    width: -webkit-fill-available;
                    height: 150px;
                }
            }

        }

        .product-sizes {
            display: flex;
            flex-direction: column;
            gap: 12px;

            .headers {
                display: flex;
                gap: 12px;
                margin-bottom: 4px;
                flex-wrap: wrap;

                .elem {
                    height: 32px;
                    padding: 0px 8px;
                    background: #f5f5f5;
                    font-weight: 400;
                    font-size: 16px;
                    line-height: 100%;
                    color: #757575;
                    display: flex;
                    align-items: center;
                    border-radius: 8px;
                    cursor: pointer;
                    position: relative;

                    &.active,
                    &:hover {
                        color: #f5f5f5;
                        background: #2c2c2c;
                    }

                    .delete {
                        position: absolute;
                        top: -10px;
                        right: -10px;

                        background-color: #fff;

                        border: 1px solid #2c2c2c;
                        border-radius: 20px;

                        width: 20px;
                        height: 20px;
                        display: flex;
                        cursor: pointer;

                        transition: border 0.3s ease;

                        &:hover {
                            border: 1px solid #1e1e1e;
                        }

                        img {
                            width: 12px;
                            height: 12px;
                            margin: auto;
                        }

                    }
                }
            }

            .size-details {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
        }

        .product-images {
            display: flex;
            flex-direction: column;
            gap: 12px;

            .gallery {
                display: flex;
                gap: 12px;
                flex-wrap: wrap;


                .img-elem {
                    width: 90px;
                    height: 90px;
                    position: relative;
                    cursor: pointer;

                    .item-pic {
                        border-radius: 5px;
                        width: 90px;
                        height: 90px;
                        object-fit: cover;
                    }

                    .delete {
                        position: absolute;
                        top: -10px;
                        right: -10px;

                        background-color: #fff;

                        border: 1px solid #2c2c2c;
                        border-radius: 20px;

                        width: 20px;
                        height: 20px;
                        display: flex;
                        cursor: pointer;

                        transition: border 0.3s ease;

                        &:hover {
                            border: 1px solid #1e1e1e;
                        }

                        img {
                            width: 12px;
                            height: 12px;
                            margin: auto;
                        }

                    }
                }

                .add-file {
                    display: flex;
                    flex-direction: column;
                    border-radius: 5px;
                    width: 90px;
                    height: 90px;
                    color: #fff;
                    background: #2c2c2c;
                    cursor: pointer;
                    transition: background 0.3s ease;

                    &:hover {
                        background: #1e1e1e;
                    }

                    img {
                        margin: auto auto 0px auto;
                        width: 40px;
                        height: 40px;
                    }

                    span {
                        margin: 2px auto auto auto;
                        font-size: 14px;
                    }

                }
            }
        }

        .product-options {

            display: flex;
            flex-direction: column;
            gap: 12px;

            .types {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
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