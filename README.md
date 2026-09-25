# 🛕 அருள்மிகு திருக்கோவில் இணையதளம் (Temple Website)
### Full Tamil Temple Website + Daily Events + Photo Gallery + Cloudinary + InfinityFree Ready

இந்த இணையதளம் திருக்கோவிலின் வரலாறு, தினசரி 6 கால பூஜைகள், விசேஷ நிகழ்வுகள், புகைப்பட தொகுப்பு (HD டவுன்லோட் & ZIP டவுன்லோட்), திருவிழாக்கள் மற்றும் நிர்வாக பலகை (Admin Dashboard) ஆகியவற்றை உள்ளடக்கியது. 

**தொழில்நுட்பம் (Tech Stack):**
- **Frontend:** தூய HTML5 + நவீன CSS3 (ஆன்மீக குங்குமம், மஞ்சள் தங்கம் & சந்தன வண்ணங்கள்) + JavaScript
- **Backend:** PHP REST API (InfinityFree-ல் 100% இயங்கக்கூடியது)
- **Database:** MySQL (`database/temple.sql`)
- **Image Storage:** Cloudinary (InfinityFree சேவையகத்தின் நினைவக வரம்புகளை கடந்து இலவசமாக புகைப்படங்களைச் சேமிக்க)
- **Hosting:** InfinityFree (இலவச PHP & MySQL ஹோஸ்டிங்)

---

## 📁 திட்டத்தின் கோப்பு அமைப்பு (Folder Structure)

```text
htdocs/
│
├── index.html          # முகப்பு பக்கம் (Home - கோவில் அறிமுகம், இன்றைய நேரங்கள், சமீபத்திய நிகழ்வுகள்)
├── history.html        # கோவில் ஸ்தல வரலாறு (About Temple - ஸ்தல மகிமை, தல விருட்சம், தீர்த்தம்)
├── events.html         # தினசரி நிகழ்வுகள் (Daily Events - தேதி & வகை வாரியாக)
├── gallery.html        # புகைப்பட தொகுப்பு (Photo Gallery - HD Download & ZIP Download)
├── festivals.html      # வருடாந்திர திருவிழாக்கள் (Festivals - சித்திரை திருவிழா, சிவராத்திரி முதலியன)
├── pooja.html          # பூஜை நேர அட்டவணை (Pooja Schedules - 6 கால பூஜைகள் & கட்டளைகள்)
├── contact.html        # தொடர்புக்கு & காணிக்கை (Contact, Google Maps & UPI QR Donation)
│
├── css/
│   └── style.css       # முழுமையான தமிழ் ஆன்மீக வண்ண பாணி தாள்
│
├── js/
│   ├── main.js         # முதன்மை ஜாவாஸ்கிரிப்ட், தமிழ் நாட்காட்டி தேதி, அறிவிப்பு பட்டை
│   ├── gallery.js      # புகைப்பட கேலரி, Lightbox, தனித்தனி HD Download & Album ZIP Download
│   └── events.js       # நிகழ்வுகள் பட்டியல் மற்றும் வடிகட்டுதல்
│
├── admin/
│   ├── login.php       # நிர்வாகி உள்நுழைவு (Admin Login)
│   ├── dashboard.php   # முழுமையான நிர்வாக பலகை (புதிய நிகழ்வு & பல படங்கள் பதிவேற்றம்)
│   ├── logout.php      # வெளியேறுதல் (Logout)
│   └── auth_check.php  # அமர்வு பாதுகாப்பு (Session Guard)
│
└── api/
    ├── config.php      # MySQL மற்றும் Cloudinary இணைப்பு விபரங்கள்
    ├── db.php          # PDO MySQL தரவுத்தள இணைப்பு
    ├── events.php      # நிகழ்வுகள் REST API
    ├── upload.php      # புகைப்படங்கள் பதிவேற்ற API
    ├── gallery.php     # புகைப்படங்கள் மற்றும் பதிவிறக்க எண்ணிக்கை API
    ├── poojas.php      # பூஜை நேரங்கள் API
    ├── festivals.php   # திருவிழாக்கள் API
    └── announcements.php # முக்கிய அறிவிப்புகள் API

database/
└── temple.sql          # MySQL அட்டவணைகள் மற்றும் தமிழ் மாதிரி தரவுகள்
```

---

## 🔑 நிர்வாகி உள்நுழைவு விபரம் (Default Admin Credentials)

- **Login URL:** `http://your-domain.com/admin/login.php`
- **பயனர் பெயர் (Username):** `admin`
- **கடவுச்சொல் (Password):** `temple@123`

---

## 🚀 உங்கள் கணினியில் உடனடியாக பரிசோதிக்க (Local Testing)

1. இந்த திட்டக் கோப்புறையில் உள்ள `start-local-server.bat` கோப்பை இரட்டை கிளிக் செய்து திறக்கவும்.
2. தானாகவே இணைய உலாவி (Browser) திறக்கப்பட்டு `http://localhost:8000` முகவரியில் இணையதளம் இயங்கும்.
3. நிர்வாக பலகையை அணுக `http://localhost:8000/admin/login.php` செல்லவும்.

---

## 🌐 InfinityFree-ல் இணையதளத்தை நேரலையாக (Live) பதிவேற்றுவது எப்படி?

### படி 1: InfinityFree கணக்கு & டொமைன் உருவாக்குதல்
1. [InfinityFree.com](https://www.infinityfree.com/) சென்று இலவச கணக்கை துவங்குங்கள்.
2. புதிய **Hosting Account** உருவாக்கி, ஒரு இலவச sub-domain (எ.கா: `my-temple.epizy.com` அல்லது `my-temple.infinityfreeapp.com`) அல்லது உங்கள் சொந்த domain-ஐ இணையுங்கள்.

### படி 2: MySQL தரவுத்தளத்தை அமைத்தல்
1. InfinityFree Control Panel (cPanel) &rarr; **MySQL Databases** செல்லவும்.
2. புதிய Database-ஐ உருவாக்கவும் (எ.கா: `if0_xxxxxx_temple`).
3. Database Name, MySQL Username, MySQL Password, MySQL Hostname (எ.கா: `sql123.infinityfree.com`) ஆகியவற்றை குறித்துக் கொள்ளுங்கள்.
4. cPanel &rarr; **phpMyAdmin** திறந்து, உருவாக்கப்பட்ட தரவுத்தளத்தை தேர்வு செய்யவும்.
5. **Import** தாவலை கிளிக் செய்து, இந்த திட்டத்தில் உள்ள `database/temple.sql` கோப்பை பதிவேற்றி **Go** கொடுக்கவும். அனைத்து அட்டவணைகளும் தமிழ் மாதிரி தகவல்களும் வந்துவிடும்.

### படி 3: Cloudinary இலவச கணக்கு அமைத்தல் (முக்கியமானது)
InfinityFree-ல் தினசரி நூற்றுக்கணக்கான புகைப்படங்களை பதிவேற்றும்போது Server Memory Limit ஏற்படாமல் இருக்க Cloudinary Direct Upload பயன்படுத்தப்படுகிறது:
1. [Cloudinary.com](https://cloudinary.com/)-ல் இலவச கணக்கு திறக்கவும்.
2. Dashboard-ல் உங்கள் **Cloud Name**-ஐ குறித்துக் கொள்ளுங்கள்.
3. Settings (பல்சக்கர ஐகான்) &rarr; **Upload** செல்லவும்.
4. கீழே உருட்டி **Upload presets** பகுதிக்குச் சென்று **Add upload preset** கிளிக் செய்யவும்.
5. **Preset name:** `temple_photos` என வைக்கவும்.
6. **Signing Mode:** என்பதை **Unsigned** என மாற்றவும்.
7. **Save** கிளிக் செய்யவும்.

### படி 4: `htdocs/api/config.php` கோப்பில் மாற்றுதல்
`htdocs/api/config.php` கோப்பை திறந்து உங்கள் InfinityFree மற்றும் Cloudinary விபரங்களை உள்ளிடவும்:
```php
define('DB_HOST', 'sql123.infinityfree.com'); // InfinityFree MySQL Hostname
define('DB_NAME', 'if0_12345678_temple');      // உங்கள் Database Name
define('DB_USER', 'if0_12345678');             // உங்கள் Username
define('DB_PASS', 'உங்கள்_mysql_கடவுச்சொல்');

define('CLOUDINARY_CLOUD_NAME', 'உங்கள்_cloud_name');
define('CLOUDINARY_UPLOAD_PRESET', 'temple_photos');
```

### படி 5: கோப்புகளை பதிவேற்றுதல் (File Upload)
1. InfinityFree Control Panel &rarr; **Online File Manager** (MonstaFTP) அல்லது FileZilla FTP மென்பொருளை திறக்கவும்.
2. `htdocs/` அடைவுக்குள் செல்லவும்.
3. இந்த திட்டத்தின் **`htdocs/` கோப்புறையில் உள்ள அனைத்து கோப்புகளையும் (index.html, css, js, admin, api, போன்றவை)** InfinityFree-ன் `htdocs/` கோப்புறையினுள் பதிவேற்றவும்.
4. இப்போது உங்கள் டொமைன் முகவரியை உலாவியில் திறந்து பார்த்தால் உங்கள் கோவில் இணையதளம் முழுமையாக இயங்கும்!

---

## ✨ சிறப்பம்சங்கள்

1. **முழுமையான தமிழ் மொழி வடிவம்:** மெனுக்கள், தலைப்புகள், அறிவிப்புகள், தேதிகள், அட்டவணைகள் அனைத்தும் சுத்தமான தமிழில்.
2. **தனித்தனி HD போட்டோ டவுன்லோட்:** பார்வையாளர்கள் ஒவ்வொரு புகைப்படத்தையும் தனித்தனியாக கிளிக் செய்து HD தரத்தில் பதிவிறக்கம் செய்து கொள்ளலாம்.
3. **முழு ஆல்பத்தை ZIP கோப்பாக பதிவிறக்கம்:** JSZip தொழில்நுட்பம் மூலம் ஒரு நிகழ்வின் 30+ புகைப்படங்களையும் பார்வையாளரின் உலாவியிலேயே (Browser-side) நொடிப் பொழுதில் ZIP கோப்பாக டவுன்லோட் செய்யலாம்.
4. **InfinityFree-க்கு ஏற்ற வடிவமைப்பு:** Cloudinary Direct Upload வசதி இணைக்கப்பட்டுள்ளதால் InfinityFree சேவையகத்திற்கு எந்த சுமையோ கோப்பு அளவு தடைகளோ ஏற்படாது!
