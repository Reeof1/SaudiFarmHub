document.addEventListener('DOMContentLoaded', () => {
    const activityChecks = document.querySelectorAll('.fh-activity-check');
    const activityLabel = document.getElementById('search-activity-label');

    function updateActivityLabel() {
        if (!activityLabel) return;
        const selected = Array.from(activityChecks)
            .filter((c) => c.checked)
            .map((c) => c.value);
        if (selected.length === 0) {
            activityLabel.textContent = 'All activity types';
            activityLabel.classList.add('fh-muted');
        } else if (selected.length <= 2) {
            activityLabel.textContent = selected.join(', ');
            activityLabel.classList.remove('fh-muted');
        } else {
            activityLabel.textContent = selected.length + ' types selected';
            activityLabel.classList.remove('fh-muted');
        }
    }
    activityChecks.forEach((c) => c.addEventListener('change', updateActivityLabel));
    updateActivityLabel();

    const searchButton = document.getElementById('search-button');
    if (searchButton) {
        searchButton.addEventListener('click', () => {
            const farmList = document.getElementById('farm-list');
            const pagination = document.querySelector('.pagination');
            const searchName = document.getElementById('search-name')?.value || '';
            const searchCity = document.getElementById('search-city')?.value || '';
            const selectedActivityTypes = Array.from(activityChecks)
                .filter((c) => c.checked)
                .map((c) => c.value);
            const availabilityDate = document.getElementById('search-availability-date')?.value || '';
            const minPrice = document.getElementById('search-min-price')?.value || '';
            const maxPrice = document.getElementById('search-max-price')?.value || '';

            if (!farmList) return;

            farmList.innerHTML = '<div class="text-muted">Searching...</div>';
            if (pagination) pagination.closest('nav')?.classList.add('d-none');

            const payload = new URLSearchParams();
            payload.append('csrf_token', window.FARMHUB?.csrfToken || '');
            payload.append('name', searchName);
            payload.append('city', searchCity);
            payload.append('availability_date', availabilityDate);
            payload.append('min_price', minPrice);
            payload.append('max_price', maxPrice);
            payload.append('page', '1');
            selectedActivityTypes.forEach((t) => {
                payload.append('activity_types[]', t);
            });

            const cachedLat = sessionStorage.getItem('fh_geo_lat');
            const cachedLng = sessionStorage.getItem('fh_geo_lng');
            if (cachedLat && cachedLng) {
                payload.append('user_lat', cachedLat);
                payload.append('user_lng', cachedLng);
            }

            fetch(window.FARMHUB?.searchUrl || '/search/farms', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: payload,
            })
                .then(async (res) => {
                    let data;
                    try {
                        data = await res.json();
                    } catch (e) {
                        throw new Error('Invalid response from server.');
                    }
                    if (!data.success) {
                        throw new Error(data.error || 'Search failed.');
                    }
                    return data;
                })
                .then((data) => {
                    farmList.innerHTML = data.html || '';
                    if (pagination) pagination.closest('nav')?.classList.add('d-none');
                })
                .catch((err) => {
                    farmList.innerHTML = `<div class="text-danger">${err.message}</div>`;
                });
        });
    }
});

