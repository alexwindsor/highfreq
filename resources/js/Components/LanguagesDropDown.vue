<script setup>
import { ref, watch } from 'vue'
import { logs } from '@/logs.js'

const props = defineProps({
    required: Boolean,
    language_id: Number,
    language_name: String,
})

watch(props, () => {
    language_id = props.language_id
    language_name = props.language_name
})

const emit = defineEmits(['languageId', 'languageName'])

var language_id = ref(props.language_id)
var language_name = ref(props.language_name.replace('.', ''))

function type() {
    emit('languageId', 0)
    emit('languageName', language_name)
}

function pickLanguage(id, name) {
    language_id = id
    emit('languageId', id)
    language_name = name
    emit('languageName', name)
}

</script>

<template>

Language:<br>
<input
    type="text"
    v-model="language_name"
    class="border-2 border-black rounded text-black text-sm p-1 mr-2 w-full"
    @keyup="type"
    :placeholder="props.required ? '' : 'All Languages'"
>

<div class="border-2 border-black rounded h-16 bg-white text-black text-sm overflow-y-scroll">
    <div class="cursor-pointer">
    </div>
    <div v-for="language in logs.languages" class="cursor-pointer">
        <span
            v-if="language.name.toLowerCase().includes(language_name.toLowerCase())"
            class="inline-block w-full"
            :class="{'bg-black text-white': language_id === language.id}"
            @click="pickLanguage(language.id, language.name)"
            >
            {{ language.name }}
        </span>
    </div>
</div>

<!-- language errors -->
<div v-if="logs.newlog.errors.language_name" class="text-sm text-red-300">Please choose a language</div>

</template>
