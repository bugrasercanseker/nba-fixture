<script setup>
import {Head, router} from '@inertiajs/vue3';

const generateFixture = () => {
    router.post(route('fixture.generate'))
}
</script>

<template>
    <Head title="Welcome" />

    <div class="h-screen flex flex-col space-y-4 justify-center items-center">
        <span class="text-3xl font-bold">Welcome to Insider NBA League</span>

        <span class="text-md font-medium">To generate fixture and start games, please use button below.</span>

        <button @click="generateFixture()" class="px-8 py-2 bg-red-700 text-white animate-pulse rounded-full">Start</button>
    </div>
</template>
