<template>
    <div class="admin-page">
        <div class="tabs">
            <span class="tab" :class="{ active: tab == 'products' }" @click="tab = 'products'">商品</span>
            <span class="tab" :class="{ active: tab == 'options' }" @click="tab = 'options'">追加オプション</span>
        </div>
        <ProductsView v-if="tab == 'products'" />
        <OptionsView v-if="tab == 'options'" />
    </div>
</template>
<script>
import { defineComponent, ref } from "vue";
import ProductsView from './components/ProductsView.vue';
import OptionsView from './components/OptionsView.vue';

export default defineComponent({
    name: "CatalogView", components: {
        ProductsView,
        OptionsView
    },
    setup() {

        const tab = ref('products');

        return {
            tab
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

}
</style>
