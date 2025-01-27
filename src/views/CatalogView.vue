<template>
  <div class="catalog-view">
    <div class="section-box services">
      <SearchInput v-model="searchQuery" />

      <div class="section-sort">
        <!-- Кнопки сортировки -->
        <div class="sort-buttons">
          <button :class="{ active: sortBy === 'new' }" @click="sortItems('new')">新着商品</button>
          <button :class="{ active: sortBy === 'highToLow' }" @click="sortItems('highToLow')">価格: 高い順</button>
          <button :class="{ active: sortBy === 'lowToHigh' }" @click="sortItems('lowToHigh')">価格: 安い順</button>
          <button :class="{ active: sortBy === 'recommended' }" @click="sortItems('recommended')">おすすめ</button>
        </div>

        <!-- Настройки пагинации -->
        <CustomSelect :values="{ 4: '4', 8: '8', 16: '16' }" v-model="itemsPerPage" :labelText="'表示件数'" :labelPosition="'side'" :notSelect="true" />

      </div>
    </div>

    <!-- Отображение товаров -->
    <div class="section-box products">
      <div class="item-card" v-for="item in items" :key="item">
        <ProductCard :item="item" />
      </div>
    </div>

    <!-- Пагинация -->
    <ItemsPaginator :totalPages="totalPages" v-model="currentPage" />
  </div>
</template>

<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import axios from "axios";
import ProductCard from "../components/ProductCard.vue";
import CustomSelect from "../components/CustomSelect.vue";
import { useRoute, useRouter } from "vue-router";

export default defineComponent({
  name: "HomeView",
  components: {
    ProductCard,
    CustomSelect,
  },
  setup() {
    const route = useRoute();
    const router = useRouter();

    const is_loading = ref(true);
    const items = ref([]); // Хранение товаров
    const searchQuery = ref(route.query.query || "");
    const itemsPerPage = ref(Number(route.query.itemsPerPage) || 8);
    const totalPages = ref(0);
    const filter = ref(route.query.filter || "");
    const currentPage = ref(Number(route.params.page) || 1);
    const sortBy = ref(route.query.sort || "recommended");

    // Метод для получения товаров с сервера
    const fetchProducts = async () => {
      is_loading.value = true;
      try {
        let sort = '';

        switch (sortBy.value) {
          case 'new':
            sort = '&sort=newest';
            break;
          case 'lowToHigh':
            sort = '&sort=lowest_price';
            break;
          case 'highToLow':
            sort = '&sort=highest_price';
            break;
          case 'recommended':
            sort = '&sort=recommended';
            break;
          default:
            break;
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
        if (searchQuery.value !== '') {
          query = '&query=' + searchQuery.value;
        }

        let url = process.env.VUE_APP_BACKEND_URL + '/backend/products.php?itemsPerPage=' + itemsPerPage.value + '&page=' + currentPage.value + sort + q_filter + query;

        const response = await axios.get(url, {
          withCredentials: true
        });

        if (Array.isArray(response.data.products)) {
          items.value = response.data.products.map(product => ({
            ...product,
            min_price: Number(product.min_price),
          }));
          totalPages.value = response.data.pagination.totalPages;
          is_loading.value = false;
        } else {
          console.error("Ожидался массив товаров, но получено что-то другое:", response.data);
        }
      } catch (error) {
        console.error("Ошибка при получении товаров:", error);
      }
    };

    const updateURL = () => {
      const queryParams = {
        filter: filter.value || undefined,
        itemsPerPage: itemsPerPage.value !== 8 ? itemsPerPage.value : undefined,
        query: searchQuery.value || undefined,
        sort: sortBy.value !== 'recommended' ? sortBy.value : undefined
      };
      router.push({
        path: `/catalog/${currentPage.value}`,
        query: queryParams
      });
    };

    function sortItems(type) {
      sortBy.value = type;
      currentPage.value = 1;
    }

    watch([itemsPerPage, filter, searchQuery, sortBy], () => {
      fetchProducts();
      currentPage.value = 1;
      updateURL();
    });

    watch(currentPage, () => {
      fetchProducts();
      updateURL();
    });

    onMounted(() => {
      fetchProducts();
    });

    return {
      searchQuery,
      sortBy,
      itemsPerPage,
      currentPage,
      totalPages,
      sortItems,
      items
    };
  },
});
</script>

<style lang="scss" scoped>
.section-box {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin: 20px 0;

  @include is-mobile() {
    padding: 0px 24px;
    flex-direction: column;
    justify-content: unset;
    gap: 20px;
  }

  .section-sort {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    align-items: center;

    @include is-mobile() {
      width: 100%;
      flex-direction: column-reverse;

    }

    .sort-buttons {
      display: flex;
      gap: 10px;

      @include is-mobile() {
        width: 100%;
        flex-wrap: wrap;

      }

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

        @include is-mobile() {
          font-size: 12px;

        }

        &.active {
          background: #2c2c2c;
          color: #f5f5f5;
        }

      }


    }


  }
}

.products {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  gap: 20px;

  @include is-mobile() {
    grid-template-columns: 1fr;
  }

  .item-card {
    display: flex;
    justify-content: center;
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

.search-sort-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
</style>
