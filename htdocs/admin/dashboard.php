<?php
/**
 * கோவில் நிர்வாக பலகை (Temple Admin Dashboard)
 * கைதடி வடக்கு கயிற்றசிட்டி அருள்மிகு கந்தசுவாமி தேவஸ்தானம்
 */
require_once __DIR__ . '/auth_check.php';

$totalEvents = 0;
$totalPhotos = 0;
$totalFestivals = 4;
$totalAnnouncements = 3;
$recentEvents = [];

try {
    if (file_exists(__DIR__ . '/../api/db.php')) {
        require_once __DIR__ . '/../api/db.php';
        $pdo = getDB();
        if ($pdo) {
            $totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn() ?: 0;
            $totalPhotos = $pdo->query("SELECT COUNT(*) FROM event_photos")->fetchColumn() ?: 0;
            $totalFestivals = $pdo->query("SELECT COUNT(*) FROM festivals")->fetchColumn() ?: 4;
            $totalAnnouncements = $pdo->query("SELECT COUNT(*) FROM announcements WHERE is_active = 1")->fetchColumn() ?: 3;
            
            $stmt = $pdo->query("SELECT e.*, COUNT(p.id) as photo_count 
                                 FROM events e 
                                 LEFT JOIN event_photos p ON e.id = p.event_id 
                                 GROUP BY e.id 
                                 ORDER BY e.event_date DESC LIMIT 20");
            $recentEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (Exception $e) {}

if (empty($recentEvents)) {
    $totalEvents = 3;
    $totalPhotos = 6;
    $totalFestivals = 4;
    $totalAnnouncements = 3;
}
?>
<!DOCTYPE html>
<html lang="ta">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | கைதடி அருள்மிகு கந்தசுவாமி தேவஸ்தானம்</title>
    <!-- Google Fonts for Tamil & English -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mukta+Malar:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --sidebar-width: 270px;
        }
        body {
            background-color: #f7f4ed;
            font-family: 'Mukta Malar', 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
            color: #333333;
            position: relative;
            min-height: 100vh;
        }

        /* Temple Gopuram background watermark (clearly visible) */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('../images/dashboard_bg.jpg'), url('../images/gopuram_front.jpg');
            background-repeat: no-repeat;
            background-position: center center;
            background-size: cover;
            opacity: 0.14;
            pointer-events: none;
            z-index: 0;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* SIDEBAR (IN ENGLISH) */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #380303 0%, #1f0202 100%);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.2);
            font-family: 'Plus Jakarta Sans', 'Mukta Malar', sans-serif;
        }
        .sidebar-brand {
            padding: 20px 18px;
            background: #2b0202;
            border-bottom: 2px solid var(--color-gold);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 2px solid var(--color-gold);
            overflow: hidden;
            flex-shrink: 0;
            background: #4a0505;
        }
        .sidebar-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .sidebar-brand h2 {
            font-size: 0.96rem;
            margin: 0;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.3;
        }
        .sidebar-brand p {
            margin: 2px 0 0;
            font-size: 0.76rem;
            color: #ffecd2;
        }
        .sidebar-nav {
            padding: 20px 0;
            flex-grow: 1;
            overflow-y: auto;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: #ebdcd0;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            border-left: 4px solid transparent;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .nav-item:hover, .nav-item.active {
            background-color: rgba(229, 169, 59, 0.18);
            color: var(--color-gold);
            border-left-color: var(--color-gold);
        }
        .nav-item .icon {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }
        .sidebar-footer {
            padding: 16px 20px;
            background: #240101;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .user-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .user-name {
            font-size: 0.88rem;
            font-weight: 600;
            color: #ffd8a8;
        }
        .btn-logout {
            background: #dc2626;
            color: #ffffff;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-logout:hover {
            background: #b91c1c;
        }

        /* MAIN CONTENT AREA */
        .admin-main {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 30px 40px;
            max-width: calc(100% - var(--sidebar-width));
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2d9cc;
        }
        .top-bar h1 {
            margin: 0;
            font-size: 1.65rem;
            color: var(--color-maroon);
            font-weight: 800;
        }
        .btn-website {
            background: #ffffff;
            border: 1.5px solid var(--color-gold);
            color: var(--color-maroon);
            padding: 9px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-website:hover {
            background: var(--color-gold);
            color: #2b0202;
        }

        /* STATS GRID */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(6px);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border-top: 4px solid var(--color-gold);
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .stat-card:nth-child(2) { border-top-color: #ea580c; }
        .stat-card:nth-child(3) { border-top-color: #0284c7; }
        .stat-card:nth-child(4) { border-top-color: #16a34a; }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: #fff8eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: var(--color-maroon);
        }
        .stat-info h3 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 800;
            color: #1f2937;
            line-height: 1;
        }
        .stat-info p {
            margin: 5px 0 0;
            font-size: 0.88rem;
            color: #6b7280;
            font-weight: 600;
        }

        /* TAB PANELS */
        .tab-panel {
            display: none;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(8px);
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 40px;
            border: 1px solid rgba(229, 169, 59, 0.2);
        }
        .tab-panel.active {
            display: block;
        }
        .tab-title {
            margin-top: 0;
            margin-bottom: 24px;
            font-size: 1.4rem;
            color: var(--color-maroon);
            font-weight: 800;
            border-bottom: 2px solid #f3ece1;
            padding-bottom: 12px;
        }

        /* FORMS & INPUTS */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 18px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #374151;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
            font-family: inherit;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--color-maroon);
            box-shadow: 0 0 0 3px rgba(104, 5, 9, 0.12);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 90px;
        }

        /* UPLOAD DROPZONE */
        .upload-dropzone {
            border: 2px dashed #d97706;
            background-color: #fffdfa;
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s;
            margin-bottom: 18px;
            position: relative;
        }
        .upload-dropzone:hover {
            background-color: #fff8e8;
            border-color: var(--color-maroon);
        }
        .upload-dropzone .upload-icon {
            font-size: 2.8rem;
            color: #d97706;
            margin-bottom: 10px;
        }
        .upload-dropzone input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 12px;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .preview-card {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            aspect-ratio: 1;
        }
        .preview-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .btn-remove-photo {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(220, 38, 38, 0.9);
            color: #ffffff;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 13px;
        }
        .upload-progress-bar {
            width: 100%;
            background: #e5e7eb;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            display: none;
            margin-bottom: 15px;
        }
        .upload-progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #d97706, #16a34a);
            transition: width 0.3s;
        }

        /* BUTTONS (IN ENGLISH) */
        .btn-submit {
            background: linear-gradient(135deg, var(--color-maroon) 0%, #941318 100%);
            color: #ffffff;
            border: none;
            padding: 13px 26px;
            border-radius: 8px;
            font-size: 1.02rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: inherit;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(104, 5, 9, 0.35);
        }

        .btn-secondary-action {
            background: #ffffff;
            border: 1.5px solid #d1d5db;
            color: #374151;
            padding: 12px 22px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            margin-left: 10px;
            transition: all 0.2s;
            font-family: inherit;
        }
        .btn-secondary-action:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        /* TABLES */
        .table-responsive {
            overflow-x: auto;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            text-align: left;
        }
        .admin-table th {
            background: #fbf9f4;
            padding: 14px 16px;
            font-weight: 700;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            font-size: 0.95rem;
        }
        .admin-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f3ece1;
            font-size: 0.92rem;
            vertical-align: middle;
        }
        .admin-table tr:hover {
            background-color: #fffdf9;
        }
        .table-thumb {
            width: 54px;
            height: 54px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid #ddd;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .badge-festival { background: #fef3c7; color: #92400e; }

        .btn-action-edit {
            background: #e0f2fe;
            color: #0369a1;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 700;
            margin-right: 6px;
            transition: all 0.2s;
            font-family: inherit;
        }
        .btn-action-edit:hover {
            background: #0284c7;
            color: #ffffff;
        }

        .btn-action-delete {
            background: #fee2e2;
            color: #b91c1c;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 700;
            transition: all 0.2s;
            font-family: inherit;
        }
        .btn-action-delete:hover {
            background: #dc2626;
            color: #ffffff;
        }

        .tip-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 14px 18px;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #1e40af;
            margin-bottom: 22px;
            line-height: 1.5;
        }

        @media (max-width: 900px) {
            .admin-sidebar {
                width: 70px;
            }
            .sidebar-brand h2, .sidebar-brand p, .nav-item span, .user-name {
                display: none;
            }
            .admin-main {
                margin-left: 70px;
                max-width: calc(100% - 70px);
                padding: 20px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <!-- SIDEBAR (IN ENGLISH) -->
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-avatar">
                <img src="../images/gopuram_front.jpg" alt="Kandaswamy Temple Gopuram">
            </div>
            <div>
                <h2>Kandaswamy Temple</h2>
                <p>Kaithady North, Jaffna</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item active" onclick="switchTab('tab-events-list')">
                <span class="icon">📅</span>
                <span>Daily Events (தினசரி நிகழ்வுகள்)</span>
            </div>
            <div class="nav-item" onclick="switchTab('tab-add-event')">
                <span class="icon">✨</span>
                <span>Create / Edit Event</span>
            </div>
            <div class="nav-item" onclick="switchTab('tab-history')">
                <span class="icon">📜</span>
                <span>Temple History (ஆலய வரலாறு)</span>
            </div>
            <div class="nav-item" onclick="switchTab('tab-announcements')">
                <span class="icon">📢</span>
                <span>Announcements</span>
            </div>
            <div class="nav-item" onclick="switchTab('tab-poojas')">
                <span class="icon">🔔</span>
                <span>Pooja Timings</span>
            </div>
            <div class="nav-item" onclick="switchTab('tab-settings')">
                <span class="icon">⚙️</span>
                <span>Temple Settings</span>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <span class="user-name">👤 <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn-logout" title="Exit to Login">Logout</a>
            </div>
        </div>
    </aside>

    <!-- MAIN DASHBOARD CONTENT -->
    <main class="admin-main">
        <div class="top-bar">
            <div>
                <h1>Temple Admin Dashboard</h1>
                <p style="margin: 4px 0 0; color: #6b7280;">Kaithady North Kayitrasitty Arulmigu Kandaswamy Devasthanam</p>
            </div>
            <a href="../index.html" class="btn-website">
                <span>🌐</span> View Public Website &rarr;
            </a>
        </div>

        <!-- STATS GRID -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-info">
                    <h3 id="statTotalEvents"><?php echo $totalEvents; ?></h3>
                    <p>Total Events</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📸</div>
                <div class="stat-info">
                    <h3 id="statTotalPhotos"><?php echo $totalPhotos; ?></h3>
                    <p>Uploaded Photos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🚩</div>
                <div class="stat-info">
                    <h3 id="statTotalFestivals"><?php echo $totalFestivals; ?></h3>
                    <p>Annual Festivals</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔔</div>
                <div class="stat-info">
                    <h3 id="statTotalNotices"><?php echo $totalAnnouncements; ?></h3>
                    <p>Active Notices</p>
                </div>
            </div>
        </div>

        <!-- TAB 1: EVENTS & PHOTOS LIST -->
        <div id="tab-events-list" class="tab-panel active">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="tab-title" style="margin-bottom: 0; border: none; padding: 0;">📅 Daily Events & Photo Albums (தினசரி நிகழ்வுகள்)</h2>
                <button class="btn-submit" onclick="prepareCreateEvent()" style="padding: 10px 18px; font-size: 0.95rem;">
                    + Add Daily Event (புதிய நிகழ்வு சேர்க்க)
                </button>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Cover</th>
                            <th>Event Title</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Photos</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="eventsTableBody">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: CREATE / EDIT EVENT -->
        <div id="tab-add-event" class="tab-panel">
            <h2 class="tab-title" id="formHeaderTitle">✨ Create / Edit Event & Photos</h2>

            <!-- Banner showing if we are editing an existing event -->
            <div id="editingBanner" style="display: none; background: #fef3c7; border: 1.5px solid #f59e0b; color: #92400e; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 700; justify-content: space-between; align-items: center;">
                <span>✏️ Editing Mode: Updating an existing event</span>
                <button type="button" onclick="cancelEditEvent()" style="background: #ffffff; border: 1px solid #d97706; color: #92400e; padding: 5px 12px; border-radius: 6px; cursor: pointer; font-weight: 700;">
                    Cancel Edit (Create New)
                </button>
            </div>
            
            <div class="tip-box">
                💡 <strong>Admin Edit Option:</strong> You can edit any existing event title, date, category, description, and upload more photos or remove specific photos!
            </div>

            <form id="createEventForm" onsubmit="handleEventSubmit(event)">
                <input type="hidden" id="editingEventId" value="">

                <div class="form-row">
                    <div class="form-group">
                        <label for="eventTitle">Event Title (நிகழ்வின் பெயர்) *</label>
                        <input type="text" id="eventTitle" class="form-control" placeholder="e.g. கந்தசஷ்டி சூரசம்ஹார பெருவிழா" required>
                    </div>
                    <div class="form-group">
                        <label for="eventDate">Event Date (தேதி) *</label>
                        <input type="date" id="eventDate" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="eventCategory">Category (வகை)</label>
                        <select id="eventCategory" class="form-control">
                            <option value="விசேஷ பூஜை">விசேஷ பூஜை (Special Pooja)</option>
                            <option value="சிறப்பு அபிஷேகம்">சிறப்பு அபிஷேகம் (Abhishekam)</option>
                            <option value="திருவிழா">திருவிழா (Festival / Utsavam)</option>
                            <option value="சஷ்டி விரதம்">சஷ்டி விரதம் (Sashti Fasting)</option>
                            <option value="அன்னதானம்">மகா அன்னதானம் (Annadhanam)</option>
                            <option value="தேரோட்டம்">தேரோட்டம் / சப்பறம் (Chariot)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="eventCoverUrl">Cover Image URL (Optional)</label>
                        <input type="url" id="eventCoverUrl" class="form-control" placeholder="First photo will be selected automatically if empty">
                    </div>
                </div>

                <div class="form-group">
                    <label for="eventDescription">Description (விளக்கம்)</label>
                    <textarea id="eventDescription" class="form-control" placeholder="Enter details of poojas, alankaram, prasadam, and celebrations..."></textarea>
                </div>

                <!-- Multi-Photo Upload Dropzone -->
                <div class="form-group">
                    <label>Event Photos (Upload Multiple Images) *</label>
                    <div class="upload-dropzone" id="dropzone">
                        <div class="upload-icon">📸</div>
                        <h4 style="margin: 0 0 6px;">Drag & Drop Photos Here or Click to Browse</h4>
                        <p style="margin: 0; color: #6b7280; font-size: 0.88rem;">Select 30+ photos at once (JPEG, PNG, WEBP)</p>
                        <input type="file" id="photoFilesInput" multiple accept="image/*" onchange="handleFileSelect(event)">
                    </div>

                    <div class="upload-progress-bar" id="uploadProgressBar">
                        <div class="upload-progress-fill" id="uploadProgressFill"></div>
                    </div>
                    <div id="uploadStatusText" style="font-size: 0.88rem; color: #d97706; font-weight: 600; margin-bottom: 10px;"></div>

                    <!-- Selected Preview Grid -->
                    <div class="preview-grid" id="previewGrid"></div>
                </div>

                <div style="display: flex; gap: 12px; align-items: center;">
                    <button type="submit" class="btn-submit" id="btnPublishEvent">
                        <span>🚀</span> Save & Publish Event
                    </button>
                    <button type="button" class="btn-secondary-action" id="btnCancelEdit" style="display: none;" onclick="cancelEditEvent()">
                        Cancel Edit
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB: TEMPLE HISTORY MANAGEMENT (ஆலய வரலாறு) -->
        <div id="tab-history" class="tab-panel">
            <h2 class="tab-title">📜 Manage Temple History (ஆலய வரலாறு திருத்துதல்)</h2>

            <div class="tip-box">
                💡 <strong>ஆலய வரலாறு மேலாண்மை:</strong> திருக்கோவிலின் தோற்றம், ராஜகோபுர சிறப்பு, திருவிழாக்கள் மற்றும் அன்னதானம் பற்றிய விபரங்களை இங்கு எளிதாக திருத்தி சேமிக்கலாம். மாற்றங்கள் உடனே <a href="../history.html" target="_blank" style="font-weight:700; color:#1e40af;">history.html</a> பக்கத்தில் காண்பிக்கப்படும்!
            </div>

            <form onsubmit="handleHistorySave(event)">
                <div style="background: #ffffff; padding: 22px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: var(--color-maroon);">1. முதன்மை தலைப்பு & அறிமுகம்</h3>
                    <div class="form-group">
                        <label>பக்கத்தின் பிரதான தலைப்பு (Page Hero Title)</label>
                        <input type="text" id="histHeroTitle" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>துணைத் தலைப்பு / சுருக்கம் (Hero Subtitle)</label>
                        <input type="text" id="histHeroSubtitle" class="form-control" required>
                    </div>
                </div>

                <div style="background: #ffffff; padding: 22px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: var(--color-maroon);">2. தோற்றமும் வழிபாட்டுச் சிறப்பும்</h3>
                    <div class="form-group">
                        <label>பிரிவு தலைப்பு (Section Title)</label>
                        <input type="text" id="histSec1Title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>முழு வரலாற்று விளக்கம் (History Content)</label>
                        <textarea id="histSec1Text" class="form-control" style="min-height: 110px;" required></textarea>
                    </div>
                </div>

                <div style="background: #ffffff; padding: 22px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: var(--color-maroon);">3. வண்ணமிகு ராஜகோபுரமும் சிற்ப அழகும்</h3>
                    <div class="form-group">
                        <label>பிரிவு தலைப்பு (Section Title)</label>
                        <input type="text" id="histSec2Title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>கோபுர விளக்கம் (Rajagopuram Details)</label>
                        <textarea id="histSec2Text" class="form-control" style="min-height: 110px;" required></textarea>
                    </div>
                </div>

                <div style="background: #ffffff; padding: 22px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: var(--color-maroon);">4. கந்தசஷ்டி மற்றும் வருடாந்திர பெருவிழாக்கள்</h3>
                    <div class="form-group">
                        <label>பிரிவு தலைப்பு (Section Title)</label>
                        <input type="text" id="histSec3Title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>உற்சவ விபரங்கள் (Festivals Details)</label>
                        <textarea id="histSec3Text" class="form-control" style="min-height: 110px;" required></textarea>
                    </div>
                </div>

                <div style="background: #ffffff; padding: 22px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: var(--color-maroon);">5. நித்திய அன்னதானமும் ஆலயத் திருப்பணியும்</h3>
                    <div class="form-group">
                        <label>பிரிவு தலைப்பு (Section Title)</label>
                        <input type="text" id="histSec4Title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>அன்னதான விளக்கம் (Annadhanam & Renovation)</label>
                        <textarea id="histSec4Text" class="form-control" style="min-height: 110px;" required></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-submit" style="padding: 14px 28px; font-size: 1.05rem;">
                    <span>💾</span> Save Temple History (ஸ்தல வரலாற்றைச் சேமி)
                </button>
            </form>
        </div>

        <!-- TAB 3: ANNOUNCEMENTS MANAGEMENT -->
        <div id="tab-announcements" class="tab-panel">
            <h2 class="tab-title">📢 Manage Temple Announcements</h2>
            
            <form onsubmit="handleAnnouncementSubmit(event)" style="background: #fffdfa; padding: 20px; border-radius: 10px; border: 1px solid #ebdcd0; margin-bottom: 25px;">
                <input type="hidden" id="editingAnnId" value="">
                <h3 id="annFormTitle" style="margin-top: 0; color: var(--color-maroon); font-size: 1.15rem;">+ Create New Announcement</h3>
                <div class="form-group">
                    <label>Announcement Title (தலைப்பு) *</label>
                    <input type="text" id="annTitle" class="form-control" placeholder="e.g. கந்தசஷ்டி விரத ஏற்பாடுகள்" required>
                </div>
                <div class="form-group">
                    <label>Content (முழு விபரம்) *</label>
                    <textarea id="annContent" class="form-control" placeholder="Enter complete announcement text..." required></textarea>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-submit" id="btnSaveAnn" style="padding: 10px 20px; font-size: 0.95rem;">
                        Save Announcement
                    </button>
                    <button type="button" class="btn-secondary-action" id="btnCancelAnn" style="display: none; padding: 10px 18px;" onclick="cancelEditAnn()">
                        Cancel
                    </button>
                </div>
            </form>

            <h3 style="color: var(--color-maroon); margin-bottom: 12px;">Active Announcements List</h3>
            <div id="announcementsListContainer"></div>
        </div>

        <!-- TAB 4: POOJA TIMINGS MANAGEMENT -->
        <div id="tab-poojas" class="tab-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="tab-title" style="margin-bottom: 0; border: none; padding: 0;">🔔 Manage Daily Pooja Timings</h2>
                <button class="btn-submit" onclick="openAddPoojaModal()" style="padding: 10px 18px; font-size: 0.95rem;">
                    + Add New Pooja Timing
                </button>
            </div>

            <div class="tip-box">
                💡 <strong>Pooja Timings Control:</strong> You can edit any pooja timing, add new schedules, or delete pooja slots with the Delete button below!
            </div>

            <!-- Edit Pooja Modal / Form -->
            <div id="poojaEditBox" style="display: none; background: #fffdfa; border: 2px solid var(--color-gold); padding: 22px; border-radius: 10px; margin-bottom: 25px;">
                <h3 id="poojaEditTitle" style="margin-top: 0; color: var(--color-maroon);">✏️ Edit Pooja Schedule</h3>
                <input type="hidden" id="poojaEditIndex" value="">
                <div class="form-row">
                    <div class="form-group">
                        <label>Pooja Name (பூஜையின் பெயர்) *</label>
                        <input type="text" id="editPoojaName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Time Slot (நேரம்) *</label>
                        <input type="text" id="editPoojaTime" class="form-control" placeholder="e.g. காலை 05:30 - 06:00" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Deity (மூர்த்தி / தேவதை)</label>
                        <input type="text" id="editPoojaDeity" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Description (விளக்கம்)</label>
                        <input type="text" id="editPoojaDesc" class="form-control">
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn-submit" onclick="savePoojaEdit()" style="padding: 10px 20px; font-size: 0.95rem;">
                        Save Pooja Timing
                    </button>
                    <button type="button" class="btn-secondary-action" onclick="closePoojaEdit()" style="padding: 10px 18px;">
                        Cancel
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pooja Name</th>
                            <th>Time Slot</th>
                            <th>Deity</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="poojasTableBody"></tbody>
                </table>
            </div>
        </div>

        <!-- TAB 5: TEMPLE SETTINGS -->
        <div id="tab-settings" class="tab-panel">
            <h2 class="tab-title">⚙️ Temple Information & Cloudinary Settings</h2>
            
            <form onsubmit="handleSettingsSave(event)">
                <div style="background: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 25px;">
                    <h3 style="margin-top: 0; color: var(--color-maroon);">🏛️ Temple Contact & Bank Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Temple Name (கோவில் பெயர்)</label>
                            <input type="text" id="cfgTempleName" class="form-control" value="கைதடி வடக்கு கயிற்றசிட்டி அருள்மிகு கந்தசுவாமி தேவஸ்தானம்">
                        </div>
                        <div class="form-group">
                            <label>Tagline (கோஷம் / வாசகம்)</label>
                            <input type="text" id="cfgTagline" class="form-control" value="வெற்றிவேல் முருகனுக்கு அரோகரா! வீரவேல் முருகனுக்கு அரோகரா!">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone Numbers (தொலைபேசி எண்கள்)</label>
                            <input type="text" id="cfgPhone" class="form-control" value="+94 77 123 4567, 021-2223456">
                        </div>
                        <div class="form-group">
                            <label>Email Address (மின்னஞ்சல்)</label>
                            <input type="email" id="cfgEmail" class="form-control" value="kaithadykandanswamy@gmail.com">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Bank Name & Account No (வங்கி கணக்கு)</label>
                            <input type="text" id="cfgBank" class="form-control" value="Bank of Ceylon (BOC): 78291045">
                        </div>
                        <div class="form-group">
                            <label>Branch (கிளை)</label>
                            <input type="text" id="cfgBranch" class="form-control" value="சாவகச்சேரி / கைதடி கிளை, யாழ்ப்பாணம்">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Full Address (முழு முகவரி)</label>
                        <textarea id="cfgAddress" class="form-control">கயிற்றசிட்டி, கைதடி வடக்கு, தென்மராட்சி, யாழ்ப்பாணம், இலங்கை.</textarea>
                    </div>
                </div>

                <div style="background: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 25px;">
                    <h3 style="margin-top: 0; color: var(--color-maroon);">☁️ Cloudinary Photo Storage Settings</h3>
                    <p style="color: #6b7280; font-size: 0.9rem; margin-top: -6px;">Enter Cloudinary keys for direct client-side photo uploading (InfinityFree free hosting friendly).</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Cloud Name</label>
                            <input type="text" id="cfgCloudName" class="form-control" value="demo" placeholder="e.g. dy1234abc">
                        </div>
                        <div class="form-group">
                            <label>Upload Preset (Unsigned)</label>
                            <input type="text" id="cfgUploadPreset" class="form-control" value="temple_photos" placeholder="e.g. temple_photos">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <span>💾</span> Save All Settings
                </button>
            </form>
        </div>
    </main>
</div>

<script>
    document.getElementById('eventDate').valueAsDate = new Date();

    function switchTab(tabId) {
        document.querySelectorAll('.tab-panel').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));

        const targetTab = document.getElementById(tabId);
        if (targetTab) targetTab.classList.add('active');

        const tabIds = ['tab-events-list', 'tab-add-event', 'tab-history', 'tab-announcements', 'tab-poojas', 'tab-settings'];
        const index = tabIds.indexOf(tabId);
        const navItems = document.querySelectorAll('.nav-item');
        if (navItems[index]) navItems[index].classList.add('active');
    }

    // Default Events
    let defaultEvents = [
        { 
            id: 1, 
            title: 'கந்தசஷ்டி பெருவிழா - சூரசம்ஹார வைபவம்', 
            event_date: '2026-11-15', 
            category: 'திருவிழா', 
            description: 'கந்தசஷ்டி நன்னாளில் தேவஸ்தானத்தில் பக்தர்கள் சூழ நடைபெற்ற சூரசம்ஹார வைபவம் மற்றும் மாலையில் நடைபெற்ற திருக்கல்யாண உற்சவம்.',
            cover_image: '../images/gopuram_front.jpg', 
            photo_count: 3,
            photos: [
                { id: 101, previewUrl: '../images/gopuram_front.jpg', image_url: '../images/gopuram_front.jpg', caption: 'ராஜகோபுர திவ்ய தரிசனம் (ஓம் முருகா)' },
                { id: 102, previewUrl: '../images/gopuram_side.jpg', image_url: '../images/gopuram_side.jpg', caption: 'தேவஸ்தான கோபுர அழகிய தோற்றம்' },
                { id: 103, previewUrl: '../images/gopuram_front.jpg', image_url: '../images/gopuram_front.jpg', caption: 'துவாரபாலகர் மற்றும் கோபுர முகப்பு' }
            ]
        },
        { 
            id: 2, 
            title: 'வைகாசி விசாக பெருவிழா 108 பாலாபிஷேகம்', 
            event_date: '2026-06-02', 
            category: 'சிறப்பு அபிஷேகம்', 
            description: 'முருகப்பெருமானின் அவதாரத் திருநாளை முன்னிட்டு மூலவருக்கு 108 சங்காபிஷேகமும் தங்கக் கவச அலங்காரமும் நடைபெற்றது.',
            cover_image: '../images/gopuram_side.jpg', 
            photo_count: 2,
            photos: [
                { id: 201, previewUrl: '../images/gopuram_side.jpg', image_url: '../images/gopuram_side.jpg', caption: '108 சங்காபிஷேகம்' },
                { id: 202, previewUrl: '../images/gopuram_front.jpg', image_url: '../images/gopuram_front.jpg', caption: 'தங்கக் கவச தரிசனம்' }
            ]
        },
        { 
            id: 3, 
            title: 'தைப்பூச நன்னாள் - மயில் வாகன பவனி', 
            event_date: '2026-02-01', 
            category: 'உற்சவம்', 
            description: 'தைப்பூச நன்னாளில் கந்தப்பெருமான் வள்ளி தெய்வானை சமேதராக மயில் வாகனத்தில் எழுந்தருளி திருவீதி உலா வந்த காட்சி.',
            cover_image: '../images/gopuram_front.jpg', 
            photo_count: 1,
            photos: [
                { id: 301, previewUrl: '../images/gopuram_front.jpg', image_url: '../images/gopuram_front.jpg', caption: 'மயில் வாகன பவனி' }
            ]
        }
    ];

    // Default Poojas
    let defaultPoojas = [
        { name: 'காலை பூஜை', time_slot: 'காலை 6.00 மணி', deity: 'மூலவர் & பரிவார மூர்த்திகள்', desc: 'வெள்ளிக்கிழமை காலை விசேட அபிஷேகம் மற்றும் மகா தீபாராதனை' },
        { name: 'உச்சிக்கால பூஜை', time_slot: 'நண்பகல் 12.00 மணி', deity: 'மூலவர் கந்தசுவாமி', desc: 'நண்பகல் உச்சிக்கால சிறப்பு நைவேத்திய தீபாராதனை மற்றும் பிரசாத விநியோகம்' },
        { name: 'சாயரட்சை பூஜை', time_slot: 'மாலை 6.00 மணி', deity: 'சுவாமி & அம்மன்', desc: 'மாலை நேர விசேட அலங்கார தீபாராதனை மற்றும் திருப்புகழ் பாராயணம்' }
    ];

    // Default Announcements
    let defaultAnnouncements = [
        { id: 1, title: 'தினசரி நித்திய அன்னதான திட்டம்', content: 'தினமும் மதியம் 12:15 மணிக்கு பக்தர்களுக்கு அறுசுவை அன்னதானம் வழங்கப்படுகிறது.' },
        { id: 2, title: 'சஷ்டி விரத சிறப்பு வழிபாடு', content: 'மாதாந்திர சஷ்டி நன்னாளில் மாலை 5:00 மணிக்கு கந்தப்பெருமானுக்கு 108 திரவியங்களால் சிறப்பு அபிஷேக ஆராதனைகள் நடைபெறும்.' },
        { id: 3, title: 'திருக்கோவில் திருப்பணி நன்கொடை', content: 'தேவஸ்தான ராஜகோபுரம் மற்றும் நந்தவனம் அமைக்கும் திருப்பணிக்கு பக்தர்கள் தங்களின் தாராள பங்களிப்பை வழங்கலாம்.' }
    ];

    // API BASE PATH - Automatically connects to local PHP Server on port 8000 if opened via another port
    const API_BASE = (
        window.location.protocol === 'file:' || 
        ((window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') && window.location.port !== '8000')
    )
        ? 'http://localhost:8000/api'
        : '../api';

    // Safe API Fetcher that handles JSON, raw PHP warning, and network errors
    async function apiFetch(endpoint, options = {}) {
        const url = endpoint.startsWith('http') ? endpoint : `${API_BASE}/${endpoint.replace(/^\//, '')}`;
        const res = await fetch(url, options);
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Non-JSON response from', url, text);
            if (text.includes('<?php')) {
                alert('⚠️ சேவையகம் PHP கோடை இயக்கவில்லை! (PHP Server is not executing).\n\nதயவுசெய்து Browser-ல் http://localhost:8000 முகவரியில் திறக்கவும்.');
            }
            throw new Error(text || 'Network request failed');
        }
    }

    // LOAD EVENTS TABLE (Synced with Database via Network API)
    async function loadEventsTable() {
        const tbody = document.getElementById('eventsTableBody');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px; color: #888;">⏳ Loading events from server...</td></tr>';

        let events = [];
        try {
            const json = await apiFetch('events.php');
            if (json.status && Array.isArray(json.data)) {
                events = json.data;
                localStorage.setItem('temple_events', JSON.stringify(events));
            }
        } catch (err) {
            console.warn('API fetch failed, falling back to local cache', err);
        }

        if (events.length === 0) {
            const stored = localStorage.getItem('temple_events');
            events = stored ? JSON.parse(stored) : defaultEvents;
        }

        tbody.innerHTML = '';
        let totalPhotos = 0;

        if (events.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 25px; color: #777;">நிகழ்வுகள் எதுவும் இல்லை (No events recorded).</td></tr>';
            document.getElementById('statTotalEvents').innerText = '0';
            document.getElementById('statTotalPhotos').innerText = '0';
            return;
        }

        events.forEach(ev => {
            const count = ev.photo_count || (ev.photos ? ev.photos.length : 0);
            totalPhotos += parseInt(count) || 0;

            const tr = document.createElement('tr');
            tr.id = `event-row-${ev.id}`;
            tr.innerHTML = `
                <td><img src="${ev.cover_image || '../images/gopuram_front.jpg'}" class="table-thumb" alt="Cover" onerror="this.src='../images/kovil_front_hero.jpg'"></td>
                <td><strong>${ev.title}</strong></td>
                <td>${ev.event_date}</td>
                <td><span class="badge badge-festival">${ev.category || 'உற்சவம்'}</span></td>
                <td><strong>${count}</strong> photos</td>
                <td>
                    <button class="btn-action-edit" onclick="editEvent(${ev.id})" title="Edit Event Details & Photos">
                        ✏️ Edit
                    </button>
                    <button class="btn-action-delete" onclick="deleteEvent(${ev.id})" title="Delete Event">
                        🗑️ Delete
                    </button>
                    <a href="../gallery.html?event_id=${ev.id}" target="_blank" style="color: #2563eb; text-decoration: none; margin-left: 8px; font-weight: 700; font-size: 0.85rem;">
                        View Album
                    </a>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('statTotalEvents').innerText = events.length;
        document.getElementById('statTotalPhotos').innerText = totalPhotos;
    }

    // EDIT EVENT (Pre-populates the form with existing event data)
    function editEvent(eventId) {
        const stored = localStorage.getItem('temple_events');
        const events = stored ? JSON.parse(stored) : defaultEvents;
        const ev = events.find(e => e.id == eventId);
        if (!ev) return;

        // Switch to Add/Edit tab
        switchTab('tab-add-event');

        // Populate fields
        document.getElementById('editingEventId').value = ev.id;
        document.getElementById('eventTitle').value = ev.title;
        document.getElementById('eventDate').value = ev.event_date;
        document.getElementById('eventCategory').value = ev.category || 'விசேஷ பூஜை';
        document.getElementById('eventDescription').value = ev.description || '';
        document.getElementById('eventCoverUrl').value = ev.cover_image || '';

        // Photos
        selectedPhotos = [];
        if (ev.photos && ev.photos.length > 0) {
            ev.photos.forEach(p => {
                selectedPhotos.push({
                    previewUrl: p.image_url || p.previewUrl,
                    image_url: p.image_url || p.previewUrl,
                    caption: p.caption || ''
                });
            });
        }
        renderPreviews();

        // Show editing UI
        document.getElementById('formHeaderTitle').innerText = '✏️ Edit Event Details & Photos';
        document.getElementById('btnPublishEvent').innerHTML = '<span>💾</span> Save Event Changes';
        document.getElementById('editingBanner').style.display = 'flex';
        document.getElementById('btnCancelEdit').style.display = 'inline-block';

        // Scroll to form top
        window.scrollTo({ top: 120, behavior: 'smooth' });
    }

    function cancelEditEvent() {
        document.getElementById('createEventForm').reset();
        document.getElementById('editingEventId').value = '';
        selectedPhotos = [];
        renderPreviews();

        document.getElementById('formHeaderTitle').innerText = '✨ Create / Edit Event & Photos';
        document.getElementById('btnPublishEvent').innerHTML = '<span>🚀</span> Save & Publish Event';
        document.getElementById('editingBanner').style.display = 'none';
        document.getElementById('btnCancelEdit').style.display = 'none';
    }

    function prepareCreateEvent() {
        cancelEditEvent();
        switchTab('tab-add-event');
    }

    // DELETE EVENT (Network Synced)
    async function deleteEvent(eventId) {
        if (!confirm('இந்த நிகழ்வை நிச்சயமாக நீக்க விரும்புகிறீர்களா? (Are you sure you want to delete this event?)')) {
            return;
        }

        try {
            const json = await apiFetch(`events.php?action=delete&id=${eventId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            if (json.status) {
                alert('✅ ' + (json.message || 'நிகழ்வு வெற்றிகரமாக நீக்கப்பட்டது!'));
            }
        } catch (err) {
            console.error('Delete network request failed:', err);
            alert('⚠️ ' + err.message);
        }

        await loadEventsTable();
    }

    // MULTI-PHOTO UPLOAD HANDLING
    let selectedPhotos = [];

    function handleFileSelect(e) {
        const files = Array.from(e.target.files);
        if (files.length === 0) return;

        const statusText = document.getElementById('uploadStatusText');
        statusText.innerText = `${files.length} புகைப்படங்கள் தேர்ந்தெடுக்கப்பட்டுள்ளன...`;

        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = function(evt) {
                selectedPhotos.push({
                    file: file,
                    previewUrl: evt.target.result,
                    caption: file.name.replace(/\.[^/.]+$/, "")
                });
                renderPreviews();
            };
            reader.readAsDataURL(file);
        });
    }

    function renderPreviews() {
        const grid = document.getElementById('previewGrid');
        grid.innerHTML = '';
        selectedPhotos.forEach((item, index) => {
            const card = document.createElement('div');
            card.className = 'preview-card';
            card.innerHTML = `
                <img src="${item.previewUrl}" alt="Photo ${index + 1}">
                <button type="button" class="btn-remove-photo" onclick="removePhoto(${index})" title="Remove photo">&times;</button>
            `;
            grid.appendChild(card);
        });

        const status = document.getElementById('uploadStatusText');
        if (selectedPhotos.length > 0) {
            status.innerText = `மொத்தம் ${selectedPhotos.length} புகைப்படங்கள் தயார் நிலையில் உள்ளன.`;
        } else {
            status.innerText = '';
        }
    }

    function removePhoto(index) {
        selectedPhotos.splice(index, 1);
        renderPreviews();
    }

    // SUBMIT EVENT (CREATE OR UPDATE - Network Synced)
    async function handleEventSubmit(e) {
        e.preventDefault();

        const editingId = document.getElementById('editingEventId').value;
        const title = document.getElementById('eventTitle').value.trim();
        const eventDate = document.getElementById('eventDate').value;
        const category = document.getElementById('eventCategory').value;
        const description = document.getElementById('eventDescription').value.trim();
        let coverUrl = document.getElementById('eventCoverUrl').value.trim();

        if (!title || !eventDate) {
            alert('தயவுசெய்து தலைப்பு மற்றும் தேதியை உள்ளிடவும்.');
            return;
        }

        const btn = document.getElementById('btnPublishEvent');
        const origBtnText = btn.innerHTML;
        btn.innerHTML = '<span>⏳</span> சேமிக்கப்படுகிறது...';
        btn.disabled = true;

        // Format photos
        let photosList = selectedPhotos.map((p, idx) => ({
            id: Date.now() + idx,
            image_url: p.previewUrl,
            caption: p.caption || title
        }));

        if (!coverUrl && photosList.length > 0) {
            coverUrl = photosList[0].image_url;
        }

        // Send POST Network Request to API
        try {
            const json = await apiFetch('events.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: editingId ? parseInt(editingId) : null,
                    title: title,
                    event_date: eventDate,
                    category: category,
                    description: description,
                    cover_image: coverUrl || '../images/gopuram_front.jpg',
                    photos: photosList
                })
            });

            if (json.status) {
                alert('✅ ' + (json.message || 'நிகழ்வு வெற்றிகரமாக சேமிக்கப்பட்டது!'));
            } else {
                alert('⚠️ ' + (json.message || 'Error saving event.'));
            }
        } catch (err) {
            console.error('Network request failed:', err);
            alert('⚠️ ' + err.message);
        }

        btn.innerHTML = origBtnText;
        btn.disabled = false;

        cancelEditEvent();
        await loadEventsTable();
        switchTab('tab-events-list');
    }

    // ANNOUNCEMENTS MANAGEMENT (Network Synced)
    async function loadAnnouncements() {
        const container = document.getElementById('announcementsListContainer');
        container.innerHTML = '<div style="text-align:center; padding: 20px; color: #888;">⏳ Loading announcements from server...</div>';

        let list = [];
        try {
            const json = await apiFetch('announcements.php');
            if (json.status && Array.isArray(json.data)) {
                list = json.data;
                localStorage.setItem('temple_announcements', JSON.stringify(list));
            }
        } catch (err) {
            console.warn('Announcements fetch failed', err);
        }

        if (list.length === 0) {
            const stored = localStorage.getItem('temple_announcements');
            list = stored ? JSON.parse(stored) : defaultAnnouncements;
        }

        container.innerHTML = '';
        if (list.length === 0) {
            container.innerHTML = '<div style="text-align: center; color: #888; padding: 20px;">முக்கிய அறிவிப்புகள் எதுவும் இல்லை (No announcements yet).</div>';
            document.getElementById('statTotalNotices').innerText = '0';
            return;
        }

        list.forEach(item => {
            const div = document.createElement('div');
            div.style.cssText = 'background: #ffffff; border-left: 4px solid #d97706; padding: 16px 20px; border-radius: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.04);';
            div.innerHTML = `
                <div style="flex-grow: 1; padding-right: 15px;">
                    <h4 style="margin: 0 0 5px; color: #1f2937; font-size: 1.05rem;">📢 ${item.title}</h4>
                    <p style="margin: 0; color: #4b5563; font-size: 0.92rem; line-height: 1.4;">${item.content}</p>
                </div>
                <div style="flex-shrink: 0; display: flex; gap: 8px;">
                    <button class="btn-action-edit" onclick="editAnnouncement(${item.id})">✏️ Edit</button>
                    <button class="btn-action-delete" onclick="deleteAnnouncement(${item.id})">🗑️ Delete</button>
                </div>
            `;
            container.appendChild(div);
        });

        document.getElementById('statTotalNotices').innerText = list.length;
    }

    function editAnnouncement(id) {
        const stored = localStorage.getItem('temple_announcements');
        const list = stored ? JSON.parse(stored) : defaultAnnouncements;
        const item = list.find(a => a.id == id);
        if (!item) return;

        document.getElementById('editingAnnId').value = item.id;
        document.getElementById('annTitle').value = item.title;
        document.getElementById('annContent').value = item.content;
        document.getElementById('annFormTitle').innerText = '✏️ Edit Announcement';
        document.getElementById('btnSaveAnn').innerText = 'Save Changes';
        document.getElementById('btnCancelAnn').style.display = 'inline-block';

        window.scrollTo({ top: 300, behavior: 'smooth' });
    }

    function cancelEditAnn() {
        document.getElementById('editingAnnId').value = '';
        document.getElementById('annTitle').value = '';
        document.getElementById('annContent').value = '';
        document.getElementById('annFormTitle').innerText = '+ Create New Announcement';
        document.getElementById('btnSaveAnn').innerText = 'Save Announcement';
        document.getElementById('btnCancelAnn').style.display = 'none';
    }

    // SUBMIT ANNOUNCEMENT (Makes Network POST to api/announcements.php)
    async function handleAnnouncementSubmit(e) {
        e.preventDefault();
        const id = document.getElementById('editingAnnId').value;
        const title = document.getElementById('annTitle').value.trim();
        const content = document.getElementById('annContent').value.trim();

        if (!title || !content) return;

        try {
            const json = await apiFetch('announcements.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: id ? parseInt(id) : null,
                    title: title,
                    content: content
                })
            });
            if (json.status) {
                alert('✅ ' + (json.message || 'அறிவிப்பு வெற்றிகரமாக சேமிக்கப்பட்டது!'));
            } else {
                alert('⚠️ ' + (json.message || 'Error'));
            }
        } catch (err) {
            console.error('Announcement submit error:', err);
            alert('⚠️ ' + err.message);
        }

        cancelEditAnn();
        await loadAnnouncements();
    }

    // DELETE ANNOUNCEMENT (Makes Network Request)
    async function deleteAnnouncement(id) {
        if (!confirm('இந்த அறிவிப்பை நீக்க வேண்டுமா? (Delete this announcement?)')) return;

        try {
            const json = await apiFetch(`announcements.php?action=delete&id=${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            if (json.status) {
                alert('✅ ' + (json.message || 'அறிவிப்பு நீக்கப்பட்டது!'));
            }
        } catch (err) {
            console.error('Announcement delete error:', err);
            alert('⚠️ ' + err.message);
        }

        await loadAnnouncements();
    }

    // POOJA TIMINGS MANAGEMENT (Network Synced)
    let currentPoojas = [];

    async function loadPoojas() {
        const tbody = document.getElementById('poojasTableBody');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px; color: #888;">⏳ Loading pooja schedules from server...</td></tr>';

        try {
            const json = await apiFetch('poojas.php');
            if (json.status && Array.isArray(json.data)) {
                currentPoojas = json.data;
                localStorage.setItem('temple_poojas', JSON.stringify(currentPoojas));
            }
        } catch (err) {
            console.warn('Poojas fetch error:', err);
        }

        if (!currentPoojas || currentPoojas.length === 0) {
            const stored = localStorage.getItem('temple_poojas');
            currentPoojas = stored ? JSON.parse(stored) : defaultPoojas;
        }

        tbody.innerHTML = '';
        currentPoojas.forEach((p, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${idx + 1}</strong></td>
                <td style="color: var(--color-maroon); font-weight: 700;">${p.name}</td>
                <td><span style="background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 0.85rem;">${p.time_slot}</span></td>
                <td>${p.deity || '-'}</td>
                <td style="color: #4b5563; font-size: 0.88rem;">${p.description || p.desc || '-'}</td>
                <td>
                    <button class="btn-action-edit" onclick="openPoojaEdit(${idx})">
                        ✏️ Edit
                    </button>
                    <button class="btn-action-delete" onclick="deletePoojaTiming(${p.id || idx}, '${(p.name || '').replace(/'/g, "\\'")}')">
                        🗑️ Delete
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function openAddPoojaModal() {
        document.getElementById('poojaEditIndex').value = '';
        document.getElementById('editPoojaName').value = '';
        document.getElementById('editPoojaTime').value = '';
        document.getElementById('editPoojaDeity').value = '';
        document.getElementById('editPoojaDesc').value = '';
        document.getElementById('poojaEditTitle').innerText = '✨ Add New Pooja Schedule';
        document.getElementById('poojaEditBox').style.display = 'block';
        window.scrollTo({ top: 220, behavior: 'smooth' });
    }

    function openPoojaEdit(index) {
        const item = currentPoojas[index];
        if (!item) return;

        document.getElementById('poojaEditIndex').value = item.id || index;
        document.getElementById('editPoojaName').value = item.name;
        document.getElementById('editPoojaTime').value = item.time_slot;
        document.getElementById('editPoojaDeity').value = item.deity || '';
        document.getElementById('editPoojaDesc').value = item.description || item.desc || '';
        document.getElementById('poojaEditTitle').innerText = '✏️ Edit Pooja Schedule';

        document.getElementById('poojaEditBox').style.display = 'block';
        window.scrollTo({ top: 220, behavior: 'smooth' });
    }

    async function deletePoojaTiming(id, name) {
        if (!confirm(`இந்த பூஜை அட்டவணையை நீக்க நிச்சயமாக விரும்புகிறீர்களா?\n(Are you sure you want to delete: "${name}"?)`)) {
            return;
        }

        try {
            const json = await apiFetch(`poojas.php?action=delete&id=${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            if (json.status) {
                alert('✅ ' + (json.message || 'பூஜை அட்டவணை நீக்கப்பட்டது!'));
            }
        } catch (err) {
            console.error('Delete pooja error:', err);
            alert('⚠️ ' + err.message);
        }

        await loadPoojas();
    }

    function closePoojaEdit() {
        document.getElementById('poojaEditBox').style.display = 'none';
        document.getElementById('poojaEditIndex').value = '';
    }

    async function savePoojaEdit() {
        const idVal = document.getElementById('poojaEditIndex').value;
        const name = document.getElementById('editPoojaName').value.trim();
        const timeSlot = document.getElementById('editPoojaTime').value.trim();
        const deity = document.getElementById('editPoojaDeity').value.trim();
        const desc = document.getElementById('editPoojaDesc').value.trim();

        if (!name || !timeSlot) {
            alert('பூஜையின் பெயர் மற்றும் நேரத்தை உள்ளிடவும்.');
            return;
        }

        try {
            const json = await apiFetch('poojas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: idVal ? parseInt(idVal) : null,
                    name: name,
                    time_slot: timeSlot,
                    deity: deity,
                    description: desc
                })
            });
            if (json.status) {
                alert('✅ ' + (json.message || 'பூஜை அட்டவணை சேமிக்கப்பட்டது!'));
            } else {
                alert('⚠️ ' + (json.message || 'Error'));
            }
        } catch (err) {
            console.error('Save pooja network error:', err);
            alert('⚠️ ' + err.message);
        }

        closePoojaEdit();
        await loadPoojas();
    }

    // SETTINGS SAVE (Network Synced)
    async function handleSettingsSave(e) {
        e.preventDefault();
        const settings = {
            type: 'settings',
            name: document.getElementById('cfgTempleName').value,
            tagline: document.getElementById('cfgTagline').value,
            phone: document.getElementById('cfgPhone').value,
            email: document.getElementById('cfgEmail').value,
            bank: document.getElementById('cfgBank').value,
            branch: document.getElementById('cfgBranch').value,
            address: document.getElementById('cfgAddress').value,
            cloudName: document.getElementById('cfgCloudName').value,
            uploadPreset: document.getElementById('cfgUploadPreset').value
        };

        try {
            const json = await apiFetch('settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(settings)
            });
            if (json.status) {
                alert('✅ ' + (json.message || 'அனைத்து அமைப்புகளும் வெற்றிகரமாக சேமிக்கப்பட்டன!'));
            }
        } catch (err) {
            console.error('Settings save error:', err);
            alert('⚠️ ' + err.message);
        }

        localStorage.setItem('temple_settings', JSON.stringify(settings));
        localStorage.setItem('temple_info', JSON.stringify(settings));
    }

    async function loadSettings() {
        try {
            const json = await apiFetch('settings.php');
            if (json.status && json.data) {
                const info = json.data;
                if (info.temple_name) document.getElementById('cfgTempleName').value = info.temple_name;
                if (info.tagline) document.getElementById('cfgTagline').value = info.tagline;
                if (info.phone) document.getElementById('cfgPhone').value = info.phone;
                if (info.email) document.getElementById('cfgEmail').value = info.email;
                if (info.bank_name) document.getElementById('cfgBank').value = info.bank_name;
                if (info.bank_branch) document.getElementById('cfgBranch').value = info.bank_branch;
                if (info.address) document.getElementById('cfgAddress').value = info.address;
            }
        } catch (err) {
            console.warn('Settings fetch error:', err);
        }

        const stored = localStorage.getItem('temple_settings') || localStorage.getItem('temple_info');
        if (stored) {
            try {
                const s = JSON.parse(stored);
                if (s.cloudName) document.getElementById('cfgCloudName').value = s.cloudName;
                if (s.uploadPreset) document.getElementById('cfgUploadPreset').value = s.uploadPreset;
            } catch(e) {}
        }
    }

    // TEMPLE HISTORY (ஸ்தல வரலாறு) MANAGEMENT (Network Synced)
    const defaultHistoryData = {
        heroTitle: 'கைதடி அருள்மிகு கயிற்றசிட்டி கந்தசுவாமி தேவஸ்தான ஆலய வரலாறு',
        heroSubtitle: 'இயற்கை வளம் நிரம்பிய ஈழத்து தென்மராட்சி கைதடி மண்ணில் 1775 முதல் அருள்பாலிக்கும் வேலவர் திருக்கோவில் முழு வரலாறு',
        sec1Title: 'டச்சு ஆட்சிக்காலமும் தோரணக் கடவையில் வேல் ஸ்தாபிப்பும் (1775)',
        sec1Text: 'யாழ்ப்பாணத்தை டச்சுக்காரர் ஆட்சிபுரிந்த 1775 ம் ஆண்டு காலப்பகுதியில் திருவாளர் சந்திரசேகர வேலப்பமுதலியார் அவர்களால் தற்போதைய ஆலயம் அமைந்திருக்கும் வடமேல் திசையிலுள்ள தோரணக் கடவை என்னும் புண்ணிய பூமியில் ஐப்பசிமாத கந்தசஷ்டி நன்னாளில் தற்போதுள்ள வேல் முதன் முதலாக ஸ்தாபிக்கப்பட்டது. இம் மடாலயத்திலுள்ள வேலினை இப்பகுதியிலுள்ள மக்கள் பயபக்தியோடு வணங்கி வந்தனர்.\n\nதோரணக் கடவையில் மடாலயம் அமைந்த காணி திரு. பொன்னம்பலம் தம்பு என்னும் அடியாரால் உவந்தளிக்கப்பட்டது. வேண்டுவார் வேண்டுவதை அள்ளிக் கொடுக்கும் முருகன், தனது மடாலயத்தின் அருகே பரந்த நிலப்பரப்பை உடையவரான சந்திரசேகர வேலப்ப முதலியாரின் வழித்தோன்றலான பொன்னம்பல முதலியார் கனவில் தோன்றி மடாலய வேலினை தற்பொழுது ஆலயம் அமைந்துள்ள இடத்தில் ஸ்தாபிக்கும்படி அசரீரிவாக்கு அருளினார்.',
        sec2Title: 'கயிற்றசிட்டி ஆலய நிர்மாணமும் கும்பாபிஷேகங்களும் (1835 - 1901)',
        sec2Text: 'திருவாளர் பொன்னம்பலமுதலியார் கயிற்றசிட்டி என்னும் பெருநிலப்பரப்பை ஆலயம் அமைவதற்கு உவந்தளித்தார். 1835ஆம் ஆண்டு மண்சுவர் அமைத்து கோவில் நிர்மாணிக்கப்பட்டு ஆனிமாதம் உத்தர நட்சத்திரத்தில் வேலாயுதம் மூலஸ்தான மூர்த்தியாக பிரதிஷ்டை செய்யப்பட்டது.\n\n1876ஆம் ஆண்டு 2439ம் இலக்க உறுதிப்படியும் 1890ஆம் ஆண்டு அரசாங்க பதிவேட்டினாலும் ஆலயம் பதிவு செய்யப்பட்டது. 1898ல் சிவஸ்ரீ அப்பாசாமிக்குருக்கள் பூசைப்பொறுப்பை ஏற்று, சுண்ணக் கற்கள், வெண்கற்கள் கொண்டு புதிய கர்ப்பக்கிரகமும் அலங்கார ஸ்தூபியும் மண்டபங்களும் நிர்மாணிக்கப்பட்டு 1901ல் முதன் முதலாக ஆனி உத்தரத்தில் கும்பாபிஷேகம் நடைபெற்றது. தலவிருட்சமாக மகிழமரம் நாட்டப்பட்டது.',
        sec3Title: 'சண்முகப் பெருமான் ஆலயம், அற்புதங்கள் & தேர்த் திருப்பணி (1915 - 1977)',
        sec3Text: '1915ல் சண்முகப்பெருமானுக்கு ஆலயம் அமைக்கப்பட்டு இரண்டாவது கும்பாபிஷேகம் நடைபெற்றது. 1920ல் கொடித்தம்ப மண்டபமும், 1923ல் 3வது கும்பாபிஷேகத்துடன் முதல் கொடியேற்ற மகோற்சவமும் ஆரம்பமானது. இரண்டாம் உலக மகாயுத்த காலத்தில் 1942ல் யாழ்ப்பாண விமானத்தளம் கைதடியில் அமையாதபடி முருகன் நடத்திய அருட்செயலும், 1945ல் அம்மை நோயிலிருந்து மக்களைக் காத்த அற்புதமும் இத்தலத்தின் கண்கண்ட அருட்கொடைகளாகும்.\n\n1972ல் அ. இலங்கையர் தலைமையில் தேர்த் திருப்பணி சபை உருவாக்கப்பட்டு, 1977 ஆனி 23ல் புதிய சித்திரத் தேர் வெள்ளோட்டம் இனிதே நடைபெற்றது.',
        sec4Title: 'அண்மைக்கால திருப்பணிகளும் மகா கும்பாபிஷேகமும் (2016 - 2024)',
        sec4Text: '2006ல் கம்பீரமான ராஜகோபுரம் கட்டப்பட்டது. 2016ல் மணியம்பத்தை காணியில் அழகிய தீர்த்தக்கேணி அமைக்கப்பட்டு முதல் தீர்த்தோற்சவம் நடைபெற்றது. 2016 முதல் வருடாந்திர ஸ்கந்த ஹோமம் நடைபெற்று வருகிறது.\n\n2022ல் திருப்பணி சபை அமைக்கப்பட்டு, 01.09.2022ல் பாலஸ்தாபனம் செய்யப்பட்டு மாபெரும் திருப்பணிகள் செவ்வனே நிறைவுற்றன. 20.03.2024 பங்குனி மாதம் எம்பெருமானின் மகா கும்பாபிஷேகமும், 48 தினங்கள் மண்டலாபிஷேகமும், தொடர்ந்து 15 திருவிழாக்களாக 2024 மஹோற்சவ பெருவிழாவும் அடியார்கள் புடைசூழ அதிவிமரிசையாக இனிதே நடைபெற்றன.'
    };

    async function loadHistory() {
        let h = defaultHistoryData;
        try {
            const json = await apiFetch('settings.php');
            if (json.status && json.data && json.data.history_full) {
                try {
                    const parsed = JSON.parse(json.data.history_full);
                    if (parsed && parsed.heroTitle) h = parsed;
                } catch(e) {}
            }
        } catch(err) {
            console.warn('History fetch error:', err);
        }

        if (document.getElementById('histHeroTitle')) document.getElementById('histHeroTitle').value = h.heroTitle || '';
        if (document.getElementById('histHeroSubtitle')) document.getElementById('histHeroSubtitle').value = h.heroSubtitle || '';
        if (document.getElementById('histSec1Title')) document.getElementById('histSec1Title').value = h.sec1Title || '';
        if (document.getElementById('histSec1Text')) document.getElementById('histSec1Text').value = h.sec1Text || '';
        if (document.getElementById('histSec2Title')) document.getElementById('histSec2Title').value = h.sec2Title || '';
        if (document.getElementById('histSec2Text')) document.getElementById('histSec2Text').value = h.sec2Text || '';
        if (document.getElementById('histSec3Title')) document.getElementById('histSec3Title').value = h.sec3Title || '';
        if (document.getElementById('histSec3Text')) document.getElementById('histSec3Text').value = h.sec3Text || '';
        if (document.getElementById('histSec4Title')) document.getElementById('histSec4Title').value = h.sec4Title || '';
        if (document.getElementById('histSec4Text')) document.getElementById('histSec4Text').value = h.sec4Text || '';
    }

    async function handleHistorySave(e) {
        e.preventDefault();
        const data = {
            type: 'history',
            heroTitle: document.getElementById('histHeroTitle').value.trim(),
            heroSubtitle: document.getElementById('histHeroSubtitle').value.trim(),
            sec1Title: document.getElementById('histSec1Title').value.trim(),
            sec1Text: document.getElementById('histSec1Text').value.trim(),
            sec2Title: document.getElementById('histSec2Title').value.trim(),
            sec2Text: document.getElementById('histSec2Text').value.trim(),
            sec3Title: document.getElementById('histSec3Title').value.trim(),
            sec3Text: document.getElementById('histSec3Text').value.trim(),
            sec4Title: document.getElementById('histSec4Title').value.trim(),
            sec4Text: document.getElementById('histSec4Text').value.trim()
        };

        try {
            const json = await apiFetch('settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (json.status) {
                alert('✅ ' + (json.message || 'திருக்கோவில் ஸ்தல வரலாறு வெற்றிகரமாக சேமிக்கப்பட்டது!'));
            }
        } catch (err) {
            console.error('History save error:', err);
            alert('⚠️ ' + err.message);
        }

        localStorage.setItem('temple_history', JSON.stringify(data));
    }

    // INITIALIZE ON LOAD
    window.addEventListener('DOMContentLoaded', () => {
        loadEventsTable();
        loadAnnouncements();
        loadPoojas();
        loadHistory();
        loadSettings();
    });
</script>

</body>
</html>
