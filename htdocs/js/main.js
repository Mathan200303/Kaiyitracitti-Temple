/**
 * கோவில் இணையதளம் - முதன்மை ஜாவாஸ்கிரிப்ட் (Main JavaScript)
 * Dynamic Sync: Events, Announcements, Pooja Timings, and Temple Info
 */

const API_BASE = (
    window.location.protocol === 'file:' || 
    ((window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') && window.location.port !== '8000')
)
    ? 'http://localhost:8000/api'
    : 'api';

document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    displayTamilDate();
    loadAnnouncements();
    initSmoothScroll();
    syncTempleSettings();
    renderHomeEvents();
    syncDarshanTimings();
    initScrollReveal();
});

// Listen for updates from other tabs (Admin Dashboard saves)
window.addEventListener('storage', (e) => {
    if (e.key === 'temple_events') {
        renderHomeEvents();
    }
    if (e.key === 'temple_announcements') {
        loadAnnouncements();
    }
    if (e.key === 'temple_info' || e.key === 'temple_settings') {
        syncTempleSettings();
    }
    if (e.key === 'temple_poojas') {
        syncDarshanTimings();
    }
});

// 1. மொபைல் மெனு செயல்பாடு (Mobile Menu Toggle)
function initMobileMenu() {
    const toggleBtn = document.getElementById('menuToggle');
    const drawer = document.getElementById('mobileDrawer');
    const navMenu = document.getElementById('navMenu');
    const tabBar = document.getElementById('headerTabBar');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (drawer) drawer.classList.toggle('active');
            if (tabBar) tabBar.classList.toggle('active');
            if (navMenu) navMenu.classList.toggle('active');
            const isOpen = (drawer && drawer.classList.contains('active')) || 
                           (tabBar && tabBar.classList.contains('active')) || 
                           (navMenu && navMenu.classList.contains('active'));
            toggleBtn.innerHTML = isOpen ? '✕ மூடு' : '☰ மெனு';
        });

        // பக்கத்தில் கிளிக் செய்தால் மெனுவை மூடுதல்
        document.addEventListener('click', (e) => {
            if (!toggleBtn.contains(e.target) && 
                (!drawer || !drawer.contains(e.target)) && 
                (!tabBar || !tabBar.contains(e.target)) && 
                (!navMenu || !navMenu.contains(e.target))) {
                if (drawer) drawer.classList.remove('active');
                if (tabBar) tabBar.classList.remove('active');
                if (navMenu) navMenu.classList.remove('active');
                toggleBtn.innerHTML = '☰ மெனு';
            }
        });
    }
}

// 2. இன்றைய தமிழ் நாள் & தேதி (Tamil Calendar Date Display)
function displayTamilDate() {
    const dateElem = document.getElementById('currentDateText');
    if (!dateElem) return;

    const days = ['ஞாயிற்றுக்கிழமை', 'திங்கட்கிழமை', 'செவ்வாய்க்கிழமை', 'புதன்கிழமை', 'வியாழக்கிழமை', 'வெள்ளிக்கிழமை', 'சனிக்கிழமை'];
    const months = ['ஜனவரி', 'பிப்ரவரி', 'மார்ச்', 'ஏப்ரல்', 'மே', 'ஜூன்', 'ஜூலை', 'ஆகஸ்ட்', 'செப்டம்பர்', 'அக்டோபர்', 'நவம்பர்', 'டிசம்பர்'];
    
    const now = new Date();
    const dayName = days[now.getDay()];
    const dateNum = now.getDate();
    const monthName = months[now.getMonth()];
    const year = now.getFullYear();

    dateElem.innerText = `இன்று: ${dateNum} ${monthName} ${year}, ${dayName}`;
}

// 3. முக்கிய அறிவிப்புகள் ஏற்றுதல் (Load Announcements Ticker - Network Synced)
async function loadAnnouncements() {
    const tickerElem = document.getElementById('noticeTickerText');
    if (!tickerElem) return;

    // 1. Try fetching from server API first
    try {
        const res = await fetch(`${API_BASE}/announcements.php`);
        if (res.ok) {
            const data = await res.json();
            if (data.status && Array.isArray(data.data) && data.data.length > 0) {
                localStorage.setItem('temple_announcements', JSON.stringify(data.data));
                const notices = data.data.map(item => item.title + (item.content ? ': ' + item.content : ''));
                tickerElem.innerText = notices.join('   ·   ');
                return;
            }
        }
    } catch (err) {
        console.warn('Announcements fetch error:', err);
    }

    // 2. Fallback to LocalStorage
    const local = JSON.parse(localStorage.getItem('temple_announcements') || '[]');
    if (Array.isArray(local) && local.length > 0) {
        const notices = local.map(item => item.title + (item.content ? ': ' + item.content : ''));
        tickerElem.innerText = notices.join('   ·   ');
        return;
    }

    tickerElem.innerText = 'தினமும் காலை 5:30 மணி முதல் இரவு 9:00 மணி வரை திருக்கோவில் நடை திறந்திருக்கும். · தினசரி மகா அன்னதானம் நடைபெறுகிறது.';
}

// 4. கோவில் அமைப்புகள் & தகவல் ஒத்திசைவு (Sync Temple Settings - Network Synced)
async function syncTempleSettings() {
    let info = null;

    // 1. Fetch live from server API
    try {
        const res = await fetch(`${API_BASE}/settings.php`);
        if (res.ok) {
            const json = await res.json();
            if (json.status && json.data) {
                const d = json.data;
                info = {
                    name: d.temple_name,
                    tagline: d.tagline,
                    phone: d.phone,
                    email: d.email,
                    bank: d.bank_name ? `${d.bank_name}: ${d.bank_account_no}` : null,
                    branch: d.bank_branch,
                    address: d.address,
                    mapUrl: d.map_embed_url
                };
                localStorage.setItem('temple_settings', JSON.stringify(info));
                localStorage.setItem('temple_info', JSON.stringify(info));
            }
        }
    } catch(err) {
        console.warn('Settings API sync error:', err);
    }

    // 2. Fallback to LocalStorage
    if (!info) {
        const raw = localStorage.getItem('temple_info') || localStorage.getItem('temple_settings');
        if (raw) {
            try { info = JSON.parse(raw); } catch(e) {}
        }
    }

    if (!info) return;

    // 1. Temple Name
    if (info.name) {
        document.querySelectorAll('.temple-brand-text h1').forEach(el => el.innerText = info.name);
        const heroTitle = document.querySelector('.hero-title');
        if (heroTitle) {
            heroTitle.innerHTML = info.name.replace('அருள்மிகு', '<br>அருள்மிகு');
        }
        const footerTitle = document.querySelector('.footer-col h4');
        if (footerTitle && footerTitle.innerText.includes('கந்தசுவாமி')) {
            footerTitle.innerText = info.name;
        }
    }

    // 2. Tagline
    if (info.tagline) {
        const deityEl = document.querySelector('.hero-deity');
        if (deityEl) deityEl.innerText = info.tagline;
    }

    // 3. Phone Numbers
    if (info.phone) {
        document.querySelectorAll('.top-contact-info a, a[href^="tel:"]').forEach(el => {
            const firstPhone = info.phone.split(',')[0].trim();
            el.href = `tel:${firstPhone.replace(/[^0-9+]/g, '')}`;
            el.innerText = firstPhone;
        });
        const contactPhoneSpan = document.getElementById('contactPhoneDisplay');
        if (contactPhoneSpan) contactPhoneSpan.innerText = info.phone;
    }

    // 4. Email
    if (info.email) {
        document.querySelectorAll('a[href^="mailto:"]').forEach(el => {
            el.href = `mailto:${info.email}`;
            el.innerText = info.email;
        });
        const contactEmailSpan = document.getElementById('contactEmailDisplay');
        if (contactEmailSpan) contactEmailSpan.innerText = info.email;
    }

    // 5. Full Address
    if (info.address) {
        const addrEl = document.getElementById('contactAddressDisplay');
        if (addrEl) addrEl.innerText = info.address;
    }

    // 6. Bank Details
    if (info.bank) {
        const bankEl = document.getElementById('contactBankDisplay');
        if (bankEl) bankEl.innerText = info.bank;
    }

    // 7. Google Maps Link
    if (info.mapUrl) {
        document.querySelectorAll('a[href*="maps.app.goo.gl"]').forEach(el => {
            el.href = info.mapUrl;
        });
    }
}

// 5. முகப்புப் பக்க நிகழ்வுகள் ஏற்றுதல் (Render Latest Events on Homepage - Network Synced)
async function renderHomeEvents() {
    const grid = document.getElementById('homeEventsGrid');
    if (!grid) return;

    let events = [];

    // 1. Fetch from Server API first
    try {
        const res = await fetch(`${API_BASE}/events.php`);
        if (res.ok) {
            const json = await res.json();
            if (json.status && Array.isArray(json.data) && json.data.length > 0) {
                events = json.data;
                localStorage.setItem('temple_events', JSON.stringify(events));
            }
        }
    } catch(err) {
        console.warn('Home events fetch error:', err);
    }

    // 2. Fallback to LocalStorage
    if (!events || events.length === 0) {
        const local = localStorage.getItem('temple_events');
        if (local) {
            try { events = JSON.parse(local); } catch(e) {}
        }
    }

    if (!events || events.length === 0) {
        // Fallback default events
        events = [
            {
                id: 1,
                title: 'கந்தசஷ்டி பெருவிழா - சூரசம்ஹார வைபவம்',
                event_date: '2026-11-15',
                category: 'திருவிழா',
                description: 'கந்தசஷ்டி நன்னாளில் தேவஸ்தானத்தில் பக்தர்கள் சூழ நடைபெற்ற சூரசம்ஹார வைபவம் மற்றும் மாலையில் நடைபெற்ற திருக்கல்யாண உற்சவம்.',
                cover_image: 'images/gopuram_front.jpg',
                photo_count: 3
            },
            {
                id: 2,
                title: 'வைகாசி விசாக பெருவிழா 108 பாலாபிஷேகம்',
                event_date: '2026-06-02',
                category: 'அபிஷேகம்',
                description: 'முருகப்பெருமானின் அவதாரத் திருநாளான வைகாசி விசாகத்தை முன்னிட்டு மூலவருக்கும் உற்சவருக்கும் நடைபெற்ற 108 சங்காபிஷேகமும், தங்கக் கவச அலங்காரமும்.',
                cover_image: 'images/gopuram_side.jpg',
                photo_count: 2
            },
            {
                id: 3,
                title: 'தைப்பூச நன்னாள் - மயில் வாகன பவனி',
                event_date: '2026-02-01',
                category: 'உற்சவம்',
                description: 'தைப்பூச நன்னாளில் கந்தப்பெருமான் வள்ளி தெய்வானை சமேதராக மயில் வாகனத்தில் எழுந்தருளி திருவீதி உலா வந்த கண்கொள்ளாக் காட்சி.',
                cover_image: 'images/gopuram_front.jpg',
                photo_count: 1
            }
        ];
    }

    // Render top 3 events
    grid.innerHTML = '';
    const displayList = events.slice(0, 3);

    displayList.forEach(ev => {
        const cover = ev.cover_image || 'images/gopuram_front.jpg';
        const pCount = ev.photo_count || (ev.photos ? ev.photos.length : 1);
        const card = document.createElement('div');
        card.className = 'event-card reveal';
        card.innerHTML = `
            <div class="event-thumb-wrap">
                <img src="${cover}" alt="${ev.title}" onerror="this.src='images/gopuram_front.jpg'">
                <div class="event-date-tag">${formatTamilDisplayDate(ev.event_date)}</div>
                <div class="event-photo-count">${pCount} படங்கள்</div>
            </div>
            <div class="event-body">
                <span class="event-category-badge">${ev.category || 'விசேஷம்'}</span>
                <h3 class="event-title">${ev.title}</h3>
                <p class="event-snippet">${ev.description || 'திருக்கோவிலில் நடைபெற்ற விசேஷ வழிபாடு மற்றும் தரிசன வைபவம்.'}</p>
                <div class="event-footer">
                    <a href="gallery.html?event_id=${ev.id}" class="btn-view-photos">புகைப்படங்கள் & பதிவிறக்கம் &rarr;</a>
                </div>
            </div>
        `;
        grid.appendChild(card);
    });

    setTimeout(initScrollReveal, 60);

    // Also update Album Highlight if available
    const albumTitle = document.getElementById('homeAlbumHighlightTitle');
    const albumMeta = document.getElementById('homeAlbumHighlightMeta');
    if (albumTitle && displayList[0]) {
        albumTitle.innerText = `${displayList[0].title} - சிறப்பு ஆல்பம்`;
        if (albumMeta) {
            albumMeta.innerText = `${formatTamilDisplayDate(displayList[0].event_date)} | ${displayList[0].category || 'திருவிழா'} | HD தரம்`;
        }
    }
}

// 6. பூஜை நேரங்கள் ஒத்திசைவு (Sync Pooja Timings on Homepage - Network Synced)
async function syncDarshanTimings() {
    let poojas = [];

    // 1. Fetch live from server API
    try {
        const res = await fetch(`${API_BASE}/poojas.php`);
        if (res.ok) {
            const json = await res.json();
            if (json.status && Array.isArray(json.data) && json.data.length > 0) {
                poojas = json.data;
                localStorage.setItem('temple_poojas', JSON.stringify(poojas));
            }
        }
    } catch(err) {
        console.warn('Poojas API sync error:', err);
    }

    // 2. Fallback to LocalStorage
    if (!poojas || poojas.length === 0) {
        const raw = localStorage.getItem('temple_poojas');
        if (raw) {
            try { poojas = JSON.parse(raw); } catch(e) {}
        }
    }

    if (!Array.isArray(poojas) || poojas.length === 0) return;

    // First pooja (morning opening)
    const morningEl = document.getElementById('homeMorningPoojaTime');
    if (morningEl && poojas[0]) {
        morningEl.innerText = poojas[0].time_slot;
    }

    // Evening pooja
    const eveningEl = document.getElementById('homeEveningPoojaTime');
    const eveningPooja = poojas.find(p => p.time_slot.includes('மாலை') || p.time_slot.includes('இரவு')) || poojas[poojas.length - 1];
    if (eveningEl && eveningPooja) {
        eveningEl.innerText = eveningPooja.time_slot;
    }
}

function formatTamilDisplayDate(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return dateStr;
}

// 7. மென்மையான திரை உருளல் (Smooth Scrolling)
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

// 8. அறிவிப்பு செய்தி டோஸ்ட் (Toast Notification Helper)
function showToast(message, type = 'info') {
    let toast = document.getElementById('templeToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'templeToast';
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #2b0202;
            color: #ffffff;
            border-left: 5px solid #e5a93b;
            padding: 14px 22px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            z-index: 9999;
            transition: all 0.3s ease;
            transform: translateY(100px);
            opacity: 0;
            max-width: 90vw;
        `;
        document.body.appendChild(toast);
    }

    toast.innerText = message;
    toast.style.transform = 'translateY(0)';
    toast.style.opacity = '1';

    setTimeout(() => {
        toast.style.transform = 'translateY(100px)';
        toast.style.opacity = '0';
    }, 3500);
}

// 9. பக்க ஸ்க்ரோல் அனிமேஷன் (Universal Scroll Reveal Animation via IntersectionObserver)
function initScrollReveal() {
    // அனைத்து பக்கங்களிலும் உள்ள முக்கிய பகுதிகளை தானாகவே ஸ்க்ரோல் அனிமேஷனில் இணைத்தல்
    const autoSelectors = [
        '.section-header',
        '.feature-box',
        '.history-article > h2',
        '.history-article > p',
        '.pooja-card',
        '.month-event-group',
        '.festival-card',
        '.festival-poster-card',
        '.events-grid > *',
        '.event-card',
        '.album-card',
        '.photo-item',
        '.contact-box',
        '.bank-details-box',
        '.donation-banner',
        '.search-bar-wrap',
        '.footer-col'
    ];

    autoSelectors.forEach(selector => {
        document.querySelectorAll(selector).forEach(el => {
            if (!el.classList.contains('reveal') && 
                !el.closest('header') && 
                !el.closest('.site-header') && 
                !el.closest('.mobile-drawer') && 
                !el.closest('.modal') && 
                !el.closest('#lightboxModal')) {
                el.classList.add('reveal');
            }
        });
    });

    const reveals = document.querySelectorAll('.reveal');
    if (!reveals || reveals.length === 0) return;

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    obs.unobserve(entry.target);
                }
            });
        }, {
            root: null,
            threshold: 0.08,
            rootMargin: '0px 0px -40px 0px'
        });

        reveals.forEach(el => {
            const rect = el.getBoundingClientRect();
            // பக்கத்தின் ஆரம்ப பார்வையில் உள்ளவை உடனே தெரியும்படி செய்தல்
            if (rect.top < window.innerHeight * 0.88 && rect.bottom > 0) {
                el.classList.add('active');
            } else {
                observer.observe(el);
            }
        });
    } else {
        reveals.forEach(el => el.classList.add('active'));
    }
}

// பக்க முழு ஏற்றுதல் முடிந்ததும் மீண்டும் சரிபார்த்தல்
window.addEventListener('load', () => {
    setTimeout(initScrollReveal, 100);
});

