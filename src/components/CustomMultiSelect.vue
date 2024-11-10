<template>
  <div :class="['custom-select-container', labelPositionClass]">
    <!-- Отображаем лейбл, если передан -->
    <label v-if="labelText" class="custom-label">{{ labelText }}</label>

    <!-- Основной блок селекта -->
    <div ref="selectBox" class="select-box" :class="{ disabled: disabled }" :style="{ width: width }" @click="toggleDropdown" v-click-out-side="() => isOpen = false">
      <!-- Отображаем выбранные значения -->
      <div class="selected-value" v-if="selectedValue.length">
        <span v-for="key in visibleSelectedValues" :key="key">{{ values[key] }}</span>
        <span v-if="remainingCount > 0">+{{ remainingCount }}</span>
      </div>
      <div class="selected-value not-selected" v-else-if="!notSelect">選択して下さい</div>
      <span class="arrow" :class="{ open: isOpen }">
        <img src="../assets/icons/chevron-dw.svg" alt="chevron">
      </span>

      <!-- Список опций -->
      <transition name="slide">
        <ul :class="['options-list', { open: isOpen }]" v-if="isOpen">
          <li v-for="(label, key) in values" :key="key" class="option-item" :class="{ selected: isSelected(key) }" @click.stop="selectOption(key)">
            {{ label }}
          </li>
        </ul>
      </transition>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, toRefs, watch, computed } from "vue";

export default defineComponent({
  name: "CustomMultiSelect",
  props: {
    values: {
      type: Object,
      required: true
    },
    modelValue: {
      type: Array,
      required: false,
      default: () => [] // Массив для хранения выбранных ключей
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
    disabled: {
      type: Boolean,
      required: false,
      default: false
    },
    width: {
      type: String,
      required: false,
      default: "-webkit-fill-available"
    }
  },
  setup(props, { emit }) {
    const { labelPosition, modelValue } = toRefs(props);
    const selectedValue = ref([...props.modelValue]); // Используем массив для мультивыбора
    const isOpen = ref(false);

    // Следим за изменениями modelValue и обновляем selectedValue
    watch(modelValue, (newValue) => {
      selectedValue.value = [...newValue];
    });

    const toggleDropdown = () => {
      if (!props.disabled) {
        isOpen.value = !isOpen.value;
      }
    };

    const selectOption = (key) => {
      // Если элемент уже выбран — убираем его из массива, иначе добавляем
      if (selectedValue.value.includes(key)) {
        selectedValue.value = selectedValue.value.filter(val => val !== key);
      } else {
        selectedValue.value.push(key);
      }
      emit("update:modelValue", selectedValue.value);
    };

    const isSelected = (key) => {
      return selectedValue.value.includes(key);
    };

    // Класс для позиции лейбла
    const labelPositionClass = ref(labelPosition.value === "side" ? "label-side" : "label-top");


    const maxVisibleItems = ref(2);
    const visibleSelectedValues = computed(() => selectedValue.value.slice(0, maxVisibleItems.value));
    const remainingCount = computed(() => selectedValue.value.length - maxVisibleItems.value);

    return {
      selectedValue,
      isOpen,
      toggleDropdown,
      selectOption,
      isSelected,
      remainingCount,
      labelPositionClass,
      visibleSelectedValues
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
  user-select: none;
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
    padding: 0px 12px;
    height: 40px;
    font-size: $select-font-size;
    border-radius: $select-border-radius;
    cursor: pointer;
    transition: border-color 0.3s ease;
    position: relative;
    min-width: 130px;

    &:hover {
      border-color: $focus-border-color;
    }

    &.disabled {
      background: #d9d9d9;
      color: #b3b3b3;

      &:hover {
        border-color: $select-border-color;
      }
    }

    .arrow {
      transition: transform 0.3s ease;

      &.open {
        transform: rotate(180deg);
      }
    }

    .selected-value {

      &.not-selected {
        color: #b3b3b3 !important;
      }

      display: flex;
      gap: 4px;
      flex-wrap: wrap;

      span {
        font-size: 12px;
        padding: 2px 4px;
        color: #f5f5f5;
        background: #2c2c2c;

        border-radius: 4px;
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
      transform-origin: top;
      transition: transform .2s ease-in-out;
      z-index: 100;
      top: 30px;
      left: 0px;

      li {
        transition: opacity 0.3s ease;
        padding: 10px;
        cursor: pointer;
        transition: background-color 0.5s ease;

        &:hover,
        &.selected {
          background-color: $hover-color;
        }

        &+li {
          border-top: 1px solid #e0e0e0;
        }
      }
    }
  }
}

.slide-enter-from,
.slide-leave-to {
  transform: scaleY(0);

  li {
    opacity: 0;
  }
}
</style>
