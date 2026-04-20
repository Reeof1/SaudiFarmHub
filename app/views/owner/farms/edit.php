<?php
/** @var array $farm */
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-pencil-square"></i>
            <span>Farm setup</span>
        </div>
        <h1 class="h4 fw-bold text-success mb-3 fh-section-title">Edit Farm</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="fh-card-soft p-4">
            <div class="card-body">
                <form method="post" action="<?= e(base_url('owner/farm/update')) ?>" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= e((string)$farm['id']) ?>">

                    <div class="mb-3">
                        <label class="form-label">Farm Name</label>
                        <input type="text" name="name" class="form-control" required value="<?= e($farm['name'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">City</label>
                        <select name="city" class="form-select" required>
                            <option value="">Select a region</option>
                            <?php foreach (($cities ?? []) as $cityOption): ?>
                                <option value="<?= e($cityOption) ?>"
                                    <?= ($farm['city'] ?? '') === $cityOption ? 'selected' : '' ?>>
                                    <?= e($cityOption) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                                <?php if (!empty($farm['latitude']) && !empty($farm['longitude'])): ?>
                                    Selected: <?= e(number_format((float)$farm['latitude'], 5)) ?>,
                                    <?= e(number_format((float)$farm['longitude'], 5)) ?>
                                <?php else: ?>
                                    No location selected
                                <?php endif; ?>
                            </span>
                        </div>

                        <div id="farm-map" style="height: 320px; border-radius: 8px; border: 1px solid #dee2e6;"></div>

                        <input type="hidden" name="latitude" id="latitude"
                               value="<?= e((string)($farm['latitude'] ?? '')) ?>">
                        <input type="hidden" name="longitude" id="longitude"
                               value="<?= e((string)($farm['longitude'] ?? '')) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" required><?= e($farm['description'] ?? '') ?></textarea>
                    </div>

                    <h2 class="h6 fw-bold text-success mb-3 mt-4">Farm Images</h2>

                    <div class="mb-3">
                        <label class="form-label">Cover Photo</label>
                        <?php if (!empty($farm['main_image'])): ?>
                            <div class="mb-2">
                                <img src="<?= e(base_url($farm['main_image'])) ?>" alt="Current cover"
                                     style="max-width: 220px; border-radius: 8px; border: 1px solid #dee2e6;">
                                <div class="small fh-muted mt-1">Current cover photo</div>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="cover_photo" id="cover_photo"
                               class="form-control" accept="image/jpeg,image/png,image/webp">
                        <div class="small fh-muted mt-1">Upload a new file to replace the current cover.</div>
                        <div class="mt-2" id="cover-preview-wrap" style="display:none;">
                            <img id="cover-preview" alt="New cover preview"
                                 style="max-width: 220px; border-radius: 8px; border: 1px solid #dee2e6;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gallery Photos (up to 9)</label>

                        <?php if (!empty($gallery)): ?>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <?php foreach ($gallery as $img): ?>
                                    <div class="position-relative">
                                        <img src="<?= e(base_url($img['image_path'])) ?>" alt="Gallery image"
                                             style="width: 90px; height: 90px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;">
                                        <button type="submit"
                                                form="delete-image-<?= (int)$img['id'] ?>"
                                                class="btn btn-danger btn-sm p-1 lh-1 position-absolute top-0 end-0"
                                                style="border-radius: 50%;" aria-label="Delete image">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="small fh-muted mb-2">
                                <?= (int)count($gallery) ?> of 9 photos used.
                            </div>
                        <?php endif; ?>

                        <input type="file" name="gallery_photos[]" id="gallery_photos"
                               class="form-control" multiple accept="image/jpeg,image/png,image/webp">
                        <div class="d-flex flex-wrap gap-2 mt-2" id="gallery-preview"></div>
                    </div>

                    <div class="small fh-muted mb-4">
                        Allowed: JPG, PNG, WebP &mdash; max 5MB each
                    </div>

                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-secondary" href="<?= e(base_url('owner/farms')) ?>">Cancel</a>
                        <button type="submit" class="btn btn-success flex-grow-1">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($gallery)): ?>
    <?php foreach ($gallery as $img): ?>
        <form id="delete-image-<?= (int)$img['id'] ?>"
              method="post" action="<?= e(base_url('owner/farm/image/delete')) ?>"
              onsubmit="return confirm('Delete this image?');">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="image_id" value="<?= e((string)$img['id']) ?>">
            <input type="hidden" name="farm_id" value="<?= e((string)$farm['id']) ?>">
        </form>
    <?php endforeach; ?>
<?php endif; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    (function () {
        var savedLat = <?= !empty($farm['latitude']) ? json_encode((float)$farm['latitude']) : 'null' ?>;
        var savedLng = <?= !empty($farm['longitude']) ? json_encode((float)$farm['longitude']) : 'null' ?>;

        var startLat = savedLat !== null ? savedLat : 24.7136;
        var startLng = savedLng !== null ? savedLng : 46.6753;
        var startZoom = savedLat !== null ? 14 : 6;

        var map = L.map('farm-map').setView([startLat, startLng], startZoom);

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

        if (savedLat !== null && savedLng !== null) {
            placeMarker(savedLat, savedLng);
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

        var coverInput = document.getElementById('cover_photo');
        var coverPreview = document.getElementById('cover-preview');
        var coverPreviewWrap = document.getElementById('cover-preview-wrap');
        coverInput.addEventListener('change', function () {
            var file = coverInput.files[0];
            if (!file) {
                coverPreviewWrap.style.display = 'none';
                return;
            }
            coverPreview.src = URL.createObjectURL(file);
            coverPreviewWrap.style.display = 'block';
        });

        var galleryInput = document.getElementById('gallery_photos');
        var galleryPreview = document.getElementById('gallery-preview');
        galleryInput.addEventListener('change', function () {
            galleryPreview.innerHTML = '';
            var files = Array.from(galleryInput.files).slice(0, 9);
            files.forEach(function (file) {
                var img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = 'Gallery preview';
                img.style.width = '90px';
                img.style.height = '90px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '6px';
                img.style.border = '1px solid #dee2e6';
                galleryPreview.appendChild(img);
            });
        });
    })();
</script>
