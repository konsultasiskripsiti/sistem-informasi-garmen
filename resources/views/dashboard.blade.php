<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Dashboard
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Welcome back, {{ Auth::user()->name }}. TailAdmin is now active in your Laravel project.
                </p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-brand-50 px-3 py-1 text-sm font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                TailAdmin + Breeze
            </span>
        </div>
    </x-slot>

    <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 space-y-6 xl:col-span-7">
            <x-ecommerce.ecommerce-metrics />
            <x-ecommerce.monthly-sale />
        </div>
        <div class="col-span-12 xl:col-span-5">
            <x-ecommerce.monthly-target />
        </div>
        <div class="col-span-12">
            <x-ecommerce.statistics-chart />
        </div>
        <div class="col-span-12 xl:col-span-5">
            <x-ecommerce.customer-demographic />
        </div>
        <div class="col-span-12 xl:col-span-7">
            <x-ecommerce.recent-orders />
        </div>
    </div>
</x-app-layout>
