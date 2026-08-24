<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center gap-8 px-6 text-center" style="background: var(--surface-page)">
        <div>
            <h1 class="text-4xl font-extrabold text-brand-700">مدير الديون</h1>
            <p class="mt-3 max-w-md text-lg" style="color: var(--text-secondary)">
                تابع ديونك وديون الناس عليك بسهولة — عملاء، كفلاء، أقساط، ووصولات جاهزة للطباعة.
            </p>
        </div>

        <div class="flex gap-3">
            <Link :href="route('register')" class="rounded-pill bg-brand-600 px-8 py-3 font-bold text-white shadow-lg transition hover:bg-brand-700">
                ابدأ تجربتك المجانية
            </Link>
            <Link :href="route('login')" class="rounded-pill border-2 border-brand-600 px-8 py-3 font-bold text-brand-700 transition hover:bg-brand-50">
                تسجيل الدخول
            </Link>
        </div>
    </div>
</template>
