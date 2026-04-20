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
                    farmList.innerHTML = '';

                    const farms = data.farms || [];
                    if (farms.length === 0) {
                        farmList.innerHTML = '<div class="text-muted">No farms matched your filters.</div>';
                        return;
                    }

                    farms.forEach((farm) => {
                        const col = document.createElement('div');
                        col.className = 'col-md-4';

                        const card = document.createElement('div');
                        card.className = 'card shadow-sm h-100 border-0';

                        const img = document.createElement('img');
                        img.className = 'card-img-top';
                        img.alt = 'Farm image';
                        img.style.height = '200px';
                        img.style.objectFit = 'cover';
                        const base = window.FARMHUB?.baseUrl || '';
                        img.src = farm.main_image
                            ? `${base}/${farm.main_image}`
                            : `${base}/assets/img/farm-placeholder.jpg`;
                        img.onerror = () => {
                            img.style.display = 'none';
                        };
                        card.appendChild(img);

                        const body = document.createElement('div');
                        body.className = 'card-body d-flex flex-column';

                        const title = document.createElement('h5');
                        title.className = 'card-title text-success';
                        title.textContent = farm.name || 'Untitled farm';
                        body.appendChild(title);

                        const loc = document.createElement('p');
                        loc.className = 'card-text small mb-1 text-muted';
                        loc.innerHTML = '<i class="bi bi-geo-alt"></i> ';
                        loc.appendChild(document.createTextNode(farm.city || 'Unknown city'));
                        if (farm.visit_count !== undefined) {
                            const sep = document.createElement('span');
                            sep.textContent = ' · ';
                            loc.appendChild(sep);
                            const eye = document.createElement('i');
                            eye.className = 'bi bi-eye';
                            loc.appendChild(eye);
                            loc.appendChild(document.createTextNode(' ' + farm.visit_count + ' views'));
                        }
                        body.appendChild(loc);

                        const desc = document.createElement('p');
                        desc.className = 'card-text small text-muted flex-grow-1';
                        const rawDesc = farm.description || '';
                        desc.textContent = rawDesc.length > 120 ? rawDesc.substring(0, 120) + '...' : rawDesc;
                        body.appendChild(desc);

                        const btn = document.createElement('a');
                        btn.className = 'btn btn-outline-success btn-sm mt-2';
                        const base = window.FARMHUB?.baseUrl || '';
                        btn.href = `${base}/farm/view?farm_id=${encodeURIComponent(farm.id)}`;
                        btn.textContent = 'View Details & Book';
                        body.appendChild(btn);

                        card.appendChild(body);
                        col.appendChild(card);
                        farmList.appendChild(col);
                    });

                    if (pagination) pagination.closest('nav')?.classList.remove('d-none');
                })
                .catch((err) => {
                    farmList.innerHTML = `<div class="text-danger">${err.message}</div>`;
                });
        });
    }
});

