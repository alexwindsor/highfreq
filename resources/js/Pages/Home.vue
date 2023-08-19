<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Layout from '@/Pages/Layout.vue'
import { reactive } from 'vue'
import { base_url } from '@/base_url.js'

defineProps({
    user: Object,
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
    <Head title="HighFrequency" />
    <Layout page="" :user="user">

    <div class="sm:mx-32">
        <div class="mb-5">
            Welcome to HighFreq, a site for short wave listeners to identify and log broadcast stations that they hear, and more resources. This website is only recently up and running and there are many more features coming.
        </div>

        <div v-if="user" class="mb-5">
            You are logged in as {{ user.name }}
        </div>

        <div v-if="! user" class="mb-5">
            You need to log in to be able to view the logs page and submit logs:

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
                    <br>
                    <button class="border-2 border-black rounded p-1 text-black text-2xl" type="submit">Login</button>
                </div>
            </form>
        </div>

        <img src="belka-dx.jpg" alt="Belka DX Shortwave Receiver" class="mx-auto my-16 border border-black">

    </div>


    </Layout>


</template>

