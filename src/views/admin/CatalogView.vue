<template>
    <div class="admin-page">
        <div class="tabs">
            <span class="tab active">商品</span>
            <span class="tab">追加オプション</span>
        </div>
        <div class="actions">
            <SearchInput v-model="searchQuery" />

            <div class="actions-btn">
                <div class="sort-buttons">
                    <button :class="{ active: sortBy === 'new' }" @click="sortItems('new')">新着商品</button>
                    <button :class="{ active: sortBy === 'highToLow' }" @click="sortItems('highToLow')">価格: 高い順</button>
                    <button :class="{ active: sortBy === 'lowToHigh' }" @click="sortItems('lowToHigh')">価格: 安い順</button>
                    <button :class="{ active: sortBy === 'recommended' }" @click="sortItems('recommended')">おすすめ</button>
                </div>
                <div class="pagination-settings">
                    <label for="items-per-page">кол-во</label>
                    <CustomSelect :values="[4, 8, 16]" :defaultValue="itemsPerPage" @update="updateItemsPerPage" />
                </div>
            </div>

        </div>

        <!-- Отображение товаров -->
        <div class="section-box products">

            <div class="header">
                <span></span>
                <span>名前</span>
                <span>英名</span>
                <span>値段</span>
            </div>

            <div class="item-card" v-for="item in paginatedItems" :key="item.id">
                <img src="/images/suit.webp" alt="name" class="product-image" />
                <span>{{ item.name }}</span>
                <span>{{ item.name_eng }}</span>
                <span>¥{{ item.min_price }}</span>
            </div>
        </div>

        <!-- Пагинация -->
        <div class="pagination-controls" v-if="visiblePages.length != 0">
            <button class="prev" @click="prevPage" :disabled="currentPage === 1">← 前へ</button>
            <button v-for="page in visiblePages" :key="page" @click="setPage(page)" :class="{ active: currentPage === page }">
                {{ page }}
            </button>
            <button class="next" @click="nextPage" :disabled="currentPage === totalPages">次へ →</button>
        </div>
    </div>
</template>

<script>
import { defineComponent, ref, computed, onMounted } from "vue";
import axios from "axios";

export default defineComponent({
    name: "CatalogView",
    setup() {
        const items = ref([]); // Хранение товаров
        const searchQuery = ref("");
        const sortBy = ref("recommended");
        const itemsPerPage = ref(8);
        const currentPage = ref(1);
        const maxVisiblePages = 5;

        // Метод для получения товаров с сервера
        const fetchProducts = async () => {
            try {
                const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/products.php', {
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

        // Логика сортировки
        const sortedItems = computed(() => {
            let to_filter = items.value;
            to_filter = to_filter.filter((item) =>
                item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
            );

            if (sortBy.value === "highToLow") {
                return to_filter.sort((a, b) => b.min_price - a.min_price);
            } else if (sortBy.value === "lowToHigh") {
                return to_filter.sort((a, b) => a.min_price - b.min_price);
            }
            return to_filter;
        });

        // Логика пагинации
        const paginatedItems = computed(() => {
            const start = (currentPage.value - 1) * itemsPerPage.value;
            const end = start + itemsPerPage.value;
            return sortedItems.value.slice(start, end);
        });

        const totalPages = computed(() => {
            return Math.ceil(sortedItems.value.length / itemsPerPage.value);
        });

        const updateItemsPerPage = (value) => {
            itemsPerPage.value = value;
            currentPage.value = 1;
        };

        function sortItems(type) {
            sortBy.value = type;
            currentPage.value = 1;
        }

        const nextPage = () => {
            if (currentPage.value < totalPages.value) currentPage.value++;
        };

        const prevPage = () => {
            if (currentPage.value > 1) currentPage.value--;
        };

        const setPage = (page) => {
            if (page >= 1 && page <= totalPages.value) currentPage.value = page;
        };

        const visiblePages = computed(() => {
            const pages = [];
            const total = totalPages.value;
            const start = Math.max(1, currentPage.value - Math.floor(maxVisiblePages / 2));
            const end = Math.min(total, start + maxVisiblePages - 1);

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        });

        // Загружаем товары при монтировании компонента
        onMounted(() => {
            fetchProducts();
        });

        return {
            searchQuery,
            sortBy,
            itemsPerPage,
            currentPage,
            paginatedItems,
            totalPages,
            updateItemsPerPage,
            sortItems,
            nextPage,
            prevPage,
            setPage,
            visiblePages,
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
        margin-top: 48px;
        margin-top: 48px;
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

.products {
    display: flex;
    flex-direction: column;
    gap: 20px;

    .header,
    .item-card {
        display: grid;
        grid-template-columns: 100px 1fr 1fr 1fr;

        font-weight: 400;
        font-size: 16px;
        line-height: 140%;
        color: #1e1e1e;
    }

    .header {
        font-weight: 700;
    }

    .item-card {
        border: 1px solid #d9d9d9;
        border-radius: 8px;
        height: 65px;
        align-items: center;
        padding-left: 7px;
        padding-right: 7px;
        cursor: pointer;


        img {
            border-radius: 5px;
            width: 45px;
            height: 45px;
            object-fit: cover;
        }

        &:hover {
            background: #f5f5f5;
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
