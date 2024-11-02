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
        <CustomSelect :values="{ 4: '4', 8: '8', 16: '16' }" v-model="itemsPerPage" :labelText="'表示件数'" :labelPosition="'side'" />

      </div>
    </div>

    <!-- Отображение товаров -->
    <div class="section-box products">
      <div class="item-card" v-for="item in items" :key="item">
        <ProductCard :item="item" />
      </div>
    </div>

    <!-- Пагинация -->
    <ItemsPaginator :items="items" :itemsPerPage="itemsPerPage" v-model="currentPage" />
  </div>
</template>

<script>
import { defineComponent, ref, onMounted } from "vue";
import axios from "axios";
import ProductCard from "../components/ProductCard.vue";
import CustomSelect from "../components/CustomSelect.vue";

export default defineComponent({
  name: "HomeView",
  components: {
    ProductCard,
    CustomSelect,
  },
  setup() {

    const is_loading = ref(true);

    const items = ref([]); // Хранение товаров
    const searchQuery = ref("");

    const itemsPerPage = ref(8);
    const totalPages = ref(0);
    const filter = ref('');
    const currentPage = ref(1);

    const sortBy = ref("recommended");



    // Метод для получения товаров с сервера
    const fetchProducts = async () => {
      is_loading.value = true;
      try {
        // console.log(sortOrder.value);

        let sort = '';

        // if (sortOrder.value.index != null) {
        //   switch (headers.value[sortOrder.value.index].field) {
        //     case 'name':
        //       sortOrder.value.ascending == true ? sort = '&sort=name_asc' : sort = '&sort=name_desc'
        //       break;
        //     case 'name_eng':
        //       sortOrder.value.ascending == true ? sort = '&sort=name_eng_asc' : sort = '&sort=name_eng_desc'
        //       break;
        //     case 'min_price':
        //       sortOrder.value.ascending == true ? sort = '&sort=lowest_price' : sort = '&sort=highest_price'
        //       break;
        //     case 'active':
        //       sortOrder.value.ascending == true ? sort = '&sort=active_asc' : sort = '&sort=active_desc'
        //       break;

        //     default:
        //       break;
        //   }
        // }

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

        let url = process.env.VUE_APP_BACKEND_URL + '/backend/products.php?itemsPerPage=' + itemsPerPage.value + '&page=' + currentPage.value + sort + q_filter + query;


        const response = await axios.get(url, {
          withCredentials: true
        });

        // console.log(response);

        // Убедимся, что товары приходят в поле `products`
        if (Array.isArray(response.data.products)) {
          // Преобразуем данные (например, конвертируем цену в число)
          items.value = response.data.products.map(product => ({
            ...product,
            min_price: Number(product.min_price), // Преобразуем строку в число
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




    // // Логика сортировки
    // const sortedItems = computed(() => {

    //   let to_filter = items;
    //   to_filter = to_filter.filter((item) =>
    //     item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    //   );

    //   if (sortBy.value === "highToLow") {
    //     return to_filter.sort((a, b) => b.price - a.price);
    //   } else if (sortBy.value === "lowToHigh") {
    //     return to_filter.sort((a, b) => a.price - b.price);
    //   }
    //   return to_filter;
    // });

    // // Логика пагинации
    // const paginatedItems = computed(() => {
    //   const start = (currentPage.value - 1) * itemsPerPage.value;
    //   const end = start + itemsPerPage.value;

    //   return sortedItems.value.slice(start, end);
    // });

    // const updateItemsPerPage = (value) => {
    //   itemsPerPage.value = value;
    //   currentPage.value = 1;
    // };

    function sortItems(type) {
      sortBy.value = type;
      currentPage.value = 1;
    }


    // Загружаем товары при монтировании компонента
    onMounted(() => {
      fetchProducts();
    });



    return {
      searchQuery,
      sortBy,
      itemsPerPage,
      currentPage,
      // paginatedItems,
      // updateItemsPerPage,
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

  .section-sort {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    align-items: center;
  }
}

.products {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  gap: 20px;

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
</style>
