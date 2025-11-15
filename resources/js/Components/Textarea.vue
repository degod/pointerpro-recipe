<script setup>
const model = defineModel({
    type: null,
    required: true,
});

defineProps({
    label: {
        type: String,
        required: true,
    },
    placeholder: {
        type: String,
        default: "",
    },
    is_required: {
        type: Boolean,
        default: false,
    },
    message: Array,
});
</script>

<template>
    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">
            {{ label }}
        </label>
        <textarea
            v-model="model" :placeholder="placeholder" :required="is_required"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-300"
        ></textarea>
        <small class="text-red-500" v-if="message">
            {{ message ? message[0] : "" }}
        </small>
    </div>
</template>