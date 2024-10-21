<template>
    <div class="admin-page">
        <div class="tabs">
            <span class="tab active">商品</span>
            <span class="tab">追加オプション</span>
        </div>
        <div class="actions">
            <SearchInput v-model="searchQuery" />

            <CustomSelect :values="{ 2: '2', 4: '4', 8: '8', 16: '16' }" :selectedValue="itemsPerPage" :labelText="'表示件数'" :labelPosition="'side'" @update="updateItemsPerPage" />
        </div>

        <!-- Отображение товаров -->
        <ItemsTable :headers="headers" :sortOrder="sortOrder" v-model="paginatedItems" @sorted="sortTable" />

        <!-- Пагинация -->
        <ItemsPaginator :items="items" :itemsPerPage="itemsPerPage" v-model="currentPage" />

    </div>
</template>
<!-- eslint-disable -->
<script>
import { defineComponent, ref, computed, onMounted } from "vue";
import axios from "axios";
import ItemsTable from './components/ItemsTable.vue';

export default defineComponent({
    name: "CatalogView", components: {
        ItemsTable,
    },
    setup() {

        const headers = ref([
            { name: "画像", field: "image_path", sortable: false },
            { name: "名前", field: "name", sortable: true },
            { name: "英名", field: "name_eng", sortable: true },
            { name: "値段", field: "min_price", sortable: true }
        ]);

        const items = ref([]); // Хранение товаров
        const searchQuery = ref("");

        const itemsPerPage = ref(8);
        const currentPage = ref(1);


        const sortOrder = ref({ index: null, ascending: true });








        // Метод для получения товаров с сервера
        const fetchProducts = async () => {
            try {
                const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/products.php?itemsPerPage=100', {
                    withCredentials: true
                });
                // Убедимся, что товары приходят в поле `products`
                if (Array.isArray(response.data.products)) {
                    // Преобразуем данные (например, конвертируем цену в число)
                    items.value = response.data.products.map(product => ({
                        ...product,
                        min_price: Number(product.min_price), // Преобразуем строку в число
                    }));
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

            const start = (currentPage.value - 1) * itemsPerPage.value;
            const end = start + itemsPerPage.value;
            return sortedItems.value.slice(start, end);
        });


        const updateItemsPerPage = (value) => {
            itemsPerPage.value = value;
            currentPage.value = 1;
        };


        // Загружаем товары при монтировании компонента
        onMounted(() => {
            fetchProducts();
        });

        return {
            searchQuery,
            itemsPerPage,
            currentPage,
            paginatedItems,
            updateItemsPerPage,
            headers,
            sortOrder,
            sortTable,
            items
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

        &-btn {
            display: flex;
            gap: 20px;

            .sort-buttons {
                display: flex;
                gap: 10px;


                button {
                    padding: 8px;
                    background: #f5f5f5;
                    border-radius: 8px;
                    border: 0px;
                    cursor: pointer;

                    font-weight: 400;
                    font-size: 16px;
                    line-height: 100%;
                    color: #757575;

                    outline: unset;


                    &.active {
                        background: #2c2c2c;
                        color: #f5f5f5;
                    }

                }


            }

        }



    }
}

.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: auto;
    gap: 8px;

    button {
        padding: 8px 12px;
        margin: 0;
        cursor: pointer;
        background-color: #f0f0f0;
        border: none;
        border-radius: 5px;
        font-weight: 400;
        font-size: 16px;
        line-height: 100%;

        &:disabled {
            color: #757575;
            cursor: not-allowed;
        }

        &.active {
            background: #2c2c2c;
            color: #f5f5f5;
        }

        &.prev,
        &.next {
            background: transparent !important;
        }
    }
}

.pagination-settings {
    display: flex;
    justify-content: flex-end;
}
</style>
