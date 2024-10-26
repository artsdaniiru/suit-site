<template>

    <div class="product-edit">

        <div class="grid-selection">
            <div class="side">
                <div class="product-main">
                    <div class="product-main-head">
                        <div class="type">
                            <label>タイプ：</label>
                            <span>{{ data.product.type == 'suit' ? 'スーツ' : '他の' }}</span>
                        </div>
                        <CustomSwitch v-model="data.product.active" :labelText="'表示'" labelPosition="side" />
                    </div>

                    <template v-for="(item, key) in product_headers" :key="item">
                        <CustomSelect v-if="key == 'type'" :values="{ suit: 'スーツ', not_suit: '他の' }" v-model="data.product[key]" :labelText="item" :notSelect="false" />
                        <CustomSwitch v-else-if="key == 'popular'" v-model="data.product[key]" :labelText="item" />
                        <div class="description" v-else-if="key == 'description'">
                            <label>{{ item }}</label>
                            <!-- <textarea :value="data.product[key]" /> -->
                            <QuillEditor theme="snow" v-model:content="data.product[key]" contentType="html" />
                        </div>
                        <CustomInput v-else v-model="data.product[key]" :labelText="item" :placeholderText="item" />
                    </template>
                </div>
                <div class="product-images">
                    <label>画像</label>
                    <div class="gallery">
                        <div class="img-elem" v-for="(img, key)  in data.product_images" :key="img">
                            <div class="delete">
                                <img src="../../../assets/icons/delete.svg" alt="delete" @click="deleteImg(key)">
                            </div>
                            <img :src="img.image_path" alt="product" class="item-pic" />
                        </div>

                        <div class="add-file">
                            <img class="close" src="../../../assets/icons/plus-white.svg" alt="add">
                            <span>画像追加</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="side">
                <div v-if="data.product.type == 'suit'" class="product-options">
                    <label>追加オプション</label>
                    <div class="types">
                        <CustomMultiSelect :values="['コットン (綿)', 'コットン (綿)2', 'コットン (綿)3', 'コットン (綿)4']" :labelText="'生地の種類'" />
                        <CustomMultiSelect :values="['赤 (あか)', '赤 (あか)2', '赤 (あか)3', '赤 (あか)4']" :labelText="'生地の色'" />
                        <CustomMultiSelect :values="['サテン裏地', 'サテン裏地2', 'サテン裏地3', 'サテン裏地4']" :labelText="'裏地の種類'" />
                        <CustomMultiSelect :values="['プラスチックボタン', 'プラスチックボタン2', 'プラスチックボタン3', 'プラスチックボタン4']" :labelText="'ボタンの種類'" />
                    </div>
                </div>
                <div class="product-sizes">
                    <label>サイズ</label>
                    <div class="headers">
                        <div class="elem" v-for="(size, key)  in data.sizes" :key="size" :class="{ active: key == active_size }" @click="active_size = key">
                            <span>{{ size.name }}</span>
                        </div>
                        <div class="elem">
                            <span>+</span>
                        </div>
                    </div>
                    <div class="size-details">
                        <template v-for="(size, key) in size_headers" :key="size">
                            <CustomInput v-if="hasSizeKey(active_size, key)" v-model="data.sizes[active_size][key]" :labelText="size" :placeholderText="size" />
                        </template>
                    </div>
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
                <button class="button" :disabled="areDataEqual">保存</button>
            </div>

        </div>
    </div>
</template>
<script>
import axios from "axios";
import { defineComponent, ref, onMounted, watch } from "vue";
import CustomSwitch from './CustomSwitch.vue';

import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css';

export default defineComponent({
    name: "EditProduct",
    components: {
        CustomSwitch,
        QuillEditor
    },
    props: {
        product_id: {
            type: Number,
            required: true
        }
    },
    setup(props) {

        const data_original = ref({ product: [], sizes: [], product_images: [] });
        const data = ref({ product: [], sizes: [], product_images: [] });

        const product_headers = ref({
            name: '名前',
            name_eng: '英名',
            description: '説明',
            popular: '人気'
        });
        const size_headers = ref({
            name: '名前',
            price: '値段',
            height_min: "身長最小",
            height_max: "身長最大",
            shoulder_width_min: "肩幅最小",
            shoulder_width_max: "肩幅最大",
            waist_size_min: "ウエスト最小",
            waist_size_max: "ウエスト最大",
            stock: "在庫数量"
        });

        const active_size = ref(0);

        const hasSizeKey = (activeSizeIndex, key) => {
            return (
                data.value.sizes[activeSizeIndex] &&
                data.value.sizes[activeSizeIndex][key] !== undefined
            );
        };

        // Метод для получения товара
        const fetchProduct = async () => {
            try {
                const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/product.php?product_id=' + props.product_id, {
                    withCredentials: true
                });
                if (response.data.status == "success") {
                    data.value = response.data.data;
                    data_original.value = JSON.parse(JSON.stringify(response.data.data));
                } else {
                    console.error("Ошибка при получении товара:", response.data.status);
                }

            } catch (error) {
                console.error("Ошибка при получении товара:", error);
            }
        };


        function deleteImg(key) {
            console.log(key);
            data.value.product_images.splice(key, 1);

        }

        onMounted(() => {
            fetchProduct();
        });


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


        return {
            data,
            product_headers,
            deleteImg,
            active_size,
            size_headers,
            hasSizeKey,
            areDataEqual
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
                gap: 8px;
                margin-bottom: 4px;

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

                    &.active,
                    &:hover {
                        color: #f5f5f5;
                        background: #2c2c2c;
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


                .img-elem {
                    width: 90px;
                    height: 90px;
                    position: relative;

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