/**
 * நிகழ்வுகள் பக்கம் - ஜாவாஸ்கிரிப்ட் (Events Page JS)
 * Filter by Category, Search, and Event Card Rendering
 */

const API_BASE = (
    window.location.protocol === 'file:' || 
    ((window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') && window.location.port !== '8000')
)
    ? 'http://localhost:8000/api'
    : 'api';

let allEventsData = [];

document.addEventListener('DOMContentLoaded', () => {
    loadEventsList();
    setupFilters();
});

async function loadEventsList() {
    const grid = document.getElementById('eventsListGrid');
    if (!grid) return;

    grid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: 50px 0; color: #8e151b;">
            <div style="font-size: 2.5rem; animation: spin 2s linear infinite;">ௐ</div>
            <p style="margin-top: 15px; font-weight: 600;">நிகழ்வுகள் ஏற்றப்படுகின்றன...</p>
        </div>
    `;

    // 1. Fetch from Server API first for live network sync
    try {
        const res = await fetch(`${API_BASE}/events.php`);
        if (res.ok) {
            const json = await res.json();
            if (json.status && Array.isArray(json.data) && json.data.length > 0) {
                allEventsData = json.data;
                localStorage.setItem('temple_events', JSON.stringify(allEventsData));
                renderEventsGrid(allEventsData);
                return;
            }
        }
    } catch (err) {
        console.warn('Events API fetch error, fallback to cache:', err);
    }

    // 2. Fallback to LocalStorage or defaults if offline
    const local = JSON.parse(localStorage.getItem('temple_events') || '[]');
    if (Array.isArray(local) && local.length > 0) {
        allEventsData = local;
    } else {
        allEventsData = getSampleEventsList();
    }
    renderEventsGrid(allEventsData);
}

function renderEventsGrid(events) {
    const grid = document.getElementById('eventsListGrid');
    if (!grid) return;

    if (!events || events.length === 0) {
        grid.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 50px 20px; background: #ffffff; border-radius: 12px; border: 1px solid #ede4d3;">
                <p style="font-size: 1.1rem; color: #680509; font-weight: 700;">இந்த வகையில் நிகழ்வுகள் எதுவும் காணப்படவில்லை.</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = '';

    events.forEach(ev => {
        const card = document.createElement('div');
        card.className = 'event-card';

        const cover = ev.cover_image || 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80';
        const photoCount = ev.photo_count || (ev.photos ? ev.photos.length : 1);

        card.innerHTML = `
            <div class="event-thumb-wrap">
                <img src="${cover}" alt="${ev.title}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80'">
                <div class="event-date-tag">📅 ${formatTamilDate(ev.event_date)}</div>
                <div class="event-photo-count">📸 ${photoCount} படங்கள்</div>
            </div>
            <div class="event-body">
                <span class="event-category-badge">${ev.category || 'விசேஷம்'}</span>
                <h3 class="event-title">${ev.title}</h3>
                <p class="event-snippet">${ev.description || 'திருக்கோவிலில் நடைபெற்ற விசேஷ வழிபாடு.'}</p>
                <div class="event-footer">
                    <a href="gallery.html?event_id=${ev.id}" class="btn-view-photos">
                        புகைப்படங்கள் & பதிவிறக்கம் &rarr;
                    </a>
                </div>
            </div>
        `;

        grid.appendChild(card);
    });
}

function setupFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            filterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const category = btn.getAttribute('data-category');
            if (category === 'all') {
                renderEventsGrid(allEventsData);
            } else {
                const filtered = allEventsData.filter(e => (e.category || '').includes(category));
                renderEventsGrid(filtered);
            }
        });
    });

    const searchInput = document.getElementById('eventSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            if (!query) {
                renderEventsGrid(allEventsData);
                return;
            }
            const filtered = allEventsData.filter(item => 
                (item.title && item.title.toLowerCase().includes(query)) ||
                (item.description && item.description.toLowerCase().includes(query)) ||
                (item.category && item.category.toLowerCase().includes(query))
            );
            renderEventsGrid(filtered);
        });
    }
}

function getSampleEventsList() {
    return [
        {
            id: 1,
            title: 'பங்குனி உத்திர திருக்கல்யாண வைபவம்',
            event_date: '2026-03-24',
            category: 'திருவிழா',
            description: 'பங்குனி உத்திர நன்னாளில் நடைபெற்ற அருள்மிகு சுவாமி மற்றும் அம்மன் திருக்கல்யாண உற்சவம் மற்றும் பூப்பல்லக்கு ஊர்வலம்.',
            cover_image: 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=1200&q=80',
            photo_count: 3
        },
        {
            id: 2,
            title: 'தமிழ் புத்தாண்டு 1008 சங்காபிஷேகம்',
            event_date: '2026-04-14',
            category: 'விசேஷ பூஜை',
            description: 'குரோதி நாம வருடப் பிறப்பை முன்னிட்டு சுவாமி மற்றும் அம்மனுக்கு 1008 சங்காபிஷேகமும் தங்கக் கவச அலங்காரமும் நடைபெற்றது.',
            cover_image: 'https://images.unsplash.com/photo-1609342122563-a43ac8917a3a?auto=format&fit=crop&w=1200&q=80',
            photo_count: 2
        },
        {
            id: 3,
            title: 'மகா சிவராத்திரி 4 கால ருத்ராபிஷேகம்',
            event_date: '2026-02-15',
            category: 'அபிஷேகம்',
            description: 'மகா சிவராத்திரியை முன்னிட்டு இரவு முழுவதும் பக்தர்கள் பக்திப் பரவசத்துடன் தரிசித்த நான்கு கால மகா அபிஷேகம்.',
            cover_image: 'https://images.unsplash.com/photo-1621847468516-1ed5d0df56fe?auto=format&fit=crop&w=1200&q=80',
            photo_count: 1
        }
    ];
}
