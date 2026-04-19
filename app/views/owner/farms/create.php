<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-plus-square"></i>
            <span>Farm setup</span>
        </div>
        <h1 class="h4 fw-bold text-success mb-3 fh-section-title">Add Farm</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="fh-card-soft p-4">
            <div class="card-body">
                <form method="post" action="<?= e(base_url('owner/farm/store')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <div class="mb-3">
                        <label class="form-label">Farm Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" required
                               placeholder="e.g. Riyadh - Al Nakheel">
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Pick location on map</label>
                        <div class="small fh-muted mb-2">
                            Click on the map to place a pin at your farm's location,
                            or use your current location.
                        </div>

                        <div class="d-flex gap-2 mb-2">
                            <button type="button" id="use-my-location" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-geo-alt"></i> Use my current location
                            </button>
                            <span id="coords-display" class="align-self-center small fh-muted">
                                No location selected
                            </span>
                        </div>

                        <div id="farm-map" style="height: 320px; border-radius: 8px; border: 1px solid #dee2e6;"></div>

                        <input type="hidden" name="latitude" id="latitude" value="">
                        <input type="hidden" name="longitude" id="longitude" value="">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-secondary" href="<?= e(base_url('owner/farms')) ?>">Cancel</a>
                        <button type="submit" class="btn btn-success flex-grow-1">Create Farm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    (function () {
        // Default view centered roughly on Saudi Arabia.
        var defaultLat = 24.7136;
        var defaultLng = 46.6753;
        var map = L.map('farm-map').setView([defaultLat, defaultLng], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker = null;
        var latInput = document.getElementById('latitude');
        var lngInput = document.getElementById('longitude');
        var coordsDisplay = document.getElementById('coords-display');

        function placeMarker(lat, lng) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }
            latInput.value = lat.toFixed(8);
            lngInput.value = lng.toFixed(8);
            coordsDisplay.textContent = 'Selected: ' + lat.toFixed(5) + ', ' + lng.toFixed(5);
        }

        map.on('click', function (e) {
            placeMarker(e.latlng.lat, e.latlng.lng);
        });

        document.getElementById('use-my-location').addEventListener('click', function () {
            if (!navigator.geolocation) {
                coordsDisplay.textContent = 'Geolocation is not supported by your browser.';
                return;
            }
            coordsDisplay.textContent = 'Locating...';
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    var lat = pos.coords.latitude;
                    var lng = pos.coords.longitude;
                    map.setView([lat, lng], 14);
                    placeMarker(lat, lng);
                },
                function () {
                    coordsDisplay.textContent = 'Could not get your location. Please click the map instead.';
                }
            );
        });
    })();
</script>
