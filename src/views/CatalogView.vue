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
        <CustomSelect :values="{ 4: '4', 8: '8', 16: '16' }" :selectedValue="itemsPerPage" :labelText="'表示件数'" :labelPosition="'side'" @update="updateItemsPerPage" />

      </div>
    </div>

    <!-- Отображение товаров -->
    <div class="section-box products">
      <div class="item-card" v-for="item in paginatedItems" :key="item">
        <ProductCard :name="item.name" :name_eng="item.name_eng" :price="item.price" />
      </div>
    </div>

    <!-- Пагинация -->
    <ItemsPaginator :items="items" :itemsPerPage="itemsPerPage" v-model="currentPage" />
  </div>
</template>

<script>
import { defineComponent, ref, computed } from "vue";
import ProductCard from "../components/ProductCard.vue";
import CustomSelect from "../components/CustomSelect.vue";

export default defineComponent({
  name: "HomeView",
  components: {
    ProductCard,
    CustomSelect,
  },
  setup() {
    const items = [
      { name: "スカーレットエレガンス", name_eng: "Scarlet Elegance", price: 32000 },
      { name: "ディープオリーブ", name_eng: "Deep Olive", price: 22500 },
      { name: "シルバーストライプ", name_eng: "Silver Stripe", price: 28500 },
      { name: "モカブラウン", name_eng: "Mocha Brown", price: 25500 },
      { name: "プラチナムブルー", name_eng: "Platinum Blue", price: 30500 },
      { name: "ジェットブラック", name_eng: "Jet Black", price: 27000 },
      { name: "ラベンダーパープル", name_eng: "Lavender Purple", price: 23000 },
      { name: "サファイアネイビー", name_eng: "Sapphire Navy", price: 29500 },
      { name: "クリムゾンレッド", name_eng: "Crimson Red", price: 26000 },
      { name: "モカブラウン", name_eng: "Mocha Brown", price: 25500 },
      // Добавьте другие товары
    ];

    const searchQuery = ref("");
    const sortBy = ref("recommended");
    const itemsPerPage = ref(8);
    const currentPage = ref(1);

    // Логика сортировки
    const sortedItems = computed(() => {

      let to_filter = items;
      to_filter = to_filter.filter((item) =>
        item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
      );

      if (sortBy.value === "highToLow") {
        return to_filter.sort((a, b) => b.price - a.price);
      } else if (sortBy.value === "lowToHigh") {
        return to_filter.sort((a, b) => a.price - b.price);
      }
      return to_filter;
    });

    // Логика пагинации
    const paginatedItems = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      const end = start + itemsPerPage.value;

      return sortedItems.value.slice(start, end);
    });

    const updateItemsPerPage = (value) => {
      itemsPerPage.value = value;
      currentPage.value = 1;
    };

    function sortItems(type) {
      sortBy.value = type;
      currentPage.value = 1;
    }



    return {
      searchQuery,
      sortBy,
      itemsPerPage,
      currentPage,
      paginatedItems,
      updateItemsPerPage,
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
