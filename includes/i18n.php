<?php
/**
 * Sharan Foundation — Internationalization (i18n) Helper
 *
 * Supports: en (English), hi (Hindi)
 * Detection order: ?lang=xx → cookie → browser Accept-Language → site default → 'en'
 */

if (session_status() === PHP_SESSION_NONE) session_start();

define('AVAILABLE_LANGS', ['en','hi']);

global $LANG, $T;

// 1. Handle language switch via ?lang=xx
if (isset($_GET['lang']) && in_array($_GET['lang'], AVAILABLE_LANGS)) {
    $LANG = $_GET['lang'];
    setcookie('site_lang', $LANG, time() + 86400*365, '/');
    $_SESSION['site_lang'] = $LANG;
}
// 2. Cookie / session
elseif (!empty($_SESSION['site_lang']) && in_array($_SESSION['site_lang'], AVAILABLE_LANGS)) {
    $LANG = $_SESSION['site_lang'];
}
elseif (!empty($_COOKIE['site_lang']) && in_array($_COOKIE['site_lang'], AVAILABLE_LANGS)) {
    $LANG = $_COOKIE['site_lang'];
    $_SESSION['site_lang'] = $LANG;
}
// 3. Browser Accept-Language (only if no other preference)
elseif (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $accept = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    if (strpos($accept, 'hi') === 0) $LANG = 'hi';
    else                              $LANG = 'en';
}
// 4. Default
else {
    $LANG = function_exists('get_setting') ? get_setting('default_language','en') : 'en';
    if (!in_array($LANG, AVAILABLE_LANGS)) $LANG = 'en';
}

// Load translation file
$_lang_file = __DIR__ . '/../lang/' . $LANG . '.php';
$_en_file   = __DIR__ . '/../lang/en.php';
$T = is_file($_lang_file) ? require $_lang_file : require $_en_file;

// Always merge with English as fallback for missing keys
if ($LANG !== 'en' && is_file($_en_file)) {
    $T = array_merge(require $_en_file, $T);
}

/**
 * Translate: t('nav_home') -> "Home" or "मुख्य पृष्ठ"
 */
function t($key, $default = null) {
    global $T;
    return $T[$key] ?? ($default ?? $key);
}

/**
 * Get a translated DB field. Falls back to English if Hindi missing.
 * tr_field($row, 'title') uses 'title_hi' when lang=hi, else 'title'
 */
function tr_field($row, $field) {
    global $LANG;
    if ($LANG === 'en') return $row[$field] ?? '';
    $hi_field = $field . '_hi';
    if (!empty($row[$hi_field])) return $row[$hi_field];
    return $row[$field] ?? '';
}

/**
 * Build language switcher URL preserving current path.
 */
function lang_url($lang_code) {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $qs  = $_GET;
    $qs['lang'] = $lang_code;
    return $uri . '?' . http_build_query($qs);
}

/**
 * Get all available languages for the switcher.
 */
function available_languages() {
    return [
        'en' => ['name' => 'English',  'native' => 'English',  'flag' => '🇬🇧'],
        'hi' => ['name' => 'Hindi',    'native' => 'हिन्दी',    'flag' => '🇮🇳'],
    ];
}

/**
 * Get current language code.
 */
function current_lang() {
    global $LANG;
    return $LANG;
}
