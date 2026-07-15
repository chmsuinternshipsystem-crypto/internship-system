<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-150 ease-in-out btn-primary']) }}>
    {{ $slot }}
</button>
