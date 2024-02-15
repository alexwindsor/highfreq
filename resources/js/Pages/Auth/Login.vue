<script setup>
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Pages/Layout.vue'
import { base_url } from '@/base_url.js'
import { reactive } from 'vue'

defineProps({
    errors: Object
})

const form = reactive({
    email: 'alexwindsormusic@gmail.com',
    password: 'password'
});

function login() {
    router.post(base_url + 'login', form)
}

</script>

<template>
    <Head title="HF / login" />

<Layout page="Login">

    <div v-if="errors.authentication" class="text-red-500">{{ errors.authentication }}</div>

<form @submit.prevent="login">
    <div class="m-auto w-full sm:w-2/3 lg:w-1/3 mt-16">
        <div class="mb-3">
            Email:
            <br>
            <input type="email" v-model="form.email" class="block w-full border-2 border-black rounded p-1 text-black">
            <div v-if="errors.email" class="text-xs text-red-500">{{ errors.email }}</div>
        </div>

        <div class="mb-3">
            Password:
            <br>
            <input type="password" v-model="form.password" class="block w-full border-2 border-black rounded p-1 text-black">
            <div v-if="errors.password" class="text-xs text-red-500">{{ errors.password }}</div>
        </div>

        <div class="mb-3">
            <label>
                <input type="checkbox" v-model="form.remember" class="mr-3 border border-black"> Remember me on this computer ?
            </label>
        </div>

        <button class="border-2 border-black rounded p-1 mt-2 text-black text-2xl" type="submit">Login</button>
    </div>
</form>



</Layout>
</template>
