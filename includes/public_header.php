<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/i18n.php';
$flash = flash_get();
$current_page = $current_page ?? '';
$page_title = $page_title ?? 'Sharan Foundation';
$page_desc = $page_desc ?? 'A Christian charity serving India & UK through education, shelter and faith.';
$BU = BASE_URL;
$LANG = current_lang();
$gt_codes = json_encode(site_language_codes());
?><!DOCTYPE html>
<html lang="<?= e($LANG) ?>">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= e($page_title) ?> | Sharan Foundation</title>
<meta name="description" content="<?= e($page_desc) ?>" />

<!-- ============ PWA / Progressive Web App ============ -->
<link rel="manifest" href="<?= $BU ?>manifest.webmanifest" />
<meta name="theme-color" content="#2563eb" media="(prefers-color-scheme: light)" />
<meta name="theme-color" content="#0d2940" media="(prefers-color-scheme: dark)" />
<meta name="color-scheme" content="light" />

<!-- Favicons (multi-format for every browser) -->
<link rel="icon" type="image/png" sizes="32x32" href="<?= $BU ?>icons/favicon-32.png" />
<link rel="icon" type="image/png" sizes="16x16" href="<?= $BU ?>icons/favicon-16.png" />
<link rel="shortcut icon" href="<?= $BU ?>favicon.ico" />

<!-- iOS / Safari (Add to Home Screen) -->
<link rel="apple-touch-icon" sizes="180x180" href="<?= $BU ?>icons/apple-touch-icon.png" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
<meta name="apple-mobile-web-app-title" content="Sharan" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="format-detection" content="telephone=no" />

<!-- Windows tiles / MS Edge -->
<meta name="msapplication-TileColor" content="#2563eb" />
<meta name="msapplication-TileImage" content="<?= $BU ?>icons/icon-144.png" />
<meta name="msapplication-config" content="none" />
<meta name="application-name" content="Sharan Foundation" />

<!-- Open Graph (social sharing) -->
<meta property="og:type"        content="website" />
<meta property="og:title"       content="<?= e($page_title) ?> | Sharan Foundation" />
<meta property="og:description" content="<?= e($page_desc) ?>" />
<meta property="og:image"       content="<?= $BU ?>icons/icon-512.png" />
<meta property="og:site_name"   content="Sharan Foundation" />
<meta name="twitter:card"       content="summary_large_image" />
<meta name="twitter:title"      content="<?= e($page_title) ?>" />
<meta name="twitter:description" content="<?= e($page_desc) ?>" />
<meta name="twitter:image"      content="<?= $BU ?>icons/icon-512.png" />
<!-- ============ END PWA ============ -->

<!-- ============ FONTS (Poppins + Roboto) ============ -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />

<link rel="stylesheet" href="<?= $BU ?>css/style.css" />
<style>
  /* Google Translate — floating icon pinned to the right edge of the screen,
     always visible on desktop & mobile (replaces the old EN/Hindi switcher) */
  .gt-float{
    position:fixed;            /* stays in place while the page scrolls */
    right:10px;                /* right side of the template */
    top:50%;
    margin-top:-22px;          /* ~half the pill height: keeps it centred without transform (safe for the GT dropdown) */
    z-index:9998;
    line-height:0;
  }
  .gt-float .goog-te-gadget{font-family:'Roboto',sans-serif;line-height:normal}
  /* the pill itself — compact white capsule */
  .gt-float .goog-te-gadget-simple{
    background:#ffffff;
    border:1px solid rgba(13,41,64,.18);
    border-radius:50px;
    padding:.55rem .62rem;
    box-shadow:0 6px 18px rgba(13,41,64,.16);
    cursor:pointer;
    white-space:nowrap;
    transition:box-shadow .2s ease, transform .2s ease;
  }
  .gt-float .goog-te-gadget-simple:hover{box-shadow:0 8px 24px rgba(37,99,235,.32);transform:translateY(-1px)}
  .gt-float .goog-te-gadget-simple:focus{outline:2px solid #2563eb;outline-offset:2px}
  .gt-float .goog-te-gadget img{vertical-align:middle;border:none}
  /* icon-only look: keep the translate glyphs, hide the “Select Language” text label */
  .gt-float .goog-te-gadget span{display:none !important}
  .gt-float .goog-te-gadget option{color:#222;background:#fff;display:block}
  /* hide Google branding/banner/tooltip chrome; language menu stays functional */
  .goog-te-banner-frame{display:none !important}
  .goog-logo-link{display:none !important}
  #goog-gt-tt{display:none !important}
  .goog-te-spinner-pos{display:none !important}

  /* slightly closer to the edge + smaller on phones */
  @media(max-width:640px){
    .gt-float{right:6px;margin-top:-19px}
    .gt-float .goog-te-gadget-simple{padding:.48rem .55rem}
  }
</style>
<?php if (!empty($extra_head)) echo $extra_head; ?>
</head>
<body>

<!-- Google Translate icon — fixed to the right side of the page, visible on web & mobile -->
<div class="gt-float" id="gt_float" title="Translate this website / अनुवाद करें" aria-label="Google Translate – select language">
  <div id="google_translate_element"></div>
</div>

<!-- TOP BAR -->
<div class="topbar">
  <div class="container">
    <div>📧 <?= e(get_setting('email_in','contact@sharanforall.org')) ?> &nbsp;|&nbsp; 📞 <?= e(get_setting('phone_in','+91 98765 43210')) ?> (IN) &nbsp;|&nbsp; <?= e(get_setting('phone_uk','+44 20 1234 5678')) ?> (UK)</div>
    <div style="display:flex;align-items:center;flex-wrap:wrap">
      <a href="<?= $BU ?>pages/volunteer.php"><?= e(t('nav_volunteer')) ?></a>
      <a href="<?= $BU ?>pages/partner.php"><?= e(t('nav_partner')) ?></a>
    </div>
  </div>
</div>

<!-- NAVBAR -->
<header class="nav">
  <div class="nav-inner">
    <a href="<?= $BU ?>" class="logo" aria-label="Sharan Foundation – Home">
      <div class="logo-mark"><img src="<?= $BU ?>images/logo.png" alt="Sharan Foundation logo"></div>
      <div class="logo-text">
        <span class="logo-name">Sharan Foundation</span>
        <small><?= e(t('site_tagline')) ?></small>
      </div>
    </a>
    <nav>
      <button class="menu-toggle" aria-label="Toggle menu">☰</button>
      <ul id="navlist">
        <li><a href="<?= $BU ?>" class="<?= $current_page==='home'?'active':'' ?>"><?= e(t('nav_home')) ?></a></li>
        <li><a href="<?= $BU ?>pages/about.php" class="<?= $current_page==='about'?'active':'' ?>"><?= e(t('nav_about')) ?></a></li>
        <li><a href="<?= $BU ?>pages/programs.php" class="<?= $current_page==='programs'?'active':'' ?>"><?= e(t('nav_programs')) ?></a></li>
        <li><a href="<?= $BU ?>pages/projects.php" class="<?= $current_page==='projects'?'active':'' ?>"><?= e(t('nav_projects')) ?></a></li>
        <li><a href="<?= $BU ?>pages/fundraisers.php" class="<?= $current_page==='fundraisers'?'active':'' ?>"><?= e(t('nav_fundraisers')) ?></a></li>
        <li><a href="<?= $BU ?>pages/gallery.php" class="<?= $current_page==='gallery'?'active':'' ?>"><?= e(t('nav_gallery')) ?></a></li>
        <li><a href="<?= $BU ?>pages/blog.php" class="<?= $current_page==='blog'?'active':'' ?>"><?= e(t('nav_blog')) ?></a></li>
        <li><a href="<?= $BU ?>pages/contact.php" class="<?= $current_page==='contact'?'active':'' ?>"><?= e(t('nav_contact')) ?></a></li>
        <li><a href="<?= $BU ?>pages/donate.php" class="btn btn-primary"><?= e(t('nav_donate')) ?> ♥</a></li>
      </ul>
    </nav>
  </div>
</header>

<!-- ===== Google Translate (client-side translation) ===== -->
<script type="text/javascript">
function googleTranslateElementInit(){
  if (window.__gtInitDone) return;
  window.__gtInitDone = true;
  var codes = <?= $gt_codes ?>; /* e.g. ["en","hi","fi"] — from Admin -> Languages & Translation */
  var el = document.getElementById('google_translate_element');
  if (!el) return;
  new google.translate.TranslateElement({
    pageLanguage: 'en',
    includedLanguages: codes.join(','),
    autoDisplay: false,
    layout: google.translate.TranslateElement.InlineLayout.SIMPLE
  }, 'google_translate_element');
}
</script>
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async defer></script>

