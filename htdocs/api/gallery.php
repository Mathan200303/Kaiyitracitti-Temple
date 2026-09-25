<?php
/**
 * புகைப்படத் தொகுப்பு API (Gallery & Albums API)
 * Get Photos, Albums, and Track Downloads
 */

require_once __DIR__ . '/db.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// 1. பதிவிறக்க எண்ணிக்கையைக் கூட்டுதல் (Track Photo Download)
if (isset($_GET['action']) && $_GET['action'] === 'track_download') {
    $photoId = intval($_GET['photo_id'] ?? 0);
    if ($pdo && $photoId > 0) {
        $stmt = $pdo->prepare("UPDATE event_photos SET download_count = download_count + 1 WHERE id = ?");
        $stmt->execute([$photoId]);
    }
    jsonResponse(true, 'பதிவிறக்கம் பதிவு செய்யப்பட்டது');
}

// 2. ஆல்பங்கள் மற்றும் புகைப்படங்களைப் பெறுதல் (Get Photo Albums)
if ($method === 'GET') {
    $eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

    if ($pdo) {
        try {
            if ($eventId > 0) {
                // குறிப்பிட்ட நிகழ்வின் புகைப்படங்கள்
                $stmt = $pdo->prepare("SELECT p.*, e.title AS event_title, e.event_date 
                                       FROM event_photos p 
                                       JOIN events e ON p.event_id = e.id 
                                       WHERE p.event_id = ? 
                                       ORDER BY p.id ASC");
                $stmt->execute([$eventId]);
                $photos = $stmt->fetchAll();
                jsonResponse(true, 'நிகழ்வு புகைப்படங்கள்', $photos);
            } else {
                // அனைத்து ஆல்பங்கள் (நிகழ்வு வாரியாக புகைப்படங்கள்)
                $stmt = $pdo->query("SELECT e.id AS event_id, e.title AS event_title, e.event_date, e.category, e.cover_image,
                                            COUNT(p.id) AS total_photos
                                     FROM events e
                                     JOIN event_photos p ON e.id = p.event_id
                                     WHERE e.is_published = 1
                                     GROUP BY e.id
                                     ORDER BY e.event_date DESC");
                $albums = $stmt->fetchAll();

                // ஆல்பங்களுக்கான புகைப்படங்களை எடுத்தல்
                foreach ($albums as &$album) {
                    $pStmt = $pdo->prepare("SELECT id, image_url, thumbnail_url, caption, download_count 
                                            FROM event_photos 
                                            WHERE event_id = ? 
                                            ORDER BY id ASC LIMIT 12");
                    $pStmt->execute([$album['event_id']]);
                    $album['photos'] = $pStmt->fetchAll();
                }

                jsonResponse(true, 'புகைப்பட ஆல்பங்கள்', $albums);
            }
        } catch (Exception $e) {
            jsonResponse(false, 'பிழை: ' . $e->getMessage(), null, 500);
        }
    } else {
        // மாதிரி ஆல்பங்கள் (Sample Albums Fallback)
        $sampleAlbums = [
            [
                'event_id' => 1,
                'event_title' => 'பங்குனி உத்திர திருக்கல்யாண வைபவம்',
                'event_date' => '2026-03-24',
                'category' => 'திருவிழா',
                'cover_image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=1200&q=80',
                'total_photos' => 3,
                'photos' => [
                    [
                        'id' => 101,
                        'image_url' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=1200&q=80',
                        'thumbnail_url' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=400&q=80',
                        'caption' => 'திருக்கல்யாண மங்கல காட்சி',
                        'download_count' => 45
                    ],
                    [
                        'id' => 102,
                        'image_url' => 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1200&q=80',
                        'thumbnail_url' => 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=400&q=80',
                        'caption' => 'மாலை மாற்றுதல் உற்சவம்',
                        'download_count' => 32
                    ],
                    [
                        'id' => 103,
                        'image_url' => 'https://images.unsplash.com/photo-1567157577867-05ccb1388e66?auto=format&fit=crop&w=1200&q=80',
                        'thumbnail_url' => 'https://images.unsplash.com/photo-1567157577867-05ccb1388e66?auto=format&fit=crop&w=400&q=80',
                        'caption' => 'பூப்பல்லக்கு திருவீதி உலா',
                        'download_count' => 19
                    ]
                ]
            ],
            [
                'event_id' => 2,
                'event_title' => 'தமிழ் புத்தாண்டு 1008 சங்காபிஷேகம்',
                'event_date' => '2026-04-14',
                'category' => 'விசேஷ பூஜை',
                'cover_image' => 'https://images.unsplash.com/photo-1609342122563-a43ac8917a3a?auto=format&fit=crop&w=1200&q=80',
                'total_photos' => 2,
                'photos' => [
                    [
                        'id' => 201,
                        'image_url' => 'https://images.unsplash.com/photo-1609342122563-a43ac8917a3a?auto=format&fit=crop&w=1200&q=80',
                        'thumbnail_url' => 'https://images.unsplash.com/photo-1609342122563-a43ac8917a3a?auto=format&fit=crop&w=400&q=80',
                        'caption' => 'தங்கக் கவச அலங்காரம்',
                        'download_count' => 68
                    ],
                    [
                        'id' => 202,
                        'image_url' => 'https://images.unsplash.com/photo-1621847468516-1ed5d0df56fe?auto=format&fit=crop&w=1200&q=80',
                        'thumbnail_url' => 'https://images.unsplash.com/photo-1621847468516-1ed5d0df56fe?auto=format&fit=crop&w=400&q=80',
                        'caption' => '1008 சங்காபிஷேக தீபாராதனை',
                        'download_count' => 54
                    ]
                ]
            ]
        ];

        jsonResponse(true, 'புகைப்பட ஆல்பங்கள் (Sample Data)', $sampleAlbums);
    }
}
