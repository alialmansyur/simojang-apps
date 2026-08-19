/**
 * dashboard.js - Minimalist Centered Welcome Dashboard
 * SIMOJANG Apps - Kanreg III BKN
 */

$(document).ready(function () {
    // 1. Dynamic Contextual Greeting & Big Time Icon Update
    updateDynamicGreeting();

    // 2. Rotating Feature Text with Smooth Blur & Morph Effect
    initBlurTextRotator();
});

/**
 * Updates greeting and big icon based on client-side local time
 */
function updateDynamicGreeting() {
    const hour = new Date().getHours();
    let greetingText = 'Selamat Datang';
    let greetingIcon = 'bi-sun-fill';
    let themeClass = 'time-theme-siang';

    if (hour >= 4 && hour < 11) {
        greetingText = 'Selamat Pagi';
        greetingIcon = 'bi-sunrise-fill';
        themeClass = 'time-theme-pagi';
    } else if (hour >= 11 && hour < 15) {
        greetingText = 'Selamat Siang';
        greetingIcon = 'bi-sun-fill';
        themeClass = 'time-theme-siang';
    } else if (hour >= 15 && hour < 18) {
        greetingText = 'Selamat Sore';
        greetingIcon = 'bi-sunset-fill';
        themeClass = 'time-theme-sore';
    } else {
        greetingText = 'Selamat Malam';
        greetingIcon = 'bi-moon-stars-fill';
        themeClass = 'time-theme-malam';
    }

    const $greetingEl = $('#dashGreetingText');
    const $bigIconBoxEl = $('#dashBigTimeIconBox');
    const $bigIconEl = $('#dashBigTimeIcon');

    if ($greetingEl.length) {
        $greetingEl.text(greetingText);
    }
    if ($bigIconBoxEl.length) {
        $bigIconBoxEl.removeClass('time-theme-pagi time-theme-siang time-theme-sore time-theme-malam').addClass(themeClass);
    }
    if ($bigIconEl.length) {
        $bigIconEl.removeClass().addClass('bi ' + greetingIcon);
    }
}

/**
 * Text rotator with modern Gaussian blur & morph transition
 */
function initBlurTextRotator() {
    const features = [
        { text: 'Manajemen Tim Kerja & Layanan Kepegawaian ASN', icon: 'bi-people-fill' },
        { text: 'Fasilitasi CAT & Penyelenggaraan Seleksi CASN', icon: 'bi-display' },
        { text: 'Pengawasan Standar NSPK & Evaluasi Sistem Merit', icon: 'bi-shield-check' },
        { text: 'Digitalisasi Naskah & Dokumen Kepegawaian (DMS)', icon: 'bi-folder2-open' },
        { text: 'Agenda Terpadu & Dokumentasi Galeri Kegiatan', icon: 'bi-camera-reels-fill' },
        { text: 'Pelayanan Publik, Kehumasan & Konsultasi ASN', icon: 'bi-chat-heart-fill' }
    ];

    let currentIndex = 0;
    const $textEl = $('#dynamicFeatureText');
    const $iconEl = $('#dynamicFeatureIcon');

    if (!$textEl.length) return;

    const intervalTime = 3200; // 3.2 seconds interval

    setInterval(function () {
        currentIndex = (currentIndex + 1) % features.length;
        const nextFeature = features[currentIndex];

        // 1. Blur out current text & scale down
        $textEl.removeClass('blur-in').addClass('blur-out');

        setTimeout(function () {
            // 2. Change text & icon while hidden in blur
            $textEl.text(nextFeature.text);
            if ($iconEl.length) {
                $iconEl.removeClass().addClass('bi ' + nextFeature.icon + ' dash-rotator-icon');
            }

            // Prep for blur-in
            $textEl.removeClass('blur-out').addClass('blur-in-prep');

            // Force reflow
            void $textEl[0].offsetWidth;

            // 3. Blur in new text
            $textEl.removeClass('blur-in-prep').addClass('blur-in');
        }, 360); // match CSS transition duration

    }, intervalTime);
}
