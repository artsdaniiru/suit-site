<template>
  <div :class="['custom-select-container', labelPositionClass]">
    <!-- Условно отображаем лейбл с учетом позиции -->
    <label v-if="labelText" class="custom-label">{{ labelText }}</label>
    <div class="select-box" :class="{ disabled: disabled }" :style="{ width: width }" @click="toggleDropdown" v-click-out-side="() => isOpen = false">
      <!-- Отображаем выбранное значение, либо скрываем текст "選択して下さい" при notSelect -->
      <div class="selected-value" v-if="selectedValue !== '' && values[selectedValue]">{{ values[selectedValue] }}</div>
      <div class="selected-value not-selected" v-else-if="!notSelect">選択して下さい</div>
      <span class="arrow" :class="{ open: isOpen }">
        <img src="../assets/icons/chevron-dw.svg" alt="chevron">
      </span>

      <!-- Добавляем анимацию через <transition> -->
      <transition name="slide">
        <ul :class="['options-list', { open: isOpen }]" v-if="isOpen">
          <li :class="{ selected: index === selectedValue }" v-for="(value, index) in values" :key="index" class="option-item" @click.stop="selectOption(index)">
            {{ value }}
          </li>
        </ul>
      </transition>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, toRefs, watch, onMounted } from "vue";

export default defineComponent({
  name: "CustomSelect",
  props: {
    values: {
      type: Object,
      required: true
    },
    modelValue: {
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
    },
    notSelect: {
      type: Boolean,
      required: false,
      default: false
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
    const { labelPosition, modelValue, values, notSelect } = toRefs(props);
    const selectedValue = ref(props.modelValue);
    const isOpen = ref(false);

    // Следим за изменением modelValue, чтобы обновить selectedValue
    watch(modelValue, (newValue) => {
      selectedValue.value = newValue;
    });

    // При монтировании проверяем, есть ли modelValue и если notSelect == true
    onMounted(() => {
      if (!selectedValue.value && notSelect.value && Object.keys(values.value).length > 0) {
        // Устанавливаем первое значение из values как выбранное
        const firstValue = Object.keys(values.value)[0];
        selectedValue.value = firstValue;
        emit("update:modelValue", firstValue);
      }
    });

    const toggleDropdown = () => {
      if (!props.disabled) {
        isOpen.value = !isOpen.value;
      }
    };

    const selectOption = (value) => {
      if (value == selectedValue.value && !props.notSelect) {
        selectedValue.value = "";
      } else {
        selectedValue.value = value;
      }

      emit("update:modelValue", selectedValue.value);
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
        color: #b3b3b3;
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
      // opacity: 0;
      // transform: translateY(-10px);
      // transition: all 0.3s ease;
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
