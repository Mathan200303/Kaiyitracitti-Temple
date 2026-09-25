<?php
/**
 * திருவிழாக்கள் API (Festivals API)
 */
require_once __DIR__ . '/db.php';

$pdo = getDB();

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM festivals ORDER BY id ASC");
        $festivals = $stmt->fetchAll();
        jsonResponse(true, 'திருவிழாக்கள் பட்டியல்', $festivals);
    } catch (Exception $e) {
        jsonResponse(false, $e->getMessage(), null, 500);
    }
} else {
    $sampleFestivals = [
        [
            'id' => 1,
            'name' => 'சித்திரை பெருவிழா (திருக்கல்யாணம் & தேரோட்டம்)',
            'tamil_month' => 'சித்திரை',
            'start_date' => '2026-04-18',
            'end_date' => '2026-04-28',
            'description' => '12 நாட்கள் கோலாகலமாக நடைபெறும் உலகப் பிரசித்தி பெற்ற சித்திரை பெருவிழா, கொடியேற்றம், மீனாட்சி பட்டாபிஷேகம், திருக்கல்யாணம், மற்றும் மாசி வீதிகளில் தேரோட்டம்.',
            'banner_image' => 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1200&q=80'
        ],
        [
            'id' => 2,
            'name' => 'ஆவணி மூலத் திருவிழா',
            'tamil_month' => 'ஆவணி',
            'start_date' => '2026-08-22',
            'end_date' => '2026-08-31',
            'description' => 'சிவபெருமானின் 64 திருவிளையாடல்கள் அரங்கேறும் சிறப்புத் திருவிழா. பிட்டுக்கு மண் சுமந்த நன்னாள் வைபவம்.',
            'banner_image' => 'https://images.unsplash.com/photo-1609342122563-a43ac8917a3a?auto=format&fit=crop&w=1200&q=80'
        ],
        [
            'id' => 3,
            'name' => 'நவராத்திரி பெருவிழா & கொலு வைபவம்',
            'tamil_month' => 'புரட்டாசி',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-19',
            'description' => 'அன்னை மீனாட்சி அம்மன் 9 நாட்களும் வெவ்வேறு திவ்ய அலங்காரங்களில் அருள்பாலிக்கும் மகா நவராத்திரி திருவிழா மற்றும் கண்கவர் கொலு காட்சி.',
            'banner_image' => 'https://images.unsplash.com/photo-1567157577867-05ccb1388e66?auto=format&fit=crop&w=1200&q=80'
        ],
        [
            'id' => 4,
            'name' => 'மகா சிவராத்திரி பெருவிழா',
            'tamil_month' => 'மாசி',
            'start_date' => '2026-02-15',
            'end_date' => '2026-02-16',
            'description' => 'இரவு முழுவதும் 4 கால சிறப்பு மகா ருத்ராபிஷேகம், அன்னாபிஷேகம், பஞ்சாட்சர ஜபம் மற்றும் நள்ளிரவு லிங்கோத்பவர் தரிசனம்.',
            'banner_image' => 'https://images.unsplash.com/photo-1621847468516-1ed5d0df56fe?auto=format&fit=crop&w=1200&q=80'
        ]
    ];
    jsonResponse(true, 'திருவிழாக்கள் பட்டியல் (Sample Data)', $sampleFestivals);
}
