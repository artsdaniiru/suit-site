<template>

    <div class="actions">
        <SearchInput v-model="searchQuery" />
        <div class="filters">
            <button class="button" @click="closeFlagAdd = true">新追加オプション作成</button>
            <CustomSelect :values="options_types" v-model="filter" :labelText="'タイプ'" :labelPosition="'side'" width="130px" />
            <CustomSelect :values="{ 2: '2', 4: '4', 8: '8', 16: '16' }" v-model="itemsPerPage" :labelText="'表示件数'" :labelPosition="'side'" width="130px" />
        </div>

    </div>
    <!-- Отображение товаров -->
    <ItemsTable v-if="is_loading" :headers="headers" :itemsPerPage="itemsPerPage" :isLoader="true" />
    <ItemsTable v-else :headers="headers" :sortOrder="sortOrder" v-model="items" @sorted="sortTable" @clickOnItem="editItem" />
    <!-- Пагинация -->
    <ItemsPaginator :totalPages="totalPages" v-model="currentPage" />

    <CustomModal v-model="closeFlag" :title="'追加オプション変更'">
        <EditOption :option_id="option_id" :types="options_types" @optionUpdate="fetchProducts" />
    </CustomModal>
    <CustomModal v-model="closeFlagAdd" :title="'新追加オプション作成'">
        <AddOption :types="options_types" @optionAdd="fetchProducts" />
    </CustomModal>

</template>
<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import axios from "axios";
import ItemsTable from './ItemsTable.vue';
import AddOption from './options/AddOption.vue';
import EditOption from './options/EditOption.vue';

export default defineComponent({
    name: "CatalogView", components: {
        ItemsTable,
        AddOption,
        EditOption,
    },
    setup() {

        const is_loading = ref(true);

        const headers = ref([

            { name: "名前", field: "name" },
            { name: "タイプ", field: "type", sortable: true },
            { name: "値段", field: "price", sortable: true }
        ]);

        const options_types = ref({
            cloth: '生地',
            color: '生地の色',
            lining: '裏地',
            button: 'ボタン',
        });

        const items = ref([]); // Хранение товаров
        const searchQuery = ref("");

        const itemsPerPage = ref(8);
        const totalPages = ref(0);
        const filter = ref('');
        const currentPage = ref(1);

        const closeFlag = ref(false);
        const closeFlagAdd = ref(false);


        const sortOrder = ref({ index: null, ascending: true });

        const option_id = ref(null);


        const editItem = (index) => {
            option_id.value = index;
            closeFlag.value = true;
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
                if (filter.value != '') {
                    q_filter = '&type=' + filter.value;
                }

                let query = '';

                if (searchQuery.value != '') {
                    query = '&query=' + searchQuery.value;
                }

                let url = process.env.VUE_APP_BACKEND_URL + '/backend/admin/products.php?action=list_all_options&itemsPerPage=' + itemsPerPage.value + '&page=' + currentPage.value + sort + q_filter + query;


                const response = await axios.get(url, {
                    withCredentials: true
                });



                // Убедимся, что товары приходят в поле `products`
                if (Array.isArray(response.data.options)) {

                    let types = options_types.value
                    // Преобразуем данные (например, конвертируем цену в число)
                    items.value = response.data.options.map(option => ({
                        ...option,
                        type: types[option.type],
                        price: `¥${Number(option.price).toLocaleString('ja-JP')}`
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
            option_id,
            editItem,
            fetchProducts,
            options_types
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
