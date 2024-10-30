<template>
    <div v-if="!isLoader" class="items-table">
        <!-- Заголовки таблицы -->
        <div class="header">
            <span v-for="(header, index) in headers" :key="index" @click="header.sortable && sortTable(index)">
                {{ header.name }}
                <span v-if="header.sortable != undefined && header.sortable" class="sort-indicator">
                    <!-- Указываем направление сортировки -->
                    {{ getSortDirection(index) }}
                </span>
            </span>
        </div>
        <!-- Строки с товарами -->
        <div class="item-card" v-for="item in modelValue" :key="item.id" @click="clickOnItem(item.id)">
            <div class="elem" v-for="(header, index) in headers" :key="index" :class="{ 'not-active': item.active != undefined && item.active == 0 }">
                <!-- Отображение данных согласно полю -->
                <img v-if="header.field === 'image_path'" :src="item[header.field] == undefined ? '/Image.png' : item[header.field]" alt="product" class="product-image" />
                <CustomSwitch @click.stop="" v-else-if="header.switch != undefined && header.switch" v-model="item[header.field]" @changed="switchChange(item.id, header.field, $event)" />
                <span v-else>
                    {{ item[header.field] }}
                </span>
            </div>
        </div>
    </div>
    <div v-else class="items-table">
        <!-- Заголовки таблицы -->
        <div class="header">
            <span v-for="(header, index) in headers" :key="index">
                {{ header.name }}
            </span>
        </div>
        <!-- Строки с товарами -->
        <div class="item-card is_loading" v-for="item in itemsPerPage" :key="item">
            <div class="elem" v-for="(header, index) in headers" :key="index">
                <!-- Отображение данных согласно полю -->
                <div v-if="header.field === 'image_path'" class="img"></div>
                <div v-else-if="header.switch != undefined && header.switch" class="switch"></div>
                <span v-else></span>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from "vue";
import CustomSwitch from './CustomSwitch.vue';

export default defineComponent({
    name: "ItemsTable",
    components: {
        CustomSwitch,
    },
    props: {
        headers: {
            type: Array,
            required: true
        },
        modelValue: {
            type: Array,
            required: false,
        },
        sortOrder: {
            type: Array,
            required: false,
        },
        itemsPerPage: {
            type: Number,
            required: false,
            default: 8
        },
        isLoader: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {

        // Сортировка
        const sortTable = (index) => {
            emit("sorted", index);
        };

        // Сортировка
        const clickOnItem = (index) => {
            emit("clickOnItem", index);
        };



        const getSortDirection = (index) => {
            if (props.sortOrder.index !== index) return "";
            return props.sortOrder.ascending ? "↑" : "↓";
        };


        // Сортировка
        const switchChange = (id, type, val) => {
            emit("switchChange", { id: id, type: type, val: val });
        };


        return {
            sortTable,
            getSortDirection,
            clickOnItem,
            switchChange
        };
    }
});
</script>

<style lang="scss" scoped>
.items-table {
    display: flex;
    flex-direction: column;
    gap: 20px;

    .header,
    .item-card {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        font-weight: 400;
        font-size: 16px;
        line-height: 140%;
        color: #1e1e1e;
    }

    .header {
        font-weight: 700;
        cursor: pointer;
        user-select: none;

        .sort-indicator {
            margin-left: 5px;
            font-size: 12px;
        }
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

        .elem {
            display: flex;
            align-items: center;
            transition: opacity 0.3s ease;

            &.not-active {
                opacity: 0.7;
            }
        }

        &.is_loading {
            //ref https: //codepen.io/chris__sev/pen/mdrzYQE

            cursor: unset;

            &:hover {
                background: unset;
            }


            .elem {
                display: flex;
                align-items: center;


                .img {
                    border-radius: 5px;
                    width: 45px;
                    height: 45px;
                }

                .switch {
                    width: 50px;
                    height: 24px;
                    border-radius: 20px;
                }

                span {
                    width: 130px;
                    height: 22px;
                    border-radius: 8px;
                }

                .img,
                .switch,
                span {

                    background: #eee;
                    background: linear-gradient(110deg, #ececec 8%, #f5f5f5 18%, #ececec 33%);
                    background-size: 200% 100%;
                    animation: 1.5s shine linear infinite;
                }

                @keyframes shine {
                    to {
                        background-position-x: -200%;
                    }
                }
            }

        }


    }
}
</style>