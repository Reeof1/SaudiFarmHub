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
            <?php if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'visitor'): ?>
                <button type="button" id="fh-fav-btn"
                        class="badge border <?= !empty($isFavorite) ? 'text-bg-danger' : 'text-bg-light fh-muted' ?>"
                        style="cursor: pointer;"
                        data-farm-id="<?= (int)$farm['id'] ?>"
                        data-favorited="<?= !empty($isFavorite) ? '1' : '0' ?>">
                    <i class="bi <?= !empty($isFavorite) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                    <span class="fh-fav-label"><?= !empty($isFavorite) ? 'Favorited' : 'Add to favorites' ?></span>
                </button>
            <?php endif; ?>
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

<?php
    $reviews = $reviews ?? [];
    $ratingSummary = $ratingSummary ?? ['count' => 0, 'average' => 0.0];
    $userReview = $userReview ?? null;
    $isVisitor = !empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'visitor';
    $currentUserId = (int)($_SESSION['user']['id'] ?? 0);
    $currentRole = $_SESSION['user']['role'] ?? '';
    $isAdmin = $currentRole === 'admin';
    $isFarmOwner = $currentRole === 'owner'
        && $currentUserId > 0
        && (int)($farm['owner_id'] ?? 0) === $currentUserId;
    $reviewError = $_SESSION['review_error'] ?? null;
    unset($_SESSION['review_error']);
?>
<div class="fh-card-soft p-4 mt-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="h6 fw-bold text-success mb-0">
            <i class="bi bi-chat-square-text"></i> Reviews &amp; Ratings
        </h2>
        <div class="fh-muted small">
            <?php if ($ratingSummary['count'] > 0): ?>
                <i class="bi bi-star-fill text-warning"></i>
                <strong><?= e(number_format((float)$ratingSummary['average'], 1)) ?></strong>
                out of 5
                &middot; <?= (int)$ratingSummary['count'] ?> review<?= $ratingSummary['count'] === 1 ? '' : 's' ?>
            <?php else: ?>
                No reviews yet
            <?php endif; ?>
        </div>
    </div>

    <?php if ($reviewError): ?>
        <div class="alert alert-danger"><?= e($reviewError) ?></div>
    <?php endif; ?>

    <?php if ($isVisitor): ?>
        <?php if ($userReview !== null): ?>
            <div class="border rounded-4 p-3 mb-4 bg-light">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <div class="small fh-muted mb-1">Your review</div>
                        <div class="text-warning mb-1">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi <?= $i <= (int)$userReview['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="mb-1"><?= nl2br(e((string)$userReview['comment'])) ?></div>
                        <div class="small fh-muted">
                            Submitted <?= e(date('Y-m-d', strtotime((string)$userReview['created_at']))) ?>
                        </div>
                        <?php if (!empty($userReview['owner_reply'])): ?>
                            <div class="mt-2 p-2 border-start border-3 border-success bg-white rounded">
                                <div class="small text-success fw-bold mb-1">
                                    <i class="bi bi-reply-fill"></i> Owner's reply
                                </div>
                                <div class="small"><?= nl2br(e((string)$userReview['owner_reply'])) ?></div>
                                <div class="small fh-muted mt-1">
                                    <?= e(date('Y-m-d', strtotime((string)$userReview['owner_reply_at']))) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <form method="POST" action="<?= e(base_url('review/delete')) ?>"
                          onsubmit="return confirm('Delete your review?');">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="farm_id" value="<?= (int)$farm['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" action="<?= e(base_url('review/submit')) ?>" class="border rounded-4 p-3 mb-4">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="farm_id" value="<?= (int)$farm['id'] ?>">
                <div class="mb-2">
                    <label class="form-label small fh-muted mb-1">Your rating</label>
                    <div class="fh-star-picker" id="fh-star-picker">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star fh-star-icon" data-value="<?= $i ?>" style="cursor:pointer; font-size:1.4rem; color:#f5b301;"></i>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="fh-rating-input" value="0" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small fh-muted mb-1">Your comment</label>
                    <textarea name="comment" class="form-control" rows="3"
                              placeholder="Share your experience" required></textarea>
                </div>
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-send"></i> Submit Review
                </button>
            </form>
            <script>
                (function () {
                    const picker = document.getElementById('fh-star-picker');
                    const input = document.getElementById('fh-rating-input');
                    if (!picker || !input) return;
                    const icons = picker.querySelectorAll('.fh-star-icon');
                    function paint(n) {
                        icons.forEach(function (ic) {
                            const v = Number(ic.dataset.value);
                            ic.classList.toggle('bi-star-fill', v <= n);
                            ic.classList.toggle('bi-star', v > n);
                        });
                    }
                    icons.forEach(function (ic) {
                        ic.addEventListener('mouseenter', function () { paint(Number(ic.dataset.value)); });
                        ic.addEventListener('click', function () {
                            input.value = String(ic.dataset.value);
                            paint(Number(ic.dataset.value));
                        });
                    });
                    picker.addEventListener('mouseleave', function () {
                        paint(Number(input.value || 0));
                    });
                })();
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($reviews)): ?>
        <div class="small fh-muted mb-2">All reviews (<?= (int)$ratingSummary['count'] ?>)</div>
        <?php foreach ($reviews as $rv): ?>
            <?php if ($userReview !== null && (int)$rv['user_id'] === $currentUserId) continue; ?>
            <div class="border-top pt-3 mt-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                    <div class="text-warning">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi <?= $i <= (int)$rv['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                        <?php endfor; ?>
                        <span class="fh-muted small ms-2">
                            <?= e((string)($rv['reviewer_name'] ?? 'Visitor')) ?>
                            &middot; <?= e(date('Y-m-d', strtotime((string)$rv['created_at']))) ?>
                        </span>
                    </div>
                    <?php if ($isAdmin): ?>
                        <form method="POST" action="<?= e(base_url('review/admin-delete')) ?>"
                              onsubmit="return confirm('Delete this review permanently?');"
                              class="d-inline m-0">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="review_id" value="<?= (int)$rv['id'] ?>">
                            <input type="hidden" name="farm_id" value="<?= (int)$farm['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                <div><?= nl2br(e((string)$rv['comment'])) ?></div>

                <?php if (!empty($rv['owner_reply'])): ?>
                    <div class="mt-2 p-2 border-start border-3 border-success bg-light rounded">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <div class="small text-success fw-bold mb-1">
                                    <i class="bi bi-reply-fill"></i> Owner's reply
                                </div>
                                <div class="small"><?= nl2br(e((string)$rv['owner_reply'])) ?></div>
                                <div class="small fh-muted mt-1">
                                    <?= e(date('Y-m-d', strtotime((string)$rv['owner_reply_at']))) ?>
                                </div>
                            </div>
                            <?php if ($isFarmOwner): ?>
                                <form method="POST" action="<?= e(base_url('review/reply-delete')) ?>"
                                      onsubmit="return confirm('Delete your reply?');"
                                      class="d-inline m-0">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="review_id" value="<?= (int)$rv['id'] ?>">
                                    <input type="hidden" name="farm_id" value="<?= (int)$farm['id'] ?>">
                                    <button type="submit" class="btn btn-link btn-sm text-danger p-0">
                                        Delete reply
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($isFarmOwner): ?>
                    <form method="POST" action="<?= e(base_url('review/reply')) ?>" class="mt-2">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="review_id" value="<?= (int)$rv['id'] ?>">
                        <input type="hidden" name="farm_id" value="<?= (int)$farm['id'] ?>">
                        <textarea name="reply" rows="2" maxlength="1000" required
                                  class="form-control form-control-sm"
                                  placeholder="Write a reply to this review..."></textarea>
                        <button type="submit" class="btn btn-sm btn-success mt-1">
                            <i class="bi bi-reply"></i> Reply
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php elseif (!$isVisitor || $userReview === null): ?>
        <div class="fh-muted small">Be the first to review this farm.</div>
    <?php endif; ?>
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

        const favBtn = document.getElementById('fh-fav-btn');
        if (favBtn) {
            favBtn.addEventListener('click', async () => {
                const farmId = favBtn.dataset.farmId;
                const csrfToken = csrfTokenEl.value;
                favBtn.disabled = true;
                try {
                    const res = await fetch('<?= e(base_url('favorite/toggle')) ?>', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: new URLSearchParams({
                            csrf_token: csrfToken,
                            farm_id: farmId,
                        }),
                    });
                    const data = await res.json();
                    if (!data.success) return;

                    const icon = favBtn.querySelector('i');
                    const label = favBtn.querySelector('.fh-fav-label');
                    if (data.favorited) {
                        favBtn.classList.remove('text-bg-light', 'fh-muted');
                        favBtn.classList.add('text-bg-danger');
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill');
                        if (label) label.textContent = 'Favorited';
                    } else {
                        favBtn.classList.remove('text-bg-danger');
                        favBtn.classList.add('text-bg-light', 'fh-muted');
                        icon.classList.remove('bi-heart-fill');
                        icon.classList.add('bi-heart');
                        if (label) label.textContent = 'Add to favorites';
                    }
                } catch (e) {
                    // ignore
                } finally {
                    favBtn.disabled = false;
                }
            });
        }

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

