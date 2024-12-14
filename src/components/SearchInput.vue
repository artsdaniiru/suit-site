<template>
    <div class="section-search">
        <input type="text" placeholder="キーワードから探す" v-model="searchQuery" />
    </div>
</template>

<script>
import { defineComponent, ref, watch } from "vue";

export default defineComponent({
    name: "SearchInput",
    props: {
        modelValue: {
            type: String,
            default: ""
        }
    },
    setup(props, { emit }) {
        // Используем ref для отслеживания поискового запроса
        const searchQuery = ref(props.modelValue);

        // Наблюдаем за изменениями searchQuery и эмитим событие 'update:modelValue'
        watch(searchQuery, (newValue) => {
            emit("update:modelValue", newValue);
        });

        // Также следим за изменениями внешнего значения
        watch(() => props.modelValue, (newValue) => {
            searchQuery.value = newValue;
        });

        return {
            searchQuery
        };
    }
});
</script>

<style lang="scss" scoped>
.section-search {

    input {
        border-radius: 20px;

        @include respond-to('md') {
            width: -webkit-fill-available;

        }
    }

    position: relative;

    &::after {
        content: url("@/assets/icons/search.svg");
        display: block;
        position: absolute;
        top: 10px;
        right: 10px;
        width: 16px;
        height: 16px;
    }

    @include respond-to('md') {
        width: -webkit-fill-available;

    }
}
</style>
