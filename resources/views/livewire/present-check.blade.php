<div>
    <div class="container mx-auto max-w-sm">
        <div class="bg-white p-6 rounded-lg mt-3 shadow-lg">
            <div class="grid grid-cols-1 gap-6 mb-6">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Informasi Pegawai</h2>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <p><strong>Nama Pegawai : </strong> {{ Auth::user()->name }}</p>
                        <p><strong>Kantor : </strong>{{ $schedule->branch->name }}</p>
                        <p><strong>Shift : </strong>{{ $schedule->shift->name }}
                            ({{ $schedule->shift->start_time->format('H:i') }} -
                            {{ $schedule->shift->end_time->format('H:i') }}) WIB</p>
                        @if ($schedule->is_wfa)
                            <p class="text-green-500"><strong>Status : </strong>WFA</p>
                        @else
                            <p><strong>Status : </strong>WFO</p>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <h4 class="text-l font-bold mb-2">Check In</h4>
                            <p><strong>{{ $attendance ? $attendance->check_in : '-' }}</p>
                        </div>
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <h4 class="text-l font-bold mb-2">Check Out</h4>
                            <p><strong>{{ $attendance ? $attendance->check_out : '-' }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-2xl font-bold mb-2">Present Check</h2>
                    <div id="map" class="mb-4 rounded-lg border border-gray-300" wire:ignore></div>
                    @if (session()->has('error'))
                        <div style="color: red; padding: 10px; border: 1px solid red; backgroud-color: #fdd;">
                            {{ session('error') }}
                        </div>
                    @endif
                    <form class="row g-3 mt-3" wire:submit="store" enctype="multipart/form-data">
                        <button type="button" onclick="tagLocation()"
                            class="px-4 py-2 bg-blue-500 text-white rounded">Tag Location</button>
                        @if ($insideRadius)
                            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded">Submit
                                Presensi</button>
                        @endif
                    </form>
                    {{-- <form class="row g-3 mt-3" wire:submit="store" enctype="multipart/form-data">
                        <button type="button" onclick="tagLocation()"
                            class="px-4 py-2 bg-blue-500 text-white rounded">Tag Location</button>
                        @if ($insideRadius && !$attendance?->check_out)
                            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded">Submit
                                Presensi</button>
                        @elseif ($attendance?->check_out)
                            <p class="text-red-500">Anda sudah melakukan check out hari ini.</p>
                        @endif
                    </form> --}}
                </div>

            </div>
        </div>

    </div>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        let map;
        let lat;
        let lng;
        const branch = [{{ $schedule->branch->latitude }}, {{ $schedule->branch->longitude }}];
        const radius = {{ $schedule->branch->radius }};
        let component;
        let marker;
        document.addEventListener('livewire:initialized', function() {
            component = @this;
            map = L.map('map').setView([{{ $schedule->branch->latitude }}, {{ $schedule->branch->longitude }}],
                15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            const circle = L.circle(branch, {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.5,
                radius: radius
            }).addTo(map);
        })


        function tagLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    lat = position.coords.latitude;
                    lng = position.coords.longitude;

                    if (marker) {
                        map.removeLayer(marker);
                    }

                    marker = L.marker([lat, lng]).addTo(map);
                    map.setView([lat, lng], 15);

                    if (isWithinRadius(lat, lng, branch, radius)) {
                        component.set('insideRadius', true);
                        component.set('latitude', lat);
                        component.set('longitude', lng);
                    }
                })
            } else {
                alert('Tidak bisa get location');
            }
        }

        function isWithinRadius(lat, lng, center, radius) {
            const is_wfa = {{ $schedule->is_wfa }}
            if (is_wfa) {
                return true;
            } else {
                let distance = map.distance([lat, lng], center);
                return distance <= radius;
            }

        }
    </script>

</div>
