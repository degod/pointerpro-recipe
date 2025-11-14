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
    type: {
        type: String,
        default: "text",
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
        <input
            v-model="model"
            :type="type"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-300"
            :required="is_required"
        />
        <small class="text-red-500" v-if="message">
            {{ message ? message[0] : "" }}
        </small>
    </div>
</template>