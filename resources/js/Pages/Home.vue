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
            You need to <Link v-if="! user" class="underline" :href="base_url + 'edit_profile'">log in</Link> to be able to view the logs page and submit logs:
        </div>

        <img src="belka-dx.jpg" alt="Belka DX Shortwave Receiver" class="mx-auto my-16 border border-black">

    </div>


    </Layout>


</template>

