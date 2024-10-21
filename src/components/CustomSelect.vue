<template>
  <div :class="['custom-select-container', labelPositionClass]" @click="toggleDropdown" v-click-out-side="() => isOpen = false">
    <!-- Условно отображаем лейбл с учетом позиции -->
    <label v-if="labelText" class="custom-label">{{ labelText }}</label>
    <div class="select-box">
      <div class="selected-value">{{ selectedValue || 'Select an option' }}</div>
      <span class="arrow" :class="{ open: isOpen }">
        <img src="../assets/icons/chevron-dw.svg" alt="chevron">
      </span>

      <ul :class="['options-list', { open: isOpen }]" v-if="isOpen">
        <li :class="{ selected: value == selectedValue }" v-for="(value, index) in values" :key="index" class="option-item" @click="selectOption(index)">
          {{ value }}
        </li>
      </ul>
    </div>


  </div>
</template>
<!-- eslint-disable -->
<script>
import { defineComponent, ref, toRefs } from "vue";

export default defineComponent({
  name: "CustomSelect",
  props: {
    values: {
      type: Object,
      required: true
    },
    selectedValue: {
      type: String,
      required: false,
      default: ""
    },
    labelText: {
      type: String,
      required: false,
      default: ""
    },
    labelPosition: {
      type: String,
      required: false,
      default: "top", // Позиция лейбла: "top" (сверху) или "side" (сбоку)
      validator: value => ["top", "side"].includes(value)
    }
  },
  setup(props, { emit }) {
    const { labelPosition } = toRefs(props);
    const selectedValue = ref(props.selectedValue);
    const isOpen = ref(false);

    const toggleDropdown = () => {
      isOpen.value = !isOpen.value;
    };

    const selectOption = (value) => {
      selectedValue.value = value;
      emit("update", value);
      isOpen.value = false; // Закрываем после выбора
    };

    // Класс для позиции лейбла
    const labelPositionClass = ref(labelPosition.value === "side" ? "label-side" : "label-top");

    return {
      selectedValue,
      isOpen,
      toggleDropdown,
      selectOption,
      labelPositionClass
    };
  }
});
</script>

<style scoped lang="scss">
$select-border-color: #d9d9d9;
$select-border-radius: 8px;
$select-bg-color: #fff;
$select-font-size: 16px;
$hover-color: #f0f0f0;
$focus-border-color: #adadad;

.custom-select-container {
  min-width: 200px;

  &.label-top {
    display: flex;
    flex-direction: column;
  }

  &.label-side {
    display: flex;
    align-items: center;

    .custom-label {
      margin-right: 10px; // Отступ для лейбла сбоку
      margin-bottom: 0px;
    }

    .select-box {
      flex-grow: 1; // Селект занимает оставшееся пространство
    }
  }

  .custom-label {
    margin-bottom: 8px;
  }

  .select-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: $select-bg-color;
    border: 1px solid $select-border-color;
    padding: 10px;
    font-size: $select-font-size;
    border-radius: $select-border-radius;
    cursor: pointer;
    transition: border-color 0.3s ease;
    position: relative;

    &:hover {
      border-color: $focus-border-color;
    }

    .arrow {
      transition: transform 0.3s ease;

      &.open {
        transform: rotate(180deg);
      }
    }


    .options-list {
      position: absolute;
      width: 100%; // Полная ширина как у select-box
      background-color: $select-bg-color;
      border: 1px solid $select-border-color;
      border-radius: $select-border-radius;
      margin-top: 5px;
      list-style: none;
      padding: 0;
      max-height: 200px; // Ограничение по высоте
      overflow-y: auto;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      opacity: 0;
      transform: translateY(-10px);
      transition: all 0.3s ease;
      z-index: 100;
      top: 30px;
      left: 0px;

      li {
        padding: 10px;
        cursor: pointer;
        transition: background-color 0.3s ease;

        &:hover,
        &.selected {
          background-color: $hover-color;
        }

        &+li {
          border-top: 1px solid #e0e0e0;
        }
      }

      // Когда класс open активен, выпадающий список отображается
      &.open {
        opacity: 1;
        transform: translateY(0);
      }
    }
  }


}
</style>
