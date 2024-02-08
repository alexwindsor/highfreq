<script setup>
import {Link} from '@inertiajs/vue3'
import {base_url} from '@/base_url.js'

defineProps({
    page: String,
    user: Object,
})


</script>

<template>
    <main>
        <header class="border-b border-black py-3 sm:py-6 text-4xl sm:text-6xl lg:text-6xl text-center">
            <Link
                :href="base_url"
                style="font-family: 'Cutive Mono', monospace;"
                class="hidden sm:block"
            >
                High Frequency Radio Logs
            </Link>
        </header>

        <!-- Mobile version -->
        <div class="sm:hidden border-b border-black bg-gray-200 grid grid-cols-3 px-3 py-1 mb-1">
            <div v-if="user" class="text-center">
                <Link v-if="page !== 'logs'" :href="base_url + 'logs'">logs</Link>
            </div>

            <div v-if="!user" class="text-center">
                <Link v-if="page !== 'logs'" :href="base_url">logs <span class="text-xs">(login required)</span></Link>
            </div>

            <div class="text-center">
                <Link :href="base_url + 'shortWaveInfoData'">broadcast stations</Link>
            </div>

            <div class="text-center">
                <Link :href="base_url + 'bands'">bands</Link>
            </div>
        </div>

        <div class="sm:hidden grid grid-cols-5 mx-3">
            <div class="col-span-3 text-xs">
                <span v-if="user">Welcome: <b>{{ user.name }}</b></span>
            </div>

            <div class="text-xs text-right">
                <Link v-if="user" :href="base_url + 'edit_profile'">Profile</Link>
                <Link v-if="! user" class="mx-4" :href="base_url + 'edit_profile'">Log in</Link>
            </div>

            <div class="text-xs text-right">
                <Link v-if="user" :href="base_url + 'logout'" method="post" as="button">Log Out</Link>
                <Link v-if="! user" :href="base_url + 'register'">Register</Link>
            </div>
        </div>

        <!-- Desktop version -->
        <div class="hidden sm:grid sm:grid-cols-2 border-b border-black bg-gray-200 text-xl">
            <div class="ml-16">
                <Link :href="base_url + 'logs'" class="m-4 inline-block">logs<span v-if="! user" class="text-sm"> (login required)</span>
                </Link>
                <Link :href="base_url + 'shortWaveInfoData'" class="m-4 inline-block">broadcast stations</Link>
                <Link :href="base_url + 'bands'" class="m-4 inline-block">bands</Link>
            </div>

            <div class="text-right mr-16">
                <span v-if="user" class="text-sm mr-16">Welcome: <b>{{ user.name }}</b></span>
                <Link v-if="user" :href="base_url + 'edit_profile'" class="m-4 inline-block">Edit Profile</Link>
                <Link v-if="! user" :href="base_url + 'login'" class="m-4 inline-block">Log In</Link>
                <Link v-if="user" :href="base_url + 'logout'" class="m-4 inline-block">Log Out</Link>
                <Link v-if="! user" :href="base_url + 'register'" class="m-4 inline-block">Register</Link>
            </div>
        </div>

        <!--    <h1 v-if="page.length" class="text-2xl sm:text-3xl ml-4 sm:ml-14 mb-3 sm:mb-3 mt-6 sm:mt-12">{{ page }}</h1>-->

        <div class="sm:mx-3">

            <article class="mt-5 sm:mt-6 mb-8 sm:mb-16">
                <slot/>
            </article>

        </div>

    </main>
</template>
