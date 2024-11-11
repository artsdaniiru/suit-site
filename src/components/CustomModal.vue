<template>
    <div v-if="modelValue" :class="['modal', randomClass]" @mousedown="handleBackdropMouseDown" @mouseup="closeModalOutside">
        <div @click.stop="" class="modal-window">
            <div class="head">
                <h3 v-if="title != ''">{{ title }}</h3>
                <img class="close" src="../assets/icons/close.svg" alt="close" @click="closeModal">
            </div>

            <div class="content">
                <slot></slot>
            </div>

        </div>
    </div>
</template>
<script>
import { defineComponent, watch, ref, onMounted } from 'vue';

export default defineComponent({
    name: "CustomModal",
    props: {
        modelValue: {
            type: Boolean,
            required: true,
        },
        title: {
            type: String,
            required: false,
            default: ''
        },
        in_modal: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {

        const randomClass = ref('');

        // Генерация случайного имени класса при монтировании компонента
        onMounted(() => {
            randomClass.value = `modal-${Math.random().toString(36).substring(2, 8)}`;
        });

        let isMouseDownOutside = false;
        const handleBackdropMouseDown = (event) => {
            isMouseDownOutside = event.target.classList.contains(randomClass.value);
        };

        const closeModalOutside = () => {
            if (isMouseDownOutside) {
                emit("update:modelValue", false);
            }
            isMouseDownOutside = false;
        };

        const closeModal = () => {
            emit('update:modelValue', false);
        };


        // Следим за изменениями modelValue
        watch(() => props.modelValue, (newValue) => {
            if (!props.in_modal) {
                if (newValue) {
                    document.body.classList.add('no-scroll');
                } else {
                    document.body.classList.remove('no-scroll');
                }
            }
        });

        return {
            closeModal,
            handleBackdropMouseDown,
            closeModalOutside,
            randomClass
        };
    }
});
</script>

<style lang="scss" scoped>
.modal-window {
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 32px;
    max-width: 90%;
    min-width: 400px;
    max-height: 90%;
    box-shadow: 0 4px 4px -4px rgba(12, 12, 13, 0.05), 0 16px 32px -4px rgba(12, 12, 13, 0.1);
    background: #fff;
    position: relative;
    margin: auto;

    display: flex;
    flex-direction: column;
    gap: 8px;


    .head {
        display: flex;

        h3 {
            margin-top: 0px;
            margin-bottom: 0px;
        }


        .close {
            margin-right: 0px;
            margin-left: auto;
            cursor: pointer;
        }
    }


    .content {
        width: -webkit-fill-available;
        height: -webkit-fill-available;
        overflow-y: auto;

    }
}
</style>
