document.addEventListener('DOMContentLoaded', () => {
    const searchButton = document.getElementById('search-button');
    if (searchButton) {
        searchButton.addEventListener('click', () => {
            const farmList = document.getElementById('farm-list');
            const pagination = document.querySelector('.pagination');
            const searchName = document.getElementById('search-name')?.value || '';
            const searchLocation = document.getElementById('search-location')?.value || '';
            const activityType = document.getElementById('search-activity-type')?.value || '';
            const availabilityDate = document.getElementById('search-availability-date')?.value || '';
            const minPrice = document.getElementById('search-min-price')?.value || '';
            const maxPrice = document.getElementById('search-max-price')?.value || '';

            if (!farmList) return;

            farmList.innerHTML = '<div class="text-muted">Searching...</div>';
            if (pagination) pagination.closest('nav')?.classList.add('d-none');

            const payload = new URLSearchParams({
                csrf_token: window.FARMHUB?.csrfToken || '',
                name: searchName,
                location: searchLocation,
                activity_type: activityType,
                availability_date: availabilityDate,
                min_price: minPrice,
                max_price: maxPrice,
                page: '1',
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
                        img.src = farm.main_image || `${window.FARMHUB?.baseUrl || ''}/assets/img/farm-placeholder.jpg`;
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
                        loc.appendChild(document.createTextNode(farm.location || 'Unknown location'));
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

