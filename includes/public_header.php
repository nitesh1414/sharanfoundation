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
$langs = available_languages();
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

<link rel="stylesheet" href="<?= $BU ?>css/style.css" />
<?php if ($LANG === 'hi'): ?>
<style>body{font-family:'Noto Sans Devanagari','Segoe UI',sans-serif}</style>
<?php endif; ?>
<style>
  /* Language switcher */
  .lang-switch{display:inline-flex;align-items:center;gap:.3rem;margin-left:1rem;background:rgba(255,255,255,.1);padding:.15rem .15rem;border-radius:50px;font-size:.8rem}
  .lang-switch a{padding:.2rem .7rem;color:#fff;opacity:.7;border-radius:50px;transition:.2s;text-decoration:none}
  .lang-switch a:hover{opacity:1}
  .lang-switch a.active{background:var(--accent);color:#fff;opacity:1;font-weight:600}
  @media(max-width:600px){.lang-switch{margin-left:.4rem;font-size:.72rem}.lang-switch a{padding:.15rem .5rem}}

  /* Mobile (hamburger-menu) language switcher — shown only on small screens,
     styled as a light pill so it fits the white dropdown menu */
  .nav-lang-mobile{width:100%}
  .nav-lang-mobile .lang-switch{background:#eef2f7;border:1px solid #e2e8f0;margin-left:0}
  .nav-lang-mobile .lang-switch a{color:#40506c;opacity:1}
  .nav-lang-mobile .lang-switch a:hover{background:rgba(37,99,235,.08);color:var(--primary)}
  .nav-lang-mobile .lang-switch a.active{background:var(--accent);color:#fff;opacity:1}
  @media(max-width:880px){
    .nav-lang-mobile{display:block;padding-top:1rem;margin-top:.4rem;border-top:1px solid #e9eef4}
  }
</style>
<?php if (!empty($extra_head)) echo $extra_head; ?>
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
  <div class="container">
    <div>📧 <?= e(get_setting('email_in','contact@sharanfoundation.org')) ?> &nbsp;|&nbsp; 📞 <?= e(get_setting('phone_in','+91 98765 43210')) ?> (IN) &nbsp;|&nbsp; <?= e(get_setting('phone_uk','+44 20 1234 5678')) ?> (UK)</div>
    <div style="display:flex;align-items:center;flex-wrap:wrap">
      <a href="<?= $BU ?>pages/volunteer.php"><?= e(t('nav_volunteer')) ?></a>
      <a href="<?= $BU ?>pages/partner.php"><?= e(t('nav_partner')) ?></a>
      <div class="lang-switch" title="Language / भाषा">
        <?php foreach ($langs as $code => $info): ?>
          <a href="<?= e(lang_url($code)) ?>" class="<?= $LANG===$code?'active':'' ?>" title="<?= e($info['name']) ?>"><?= e($info['flag']) ?> <?= e($code === 'hi' ? 'हि' : 'EN') ?></a>
        <?php endforeach; ?>
      </div>
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
        <li class="nav-lang-mobile"><div class="lang-switch" title="Language / भाषा">
          <?php foreach ($langs as $code => $info): ?>
            <a href="<?= e(lang_url($code)) ?>" class="<?= $LANG===$code?'active':'' ?>" title="<?= e($info['name']) ?>"><?= e($info['flag']) ?> <?= e($code === 'hi' ? 'हि' : 'EN') ?></a>
          <?php endforeach; ?>
        </div></li>
      </ul>
    </nav>
  </div>
</header>
