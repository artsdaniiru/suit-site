<template>

    <div class="product-edit">

        <div class="grid-selection">
            <div class="side">
                <div class="product-main">
                    <div class="product-main-head">
                        <div class="type">
                            <label>タイプ：</label>
                            <span>{{ data.product.type == 'suit' ? 'スーツ' : '他' }}</span>
                        </div>
                        <CustomSwitch v-model="data.product.active" :labelText="'表示'" labelPosition="side" />
                    </div>

                    <template v-for="(item, key) in product_headers" :key="item">
                        <CustomSelect v-if="key == 'type'" :values="{ suit: 'スーツ', not_suit: '他' }" v-model="data.product[key]" :labelText="item" :notSelect="false" />
                        <CustomSwitch v-else-if="key == 'popular'" v-model="data.product[key]" :labelText="item" />
                        <div class="description" v-else-if="key == 'description'">
                            <label>{{ item }}</label>
                            <QuillEditor theme="snow" v-model:content="data.product[key]" contentType="html" />
                        </div>
                        <CustomInput v-else v-model="data.product[key]" :labelText="item" :placeholderText="item" />
                    </template>
                </div>
                <div class="product-images">
                    <label>画像</label>
                    <div class="gallery">
                        <div class="img-elem" v-for="(img, index) in galleryImages" :key="index" @click="showImage(img, index)">
                            <div class="delete">
                                <img src="@/assets/icons/delete-admin.svg" alt="delete" @click.stop="deleteImg(img)">
                            </div>
                            <img :src="img.image_path" alt="product" class="item-pic" />
                        </div>

                        <div class="add-file" @click="triggerFileInput">
                            <input type="file" @change="previewImage" style="display: none" ref="fileInput" multiple accept="image/*" />
                            <img class="close" src="@/assets/icons/plus-white.svg" alt="add">
                            <span>画像追加</span>
                        </div>
                    </div>

                </div>
            </div>

            <div class="side">
                <div v-if="data.product.type == 'suit'" class="product-options">
                    <label>追加オプション</label>
                    <div class="types">
                        <CustomMultiSelect :values="options_e.cloth" v-model="selected_options.cloth" :labelText="'生地の種類'" />
                        <CustomMultiSelect :values="options_e.color" v-model="selected_options.color" :labelText="'生地の色'" />
                        <CustomMultiSelect :values="options_e.lining" v-model="selected_options.lining" :labelText="'裏地の種類'" />
                        <CustomMultiSelect :values="options_e.button" v-model="selected_options.button" :labelText="'ボタンの種類'" />
                    </div>
                </div>
                <div class="product-sizes">
                    <label>サイズ</label>
                    <div class="headers">
                        <div class="elem" v-for="(size, key)  in data.sizes" :key="size" :class="{ active: key == active_size }" @click="active_size = key">
                            <span>{{ size.name }}</span>
                            <div class="delete">
                                <img src="@/assets/icons/delete-admin.svg" alt="delete" @click.stop="deleteSize(key)">
                            </div>
                        </div>
                        <div class="elem" @click="addSize">
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
                <button class="button danger" @click="deleteModalFlag = true">削除</button>
                <button class="button" :disabled="areDataEqual" @click="saveProduct">保存</button>
            </div>

        </div>
    </div>

    <div v-if="show_image" class="modal images-full" @click="show_image = false">
        <img class="close" src="@/assets/icons/close-white.svg" alt="close">
        <img class="prev" src="@/assets/icons/prev-white.svg" alt="prev" @click.stop="showImagePrev">
        <img class="image" @click.stop="showImageNext" :src="show_image_path" alt="">
        <img class="next" src="@/assets/icons/next-white.svg" alt="next" @click.stop="showImageNext">
    </div>

    <CustomModal class="delete" v-model="deleteModalFlag" :title="'商品削除'" :in_modal="true">
        <div class="delete-container">
            <button class="button danger" @click="deleteProduct">削除</button>
            <button class="button" @click="deleteModalFlag = false;">戻る</button>
        </div>
    </CustomModal>

</template>
<script>
import axios from "axios";
import { defineComponent, ref, onMounted, watch, computed } from "vue";
import CustomSwitch from '../CustomSwitch.vue';

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
        },
        options: {
            type: Object,
            required: true
        }
    },
    setup(props, { emit }) {
        //main data
        const data_original = ref({ product: [], sizes: [], product_images: [], options: [] });
        const data = ref({ product: [], sizes: [], product_images: [], options: [] });

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
            name_eng: '英名',
            description: '説明',
            popular: '人気'
        });

        //Images 
        const show_image = ref(false);
        const show_image_path = ref('/image.png');
        const show_image_id = ref(0);
        const show_message = ref(false);

        function showImage(img, index) {

            show_image_path.value = img.image_path
            show_image_id.value = index
            show_image.value = true;
        }

        function showImageNext() {
            if ((show_image_id.value + 1) != galleryImages.value.length) {
                show_image_id.value = show_image_id.value + 1;
            } else {
                show_image_id.value = 0;
            }

            show_image_path.value = galleryImages.value[show_image_id.value].image_path;
        }

        function showImagePrev() {
            if ((show_image_id.value - 1) != -1) {
                show_image_id.value = show_image_id.value - 1;
            } else {
                show_image_id.value = galleryImages.value.length - 1;
            }

            show_image_path.value = galleryImages.value[show_image_id.value].image_path;
        }



        const tempImages = ref([]);

        const galleryImages = computed(() => {
            let images = [];
            data.value.product_images.forEach((val, index) => {
                images.push({
                    type: 'db',
                    image_path: val.image_path,
                    original_index: index,
                })
            })
            tempImages.value.forEach((val, index) => {
                images.push({
                    type: 'tmp',
                    image_path: val.preview,
                    original_index: index,
                })
            })

            return images
        });

        function deleteImg(img) {

            if (img.type == 'db') {
                data.value.product_images.splice(img.type.original_index, 1);
            } else {
                tempImages.value.splice(img.type.original_index, 1);
            }

        }

        const fileInput = ref(null);

        const triggerFileInput = () => {

            if (fileInput.value) {
                fileInput.value.click();
            }
        };


        const previewImage = (event) => {
            const files = Array.from(event.target.files);

            files.forEach((file) => {
                if (file && file.type.startsWith("image/")) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        tempImages.value.push({
                            file: file,
                            preview: e.target.result,
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });
            areDataEqual.value = false;
        };




        //options
        const options_e = computed(() => {

            let options = {};

            Object.keys(props.options).forEach(key => {
                options[key] = {};
                props.options[key].forEach(val => {

                    let id = val.id
                    options[key][id] = val.name;
                })
            })
            return options;
        })

        const selected_options = ref({})

        function getSelectedOptions() {

            let options = {};
            Object.keys(props.options).forEach(key => {


                options[key] = [];
                if (data.value.options != undefined) {
                    data.value.options.forEach(val => {
                        if (val.type == key) {
                            options[key].push(val.id)
                        }
                    })
                }
            })
            return options;
        }

        watch(selected_options, () => {
            let options = [];
            Object.keys(selected_options.value).forEach(key => {

                selected_options.value[key].forEach(val => {

                    props.options[key].forEach(val_o => {
                        if (val == val_o.id) {
                            options.push(val_o)
                        }
                    })

                })
            })
            data.value.options = options;
        }, { deep: true })




        //Sizes
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

        function deleteSize(key) {
            data.value.sizes.splice(key, 1);

            if (active_size.value == key) {
                active_size.value = 0;
            } else {

                if (active_size.value != 0) {
                    if (active_size.value > key) {
                        active_size.value--;
                    }
                }
            }

        }

        function addSize() {
            let size_template = {
                id: null,
                product_id: data.value.product.id,
                name: "New size",
                price: 0,
                date_of_creation: new Date(),
                date_of_change: new Date(),
                stock: 0
            };

            if (data.value.product.type === 'suit') {
                size_template = {
                    id: null,
                    product_id: data.value.product.id,
                    name: "New size",
                    price: 0,
                    height_min: 0,
                    height_max: 0,
                    shoulder_width_min: 0,
                    shoulder_width_max: 0,
                    waist_size_min: 0,
                    waist_size_max: 0,
                    date_of_creation: new Date(),
                    date_of_change: new Date(),
                    stock: 0
                };
            }

            if (data.value.sizes.length !== 0) {
                // Использование structuredClone для глубокой копии
                size_template = structuredClone(data.value.sizes[data.value.sizes.length - 1]);
                size_template.id = null;
                size_template.date_of_creation = new Date();
                size_template.date_of_change = new Date();
            }

            data.value.sizes.push(size_template);
            active_size.value = data.value.sizes.length - 1;
        }

        const deleteModalFlag = ref(false);

        // Следим за изменениями modelValue
        watch(() => deleteModalFlag.value, () => {
            document.body.classList.add('no-scroll');
        });

        // Метод для удаления товара
        const deleteProduct = async () => {
            try {
                const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/admin/products.php?action=delete_product&product_id=' + data.value.product.id, {
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
        const fetchProduct = async () => {
            try {
                const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/product.php?product_id=' + props.product_id, {
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

                // Затем загружаем изображения
                for (const img of tempImages.value) {
                    const formData = new FormData();
                    formData.append("image", img.file);
                    formData.append("product_id", data.value.product.id);

                    const imageResponse = await axios.post(
                        process.env.VUE_APP_BACKEND_URL + '/backend/admin/upload_image.php?product_id=' + data.value.product.id,
                        formData,
                        {
                            headers: { "Content-Type": "multipart/form-data" },
                            withCredentials: true,
                        }
                    );

                    if (imageResponse.data.status === "success") {
                        data.value.product_images = data.value.product_images.concat(imageResponse.data.uploadedImages);
                    } else {
                        console.error("Ошибка при загрузке изображения:", imageResponse.data.message);
                    }
                }

                // Очищаем временные изображения после успешного сохранения
                tempImages.value = [];

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
            deleteImg,
            active_size,
            size_headers,
            hasSizeKey,
            areDataEqual,
            deleteSize,
            addSize,
            tempImages,
            previewImage,
            triggerFileInput,
            fileInput,
            saveProduct,
            show_image,
            show_message,
            show_image_path,
            showImage,
            galleryImages,
            showImageNext,
            showImagePrev,
            options_e,
            selected_options,
            deleteProduct,
            deleteModalFlag
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