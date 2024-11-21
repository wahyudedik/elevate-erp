<x-filament-panels::page>
    @pushOnce('scripts')
        @vite('resources/js/cctv.js')
    @endPushOnce
    
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">CCTV Monitoring</h2>
        <button x-data="" x-on:click="$dispatch('open-modal', { id: 'cctv-info' })"
            class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 text-black rounded-lg hover:bg-blue-600">
            <x-heroicon-o-information-circle class="w-5 h-5 mr-2" />
            Information
        </button>
    </div>

    <!-- Rest of your existing CCTV content -->
    <div class="mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            {{-- <h2 class="text-2xl font-bold mb-4">Add New Camera</h2> --}}
            <form wire:submit="create">
                {{ $this->form }}
                <br>
                <button type="submit" class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Add Camera
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        @foreach ($cameras as $camera)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-center mb-3 border-b pb-2">
                    <h3 class="text-xl font-semibold text-gray-800">{{ $camera['name'] }}</h3>
                    <span
                        class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">{{ $camera['location'] }}</span>
                </div>
                <div class="aspect-video">
                    <video id="camera-{{ $camera['id'] }}" class="w-full h-full" autoplay controls
                        src="{{ $camera['stream_url'] }}">
                    </video>
                </div>
                <p class="mt-2 text-sm text-gray-600">{{ $camera['description'] }}</p>
            </div>
        @endforeach
    </div>

    <!-- Info Modal -->
    <x-filament::modal id="cctv-info" width="lg">
        <x-slot name="header">
            <h2 class="text-xl font-bold">CCTV Information</h2>
        </x-slot>

        <div class="space-y-4 p-4">
            <div class="prose max-w-none">
                <h3 class="text-lg font-semibold">Camera Guidelines</h3>
                <ul class="list-disc pl-4 space-y-2">
                    <li>All cameras must be properly configured with RTSP/HLS streams</li>
                    <li>Ensure camera locations are clearly labeled</li>
                    <li>Regular maintenance is required every 3 months</li>
                    <li>Key features to implement:
                        <ol>
                            <li>RTSP/HLS stream support</li>
                            <li>Multiple camera views</li>
                            <li>Camera controls (pan/tilt/zoom if supported)</li>
                            <li>Recording capabilities</li>
                            <li>Motion detection alerts</li>
                            <li>Access control based on user permissions</li>
                        </ol>
                    </li>
                </ul>

                <h3 class="text-lg font-semibold mt-4">Usage Instructions</h3>
                <ul class="list-disc pl-4 space-y-2">
                    <li>Double click video feed for fullscreen view</li>
                    <li>Use controls to adjust volume if audio is enabled</li>
                    <li>Report any issues immediately to IT support</li>
                </ul>

                <h3 class="text-lg font-semibold mt-4">Security Notes</h3>
                <ul class="list-disc pl-4 space-y-2">
                    <li>Access to camera feeds is logged and monitored</li>
                    <li>Do not share camera access credentials</li>
                    <li>Follow company privacy guidelines when monitoring</li>
                </ul>
            </div>
        </div>

        <x-slot name="footer">
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'cctv-info' })">
                Close
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
