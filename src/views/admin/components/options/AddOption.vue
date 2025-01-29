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
                <button class="button" @click="saveOption">新商品作成</button>
            </div>

        </div>
    </div>

</template>
<script>
import axios from "axios";
import { defineComponent, ref } from "vue";
import { useToast } from "vue-toast-notification";
const toast = useToast();

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



        const saveOption = async () => {
            try {


                console.log(data.value);


                const optionResponse = await axios.post(
                    process.env.VUE_APP_BACKEND_URL + '/backend/admin/products.php?action=add_options',
                    data.value,
                    { withCredentials: true }
                );


                if (optionResponse.data.status !== "success") {
                    console.error("Ошибка при сохранении продукта:", optionResponse.data.message);
                    toast.error("エラー:" + optionResponse.data.message);
                    return;
                } else {


                    setTimeout(() => {
                        emit("optionAdd");

                    }, 200);

                }
            } catch (error) {
                console.error("Ошибка при сохранении продукта:", error);
                toast.error("エラー:" + error);
            }
        };




        return {
            data,
            product_headers,
            saveOption,
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
