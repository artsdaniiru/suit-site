<template>
    <div class="admin-page">
        <div class="tabs">
            <span class="tab" :class="{ active: tab == 'products' }" @click="switchTab('products')">商品</span>
            <span class="tab" :class="{ active: tab == 'options' }" @click="switchTab('options')">追加オプション</span>
        </div>
        <ProductsView v-if="tab == 'products'" />
        <OptionsView v-if="tab == 'options'" />
    </div>
</template>
<script>
import { defineComponent, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import ProductsView from './components/ProductsView.vue';
import OptionsView from './components/OptionsView.vue';

export default defineComponent({
    name: "CatalogView", components: {
        ProductsView,
        OptionsView
    },
    props: {
        defaultTab: String
    },
    setup(props) {
        const route = useRoute();
        const router = useRouter();

        // Set initial tab based on route or defaultTab prop
        const tab = ref(props.defaultTab || 'products');

        // Watch for route changes and update the tab
        watch(route, (newRoute) => {
            tab.value = newRoute.path.includes('/products') ? 'products' : 'options';
        });

        // Method to switch tabs and navigate
        const switchTab = (selectedTab) => {
            tab.value = selectedTab;
            router.push(`/admin/catalog/${selectedTab}`);
        };

        return {
            tab,
            switchTab
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
