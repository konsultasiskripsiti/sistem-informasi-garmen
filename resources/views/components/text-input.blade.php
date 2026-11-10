@props(['disabled' => false])

{{-- Padding, border, dan focus ring ditulis eksplisit karena plugin @tailwindcss/forms tidak dipakai (Tailwind v4). --}}
<input @disabled($disabled) {{ $attributes->merge(['class' => 'h-11 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600/20']) }}>
