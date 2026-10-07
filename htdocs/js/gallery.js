/**
 * புகைப்படத் தொகுப்பு மற்றும் பதிவிறக்கங்கள் (Gallery & Download System)
 * Lightbox Modal, Individual HD Photo Download, and Album ZIP Download
 */

const API_BASE = (
    window.location.protocol === 'file:' || 
    ((window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') && window.location.port !== '8000')
)
    ? 'http://localhost:8000/api'
    : 'api';

let allAlbums = [];
let currentAlbumPhotos = [];
let currentPhotoIndex = 0;

document.addEventListener('DOMContentLoaded', () => {
    loadGalleryAlbums();
    setupLightboxEvents();
});

// 1. ஆல்பங்கள் மற்றும் புகைப்படங்களை ஏற்றுதல்
async function loadGalleryAlbums() {
    const container = document.getElementById('galleryContainer');
    if (!container) return;

    container.innerHTML = `
        <div style="text-align: center; padding: 50px 0; color: #8e151b;">
            <div style="font-size: 2.5rem; animation: spin 2s linear infinite;">ௐ</div>
            <p style="margin-top: 15px; font-weight: 600;">புகைப்பட ஆல்பங்கள் ஏற்றப்படுகின்றன...</p>
        </div>
    `;

    const urlParams = new URLSearchParams(window.location.search);
    const filterEventId = urlParams.get('event_id');

    // 1. Fetch live from server API first
    try {
        let fetchUrl = `${API_BASE}/gallery.php`;
        if (filterEventId) {
            fetchUrl += `?event_id=${filterEventId}`;
        }

        const res = await fetch(fetchUrl);
        if (res.ok) {
            const json = await res.json();
            if (json.status && json.data && json.data.length > 0) {
                allAlbums = Array.isArray(json.data) ? json.data : [json.data];
                renderAlbums(allAlbums);
                return;
            }
        }
    } catch(err) {
        console.warn('Gallery API fetch error, fallback to cache:', err);
    }

    // 2. Fallback to LocalStorage events if offline
    const localEvents = JSON.parse(localStorage.getItem('temple_events') || '[]');
    if (Array.isArray(localEvents) && localEvents.length > 0) {
        let matched = localEvents;
        if (filterEventId) {
            matched = localEvents.filter(e => e.id == filterEventId);
        }
        allAlbums = matched.map(e => ({
            event_id: e.id,
            event_title: e.title,
            event_date: e.event_date,
            category: e.category,
            cover_image: e.cover_image,
            total_photos: e.photos ? e.photos.length : 0,
            photos: e.photos || []
        }));
        renderAlbums(allAlbums);
        return;
    }

    allAlbums = getSampleAlbums();
    renderAlbums(allAlbums);
}

// 2. ஆல்பங்களை HTML ஆக வழங்குதல்
function renderAlbums(albums) {
    const container = document.getElementById('galleryContainer');
    if (!container) return;

    if (!albums || albums.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 60px 20px; background: #ffffff; border-radius: 12px; border: 1px solid #ede4d3;">
                <div style="font-size: 2.5rem; color: #d4af37; margin-bottom: 12px;"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg></div>
                <h3 style="color: #680509; margin-bottom: 8px;">தற்போது புகைப்படங்கள் எதுவும் இல்லை</h3>
                <p style="color: #6b7280;">விரைவில் புதிய நிகழ்வுகளின் புகைப்படங்கள் பதிவேற்றப்படும்.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = '';

    albums.forEach((album, albumIndex) => {
        const photos = album.photos || [];
        if (photos.length === 0) return;

        const albumCard = document.createElement('div');
        albumCard.className = 'album-card';
        albumCard.id = `album-${album.event_id}`;

        albumCard.innerHTML = `
            <div class="album-header">
                <div class="album-header-info">
                    <h3>${album.event_title || 'திருக்கோவில் நிகழ்வு'}</h3>
                    <span>${formatTamilDate(album.event_date)} | ${album.category || 'விசேஷம்'} | ${photos.length} புகைப்படங்கள்</span>
                </div>
                <div>
                    <button class="btn-download-album" onclick="downloadAlbumZip(${albumIndex})" title="முழு ஆல்பத்தையும் ஒரே ZIP கோப்பாக பதிவிறக்குக">
                        முழு ஆல்பத்தையும் ZIP ஆக டவுன்லோட் செய்
                    </button>
                </div>
            </div>

            <div class="photos-masonry">
                ${photos.map((photo, pIdx) => `
                    <div class="photo-item">
                        <img src="${photo.thumbnail_url || photo.image_url}" 
                             alt="${photo.caption || 'கோவில் புகைப்படம்'}" 
                             loading="lazy" 
                             onerror="this.src='https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=400&q=80'">
                        <div class="photo-overlay">
                            <div class="photo-caption">${photo.caption || album.event_title}</div>
                            <div class="photo-actions">
                                <button class="btn-icon-action" onclick="openLightbox(${albumIndex}, ${pIdx})" title="பெரிதாகப் பார்">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                </button>
                                <button class="btn-icon-action" onclick="downloadSinglePhoto('${photo.image_url}', '${photo.caption || 'temple_photo'}', ${photo.id || 0})" title="HD பதிவிறக்கம்">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        container.appendChild(albumCard);
    });
}

// 3. ஒற்றை புகைப்படத்தை HD தரத்தில் பதிவிறக்குதல் (Download Single Photo)
async function downloadSinglePhoto(imageUrl, caption, photoId = 0) {
    showToast('புகைப்படம் பதிவிறக்கம் தொடங்குகிறது...');

    // பதிவிறக்க எண்ணிக்கையை API-க்கு அனுப்புதல்
    if (photoId > 0) {
        fetch(`api/gallery.php?action=track_download&photo_id=${photoId}`).catch(() => {});
    }

    try {
        // Fetch as blob for proper cross-origin direct download
        const response = await fetch(imageUrl, { mode: 'cors' });
        const blob = await response.blob();
        const blobUrl = window.URL.createObjectURL(blob);

        const safeName = (caption || 'temple_divine_photo').replace(/[^a-zA-Z0-9_\u0B80-\u0BFF]/g, '_');
        const filename = `${safeName}.jpg`;

        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = blobUrl;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        
        window.URL.revokeObjectURL(blobUrl);
        document.body.removeChild(a);

        showToast('புகைப்படம் வெற்றிகரமாக பதிவிறக்கப்பட்டது!');
    } catch (err) {
        // Fallback for strict CORS restrictions
        const a = document.createElement('a');
        a.href = imageUrl;
        a.target = '_blank';
        a.download = 'temple_photo.jpg';
        a.click();
        showToast('புகைப்படம் புதிய தாவலில் திறக்கப்பட்டது. சேமித்துக் கொள்ளலாம்.');
    }
}

// 4. முழு ஆல்பத்தையும் ZIP கோப்பாக பதிவிறக்குதல் (JSZip Client-Side Download)
async function downloadAlbumZip(albumIndex) {
    const album = allAlbums[albumIndex];
    if (!album || !album.photos || album.photos.length === 0) return;

    if (typeof JSZip === 'undefined') {
        alert('JSZip நூலகம் இன்னும் தயாராகவில்லை. தயவுசெய்து பக்கத்தை புதுப்பிக்கவும்.');
        return;
    }

    const zip = new JSZip();
    showToast(`"${album.event_title}" ZIP கோப்பு தயாராகிறது... தயவுசெய்து காத்திருக்கவும்...`);

    const photos = album.photos;
    let successCount = 0;

    for (let i = 0; i < photos.length; i++) {
        const photo = photos[i];
        try {
            const res = await fetch(photo.image_url, { mode: 'cors' });
            const blob = await res.blob();
            const fileName = `photo_${i + 1}_${photo.caption ? photo.caption.substring(0, 15) : 'temple'}.jpg`;
            zip.file(fileName, blob);
            successCount++;
        } catch (e) {
            console.warn(`Error adding image ${i} to zip:`, e);
        }
    }

    if (successCount === 0) {
        alert('புகைப்படங்களை ZIP-ல் இணைப்பதில் சிக்கல் ஏற்பட்டது. தனித்தனியாக டவுன்லோட் செய்யவும்.');
        return;
    }

    zip.generateAsync({ type: 'blob' }).then(function (content) {
        const safeAlbumTitle = (album.event_title || 'Temple_Album').replace(/[^a-zA-Z0-9_\u0B80-\u0BFF]/g, '_');
        const zipName = `${safeAlbumTitle}_Photos.zip`;

        const a = document.createElement('a');
        a.href = URL.createObjectURL(content);
        a.download = zipName;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        showToast(`"${album.event_title}" ஆல்பம் ZIP கோப்பாக பதிவிறக்கப்பட்டது!`);
    });
}

// 5. லைட்பாக்ஸ் (Fullscreen Lightbox Modal)
function setupLightboxEvents() {
    const modal = document.getElementById('lightboxModal');
    if (!modal) return;

    const btnClose = document.getElementById('lightboxClose');
    const btnPrev = document.getElementById('lightboxPrev');
    const btnNext = document.getElementById('lightboxNext');

    if (btnClose) btnClose.addEventListener('click', closeLightbox);
    if (btnPrev) btnPrev.addEventListener('click', showPrevPhoto);
    if (btnNext) btnNext.addEventListener('click', showNextPhoto);

    // விசைப்பலகை விசைகள் (Arrow Keys Support)
    document.addEventListener('keydown', (e) => {
        if (!modal.classList.contains('active')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') showPrevPhoto();
        if (e.key === 'ArrowRight') showNextPhoto();
    });

    // வெளியில் கிளிக் செய்தால் மூடுதல்
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeLightbox();
    });
}

function openLightbox(albumIndex, photoIndex) {
    const album = allAlbums[albumIndex];
    if (!album || !album.photos) return;

    currentAlbumPhotos = album.photos;
    currentPhotoIndex = photoIndex;

    updateLightboxView();

    const modal = document.getElementById('lightboxModal');
    if (modal) modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const modal = document.getElementById('lightboxModal');
    if (modal) modal.classList.remove('active');
    document.body.style.overflow = '';
}

function showPrevPhoto() {
    if (currentAlbumPhotos.length <= 1) return;
    currentPhotoIndex = (currentPhotoIndex - 1 + currentAlbumPhotos.length) % currentAlbumPhotos.length;
    updateLightboxView();
}

function showNextPhoto() {
    if (currentAlbumPhotos.length <= 1) return;
    currentPhotoIndex = (currentPhotoIndex + 1) % currentAlbumPhotos.length;
    updateLightboxView();
}

function updateLightboxView() {
    const photo = currentAlbumPhotos[currentPhotoIndex];
    if (!photo) return;

    const img = document.getElementById('lightboxImg');
    const caption = document.getElementById('lightboxCaption');
    const downloadBtn = document.getElementById('lightboxDownloadBtn');
    const counter = document.getElementById('lightboxCounter');

    if (img) img.src = photo.image_url;
    if (caption) caption.innerText = photo.caption || 'கோவில் தரிசன காட்சி';
    if (counter) counter.innerText = `${currentPhotoIndex + 1} / ${currentAlbumPhotos.length}`;

    if (downloadBtn) {
        downloadBtn.onclick = () => downloadSinglePhoto(photo.image_url, photo.caption, photo.id);
    }
}

// தமிழ் தேதி வடிவம் (Tamil Date Formatter)
function formatTamilDate(dateStr) {
    if (!dateStr) return '';
    try {
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
    } catch (e) {}
    return dateStr;
}

// மாதிரி புகைப்படத் தொகுப்பு (Fallback Data)
function getSampleAlbums() {
    return [
        {
            event_id: 1,
            event_title: 'பங்குனி உத்திர திருக்கல்யாண வைபவம்',
            event_date: '2026-03-24',
            category: 'திருவிழா',
            photos: [
                { id: 101, image_url: 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=1200&q=80', thumbnail_url: 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=400&q=80', caption: 'திருக்கல்யாண மங்கல காட்சி' },
                { id: 102, image_url: 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1200&q=80', thumbnail_url: 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=400&q=80', caption: 'மாலை மாற்றுதல் உற்சவம்' },
                { id: 103, image_url: 'https://images.unsplash.com/photo-1567157577867-05ccb1388e66?auto=format&fit=crop&w=1200&q=80', thumbnail_url: 'https://images.unsplash.com/photo-1567157577867-05ccb1388e66?auto=format&fit=crop&w=400&q=80', caption: 'பூப்பல்லக்கு திருவீதி உலா' }
            ]
        },
        {
            event_id: 2,
            event_title: 'தமிழ் புத்தாண்டு 1008 சங்காபிஷேகம்',
            event_date: '2026-04-14',
            category: 'விசேஷ பூஜை',
            photos: [
                { id: 201, image_url: 'https://images.unsplash.com/photo-1609342122563-a43ac8917a3a?auto=format&fit=crop&w=1200&q=80', thumbnail_url: 'https://images.unsplash.com/photo-1609342122563-a43ac8917a3a?auto=format&fit=crop&w=400&q=80', caption: 'தங்கக் கவச அலங்காரம்' },
                { id: 202, image_url: 'https://images.unsplash.com/photo-1621847468516-1ed5d0df56fe?auto=format&fit=crop&w=1200&q=80', thumbnail_url: 'https://images.unsplash.com/photo-1621847468516-1ed5d0df56fe?auto=format&fit=crop&w=400&q=80', caption: '1008 சங்காபிஷேக தீபாராதனை' }
            ]
        }
    ];
}
