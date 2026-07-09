@props(['href'])

<a href="{{ $href }}" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105 mb-6 w-fit">
    <i class="fas fa-arrow-left mr-2"></i> {{ $slot }}
</a>