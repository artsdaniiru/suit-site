<template>
  <div :class="['custom-input-container', labelPositionClass]">
    <!-- Условно отображаем лейбл с учетом позиции -->
    <label v-if="labelText" class="custom-label">{{ labelText }}</label>
    <input :class="{ 'danger': showError }" :type="type" :value="formattedValue" @input="onInput" @blur="validateInput" :placeholder="placeholderText" :maxlength="computedMaxLength" :required="required" :disabled="disabled" />
    <!-- Условно отображаем сообщение об ошибке -->
    <span v-if="showError" class="error-message">このフィールドは必須です</span>
  </div>
</template>

<script>
import { defineComponent, ref, toRefs, watch, computed } from "vue";

export default defineComponent({
  name: "CustomInput",
  props: {
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
      default: "top",
      validator: (value) => ["top", "side"].includes(value)
    },
    placeholderText: {
      type: String,
      required: false,
      default: ""
    },
    required: {
      type: Boolean,
      required: false,
      default: false
    },
    type: {
      type: String,
      required: false,
      default: "text"
    },
    disabled: {
      type: Boolean,
      required: false,
      default: false
    },
    maxLength: {
      type: Number,
      required: false,
      default: null // Если не указано, максимальная длина не ограничена
    }
  },
  setup(props, { emit }) {
    const { modelValue, labelPosition, required, type, maxLength } = toRefs(props);
    const labelPositionClass = ref(labelPosition.value === "side" ? "label-side" : "label-top");
    const showError = ref(false);

    // Вычисляемое максимальное значение для длины ввода
    const computedMaxLength = computed(() => {
      if (type.value === "credit-card") {
        return 19; // 16 цифр + 3 пробела
      }
      return maxLength.value || undefined; // Либо значение из пропсов, либо не ограничено
    });

    // Форматированное значение для отображения (только для типа "credit-card")
    const formattedValue = computed({
      get() {
        return type.value === "credit-card"
          ? modelValue.value
            .replace(/\D/g, "") // Удалить нецифровые символы
            .replace(/(.{4})/g, "$1 ") // Добавить пробел после каждых 4 цифр
            .trim() // Убрать пробелы в конце
          : modelValue.value;
      },
      set(value) {
        emit("update:modelValue", cleanValue(value));
      }
    });

    // Очистка значения (удаление пробелов для хранения)
    const cleanValue = (value) => {
      return value.trim(); // Удалить все пробелы
    };

    // Обработка ввода
    const onInput = (event) => {
      const rawValue = event.target.value;
      emit("update:modelValue", cleanValue(rawValue));
    };

    // Метод для валидации поля
    const validateInput = () => {
      if (required.value && !modelValue.value) {
        showError.value = true;
      } else {
        showError.value = false;
      }
    };

    // Следим за изменением значения
    watch(modelValue, (newValue) => {
      if (newValue && showError.value) {
        showError.value = false;
      }
    });

    return {
      labelPositionClass,
      formattedValue,
      computedMaxLength,
      onInput,
      validateInput,
      showError
    };
  }
});
</script>

<style scoped lang="scss">
.custom-input-container {
  min-width: 100px;
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

  .error-message {
    color: red;
    font-size: 12px;
    margin-top: 4px;
  }

  input {
    width: -webkit-fill-available;
  }
}
</style>
