<template>
    <div class="admin-page">
        <div class="actions">
            <SearchInput v-model="searchQuery" />
            <div class="filters">
                <CustomSelect :values="status_field_names" v-model="filter" :labelText="'フィルタリング'" :labelPosition="'side'" width="130px" />
                <CustomSelect :values="{ 2: '2', 4: '4', 8: '8', 16: '16' }" v-model="itemsPerPage" :labelText="'表示件数'" :labelPosition="'side'" width="130px" :notSelect="true" />
            </div>
        </div>
        <!-- Отображение товаров -->
        <ItemsTable v-if="is_loading" :headers="headers" :itemsPerPage="itemsPerPage" :isLoader="true" />
        <ItemsTable v-else :headers="headers" :sortOrder="sortOrder" v-model="items" @sorted="sortTable" @clickOnItem="editItem" @switchChange="switchAction" />
        <!-- Пагинация -->
        <ItemsPaginator :totalPages="totalPages" v-model="currentPage" />

        <CustomModal v-model="closeFlag" :title="'注文内容変更'">
            <EditOrder :order_id="order_id" />
        </CustomModal>
    </div>
</template>
<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import axios from "axios";
import ItemsTable from './components/ItemsTable.vue';
import EditOrder from "./components/EditOrder.vue";
import { useToast } from "vue-toast-notification";
const toast = useToast();

export default defineComponent({
    name: "OrdersView", components: {
        ItemsTable,
        EditOrder
    },
    setup() {

        const is_loading = ref(true);
        const headers = ref([
            { name: "注文番号", field: "order_id", sortable: true },
            { name: "顧客名前", field: "client_name", sortable: true },
            { name: "状態", field: "status", sortable: true },
            { name: "メールアドレス", field: "email", sortable: true },
            { name: "電話番号", field: "phone", sortable: true },
            { name: "作成日", field: "date_of_creation", sortable: true },
        ]);

        const status_field_names = ref({
            confirmed: '確定済(confirmed)',
            processing: '処理中(processing)',
            shipped: '発送済(shipped)',
            in_transit: '配送中(in_transit)',
            delivered: '配達済(delivered)'
        });

        const items = ref([]); // Хранение товаров
        const searchQuery = ref("");

        const itemsPerPage = ref(8);
        const totalPages = ref(0);
        const filter = ref('');
        const currentPage = ref(1);

        const closeFlag = ref(false);

        const sortOrder = ref({ index: null, ascending: true });

        const order_id = ref(null);

        const editItem = (index) => {
            order_id.value = index;
            closeFlag.value = true;
        }

        // Метод для получения товаров с сервера

        //TODO: Переименовать
        const fetchProducts = async () => {
            is_loading.value = true;
            try {

                let sort = '';

                if (sortOrder.value.index != null) {
                    switch (headers.value[sortOrder.value.index].field) {
                        case 'order_id':
                            sortOrder.value.ascending ? sort = '&sort=id_asc' : sort = '&sort=id_desc';
                            break;
                        case 'client_name':
                            sortOrder.value.ascending ? sort = '&sort=name_asc' : sort = '&sort=name_desc';
                            break;
                        case 'status':
                            sortOrder.value.ascending ? sort = '&sort=status_asc' : sort = '&sort=status_desc';
                            break;
                        case 'email':
                            sortOrder.value.ascending ? sort = '&sort=email_asc' : sort = '&sort=email_desc';
                            break;
                        case 'phone':
                            sortOrder.value.ascending ? sort = '&sort=phone_asc' : sort = '&sort=phone_desc';
                            break;
                        case 'date_of_creation':
                            sortOrder.value.ascending ? sort = '&sort=date_asc' : sort = '&sort=date_desc';
                            break;
                        default:
                            sort = '&sort=id_desc';
                            break;
                    }

                }

                let q_filter = filter.value ? `&status=${filter.value}` : '';

                let query = '';

                if (searchQuery.value != '') {
                    query = '&query=' + searchQuery.value;
                }

                let url = process.env.VUE_APP_BACKEND_URL + '/backend/admin/orders.php?action=list_all_orders&itemsPerPage=' + itemsPerPage.value + '&page=' + currentPage.value + sort + q_filter + query;


                const response = await axios.get(url, {
                    withCredentials: true
                });


                // Убедимся, что товары приходят в поле `products`
                if (Array.isArray(response.data.orders)) {
                    // Преобразуем данные (например, конвертируем цену в число)
                    items.value = response.data.orders.map(order => ({
                        ...order,
                        order_id: "#" + order.id.toString().padStart(5, '0'),
                        status: status_field_names.value[order.status],
                        date_of_creation: new Date(order.date_of_creation).toLocaleDateString('ja-JP')
                    }));
                    // totalPages.value = response.data.pagination.totalPages
                    is_loading.value = false;
                } else {
                    console.error("Ожидался массив товаров, но получено что-то другое:", response.data);
                    toast.error("エラー。");
                }
            } catch (error) {
                console.error("Ошибка при получении товаров:", error);
                toast.error("エラー。");
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
            order_id,
            editItem,
            fetchProducts,
            status_field_names
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
