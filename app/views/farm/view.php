<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <div class="fh-chip d-inline-flex align-items-center gap-2 mb-2">
            <i class="bi bi-tree"></i>
            <span>Farm details</span>
        </div>
        <h1 class="h3 fw-bold text-success mb-1 fh-section-title"><?= e((string)($farm['name'] ?? 'Farm')) ?></h1>
        <div class="fh-muted mb-2">
            <i class="bi bi-geo-alt"></i>
            <?= e((string)($farm['city'] ?? 'Unknown city')) ?>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <?php if (!empty($farm['latitude']) && !empty($farm['longitude'])): ?>
                <a href="https://www.google.com/maps?q=<?= e((string)$farm['latitude']) ?>,<?= e((string)$farm['longitude']) ?>"
                   target="_blank" rel="noopener"
                   class="btn btn-sm btn-outline-success">
                    View on Google Maps <i class="bi bi-geo-alt"></i>
                </a>
            <?php endif; ?>
            <?php if (!empty($gallery)): ?>
                <button type="button" class="btn btn-sm btn-outline-success"
                        data-bs-toggle="modal" data-bs-target="#galleryModal">
                    <i class="bi bi-camera"></i> See All Photos
                </button>
            <?php endif; ?>
            <span class="badge text-bg-light border fh-muted">
                <i class="bi bi-eye"></i> <?= (int)($visitCount ?? 0) ?> views
            </span>
        </div>
    </div>
    <a class="btn btn-outline-success" href="<?= e(base_url('farms')) ?>">
        <i class="bi bi-arrow-left me-2"></i>Back to farms
    </a>
</div>

<?php if (!empty($gallery)): ?>
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryModalLabel">
                        <?= e((string)($farm['name'] ?? 'Farm')) ?> &mdash; Photos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="farmGalleryCarousel" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-indicators">
                            <?php foreach ($gallery as $i => $img): ?>
                                <button type="button" data-bs-target="#farmGalleryCarousel"
                                        data-bs-slide-to="<?= (int)$i ?>"
                                        class="<?= $i === 0 ? 'active' : '' ?>"
                                        aria-label="Slide <?= (int)$i + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="carousel-inner">
                            <?php foreach ($gallery as $i => $img): ?>
                                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                                    <img src="<?= e(base_url($img['image_path'])) ?>"
                                         class="d-block w-100" alt="Farm photo <?= (int)$i + 1 ?>"
                                         style="max-height: 500px; object-fit: contain; background: #000;">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($gallery) > 1): ?>
                            <button class="carousel-control-prev" type="button"
                                    data-bs-target="#farmGalleryCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button"
                                    data-bs-target="#farmGalleryCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="fh-card-soft p-4 h-100">
            <h2 class="h6 fw-bold mb-2">About this farm</h2>
            <p class="fh-muted mb-0">
                <?= e((string)($farm['description'] ?? '')) ?>
            </p>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="fh-card-soft p-4">
            <h2 class="h6 fw-bold text-success mb-3">Book an Activity</h2>
            <div class="fh-muted small mb-3">Choose activity, date, and available slot to submit a booking request.</div>

            <div class="border rounded-4 p-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Activity</label>
                        <select id="activity_id" class="form-select">
                            <option value="">Select activity</option>
                            <?php foreach (($activities ?? []) as $a): ?>
                                <option value="<?= e((string)$a['id']) ?>">
                                    <?= e((string)$a['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input id="date" type="date" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <button type="button" id="load-times" class="btn btn-success w-100">Load Available Times</button>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label">Available Time Slots</label>
                    <div id="timeslots" class="d-flex flex-wrap gap-2"></div>
                </div>

                <div class="row g-3 mt-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Party Size</label>
                        <input id="party_size" type="number" class="form-control" min="1" max="50" value="1">
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex gap-2">
                            <button id="book-btn" class="btn btn-primary flex-grow-1" type="button">Submit Booking</button>
                            <div id="booking-msg" class="text-muted align-self-center"></div>
                        </div>
                        <input type="hidden" id="schedule_id" value="">
                        <input type="hidden" id="csrf_token" value="<?= e(csrf_token()) ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const activitySel = document.getElementById('activity_id');
        const dateInput = document.getElementById('date');
        const loadBtn = document.getElementById('load-times');
        const timeslotsEl = document.getElementById('timeslots');
        const partySizeEl = document.getElementById('party_size');
        const scheduleIdEl = document.getElementById('schedule_id');
        const bookingMsgEl = document.getElementById('booking-msg');
        const bookBtn = document.getElementById('book-btn');
        const csrfTokenEl = document.getElementById('csrf_token');

        function setMessage(msg, isError) {
            bookingMsgEl.textContent = msg;
            bookingMsgEl.className = isError ? 'text-danger' : 'text-muted';
        }

        function renderTimeslots(schedules) {
            timeslotsEl.innerHTML = '';
            scheduleIdEl.value = '';

            if (!schedules || schedules.length === 0) {
                timeslotsEl.innerHTML = '<div class="text-muted">No available slots.</div>';
                return;
            }

            schedules.forEach((s) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-success';
                btn.textContent = `${s.start_time} - ${s.end_time} (Remaining: ${s.remaining})`;
                btn.dataset.scheduleId = String(s.id);
                btn.disabled = Number(s.remaining) <= 0;
                btn.addEventListener('click', () => {
                    scheduleIdEl.value = String(s.id);
                    [...timeslotsEl.querySelectorAll('button')].forEach(x => x.classList.remove('btn-success'));
                    [...timeslotsEl.querySelectorAll('button')].forEach(x => x.classList.add('btn-outline-success'));
                    btn.classList.remove('btn-outline-success');
                    btn.classList.add('btn-success');
                });
                timeslotsEl.appendChild(btn);
            });
        }

        loadBtn.addEventListener('click', async () => {
            const activityId = activitySel.value;
            const date = dateInput.value;

            if (!activityId) return setMessage('Select an activity first.', true);
            if (!date) return setMessage('Select a date first.', true);

            setMessage('Loading availability...', false);
            try {
                const url = new URL('<?= e(base_url('booking/availability')) ?>');
                url.searchParams.set('activity_id', activityId);
                url.searchParams.set('date', date);

                const res = await fetch(url.toString(), {method: 'GET'});
                const data = await res.json();

                if (!data.success) return setMessage(data.error || 'Failed to load availability.', true);
                renderTimeslots(data.schedules || []);
                setMessage('Select a slot to continue.', false);
            } catch (e) {
                setMessage('Network error while loading availability.', true);
            }
        });

        bookBtn.addEventListener('click', async () => {
            const scheduleId = scheduleIdEl.value;
            const partySize = Number(partySizeEl.value || 1);
            const csrfToken = csrfTokenEl.value;

            if (!scheduleId) return setMessage('Select an available time slot.', true);
            if (!partySize || partySize < 1) return setMessage('Invalid party size.', true);

            setMessage('Submitting booking...', false);
            try {
                const res = await fetch('<?= e(base_url('booking/create')) ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({
                        csrf_token: csrfToken,
                        schedule_id: scheduleId,
                        party_size: String(partySize),
                    }),
                });
                const data = await res.json();
                if (!data.success) return setMessage(data.error || 'Booking failed.', true);

                setMessage(`Booking submitted (ID: ${data.booking.id}).`, false);
            } catch (e) {
                setMessage('Network error while submitting booking.', true);
            }
        });
    })();
</script>

