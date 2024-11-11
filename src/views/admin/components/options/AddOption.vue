<template>

    <div class="product-edit">



        <div class="product-main">
            <template v-for="(item, key) in product_headers" :key="item">
                <CustomSelect v-if="key == 'type'" :values="types" v-model="data[key]" :labelText="item" :notSelect="false" />
                <CustomInput v-else v-model="data[key]" :labelText="item" :placeholderText="item" />
            </template>
        </div>

        <div class="actions">
            <div class="dates">

            </div>
            <div class="buttons">
                <button class="button" @click="saveProduct">新商品作成</button>
            </div>

        </div>
    </div>

</template>
<script>
// import axios from "axios";
import { defineComponent, ref } from "vue";

export default defineComponent({
    name: "AddOption",
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
        const data = ref({
            name: "",
            type: "cloth",
            price: "0",
            stock: "0"
        });


        const product_headers = ref({
            type: 'タイプ',
            name: '名前',
            price: '値段',
            stock: "在庫数量"
        });



        const saveProduct = async () => {
            try {


                console.log(data.value);


                // const productResponse = await axios.post(
                //     process.env.VUE_APP_BACKEND_URL + '/backend/admin/products.php?action=add_product',
                //     {
                //         data: data.value,
                //     },
                //     { withCredentials: true }
                // );


                // if (productResponse.data.status !== "success") {
                //     console.error("Ошибка при сохранении продукта:", productResponse.data.message);
                //     return;
                // } else {

                //     // Затем загружаем изображения
                //     for (const img of tempImages.value) {
                //         const formData = new FormData();
                //         formData.append("image", img.file);
                //         formData.append("option_id", data.value.product.id);

                //         const imageResponse = await axios.post(
                //             process.env.VUE_APP_BACKEND_URL + '/backend/admin/upload_image.php?option_id=' + data.value.product.id,
                //             formData,
                //             {
                //                 headers: { "Content-Type": "multipart/form-data" },
                //                 withCredentials: true,
                //             }
                //         );

                //         if (imageResponse.data.status === "success") {
                //             data.value.product_images = data.value.product_images.concat(imageResponse.data.uploadedImages);
                //         } else {
                //             console.error("Ошибка при загрузке изображения:", imageResponse.data.message);
                //         }
                //     }

                //     // Очищаем временные изображения после успешного сохранения
                //     tempImages.value = [];

                //     setTimeout(() => {
                //         emit("productAdd");

                //     }, 200);

                // }

                emit("productAdd");
            } catch (error) {
                console.error("Ошибка при сохранении продукта:", error);
            }
        };




        return {
            data,
            product_headers,
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
    margin: 0 10px;

    .product-main {
        display: flex;
        flex-direction: column;
        gap: 12px;





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
</style>
