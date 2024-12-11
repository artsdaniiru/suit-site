<template>
    <div class="pagination-controls" v-if="visiblePages.length > 1">
        <button class="prev" @click="prevPage" :disabled="currentPage === 1">← 前へ</button>
        <button v-for="page in visiblePages" :key="page" @click="setPage(page)" :class="{ active: currentPage === page }">
            {{ page }}
        </button>
        <button class="next" @click="nextPage" :disabled="currentPage === totalPages">次へ →</button>
    </div>
</template>

<script>
import { defineComponent, ref, watch, computed } from "vue";

export default defineComponent({
    name: "ItemsPaginator",
    props: {

        modelValue: {
            type: Number,
            required: true
        },
        itemsPerPage: {
            type: Number,
            required: false
        },
        items: {
            type: Array,
            required: false
        },
        totalPages: {
            type: Number,
            required: false,
            default: null
        }
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {

        const currentPage = ref(props.modelValue)
        const maxVisiblePages = 5;

        // Следим за обновлениями в modelValue, чтобы синхронизировать изменения извне
        watch(
            () => props.modelValue,
            (newVal) => {
                currentPage.value = newVal;
            }
        );

        const totalPages = computed(() => {

            if (props.totalPages != null) {
                return Number(props.totalPages)
            } else {
                return Math.ceil(props.items.length / props.itemsPerPage);
            }


        });

        const nextPage = () => {
            if (currentPage.value < totalPages.value) currentPage.value++;
            emit("update:modelValue", currentPage.value);
            scrollToTop();
        };

        const prevPage = () => {
            if (currentPage.value > 1) currentPage.value--;
            emit("update:modelValue", currentPage.value);
            scrollToTop();
        };

        const setPage = (page) => {
            if (page >= 1 && page <= totalPages.value) currentPage.value = page;
            emit("update:modelValue", currentPage.value);
            scrollToTop();
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

        const scrollToTop = () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth" // добавляет плавную анимацию прокрутки
            });
        };


        return {
            nextPage,
            prevPage,
            setPage,
            visiblePages,
            currentPage
        };
    }
});
</script>

<style lang="scss" scoped>
.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: auto;
    gap: 8px;

    @include respond-to('md') {
        margin-bottom: 20px;
    }

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
</style>