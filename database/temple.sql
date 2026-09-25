-- ==============================================================
-- கைதடி வடக்கு கயிற்றசிட்டி அருள்மிகு கந்தசுவாமி தேவஸ்தானம்
-- Temple Website Database Schema & Seed Data (Full Tamil)
-- ==============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 1. கோவில் நிர்வாகிகள் அட்டவணை (Admins Table)
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `role` VARCHAR(20) DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- இயல்புநிலை நிர்வாகி (Default Admin: admin / temple@123)
INSERT INTO `admins` (`id`, `username`, `password_hash`, `full_name`, `email`, `role`) VALUES
(1, 'admin', '$2y$10$zbUqxr6sk.tO9bIFlTg7duTiNsunkM82ebTDCRU7cwS0ysw11tt2O', 'தேவஸ்தான தலைமை நிர்வாகி', 'kaithadykandanswamy@gmail.com', 'superadmin')
ON DUPLICATE KEY UPDATE `password_hash`=VALUES(`password_hash`);

-- 2. கோவில் அடிப்படை விபரங்கள் (Temple Information)
CREATE TABLE IF NOT EXISTS `temple_info` (
  `id` INT PRIMARY KEY DEFAULT 1,
  `temple_name` VARCHAR(255) NOT NULL,
  `deity_name` VARCHAR(255) NOT NULL,
  `tagline` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `darshan_timings` TEXT DEFAULT NULL,
  `history_summary` TEXT DEFAULT NULL,
  `history_full` LONGTEXT DEFAULT NULL,
  `upi_id` VARCHAR(100) DEFAULT NULL,
  `bank_name` VARCHAR(100) DEFAULT NULL,
  `bank_account_no` VARCHAR(50) DEFAULT NULL,
  `bank_ifsc` VARCHAR(50) DEFAULT NULL,
  `bank_branch` VARCHAR(100) DEFAULT NULL,
  `map_embed_url` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `temple_info` (
  `id`, `temple_name`, `deity_name`, `tagline`, `phone`, `email`, `address`,
  `darshan_timings`, `history_summary`, `history_full`, `upi_id`, `bank_name`,
  `bank_account_no`, `bank_ifsc`, `bank_branch`, `map_embed_url`
) VALUES (
  1,
  'கைதடி வடக்கு கயிற்றசிட்டி அருள்மிகு கந்தசுவாமி தேவஸ்தானம்',
  'அருள்மிகு கந்தசுவாமி (முருகப்பெருமான் - வள்ளி தெய்வானை சமேதர்)',
  'வெற்றிவேல் முருகனுக்கு அரோகரா! வீரவேல் முருகனுக்கு அரோகரா!',
  '+94 77 123 4567, 021-2223456',
  'kaithadykandanswamy@gmail.com',
  'கைதடி வடக்கு, கயிற்றசிட்டி, யாழ்ப்பாணம், இலங்கை.',
  'காலை 05:30 மணி முதல் மதியம் 12:30 மணி வரை | மாலை 04:00 மணி முதல் இரவு 08:30 மணி வரை',
  'யாழ்ப்பாணம் கைதடி வடக்கு கயிற்றசிட்டி கிராமத்தில் எழுந்தருளி அருள்பாலிக்கும் அருள்மிகு கந்தசுவாமி தேவஸ்தானம் தொன்மை வாய்ந்த ஆன்மீகத் திருத்தலமாகும்.',
  'இத்திருக்கோவில் வரலாற்றுச் சிறப்புமிக்க புண்ணிய கந்தவேள் திருத்தலமாகும். மூலவர் அருள்மிகு கந்தசுவாமி வேலும் மயிலும் கொண்டு பக்தர்களுக்கு வேண்டும் வரங்களை அள்ளி வழங்கும் வள்ளலாக எழுந்தருளியுள்ளார். ஆண்டுதோறும் நடைபெறும் கந்தசஷ்டி பெருவிழா, சூரசம்ஹாரம், தைப்பூசம், வைகாசி விசாகம் மற்றும் பங்குனி உத்திர பெருவிழாக்கள் அதிவிமரிசையாக நடைபெறுகின்றன.',
  'temple@boc',
  'இலங்கை வங்கி (Bank of Ceylon)',
  '78291045',
  'BOCLKJAFF',
  'சாவகச்சேரி / கைதடி கிளை',
  'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3933.284279581536!2d80.082725!3d9.664426!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3afe5410c8501235%3A0x7d8123456789abcd!2sKaithady%20North!5e0!3m2!1sen!2slk!4v1700000000000'
) ON DUPLICATE KEY UPDATE `temple_name`=VALUES(`temple_name`);

-- 3. தினசரி & விசேஷ நிகழ்வுகள் (Events Table)
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `event_date` DATE NOT NULL,
  `category` VARCHAR(50) DEFAULT 'விசேஷ பூஜை',
  `description` TEXT DEFAULT NULL,
  `cover_image` TEXT DEFAULT NULL,
  `is_published` TINYINT(1) DEFAULT 1,
  `views_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. நிகழ்வு புகைப்படங்கள் (Event Photos Table)
CREATE TABLE IF NOT EXISTS `event_photos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT NOT NULL,
  `image_url` TEXT NOT NULL,
  `thumbnail_url` TEXT DEFAULT NULL,
  `cloudinary_id` VARCHAR(150) DEFAULT NULL,
  `caption` VARCHAR(255) DEFAULT NULL,
  `download_count` INT DEFAULT 0,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. பூஜை நேர அட்டவணை (Pooja Timings Table)
CREATE TABLE IF NOT EXISTS `pooja_schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `time_slot` VARCHAR(50) NOT NULL,
  `deity` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pooja_schedules` (`id`, `name`, `time_slot`, `deity`, `description`, `sort_order`) VALUES
(1, 'திருப்பள்ளியெழுச்சி பூஜை', 'காலை 05:30 - 06:00', 'அருள்மிகு கந்தசுவாமி', 'மங்கல நாதஸ்வர இசை, திருவெம்பாவை, கந்தர் சஷ்டி கவசம் & சுப்ரபாதம்', 1),
(2, 'காலசந்தி பூஜை & பால் அபிஷேகம்', 'காலை 07:00 - 08:00', 'கந்தவேள் & பரிவாரம்', 'சிறப்பு நைவேத்திய தீபாராதனை மற்றும் பால் அபிஷேகம்', 2),
(3, 'உச்சிகால பூஜை', 'பகல் 11:30 - 12:00', 'மூலவர்', 'மதிய உச்சி கால மகா தீபாராதனை மற்றும் அன்னதானம்', 3),
(4, 'சாயரட்சை பூஜை', 'மாலை 05:30 - 06:30', 'முருகப்பெருமான்', 'மாலை நேர சிறப்பு அலங்கார தீபாராதனை', 4),
(5, 'இரண்டாம் கால பூஜை', 'இரவு 07:30 - 08:00', 'வள்ளி தெய்வானை சமேதர்', 'விசேஷ அர்ச்சனை மற்றும் வழிபாடு', 5),
(6, 'அர்த்தஜாம பூஜை & பள்ளியறை சேவை', 'இரவு 08:15 - 08:30', 'பள்ளியறை சேவை', 'ஏகசிம்மாசன சேவை மற்றும் திருக்காப்பிடுதல்', 6)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 6. வருடாந்திர உற்சவங்கள் (Festivals Table)
CREATE TABLE IF NOT EXISTS `festivals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `tamil_month` VARCHAR(50) NOT NULL,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `banner_image` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `festivals` (`id`, `name`, `tamil_month`, `start_date`, `end_date`, `description`, `banner_image`) VALUES
(1, 'கந்தசஷ்டி பெருவிழா & சூரசம்ஹாரம்', 'ஐப்பசி', '2026-11-10', '2026-11-16', '6 நாட்கள் விரதமிருந்து நடைபெறும் மகா கந்தசஷ்டி திருவிழா, சூரசம்ஹாரம் மற்றும் திருக்கல்யாணம்.', 'images/gopuram_front.jpg'),
(2, 'தைப்பூச நன்னாள் உற்சவம்', 'தை', '2026-02-01', '2026-02-01', 'வேல் பெருமானுக்கு சிறப்பு அபிஷேகம், காவடி, பால்குடம் மற்றும் மயில் வாகன பவனி.', 'images/gopuram_side.jpg'),
(3, 'வைகாசி விசாக பெருவிழா', 'வைகாசி', '2026-06-02', '2026-06-02', 'முருகப்பெருமானின் அவதாரத் திருநாளில் 108 சங்காபிஷேகமும் தங்கக் கவச தரிசனமும்.', 'images/gopuram_front.jpg'),
(4, 'பங்குனி உத்திர திருக்கல்யாணம்', 'பங்குனி', '2026-03-24', '2026-03-24', 'வள்ளி தெய்வானை சமேத கந்தசுவாமிக்கு திருக்கல்யாண வைபவம் மற்றும் பூப்பல்லக்கு ஊர்வலம்.', 'images/gopuram_side.jpg')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 7. முக்கிய அறிவிப்புகள் (Announcements Table)
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `announcements` (`id`, `title`, `content`, `is_active`) VALUES
(1, 'தினசரி நித்திய அன்னதான திட்டம்', 'தினமும் மதியம் 12:15 மணிக்கு பக்தர்களுக்கு அறுசுவை அன்னதானம் வழங்கப்படுகிறது. அன்னதான காணிக்கை செலுத்த விரும்புவோர் தேவஸ்தான அலுவலகத்தை தொடர்பு கொள்ளலாம்.', 1),
(2, 'சஷ்டி விரத சிறப்பு வழிபாடு', 'மாதாந்திர சஷ்டி நன்னாளில் மாலை 5:00 மணிக்கு கந்தப்பெருமானுக்கு 108 திரவியங்களால் சிறப்பு அபிஷேக ஆராதனைகள் நடைபெறும்.', 1),
(3, 'திருக்கோவில் திருப்பணி நன்கொடை', 'தேவஸ்தான ராஜகோபுரம் மற்றும் நந்தவனம் அமைக்கும் திருப்பணிக்கு பக்தர்கள் தங்களின் தாராள பங்களிப்பை வழங்க அன்போடு வேண்டுகிறோம்.', 1)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

-- மாதிரி நிகழ்வுகள் (Sample Events)
INSERT INTO `events` (`id`, `title`, `event_date`, `category`, `description`, `cover_image`, `is_published`) VALUES
(1, 'கந்தசஷ்டி பெருவிழா - சூரசம்ஹார வைபவம்', '2026-11-15', 'திருவிழா', 'கந்தசஷ்டி நன்னாளில் தேவஸ்தானத்தில் பக்தர்கள் சூழ நடைபெற்ற சூரசம்ஹார வைபவம் மற்றும் திருக்கல்யாணம்.', 'images/gopuram_front.jpg', 1),
(2, 'வைகாசி விசாக பெருவிழா 108 பாலாபிஷேகம்', '2026-06-02', 'சிறப்பு அபிஷேகம்', 'முருகப்பெருமானின் அவதாரத் திருநாளை முன்னிட்டு மூலவருக்கு 108 சங்காபிஷேகமும் தங்கக் கவச அலங்காரமும் நடைபெற்றது.', 'images/gopuram_side.jpg', 1),
(3, 'தைப்பூச நன்னாள் - மயில் வாகன பவனி', '2026-02-01', 'உற்சவம்', 'தைப்பூச நன்னாளில் கந்தப்பெருமான் வள்ளி தெய்வானை சமேதராக மயில் வாகனத்தில் எழுந்தருளி திருவீதி உலா வந்த காட்சி.', 'images/gopuram_front.jpg', 1)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

INSERT INTO `event_photos` (`id`, `event_id`, `image_url`, `thumbnail_url`, `caption`, `download_count`) VALUES
(1, 1, 'images/gopuram_front.jpg', 'images/gopuram_front.jpg', 'ராஜகோபுர திவ்ய தரிசனம் (ஓம் முருகா)', 84),
(2, 1, 'images/gopuram_side.jpg', 'images/gopuram_side.jpg', 'தேவஸ்தான கோபுர அழகிய தோற்றம்', 52),
(3, 1, 'images/gopuram_front.jpg', 'images/gopuram_front.jpg', 'துவாரபாலகர் மற்றும் கோபுர முகப்பு', 39)
ON DUPLICATE KEY UPDATE `caption`=VALUES(`caption`);
