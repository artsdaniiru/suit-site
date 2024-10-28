<template>
    <div class="admin-page">
        <div class="tabs">
            <span class="tab active">商品</span>
            <span class="tab">追加オプション</span>
        </div>
        <div class="actions">
            <SearchInput v-model="searchQuery" />
            <div class="filters">
                <!-- <button class="button" @click="closeFlag = true">新商品作成</button> -->
                <CustomSelect :values="{ active: '表示している', popular: '人気', suit: 'タイプ：スーツ', not_suit: 'タイプ：他' }" v-model="filter" :labelText="'フィルタリング'" :labelPosition="'side'" width="130px" />
                <CustomSelect :values="{ 2: '2', 4: '4', 8: '8', 16: '16' }" v-model="itemsPerPage" :labelText="'表示件数'" :labelPosition="'side'" width="130px" />
            </div>

        </div>
        <!-- Отображение товаров -->
        <ItemsTable v-if="is_loading" :headers="headers" :itemsPerPage="itemsPerPage" :isLoader="true" />
        <ItemsTable v-else :headers="headers" :sortOrder="sortOrder" v-model="paginatedItems" @sorted="sortTable" @clickOnItem="editItem" />
        <!-- Пагинация -->
        <ItemsPaginator :items="items" :itemsPerPage="itemsPerPage" v-model="currentPage" />

        <CustomModal v-model="closeFlag" :title="'商品変更'">
            <EditProduct :product_id="product_id" />
        </CustomModal>
    </div>
</template>
<!-- eslint-disable -->
<script>
import { defineComponent, ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import ItemsTable from './components/ItemsTable.vue';
import EditProduct from './components/EditProduct.vue';

export default defineComponent({
    name: "CatalogView", components: {
        ItemsTable,
        EditProduct,
    },
    setup() {

        const is_loading = ref(true);

        const headers = ref([
            { name: "画像", field: "image_path" },
            { name: "名前", field: "name", sortable: true },
            { name: "英名", field: "name_eng", sortable: true },
            { name: "値段", field: "min_price", sortable: true },
            { name: "表示", field: "active", switch: true }
        ]);

        const items = ref([]); // Хранение товаров
        const searchQuery = ref("");

        const itemsPerPage = ref(8);
        const filter = ref('');
        const currentPage = ref(1);

        const closeFlag = ref(false);


        const sortOrder = ref({ index: null, ascending: true });

        const product_id = ref(null);


        const editItem = (index) => {


            product_id.value = index;
            closeFlag.value = true;
        }




        // Метод для получения товаров с сервера
        const fetchProducts = async () => {
            is_loading.value = true;
            try {
                const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/admin/products.php?action=list_all_products&itemsPerPage=100', {
                    withCredentials: true
                });
                // Убедимся, что товары приходят в поле `products`
                if (Array.isArray(response.data.products)) {
                    // Преобразуем данные (например, конвертируем цену в число)
                    items.value = response.data.products.map(product => ({
                        ...product,
                        min_price: Number(product.min_price), // Преобразуем строку в число
                    }));
                    is_loading.value = false;
                } else {
                    console.error("Ожидался массив товаров, но получено что-то другое:", response.data);
                }
            } catch (error) {
                console.error("Ошибка при получении товаров:", error);
            }
        };

        const updateSortedItems = () => {
            const field = headers.value[sortOrder.value.index].field;
            const sorted = [...items.value].sort((a, b) => {
                const valA = a[field];
                const valB = b[field];
                if (typeof valA === "number" && typeof valB === "number") {
                    return sortOrder.value.ascending ? valA - valB : valB - valA;
                }
                return sortOrder.value.ascending
                    ? String(valA).localeCompare(String(valB))
                    : String(valB).localeCompare(String(valA));
            });

            items.value = sorted;
        };

        // Сортировка
        const sortTable = (index) => {
            if (sortOrder.value.index === index) {
                sortOrder.value.ascending = !sortOrder.value.ascending;
            } else {
                sortOrder.value.index = index;
                sortOrder.value.ascending = true;
            }
            updateSortedItems();
        };

        // Логика сортировки
        const sortedItems = computed(() => items.value);




        // Логика пагинации
        const paginatedItems = computed(() => {

            sortedItems.value = sortedItems.value.filter((item) =>
                item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
            );

            const start = (currentPage.value - 1) * Number(itemsPerPage.value);
            const end = Number(start) + Number(itemsPerPage.value);
            return sortedItems.value.slice(start, end);
        });




        watch(itemsPerPage, () => {
            currentPage.value = 1;
        });

        // Загружаем товары при монтировании компонента
        onMounted(() => {
            fetchProducts();
        });

        return {
            is_loading,
            searchQuery,
            itemsPerPage,
            currentPage,
            paginatedItems,
            headers,
            sortOrder,
            sortTable,
            items,
            filter,
            closeFlag,
            product_id,
            editItem
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

    .tabs {
        display: flex;

        .tab {
            font-weight: 400;
            font-size: 16px;
            line-height: 140%;
            color: #767676;

            padding: 4px 12px;
            border-bottom: 1px solid #b2b2b2;
            cursor: pointer;

            &.active {
                color: #303030;
                border-bottom: 1px solid #303030;
            }
        }
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
