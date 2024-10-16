<template>
  <div>
    <div class="section-box services">
      <div class="section-search">
        <input 
          type="text" 
          placeholder="キーワードから探す" 
          v-model="searchQuery" 
        />
      </div>

      <div class="section-sort">
        <!-- Кнопки сортировки -->
        <div class="sort-buttons">
          <button :class="{ active: sortBy === 'new' }" @click="sortItems('new')">新着商品</button>
          <button :class="{ active: sortBy === 'highToLow' }" @click="sortItems('highToLow')">価格: 高い順</button>
          <button :class="{ active: sortBy === 'lowToHigh' }" @click="sortItems('lowToHigh')">価格: 安い順</button>
          <button :class="{ active: sortBy === 'recommended' }" @click="sortItems('recommended')">おすすめ</button>
        </div>

        <!-- Настройки пагинации -->
        <div class="pagination-settings">
          <label for="items-per-page">кол-во</label>
          <CustomSelect 
            :values="[8, 16, 32]" 
            :defaultValue="itemsPerPage" 
            @update="updateItemsPerPage" 
          />
        </div>
      </div>
    </div>

    <!-- Отображение товаров -->
    <div class="section-box products">
      <div class="item-card" v-for="item in paginatedItems" :key="item.name">
        <ProductCard :name="item.name" :name_eng="item.name_eng" :price="item.price" />
      </div>
    </div>

    <!-- Пагинация -->
    <div class="pagination-controls">
      <button @click="prevPage" :disabled="currentPage === 1">← 前へ</button>
      <button v-for="page in visiblePages" :key="page" @click="setPage(page)" :class="{ active: currentPage === page }">
        {{ page }}
      </button>
      <button @click="nextPage" :disabled="currentPage === totalPages">次へ →</button>
    </div>
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
    CustomSelect
  },
  setup() {
    const new_items = ref([
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
      { name: "プラチナムブルー", name_eng: "Platinum Blue", price: 30500 },
      { name: "ジェットブラック", name_eng: "Jet Black", price: 27000 },
      { name: "ラベンダーパープル", name_eng: "Lavender Purple", price: 23000 },
      { name: "サファイアネイビー", name_eng: "Sapphire Navy", price: 29500 },
      { name: "クリムゾンレッド", name_eng: "Crimson Red", price: 26000 },
      { name: "モカブラウン", name_eng: "Mocha Brown", price: 25500 },
      { name: "プラチナムブルー", name_eng: "Platinum Blue", price: 30500 },
      { name: "ジェットブラック", name_eng: "Jet Black", price: 27000 },
      { name: "ラベンダーパープル", name_eng: "Lavender Purple", price: 23000 },
      { name: "サファイアネイビー", name_eng: "Sapphire Navy", price: 29500 },
      { name: "クリムゾンレッド", name_eng: "Crimson Red", price: 26000 },
      { name: "モカブラウン", name_eng: "Mocha Brown", price: 25500 },
      { name: "プラチナムブルー", name_eng: "Platinum Blue", price: 30500 },
      { name: "ジェットブラック", name_eng: "Jet Black", price: 27000 },
      { name: "ラベンダーパープル", name_eng: "Lavender Purple", price: 23000 },
      { name: "サファイアネイビー", name_eng: "Sapphire Navy", price: 29500 },
      { name: "クリムゾンレッド", name_eng: "Crimson Red", price: 26000 },
      { name: "モカブラウン", name_eng: "Mocha Brown", price: 25500 },
      { name: "プラチナムブルー", name_eng: "Platinum Blue", price: 30500 },
      { name: "ジェットブラック", name_eng: "Jet Black", price: 27000 },
      { name: "ラベンダーパープル", name_eng: "Lavender Purple", price: 23000 },
      { name: "サファイアネイビー", name_eng: "Sapphire Navy", price: 29500 },
      { name: "クリムゾンレッド", name_eng: "Crimson Red", price: 26000 },
      { name: "モカブラウン", name_eng: "Mocha Brown", price: 25500 },
      { name: "プラチナムブルー", name_eng: "Platinum Blue", price: 30500 },
      { name: "ジェットブラック", name_eng: "Jet Black", price: 27000 },
      { name: "ラベンダーパープル", name_eng: "Lavender Purple", price: 23000 },
      { name: "サファイアネイビー", name_eng: "Sapphire Navy", price: 29500 },
      { name: "クリムゾンレッド", name_eng: "Crimson Red", price: 26000 },
      { name: "モカブラウン", name_eng: "Mocha Brown", price: 25500 },
      { name: "プラチナムブルー", name_eng: "Platinum Blue", price: 30500 },
      { name: "ジェットブラック", name_eng: "Jet Black", price: 27000 },
      { name: "ラベンダーパープル", name_eng: "Lavender Purple", price: 23000 },
      { name: "サファイアネイビー", name_eng: "Sapphire Navy", price: 29500 },
      { name: "クリムゾンレッド", name_eng: "Crimson Red", price: 26000 },
      { name: "モカブラウン", name_eng: "Mocha Brown", price: 25500 },
      { name: "プラチナムブルー", name_eng: "Platinum Blue", price: 30500 },
      { name: "ジェットブラック", name_eng: "Jet Black", price: 27000 },
      { name: "ラベンダーパープル", name_eng: "Lavender Purple", price: 23000 },
      { name: "サファイアネイビー", name_eng: "Sapphire Navy", price: 29500 },
      { name: "クリムゾンレッド", name_eng: "Crimson Red", price: 26000 },
      { name: "モカブラウン", name_eng: "Mocha Brown", price: 25500 },
      { name: "プラチナムブルー", name_eng: "Platinum Blue", price: 30500 },
      { name: "ジェットブラック", name_eng: "Jet Black", price: 27000 },
      { name: "ラベンダーパープル", name_eng: "Lavender Purple", price: 23000 },
      { name: "サファイアネイビー", name_eng: "Sapphire Navy", price: 29500 },
      { name: "クリムゾンレッド", name_eng: "Crimson Red", price: 26000 },
      { name: "モカブラウン", name_eng: "Mocha Brown", price: 25500 },
      { name: "プラチナムブルー", name_eng: "Platinum Blue", price: 30500 },
      { name: "ジェットブラック", name_eng: "Jet Black", price: 27000 },
      { name: "ラベンダーパープル", name_eng: "Lavender Purple", price: 23000 },
      { name: "サファイアネイビー", name_eng: "Sapphire Navy", price: 29500 },
      { name: "クリムゾンレッド", name_eng: "Crimson Red", price: 26000 },
      { name: "モカブラウン", name_eng: "Mocha Brown", price: 25500 },
      { name: "プラチナムブルー", name_eng: "Platinum Blue", price: 30500 },
      { name: "ジェットブラック", name_eng: "Jet Black", price: 27000 },
      { name: "ラベンダーパープル", name_eng: "Lavender Purple", price: 23000 },
      { name: "サファイアネイビー", name_eng: "Sapphire Navy", price: 29500 },
      { name: "クリムゾンレッド", name_eng: "Crimson Red", price: 26000 },
      { name: "モカブラウン", name_eng: "Mocha Brown", price: 25500 },
      { name: "プラチナムブルー", name_eng: "Platinum Blue", price: 30500 },
      { name: "ジェットブラック", name_eng: "Jet Black", price: 27000 },
      { name: "ラベンダーパープル", name_eng: "Lavender Purple", price: 23000 },
      { name: "サファイアネイビー", name_eng: "Sapphire Navy", price: 29500 },
      { name: "クリムゾンレッド", name_eng: "Crimson Red", price: 26000 },
      { name: "ダスクグレー", name_eng: "Dusk Gray", price: 24000 }
    ]);

    const searchQuery = ref("");
    const sortBy = ref("recommended");
    const itemsPerPage = ref(8);
    const currentPage = ref(1);

    // Фильтрованный и отсортированный массив
    const filteredAndSortedItems = computed(() => {
      let items = new_items.value;

      // Поиск по имени
      if (searchQuery.value) {
        items = items.filter(item =>
          item.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          item.name_eng.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
      }

      // Сортировка
      if (sortBy.value === "highToLow") {
        items = items.sort((a, b) => b.price - a.price);
      } else if (sortBy.value === "lowToHigh") {
        items = items.sort((a, b) => a.price - b.price);
      }

      return items;
    });

    // Пагинация
    const paginatedItems = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      const end = start + itemsPerPage.value;
      return filteredAndSortedItems.value.slice(start, end);
    });

    const totalPages = computed(() => Math.ceil(filteredAndSortedItems.value.length / itemsPerPage.value));

    const updateItemsPerPage = (value) => {
      itemsPerPage.value = value;
      currentPage.value = 1;
    };

    const sortItems = (type) => {
      sortBy.value = type;
    };

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
      for (let i = 1; i <= totalPages.value; i++) {
        pages.push(i);
      }
      return pages.slice(Math.max(currentPage.value - 2, 0), currentPage.value + 1);
    });

    return {
      new_items,
      searchQuery,
      sortBy,
      itemsPerPage,
      currentPage,
      filteredAndSortedItems,
      paginatedItems,
      totalPages,
      updateItemsPerPage,
      sortItems,
      nextPage,
      prevPage,
      setPage,
      visiblePages
    };
  }
});
</script>

  <style lang="scss" scoped>


  .section-box{
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin: 20px 0;

    .section-search input {
      border-radius: 15px;

    }
    .section-search{
      position: relative;


      &::after{
      content: url("../assets/icons/search.svg");
      display: block;
      position: absolute;
      top: 10px;
      right: 10px;
      width: 16px;
      height: 16px;
      }

    }


    .section-sort{
      display: flex;
      justify-content: space-between;
      gap: 20px;
      align-items: baseline;
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
    margin-top: 20px;
  
    button {
      padding: 10px 20px;
      margin: 0 5px;
      cursor: pointer;
      background-color: #f0f0f0;
      border: none;
      border-radius: 5px;
  
      &:disabled {
        background-color: #e0e0e0;
        cursor: not-allowed;
      }
  
      &.active {
        background-color: black;
        color: white;
      }
    }
  
    span {
      font-weight: bold;
    }
    
  }
  
  .pagination-settings {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
  

  }
  .search-sort-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.search-box {
  display: flex;
  align-items: center;
}

.search-box input {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 20px;
  outline: none;
}

.search-box button {
  margin-left: 10px;
  background-color: transparent;
  border: none;
}

.sort-buttons {
  display: flex;
  gap: 10px;
}

.sort-buttons button {
  padding: 10px 20px;
  background-color: #f0f0f0;
  border: 1px solid #ccc;
  border-radius: 20px;
  cursor: pointer;
}

.sort-buttons button.active {
  background-color: black;
  color: white;
}
</style>
  