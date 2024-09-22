<div>
    {{-- @dd($schedules) --}}
    <div class="container mx-auto">
        <div class="bg-white p-6 rounded-lg mt-3 shadow-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Informasi Karyawan</h2>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <p class="mb-2"><strong>Nama Karyawan : </strong>{{ Auth::user()->name }}</p>
                        <p class="mb-2"><strong>Kantor : </strong>{{ $schedules->company->name }}</p>
                        <p class="mb-2"><strong>Shift : </strong>{{ $schedules->shift->name }}
                            ({{ \Carbon\Carbon::parse($schedules->shift->start_time)->format('H:i') }} -
                            {{ \Carbon\Carbon::parse($schedules->shift->end_time)->format('H:i') }}) WIB</p>
                    </div>
                </div>
                <div>
                    <h2 class="text-2xl font-bold mb-2">Informasi Lokasi</h2>
                    <div id="map" class="mb-4 rounded-lg border border-gray-300"></div>
                    <button type="button" onclick="checkLocation" class="px-4 py-2 bg-blue-500 text-white rounded">Tag
                        Location</button>
                    <button type="button" onclick="isWithinRadius" class="px-4 py-2 bg-green-500 text-white rounded">Submit Present</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const map = L.map('map').setView([{{ $schedules->company->latitude }}, {{ $schedules->company->longitude }}], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        const center = L.latLng({{ $schedules->company->latitude }},
            {{ $schedules->company->longitude }}); //koordinat company
        const marker = L.marker(center).addTo(map);
        marker.bindPopup('{{ $schedules->company->name }}').openPopup();
        map.setView(center, 13);

        const radius = {{ $schedules->company->radius }}; // Radius in meters

        const circle = L.circle(center, {
            radius: radius,
            color: 'red',
            fillColor: 'red',
            fillOpacity: 0.2
        }).addTo(map);

        function checkLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;

                    if (marker) {
                        map.removeLayer(marker);
                    }

                    marker = L.marker([userLat, userLng]).addTo(map);
                    map.setView([userLat, userLng], 13);

                    if (isWithinRadius(userLat, userLng, center, radius)) {
                        alert('Anda berada di dalam radius');
                        console.log('Anda berada di dalam radius');
                    } else {
                        alert('Anda berada di luar radius');
                        console.log('Anda berada di luar radius');
                    }

                });
            } else {
                alert('Geolocation is not supported by this browser.');
                console.log('Geolocation is not supported by this browser.');
            }
        }

        // Check if the user is within the radius
        function isWithinRadius(userLat, userLng, center, radius) {
            let distance = map.distance(center, [userLat, userLng]);
            return distance <= radius;
        }
    </script>
</div>
