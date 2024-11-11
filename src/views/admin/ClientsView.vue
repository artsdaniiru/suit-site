<template>
    <div class="admin-page">
        <div class="actions">
            <SearchInput v-model="searchQuery" />
            <div class="filters">
                <CustomSelect :values="{ 2: '2', 4: '4', 8: '8', 16: '16' }" v-model="itemsPerPage" :labelText="'表示件数'" :labelPosition="'side'" width="130px" :notSelect="true" />
            </div>
        </div>
        <!-- Отображение товаров -->
        <ItemsTable v-if="is_loading" :headers="headers" :itemsPerPage="itemsPerPage" :isLoader="true" />
        <ItemsTable v-else :headers="headers" :sortOrder="sortOrder" v-model="items" @sorted="sortTable" @clickOnItem="editItem" @switchChange="switchAction" />
        <!-- Пагинация -->
        <ItemsPaginator :totalPages="totalPages" v-model="currentPage" />

        <CustomModal v-model="closeFlag" :title="'顧客情報変更'">
            <EditClient :client_id="client_id" />
        </CustomModal>
    </div>
</template>
<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import axios from "axios";
import ItemsTable from './components/ItemsTable.vue';

import EditClient from './components/EditClient.vue';

export default defineComponent({
    name: "ClientsView", components: {
        ItemsTable,
        EditClient
    },
    setup() {

        const is_loading = ref(true);
        const headers = ref([
            { name: "名前", field: "name", sortable: true },
            { name: "ログイン", field: "login" },
            { name: "メールアドレス", field: "email", sortable: true },
            { name: "登録日", field: "date_of_registration", sortable: true }
        ]);

        const items = ref([]); // Хранение товаров
        const searchQuery = ref("");

        const itemsPerPage = ref(8);
        const totalPages = ref(0);
        const filter = ref('');
        const currentPage = ref(1);

        const closeFlag = ref(false);

        const sortOrder = ref({ index: null, ascending: true });

        const client_id = ref(null);

        const editItem = (index) => {
            client_id.value = index;
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
                        case 'date_of_registration':
                            sortOrder.value.ascending == true ? sort = '&sort=date_asc' : sort = '&sort=date_desc'
                            break;
                        case 'email':
                            sortOrder.value.ascending == true ? sort = '&sort=email_asc' : sort = '&sort=email_desc'
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

                let url = process.env.VUE_APP_BACKEND_URL + '/backend/admin/clients.php?action=list_all_clients&itemsPerPage=' + itemsPerPage.value + '&page=' + currentPage.value + sort + q_filter + query;


                const response = await axios.get(url, {
                    withCredentials: true
                });

                console.log(response);

                // Убедимся, что товары приходят в поле `products`
                if (Array.isArray(response.data.clients)) {
                    // Преобразуем данные (например, конвертируем цену в число)
                    items.value = response.data.clients.map(client => ({
                        ...client
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
            client_id,
            editItem,
            fetchProducts,
        };
    },
});
</script>
<style lang="scss" scoped>
.section-box {
    margin: 20px 0;

}

.admin-page {

    padding: 32px;
    gap: 32px;

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
}
</style>
