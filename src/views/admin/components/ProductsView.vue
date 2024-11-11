<template>

    <div class="actions">
        <SearchInput v-model="searchQuery" />
        <div class="filters">
            <button class="button" @click="closeFlagAdd = true">新商品作成</button>
            <CustomSelect :values="{ active: '表示している', popular: '人気', suit: 'タイプ：スーツ', not_suit: 'タイプ：他' }" v-model="filter" :labelText="'フィルタリング'" :labelPosition="'side'" width="130px" />
            <CustomSelect :values="{ 2: '2', 4: '4', 8: '8', 16: '16' }" v-model="itemsPerPage" :labelText="'表示件数'" :labelPosition="'side'" width="130px" :notSelect="true" />
        </div>

    </div>
    <!-- Отображение товаров -->
    <ItemsTable v-if="is_loading" :headers="headers" :itemsPerPage="itemsPerPage" :isLoader="true" />
    <ItemsTable v-else :headers="headers" :sortOrder="sortOrder" v-model="items" @sorted="sortTable" @clickOnItem="editItem" @switchChange="switchAction" />
    <!-- Пагинация -->
    <ItemsPaginator :totalPages="totalPages" v-model="currentPage" />

    <CustomModal v-model="closeFlag" :title="'商品変更'">
        <EditProduct :product_id="product_id" :options="options" @productUpdate="fetchProducts" @productDelete="closeFlag = false; fetchProducts()" />
    </CustomModal>
    <CustomModal v-model="closeFlagAdd" :title="'新商品作成'">
        <AddProduct :options="options" @productAdd="fetchProducts" />
    </CustomModal>

</template>
<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import axios from "axios";
import ItemsTable from './ItemsTable.vue';
import EditProduct from './product/EditProduct.vue';
import AddProduct from './product/AddProduct.vue';

export default defineComponent({
    name: "CatalogView", components: {
        ItemsTable,
        EditProduct,
        AddProduct,
    },
    setup() {

        const is_loading = ref(true);

        const headers = ref([
            { name: "画像", field: "image_path" },
            { name: "名前", field: "name", sortable: true },
            { name: "英名", field: "name_eng", sortable: true },
            { name: "値段", field: "min_price", sortable: true },
            { name: "表示", field: "active", switch: true, sortable: true }
        ]);

        const items = ref([]); // Хранение товаров
        const searchQuery = ref("");

        const itemsPerPage = ref(8);
        const totalPages = ref(0);
        const filter = ref('');
        const currentPage = ref(1);

        const closeFlag = ref(false);
        const closeFlagAdd = ref(false);


        const sortOrder = ref({ index: null, ascending: true });

        const product_id = ref(null);


        const editItem = (index) => {
            product_id.value = index;
            closeFlag.value = true;
        }


        const switchAction = async (data) => {
            if (data.type == 'active') {
                let url = '';

                if (data.val == '0') {
                    url = process.env.VUE_APP_BACKEND_URL + '/backend/admin/products.php?action=deactive_product&product_id=' + data.id;
                } else {
                    url = process.env.VUE_APP_BACKEND_URL + '/backend/admin/products.php?action=active_product&product_id=' + data.id;
                }
                await axios.get(url, {
                    withCredentials: true
                });
            }
        }




        // Метод для получения товаров с сервера
        const fetchProducts = async () => {
            is_loading.value = true;
            try {

                let sort = '';

                if (sortOrder.value.index != null) {
                    switch (headers.value[sortOrder.value.index].field) {
                        case 'name':
                            sortOrder.value.ascending == true ? sort = '&sort=name_asc' : sort = '&sort=name_desc'
                            break;
                        case 'name_eng':
                            sortOrder.value.ascending == true ? sort = '&sort=name_eng_asc' : sort = '&sort=name_eng_desc'
                            break;
                        case 'min_price':
                            sortOrder.value.ascending == true ? sort = '&sort=lowest_price' : sort = '&sort=highest_price'
                            break;
                        case 'active':
                            sortOrder.value.ascending == true ? sort = '&sort=active_asc' : sort = '&sort=active_desc'
                            break;

                        default:
                            break;
                    }
                }

                let q_filter = '';
                switch (filter.value) {
                    case 'active':
                        q_filter = '&active=1';
                        break;
                    case 'popular':
                        q_filter = '&popular=1';
                        break;
                    case 'suit':
                        q_filter = '&productType=suit';
                        break;
                    case 'not_suit':
                        q_filter = '&productType=not_suit';
                        break;

                    default:
                        break;
                }

                let query = '';

                if (searchQuery.value != '') {
                    query = '&query=' + searchQuery.value;
                }

                let url = process.env.VUE_APP_BACKEND_URL + '/backend/admin/products.php?action=list_all_products&itemsPerPage=' + itemsPerPage.value + '&page=' + currentPage.value + sort + q_filter + query;


                const response = await axios.get(url, {
                    withCredentials: true
                });


                // Убедимся, что товары приходят в поле `products`
                if (Array.isArray(response.data.products)) {
                    // Преобразуем данные (например, конвертируем цену в число)
                    items.value = response.data.products.map(product => ({
                        ...product,
                        // min_price: Number(product.min_price), // Преобразуем строку в число
                        min_price: `¥${Number(product.min_price).toLocaleString('ja-JP')}` + (product.type == 'suit' ? '〜' : ''), // Преобразуем строку в число
                    }));
                    totalPages.value = response.data.pagination.totalPages
                    is_loading.value = false;
                } else {
                    console.error("Ожидался массив товаров, но получено что-то другое:", response.data);
                }
            } catch (error) {
                console.error("Ошибка при получении товаров:", error);
            }
        };


        const options = ref({}); // Хранение опций


        const fetchAllOptions = async () => {
            try {


                let url = process.env.VUE_APP_BACKEND_URL + '/backend/admin/products.php?action=list_all_options&splitByType=true&itemsPerPage=1000';
                const response = await axios.get(url, {
                    withCredentials: true
                });


                // Убедимся, что товары приходят в поле `products`
                if (response.data.status == 'success') {
                    // Преобразуем данные (например, конвертируем цену в число)
                    options.value = response.data.options;
                } else {
                    console.error("Ожидался массив товаров, но получено что-то другое:", response.data);
                }
            } catch (error) {
                console.error("Ошибка при получении товаров:", error);
            }
        };


        // Сортировка
        const sortTable = (index) => {
            if (sortOrder.value.index === index) {
                sortOrder.value.ascending = !sortOrder.value.ascending;
            } else {
                sortOrder.value.index = index;
                sortOrder.value.ascending = true;
            }
            // updateSortedItems();
            fetchProducts();
            currentPage.value = 1;
        };


        watch([itemsPerPage, filter, searchQuery], () => {
            fetchProducts()
            currentPage.value = 1;
        });
        watch(currentPage, () => {
            fetchProducts()
        });
        // Загружаем товары при монтировании компонента
        onMounted(() => {
            fetchProducts();
            fetchAllOptions();
        });

        return {
            is_loading,
            searchQuery,
            itemsPerPage,
            totalPages,
            currentPage,
            headers,
            sortOrder,
            sortTable,
            items,
            filter,
            closeFlag,
            closeFlagAdd,
            product_id,
            editItem,
            fetchProducts,
            switchAction,
            options
        };
    },
});
</script>
<style lang="scss" scoped>
.section-box {
    margin: 20px 0;

}



.actions {
    display: flex;
    justify-content: space-between;

    .filters {
        display: flex;
        gap: 20px;
    }



}
</style>
