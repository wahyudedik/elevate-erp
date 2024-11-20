<!-- Wrap everything in a single root div -->
<div>
    <!-- Card dengan tulisan berjalan -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-4 w-full">
        <div class="overflow-hidden whitespace-nowrap w-full border-2 border-gray-300 rounded-lg">
            <div class="animate-marquee inline-block w-full" style="animation: marquee 20s linear infinite;">
                <b><strong>
                        <h1>{{ $dangernews->description ?? 'No Data' }}</h1>
                    </strong></b>
            </div>
        </div>
    </div>
    <style>
        @keyframes marquee {
            0% {
                transform: translateX(100%);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        .animate-marquee {
            animation: marquee 20s linear infinite;
        }
    </style>

    <!-- Section Berita -->
    <section id="news" class="news-section">
        <!-- Card untuk berita -->
        @foreach ($news as $new)
            <div x-data="{ isOpen: true, isMinimized: false }"
                class="bg-white rounded-lg shadow-md mb-6 hover:shadow-lg transition-shadow duration-300">
                <div class="flex justify-between items-center p-4 border-b bg-gray-50">
                    <h3 class="font-bold text-xl text-blue-600">{{ $new->title }}</h3>
                    <div class="flex space-x-2">
                        <button @click="isMinimized = !isMinimized"
                            class="text-gray-500 hover:text-blue-600 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path x-show="isMinimized" fill-rule="evenodd"
                                    d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
                                <path x-show="!isMinimized" fill-rule="evenodd"
                                    d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                        <button @click="isOpen = false"
                            class="text-gray-500 hover:text-red-600 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95">
                    <div x-show="!isMinimized" x-transition class="p-6">
                        <!-- Gambar dengan efek hover -->
                        <div class="flex justify-center mb-6">
                            <img src="{{ asset('storage/' . $new->image) }}" alt="Announcement Image"
                                class="rounded-lg shadow-md max-w-full h-auto hover:shadow-xl transition-shadow duration-300 object-cover"
                                style="max-height: 100px; width: auto;">
                        </div>
                        <!-- Konten berita dengan styling yang lebih baik -->
                        <div class="prose max-w-none text-gray-700 leading-relaxed">
                            {!! $new->description !!}
                        </div>
                        <!-- Timestamp dan metadata -->
                        <div class="mt-4 pt-4 border-t text-sm text-gray-500 flex justify-between items-center">
                            <span>Dipublikasikan: {{ $new->created_at->format('d M Y, H:i') }}</span>
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full">Pengumuman ERP</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <!-- Pagination dengan styling yang lebih baik -->
        <div class="mt-6 flex justify-center">
            {{ $news->links() }}
        </div>
    </section>
</div>
