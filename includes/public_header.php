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
  /* Google Translate widget — replaces the old EN/Hindi manual switcher */
  .gt-widget{display:inline-flex;align-items:center}
  .gt-widget .goog-te-gadget{font-family:'Roboto',sans-serif}
  .gt-widget .goog-te-combo{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.35);border-radius:50px;padding:.28rem .7rem;font-size:.8rem;font-family:'Roboto',sans-serif;cursor:pointer;outline:none;max-width:150px}
  .gt-widget .goog-te-combo option{color:#222;background:#fff}
  .gt-widget .goog-logo-link,.gt-widget .goog-te-gadget span{display:none !important}
  .goog-te-banner-frame{display:none !important}
  #goog-gt-tt{display:none !important}
  .goog-te-spinner-pos{display:none !important}

  /* Mobile menu version (light background) */
  .nav-lang-mobile{display:none;width:100%}
  .nav-lang-mobile .gt-widget{width:100%}
  .nav-lang-mobile .goog-te-combo{width:100%;max-width:none;background:#fff;color:#333;border:1px solid #d5dde3;border-radius:8px;padding:.55rem .8rem;font-size:.9rem}
  @media(max-width:880px){
    .nav-lang-mobile{display:block;padding-top:.9rem;margin-top:.2rem;border-top:1px solid #e9eef4}
  }
</style>
<?php if (!empty($extra_head)) echo $extra_head; ?>
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
  <div class="container">
    <div>📧 <?= e(get_setting('email_in','contact@sharanforall.org')) ?> &nbsp;|&nbsp; 📞 <?= e(get_setting('phone_in','+91 98765 43210')) ?> (IN) &nbsp;|&nbsp; <?= e(get_setting('phone_uk','+44 20 1234 5678')) ?> (UK)</div>
    <div style="display:flex;align-items:center;flex-wrap:wrap">
      <a href="<?= $BU ?>pages/volunteer.php"><?= e(t('nav_volunteer')) ?></a>
      <a href="<?= $BU ?>pages/partner.php"><?= e(t('nav_partner')) ?></a>
      <span class="gt-widget" id="google_translate_element"></span>
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
        <li class="nav-lang-mobile"><span class="gt-widget" id="google_translate_element_mobile"></span></li>
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
  var opts = {
    pageLanguage: 'en',
    includedLanguages: codes.join(','),
    autoDisplay: false,
    layout: google.translate.TranslateElement.InlineLayout.SIMPLE
  };
  try {
    if (document.getElementById('google_translate_element'))
      new google.translate.TranslateElement(opts, 'google_translate_element');
  } catch(e){}
  try {
    if (document.getElementById('google_translate_element_mobile'))
      new google.translate.TranslateElement(opts, 'google_translate_element_mobile');
  } catch(e){}
}
</script>
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async defer></script>

