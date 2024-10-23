<template>
    <div :class="['switch-container', labelPositionClass]">
        <span v-if="labelText" class="custom-label">{{ labelText }}</span>
        <label class="switch">
            <input type="checkbox" :checked="booleanValue" @change="toggleSwitch">
            <span class="slider"></span>
        </label>
    </div>
</template>

<script>
import { defineComponent, computed, ref, toRefs } from "vue";

export default defineComponent({
    name: "CustomSwitch",
    props: {
        modelValue: {
            type: [String, Boolean],
            required: true,
        },
        labelText: {
            type: String,
            required: false,
            default: ""
        },
        labelPosition: {
            type: String,
            required: false,
            default: "top",
            validator: value => ["top", "side"].includes(value)
        },

    },
    setup(props, { emit }) {

        const { labelPosition } = toRefs(props);
        // Конвертируем строковые значения "1" и "0" в true/false
        const booleanValue = computed(() => {
            return props.modelValue === "1" || props.modelValue === true;
        });

        // Обрабатываем изменение свича
        const toggleSwitch = (event) => {
            emit("update:modelValue", event.target.checked ? "1" : "0");
        };

        const labelPositionClass = ref(labelPosition.value === "side" ? "label-side" : "label-top");

        return {
            booleanValue,
            toggleSwitch,
            labelPositionClass
        };
    }
});
</script>

<style lang="scss">
.switch-container {
    user-select: none;
    display: flex;
    cursor: pointer;
    width: -webkit-fill-available;

    &.label-top {
        display: flex;
        flex-direction: column;
    }

    &.label-side {
        display: flex;
        align-items: center;

        .custom-label {
            margin-right: 10px;
            margin-bottom: 0px;
        }
    }

    .custom-label {
        margin-bottom: 8px;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
        margin-right: 10px;
        cursor: pointer;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.4s;
        border-radius: 24px;

        &:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }
    }

    input:checked+.slider {
        background-color: #2c2c2c;

        &:before {
            transform: translateX(26px);
        }
    }
}
</style>
