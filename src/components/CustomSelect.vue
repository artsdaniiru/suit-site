<template>
    <div>
      <!-- <label for="custom-select">Выберите значение:</label> -->
      <select v-model="selectedValue" @change="emitSelectedValue">
        <option v-for="value in values" :key="value" :value="value">{{ value }}</option>
      </select>
    </div>
  </template>
  
  <script>
  import { defineComponent, ref, toRefs } from "vue";
  
  export default defineComponent({
    name: "CustomSelect",
    props: {
      values: {
        type: Array,
        required: true
      },
      defaultValue: {
        type: Number,
        required: true
      }
    },
    setup(props, { emit }) {
      // Используем ref для реактивного отслеживания выбранного значения
      const selectedValue = ref(props.defaultValue);
  
      // Метод для эмита события с выбранным значением
      const emitSelectedValue = () => {
        emit("update", selectedValue.value);
      };
  
      return {
        ...toRefs(props),
        selectedValue,
        emitSelectedValue
      };
    }
  });
  </script>
  
  <style scoped>
  /* Добавьте необходимые стили */
  </style>
  