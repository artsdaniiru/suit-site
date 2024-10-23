<template>
  <div :class="['custom-input-container', labelPositionClass]">
    <!-- Условно отображаем лейбл с учетом позиции -->
    <label v-if="labelText" class="custom-label">{{ labelText }}</label>
    <input :type="type" :value="modelValue" @input="updateValue($event.target.value)" :placeholder="placeholderText" :required="required" @blur="validateInput" :disabled="disabled" />
    <!-- Условно отображаем сообщение об ошибке -->
    <span v-if="showError" class="error-message">This field is required</span>
  </div>
</template>

<script>
import { defineComponent, ref, toRefs, watch } from "vue";

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
      validator: value => ["top", "side"].includes(value)
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
    }
  },
  setup(props, { emit }) {
    const { modelValue, labelPosition, required } = toRefs(props);
    const labelPositionClass = ref(labelPosition.value === "side" ? "label-side" : "label-top");
    const showError = ref(false);

    // Метод для обновления значения через emit
    const updateValue = (value) => {
      emit("update:modelValue", value);
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
      updateValue,
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
