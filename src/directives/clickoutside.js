export default {
    beforeMount(el, binding) {
        el.clickOutsideEvent = (event) => {
            // Проверяем, что клик был за пределами элемента
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event); // вызываем функцию-обработчик из директивы
            }
        };
        document.addEventListener("mousedown", el.clickOutsideEvent); // прослушиваем mousedown
    },
    unmounted(el) {
        document.removeEventListener("mousedown", el.clickOutsideEvent);
    },
};