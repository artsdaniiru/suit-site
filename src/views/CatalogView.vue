<template>
    <div>
      <div class="section-box services">
        <h2>当社のサービス</h2>
      </div>
  
      <div class="pagination-settings">
        <label for="items-per-page">Элементов на странице:</label>
        <select id="items-per-page" v-model="itemsPerPage">
          <option :value="4">4</option>
          <option :value="6">6</option>
          <option :value="8">8</option>
          <option :value="10">10</option>
        </select>
      </div>
  
      <div class="section-box products">
        <div class="item-card" v-for="item in paginatedItems" :key="item.name">
          <ProductCard :name="item.name" :name_eng="item.name_eng" :price="item.price" />
        </div>
      </div>
  
      <!-- Пагинация -->
      <div class="pagination-controls">
        <button @click="prevPage" :disabled="currentPage === 1">← 前へ</button>
  
        <!-- Отображение страниц -->
        <button v-for="page in visiblePages" :key="page" @click="setPage(page)" :class="{ active: currentPage === page }">
          {{ page }}
        </button>
  
        <!-- Многоточие и последняя страница -->
        <span v-if="currentPage < totalPages - maxVisiblePages">...</span>
        <button @click="setPage(totalPages)" v-if="currentPage < totalPages - maxVisiblePages">
          {{ totalPages }}
        </button>
  
        <button @click="nextPage" :disabled="currentPage === totalPages">次へ →</button>
      </div>
    </div>
  </template>
  
  <script>
  import { defineComponent, ref, computed } from "vue";
  
  export default defineComponent({
    name: "HomeView",
    setup() {
      const itemsPerPage = ref(4);
      const currentPage = ref(1);
      const maxVisiblePages = 3;  // Ограничение на количество отображаемых страниц
  
      const new_items = ref([
        { name: "スカーレットエレガンス", name_eng: "Scarlet Elegance", price: "¥32,000" },
        { name: "ディープオリーブ", name_eng: "Deep Olive", price: "¥22,500" },
        { name: "シルバーストライプ", name_eng: "Silver Stripe", price: "¥28,500" },
        { name: "モカブラウン", name_eng: "Mocha Brown", price: "¥25,500" },
        { name: "プラチナムブルー", name_eng: "Platinum Blue", price: "¥30,500" },
        { name: "ジェットブラック", name_eng: "Jet Black", price: "¥27,000" },
        { name: "ラベンダーパープル", name_eng: "Lavender Purple", price: "¥23,000" },
        { name: "サファイアネイビー", name_eng: "Sapphire Navy", price: "¥29,500" },
        { name: "クリムゾンレッド", name_eng: "Crimson Red", price: "¥26,000" },
        { name: "ダスクグレー", name_eng: "Dusk Gray", price: "¥24,000" }
      ]);
  
      const totalPages = computed(() => Math.ceil(new_items.value.length / itemsPerPage.value));
  
      const paginatedItems = computed(() => {
        const start = (currentPage.value - 1) * itemsPerPage.value;
        return new_items.value.slice(start, start + itemsPerPage.value);
      });
  
      const nextPage = () => {
        if (currentPage.value < totalPages.value) {
          currentPage.value++;
        }
      };
  
      const prevPage = () => {
        if (currentPage.value > 1) {
          currentPage.value--;
        }
      };
  
      const setPage = (page) => {
        currentPage.value = page;
      };
  
      const visiblePages = computed(() => {
        const pages = [];
  
        // Ограничение количества отображаемых страниц
        for (let i = currentPage.value - 1; i <= currentPage.value + 1; i++) {
          if (i > 0 && i <= totalPages.value) {
            pages.push(i);
          }
        }
  
        return pages;
      });
  
      return {
        new_items,
        paginatedItems,
        currentPage,
        totalPages,
        nextPage,
        prevPage,
        setPage,
        itemsPerPage,
        visiblePages,
        maxVisiblePages  // Передаем maxVisiblePages для многоточия
      };
    }
  });
  </script>
  
  <style scoped>
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
  
    select {
      padding: 5px;
      margin-left: 10px;
    }
  }
  </style>
  