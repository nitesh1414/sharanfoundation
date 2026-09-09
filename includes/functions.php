<?php
/** Sharan Foundation — Shared Utility Functions */

function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function redirect($url){ header("Location: $url"); exit; }

function flash_set($type, $msg){
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type'=>$type, 'msg'=>$msg];
}

function flash_get(){
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function flash_render(){
    $f = flash_get();
    if (!$f) return '';
    $color = $f['type']==='success' ? '#2563eb' : ($f['type']==='error' ? '#c0392b' : '#d4a017');
    $bg = $f['type']==='success' ? '#e8f5ef' : ($f['type']==='error' ? '#fdecea' : '#fef7e0');
    return "<div style='background:$bg;color:$color;padding:.9rem 1.2rem;border-left:4px solid $color;border-radius:6px;margin-bottom:1.5rem;font-weight:500'>" . e($f['msg']) . "</div>";
}

function slugify($text){
    $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return preg_replace('~[^-a-z0-9]+~', '', $text) ?: 'item-' . time();
}

function upload_image($file_input_name, $subfolder = 'misc'){
    if (empty($_FILES[$file_input_name]['name'])) return null;
    $f = $_FILES[$file_input_name];
    if ($f['error'] !== UPLOAD_ERR_OK) return null;

    $allowed = ['jpg','jpeg','png','webp','gif'];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return false;
    if ($f['size'] > 5 * 1024 * 1024) return false; // 5 MB

    $dir = UPLOAD_PATH . $subfolder . '/';
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $name = $subfolder . '_' . time() . '_' . random_int(1000,9999) . '.' . $ext;
    $dest = $dir . $name;
    if (move_uploaded_file($f['tmp_name'], $dest)) {
        return 'uploads/' . $subfolder . '/' . $name;
    }
    return false;
}

/**
 * Upload a video file (MP4 / WebM / Ogg) — accepts up to 50 MB by default.
 * Returns relative path (uploads/hero/videos/...) on success, false on validation failure,
 * or null when no file was supplied.
 */
function upload_video($file_input_name, $subfolder = 'hero/videos', $max_mb = 50){
    if (empty($_FILES[$file_input_name]['name'])) return null;
    $f = $_FILES[$file_input_name];
    if ($f['error'] !== UPLOAD_ERR_OK) return null;

    $allowed_ext  = ['mp4','webm','ogg','ogv','mov'];
    $allowed_mime = ['video/mp4','video/webm','video/ogg','video/quicktime'];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext)) return false;
    if ($f['size'] > $max_mb * 1024 * 1024) return false;

    // MIME check (defensive)
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $f['tmp_name']);
        finfo_close($finfo);
        if ($mime && !in_array($mime, $allowed_mime)) return false;
    }

    $dir = UPLOAD_PATH . $subfolder . '/';
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $name = 'vid_' . time() . '_' . random_int(1000,9999) . '.' . $ext;
    $dest = $dir . $name;
    if (move_uploaded_file($f['tmp_name'], $dest)) {
        return 'uploads/' . $subfolder . '/' . $name;
    }
    return false;
}

function csrf_token(){
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_check($token){
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
}

function csrf_field(){
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Admin helper — inline instructions for emoji/icon fields.
 * Prints a short 'how to add the icon' help line under the input.
 */
function icon_howto_help($label = 'icon'){
    return '<p class="help">💡 <strong>How to add the ' . e($label) . ':</strong> paste a <strong>single emoji</strong> into the box above — e.g. 📚, 🎯, 🌱, 🙏. To insert one: on <strong>Windows</strong> press <code>Win + .</code> (Windows key + full stop), on <strong>Mac</strong> press <code>Ctrl + ⌘ + Space</code>, or copy an emoji from <strong>emojipedia.org</strong>. Leave the box blank to use the built-in default.</p>';
}

/**
 * Site media manager — per-key images editable from Admin → Banners & Images.
 * Returns the stored relative path (e.g. 'uploads/media/xyz.jpg') or $default.
 * Reads the whole `site_media` table once per request; missing table → default.
 */
function site_media_path($slug, $default = ''){
    static $cache = null;
    if ($cache === null) {
        global $pdo;
        $cache = [];
        try {
            foreach ($pdo->query("SELECT slug, path FROM site_media")->fetchAll() as $r) {
                $cache[$r['slug']] = $r['path'];
            }
        } catch (Throwable $e) { /* table not migrated yet */ }
    }
    $p = $cache[$slug] ?? $default;
    return $p !== '' && $p !== null ? $p : $default;
}

/** Absolute URL of a site-managed image (prefixes BASE_URL). */
function site_image_url($slug, $default = ''){
    $p = site_media_path($slug, $default);
    return $p !== '' ? BASE_URL . ltrim($p, '/') : '';
}

/**
 * Inline `style` value that paints a site-managed image as a background,
 * optionally stacked over a CSS gradient (e.g. a navy overlay for legibility).
 * Caller prints it inside style="…":   style="<?= e(site_bg_attr('banner_about')) ?>"
 * $gradient must be a full CSS gradient value, e.g. 'linear-gradient(rgba(13,41,64,.5), rgba(29,78,216,.25))'.
 */
function site_bg_attr($slug, $gradient = null, $default = ''){
    $u = site_image_url($slug, $default);
    if ($u === '') return 'background-color:#0d2940;';
    $img = "url('" . $u . "')";
    $bg  = $gradient ? $gradient . ', ' . $img : $img;
    return 'background-image:' . $bg . ';background-size:cover;background-position:center';
}


function format_money($amount, $currency = 'INR'){
    $symbols = ['INR' => '₹', 'GBP' => '£', 'USD' => '$'];
    $sym = $symbols[$currency] ?? '';
    return $sym . number_format($amount, 0);
}

function time_ago($datetime){
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff/60) . ' min ago';
    if ($diff < 86400) return floor($diff/3600) . ' hr ago';
    if ($diff < 604800) return floor($diff/86400) . ' day' . (floor($diff/86400)>1?'s':'') . ' ago';
    return date('M j, Y', $time);
}

function get_setting($key, $default = ''){
    global $pdo;
    static $cache = null;
    if ($cache === null) {
        $row = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
        $cache = $row ?: [];
    }
    return $cache[$key] ?? $default;
}
