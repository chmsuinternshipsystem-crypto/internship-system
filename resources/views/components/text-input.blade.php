@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 bg-gray-50 focus:bg-white focus:border-emerald-600 focus:ring-emerald-600 rounded-lg shadow-sm transition-all duration-200', 'maxlength' => '255']) }}>
