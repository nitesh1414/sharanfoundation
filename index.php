<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$programs = $pdo->query("SELECT * FROM programs WHERE status='active' ORDER BY display_order, id LIMIT 8")->fetchAll();
$testimonials = $pdo->query("SELECT * FROM testimonials WHERE status='active' ORDER BY display_order, id LIMIT 3")->fetchAll();

// Hero slides for the carousel — graceful fallback if table doesn't exist yet
$slides = [];
try {
    $slides = $pdo->query("SELECT * FROM hero_slides WHERE status='active' ORDER BY display_order, id")->fetchAll();
} catch (Throwable $e) { /* table not migrated yet */ }

// Carousel settings (with sensible defaults)
$carousel_config = [
    'autoplay'     => (int)(get_setting('carousel_autoplay', 1) ?? 1),
    'interval'     => (int)(get_setting('carousel_interval', 6000) ?: 6000),
    'pause_hover'  => (int)(get_setting('carousel_pause_hover', 1) ?? 1),
    'show_arrows'  => (int)(get_setting('carousel_show_arrows', 1) ?? 1),
    'show_dots'    => (int)(get_setting('carousel_show_dots', 1) ?? 1),
    'show_counter' => (int)(get_setting('carousel_show_counter', 1) ?? 1),
    'transition'   => get_setting('carousel_transition', 'fade') ?: 'fade',
    'video_audio'  => (int)(get_setting('carousel_video_audio', 0) ?? 0),
];

/** Extract a YouTube video ID from any URL form, or pass through if already an ID */
function youtube_id($s) {
    if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|v/))([\w-]{6,})~', $s, $m)) return $m[1];
    return preg_match('~^[\w-]{6,15}$~', $s) ? $s : '';
}
/** Extract a Vimeo video ID from any URL form */
function vimeo_id($s) {
    if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $s, $m)) return $m[1];
    return preg_match('~^\d+$~', $s) ? $s : '';
}

// Aggregate stats
$total_programs   = $pdo->query("SELECT COUNT(*) FROM programs WHERE status='active'")->fetchColumn();
$total_volunteers = $pdo->query("SELECT COUNT(*) FROM volunteers WHERE status='approved'")->fetchColumn();

$page_title = 'Home';
$page_desc = 'Sharan Foundation — a Christian charity serving India & UK through education, shelter and faith.';
$current_page = 'home';
$extra_head = '<style>
  /* ============ HERO CAROUSEL ============ */
  .hero-carousel{position:relative;min-height:88vh;overflow:hidden;background:#0d2940}
  .hero-slides{position:relative;width:100%;height:88vh;min-height:560px}
  .hero-slide{position:absolute;inset:0;display:flex;align-items:center;color:#fff;opacity:0;visibility:hidden;transition:opacity 1.1s ease,visibility 1.1s ease,transform 1.1s cubic-bezier(.22,1,.36,1)}
  .hero-slide.active{opacity:1;visibility:visible;z-index:2}
  .hero-slide .bg{position:absolute;inset:0;background-size:cover;background-position:center;transform:scale(1.05);transition:transform 8s ease}
  .hero-slide.active .bg{transform:scale(1)}
  /* Slide transition variant */
  .hero-carousel.trans-slide .hero-slide{opacity:1;visibility:visible;transform:translateX(100%)}
  .hero-carousel.trans-slide .hero-slide.active{transform:translateX(0)}
  .hero-carousel.trans-slide .hero-slide.exiting{transform:translateX(-100%);z-index:1}
  /* Zoom transition variant */
  .hero-carousel.trans-zoom .hero-slide{transform:scale(.94)}
  .hero-carousel.trans-zoom .hero-slide.active{transform:scale(1)}
  /* Video background element */
  .hero-slide .video-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;z-index:0;background:#0d2940}
  .hero-slide .video-overlay{position:absolute;inset:0;z-index:1;pointer-events:none}
  .hero-slide.overlay-blue   .video-overlay{background:linear-gradient(120deg,rgba(37,99,235,.78) 0%,rgba(29,78,216,.62) 45%,rgba(13,41,64,.35) 100%)}
  .hero-slide.overlay-dark   .video-overlay{background:linear-gradient(120deg,rgba(13,41,64,.78) 0%,rgba(13,41,64,.55) 60%,rgba(13,41,64,.28) 100%)}
  .hero-slide.overlay-amber  .video-overlay{background:linear-gradient(120deg,rgba(37,99,235,.68) 0%,rgba(231,111,81,.58) 100%)}
  .hero-slide.overlay-minimal .video-overlay{background:linear-gradient(120deg,rgba(13,41,64,.45),rgba(13,41,64,.18))}
  /* iframe for youtube/vimeo */
  .hero-slide .iframe-bg{position:absolute;top:50%;left:50%;width:100vw;height:56.25vw;min-height:100%;min-width:177.78vh;transform:translate(-50%,-50%);border:0;pointer-events:none;z-index:0}
  /* Video-loading skeleton */
  .hero-slide .video-loading{position:absolute;inset:0;background:linear-gradient(135deg,#2563eb,#0d2940);z-index:0;animation:pulse 2s ease-in-out infinite}
  @keyframes pulse{0%,100%{opacity:.7}50%{opacity:1}}
  /* Overlay variants */
  .hero-slide.overlay-blue .bg::after{content:"";position:absolute;inset:0;background:linear-gradient(120deg,rgba(37,99,235,.85) 0%,rgba(29,78,216,.7) 45%,rgba(13,41,64,.4) 100%)}
  .hero-slide.overlay-dark .bg::after{content:"";position:absolute;inset:0;background:linear-gradient(120deg,rgba(13,41,64,.85) 0%,rgba(13,41,64,.6) 60%,rgba(13,41,64,.3) 100%)}
  .hero-slide.overlay-amber .bg::after{content:"";position:absolute;inset:0;background:linear-gradient(120deg,rgba(37,99,235,.75) 0%,rgba(231,111,81,.65) 100%)}
  .hero-slide.overlay-minimal .bg::after{content:"";position:absolute;inset:0;background:linear-gradient(120deg,rgba(13,41,64,.55),rgba(13,41,64,.25))}
  /* Subtle decorative blue glow on the right edge */
  .hero-slide::after{content:"";position:absolute;top:0;right:-15%;bottom:0;width:50%;background:radial-gradient(ellipse at right,rgba(37,99,235,.35),transparent 70%);pointer-events:none;z-index:1}

  .hero-content{position:relative;z-index:3;max-width:780px;padding:4rem 0;animation:slideFade 1.2s ease}
  .hero-slide.text-center .hero-content{margin:0 auto;text-align:center}
  .hero-slide.text-right  .hero-content{margin-left:auto;text-align:right}
  .hero .tag{background:rgba(255,255,255,.18);color:#fff;backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);padding:.4rem 1rem;border-radius:50px;font-size:.8rem;letter-spacing:1px;margin-bottom:1rem;display:inline-block;font-weight:600}
  .hero h1{font-size:clamp(2rem,5vw,3.6rem);font-weight:800;line-height:1.15;margin-bottom:1rem;text-shadow:0 2px 20px rgba(0,0,0,.3)}
  .hero h1 span{color:var(--accent)}
  .hero .subtitle{font-size:clamp(1rem,1.8vw,1.3rem);color:var(--accent);font-weight:600;margin-bottom:.8rem;letter-spacing:.5px}
  .hero p{font-size:clamp(1rem,1.4vw,1.15rem);margin-bottom:2rem;opacity:.95;max-width:620px;line-height:1.7}
  .hero-slide.text-center p{margin-left:auto;margin-right:auto}
  .hero-cta{display:flex;gap:1rem;flex-wrap:wrap}
  .hero-slide.text-center .hero-cta{justify-content:center}
  .hero-slide.text-right  .hero-cta{justify-content:flex-end}

  /* Nav arrows */
  .hero-nav{position:absolute;top:50%;transform:translateY(-50%);z-index:10;width:54px;height:54px;border-radius:50%;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);color:#fff;font-size:1.5rem;cursor:pointer;backdrop-filter:blur(10px);transition:.25s;display:grid;place-items:center}
  .hero-nav:hover{background:var(--accent);border-color:var(--accent);transform:translateY(-50%) scale(1.1)}
  .hero-nav.prev{left:1.5rem}
  .hero-nav.next{right:1.5rem}

  /* Dots */
  .hero-dots{position:absolute;bottom:2rem;left:50%;transform:translateX(-50%);z-index:10;display:flex;gap:.6rem}
  .hero-dot{width:38px;height:5px;border-radius:50px;background:rgba(255,255,255,.35);border:none;cursor:pointer;padding:0;transition:.3s;overflow:hidden;position:relative}
  .hero-dot.active{background:rgba(255,255,255,.25);width:60px}
  .hero-dot.active::after{content:"";position:absolute;left:0;top:0;height:100%;width:0;background:var(--accent);border-radius:50px;animation:dotProgress 6s linear forwards}
  .hero-dot:hover{background:rgba(255,255,255,.55)}

  /* Slide counter */
  .hero-counter{position:absolute;top:6.5rem;right:2rem;z-index:10;color:#fff;font-size:.85rem;background:rgba(0,0,0,.3);padding:.4rem .9rem;border-radius:50px;backdrop-filter:blur(8px);font-weight:600;letter-spacing:1px}

  @keyframes slideFade{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
  @keyframes dotProgress{from{width:0}to{width:100%}}
  @media(max-width:780px){
    .hero-carousel,.hero-slides{min-height:70vh}
    .hero-content{padding:2rem 0}
    .hero-nav{width:42px;height:42px;font-size:1.2rem}
    .hero-nav.prev{left:.5rem}.hero-nav.next{right:.5rem}
    .hero-counter{top:4.5rem;right:1rem;font-size:.75rem}
    .hero-dot{width:30px}
    .hero-dot.active{width:42px}
  }
  .stats{background:var(--primary-dark);color:#fff;padding:3rem 0}
  .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:2rem;text-align:center}
  .stat h3{font-size:2.5rem;color:var(--accent);font-weight:800}
  .stat p{font-size:.95rem;opacity:.9;letter-spacing:1px;text-transform:uppercase}
  .about-img{position:relative;border-radius:16px;overflow:hidden;box-shadow:var(--shadow);min-height:420px;
    background:linear-gradient(rgba(37,99,235,.15),rgba(26,46,53,.25)),url(\''.BASE_URL.'images/about.jpg\') center/cover}
  .about-img::before{content:"";position:absolute;inset:0;border:6px solid rgba(255,255,255,.5);border-radius:16px;margin:14px;pointer-events:none}
  .about-text h2{font-size:2.2rem;color:var(--dark);margin-bottom:1rem;font-weight:700}
  .about-text h2 span{color:var(--primary)}
  .about-text p{color:#555;margin-bottom:1rem}
  .verse{border-left:4px solid var(--accent);padding:1rem 1.2rem;background:#fffaf0;font-style:italic;color:#5b4a2c;margin:1.5rem 0;border-radius:6px}
  .programs{background:#fff}
  .programs-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:1.8rem}
  .program{background:#fff;border-radius:14px;overflow:hidden;box-shadow:var(--shadow);transition:.3s;border-top:4px solid transparent}
  .program:hover{transform:translateY(-8px);border-top-color:var(--accent)}
  .program-img{height:200px;background-size:cover;background-position:center;position:relative;display:grid;place-items:center;color:#fff;font-size:4rem}
  .program-img::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,transparent,rgba(0,0,0,.3))}
  .icon-circle{position:absolute;bottom:-22px;right:20px;width:50px;height:50px;border-radius:50%;background:var(--accent);color:#fff;display:grid;place-items:center;font-size:1.3rem;box-shadow:0 6px 14px rgba(231,111,81,.4);z-index:2}
  .program-body{padding:2rem 1.5rem 1.5rem}
  .program h3{color:var(--primary-dark);margin-bottom:.6rem;font-size:1.2rem}
  .program p{color:var(--gray);font-size:.95rem;margin-bottom:1rem}
  .program a{color:var(--primary);font-weight:600;font-size:.9rem}
  .program a:hover{color:var(--accent)}
  .mv{background:linear-gradient(135deg,#f5f5f0,#fff)}
  .mv-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:2rem}
  .mv-card{background:#fff;padding:2.5rem;border-radius:14px;box-shadow:var(--shadow);text-align:center;transition:.3s}
  .mv-card:hover{transform:translateY(-5px)}
  .mv-card .ico{width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));margin:0 auto 1.2rem;display:grid;place-items:center;color:#fff;font-size:1.8rem}
  .mv-card h3{color:var(--dark);margin-bottom:.8rem;font-size:1.3rem}
  .mv-card p{color:var(--gray)}
  .locations{background:var(--dark);color:#fff}
  .locations .section-head h2{color:#fff}
  .locations .section-head p{color:#bfc8cb}
  .loc-grid{display:grid;grid-template-columns:1fr 1fr;gap:2rem}
  .loc-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);padding:2.5rem;border-radius:14px;transition:.3s}
  .loc-card:hover{background:rgba(255,255,255,.08);transform:translateY(-5px)}
  .loc-card .flag{font-size:2.5rem;margin-bottom:1rem}
  .loc-card h3{color:var(--accent);font-size:1.6rem;margin-bottom:1rem}
  .loc-card p{opacity:.9;margin-bottom:.7rem}
  .loc-card ul{margin-top:1rem}
  .loc-card li{padding:.4rem 0;border-bottom:1px solid rgba(255,255,255,.08);font-size:.95rem}
  .loc-card li::before{content:"✦ ";color:var(--accent)}
  .cta-band{background:linear-gradient(rgba(37,99,235,.88),rgba(29,78,216,.88)),url(\''.BASE_URL.'images/donate-bg.jpg\') center/cover fixed;color:#fff;text-align:center;padding:5rem 1rem}
  .cta-band h2{font-size:clamp(1.8rem,3.5vw,2.8rem);margin-bottom:1rem;font-weight:700}
  .cta-band p{font-size:1.1rem;max-width:680px;margin:0 auto 2rem;opacity:.95}
  .cta-band .btn-primary{padding:1rem 2.2rem;font-size:1.05rem}
  .test-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.8rem}
  .test-card{background:#fff;padding:2rem;border-radius:14px;box-shadow:var(--shadow);position:relative;border-left:4px solid var(--accent)}
  .test-card .quote{font-size:3rem;color:var(--accent);line-height:1;opacity:.4;position:absolute;top:1rem;right:1.4rem}
  .test-card p{font-style:italic;color:#555;margin-bottom:1.2rem}
  .test-author{display:flex;align-items:center;gap:.8rem}
  .avatar{width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;display:grid;place-items:center;font-weight:700}
  .test-author h4{color:var(--dark);font-size:1rem}
  .test-author span{color:var(--gray);font-size:.85rem}
  @media(max-width:880px){.loc-grid{grid-template-columns:1fr}}
</style>';

require __DIR__ . '/includes/public_header.php';
?>

<!-- HERO CAROUSEL -->
<section class="hero hero-carousel trans-<?= e($carousel_config['transition']) ?>" id="home"
         data-autoplay="<?= $carousel_config['autoplay'] ?>"
         data-interval="<?= (int)$carousel_config['interval'] ?>"
         data-pause-hover="<?= $carousel_config['pause_hover'] ?>">

<?php if (!$slides): ?>
  <!-- Fallback single hero when no slides configured -->
  <div class="hero-slides">
    <div class="hero-slide active overlay-blue text-left">
      <div class="bg" style="background-image:url('<?= BASE_URL ?>images/hero.jpg')"></div>
      <div class="container">
        <div class="hero-content">
          <span class="tag"><?= e(t('hero_badge')) ?></span>
          <h1><?= e(t('hero_title_1')) ?> <span><?= e(t('hero_title_2')) ?></span> <?= e(t('hero_title_3')) ?></h1>
          <p><?= e(get_setting('about_short', 'Sharan Foundation is a charitable ministry dedicated to empowering children, girls, women, and the elderly through education, shelter, and the love of Christ.')) ?></p>
          <div class="hero-cta">
            <a href="<?= BASE_URL ?>pages/donate.php" class="btn btn-primary"><?= e(t('btn_donate_now')) ?> ♥</a>
            <a href="<?= BASE_URL ?>pages/about.php" class="btn btn-outline"><?= e(t('btn_learn_more')) ?> →</a>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php else: ?>

  <div class="hero-slides" id="heroSlides">
    <?php foreach ($slides as $i => $sl):
      $media_type = $sl['media_type'] ?? 'image';
      $has_img = !empty($sl['image']) && file_exists(__DIR__ . '/' . $sl['image']);
      $poster_url = $has_img ? BASE_URL . e($sl['image']) : BASE_URL . 'images/hero.jpg';
      $overlay = $sl['overlay_color'] ?: 'blue';
      $pos = $sl['text_position'] ?: 'left';
      $title    = tr_field($sl, 'title');
      $subtitle = tr_field($sl, 'subtitle');
      $desc     = tr_field($sl, 'description');
      $cta1     = tr_field($sl, 'cta_text')   ?: 'Learn More';
      $cta2     = tr_field($sl, 'cta_text_2');
      $audio_attr = $carousel_config['video_audio'] ? '' : 'muted';
    ?>
      <div class="hero-slide overlay-<?= e($overlay) ?> text-<?= e($pos) ?> media-<?= e($media_type) ?> <?= $i===0?'active':'' ?>"
           data-index="<?= $i ?>"
           data-media="<?= e($media_type) ?>"
           data-loaded="<?= $media_type==='image' ? '1' : '0' ?>">

        <?php if ($media_type === 'image'): ?>
          <div class="bg" style="background-image:url('<?= $poster_url ?>')"></div>

        <?php elseif ($media_type === 'video' && !empty($sl['video_file']) && file_exists(__DIR__ . '/' . $sl['video_file'])): ?>
          <video class="video-bg" <?= $audio_attr ?> loop playsinline preload="metadata" poster="<?= $poster_url ?>" data-src="<?= BASE_URL . e($sl['video_file']) ?>">
            <!-- src injected by JS only when slide becomes active (saves bandwidth) -->
          </video>
          <div class="video-overlay"></div>

        <?php elseif ($media_type === 'youtube' && ($yt_id = youtube_id($sl['video_url'] ?? ''))): ?>
          <div class="video-loading"></div>
          <!-- iframe injected only when slide becomes active -->
          <div class="iframe-wrap" data-iframe-src="https://www.youtube.com/embed/<?= e($yt_id) ?>?autoplay=1&mute=<?= $carousel_config['video_audio']?'0':'1' ?>&loop=1&playlist=<?= e($yt_id) ?>&controls=0&showinfo=0&modestbranding=1&playsinline=1&rel=0"></div>
          <div class="video-overlay"></div>

        <?php elseif ($media_type === 'vimeo' && ($vm_id = vimeo_id($sl['video_url'] ?? ''))): ?>
          <div class="video-loading"></div>
          <div class="iframe-wrap" data-iframe-src="https://player.vimeo.com/video/<?= e($vm_id) ?>?autoplay=1&muted=<?= $carousel_config['video_audio']?'0':'1' ?>&loop=1&background=1&autopause=0"></div>
          <div class="video-overlay"></div>

        <?php else: /* fallback to poster image */ ?>
          <div class="bg" style="background-image:url('<?= $poster_url ?>')"></div>
        <?php endif; ?>

        <div class="container">
          <div class="hero-content">
            <?php if ($sl['badge_text']): ?>
              <span class="tag"><?= e($sl['badge_text']) ?></span>
            <?php endif; ?>
            <?php if ($subtitle): ?>
              <div class="subtitle"><?= e($subtitle) ?></div>
            <?php endif; ?>
            <h1><?= e($title) ?></h1>
            <?php if ($desc): ?>
              <p><?= nl2br(e($desc)) ?></p>
            <?php endif; ?>
            <div class="hero-cta">
              <a href="<?= BASE_URL . e(ltrim($sl['cta_link'] ?: '#', '/')) ?>" class="btn btn-primary"><?= e($cta1) ?> →</a>
              <?php if ($cta2 && $sl['cta_link_2']): ?>
                <a href="<?= BASE_URL . e(ltrim($sl['cta_link_2'], '/')) ?>" class="btn btn-outline"><?= e($cta2) ?></a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (count($slides) > 1): ?>
    <?php if ($carousel_config['show_counter']): ?>
    <div class="hero-counter"><span id="heroCurrent">1</span> / <?= count($slides) ?></div>
    <?php endif; ?>

    <?php if ($carousel_config['show_arrows']): ?>
    <button class="hero-nav prev" id="heroPrev" aria-label="Previous slide">‹</button>
    <button class="hero-nav next" id="heroNext" aria-label="Next slide">›</button>
    <?php endif; ?>

    <?php if ($carousel_config['show_dots']): ?>
    <div class="hero-dots" id="heroDots">
      <?php foreach ($slides as $i => $_): ?>
        <button class="hero-dot <?= $i===0?'active':'' ?>" data-go="<?= $i ?>" aria-label="Go to slide <?= $i+1 ?>"></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  <?php endif; ?>

<?php endif; ?>
</section>

<script>
(function(){
  const carousel = document.querySelector('.hero-carousel');
  const slidesEl = document.getElementById('heroSlides');
  if (!carousel || !slidesEl) return;
  const slides   = slidesEl.querySelectorAll('.hero-slide');
  if (slides.length < 1) return;

  const dots     = document.querySelectorAll('#heroDots .hero-dot');
  const current  = document.getElementById('heroCurrent');
  const autoplay = carousel.dataset.autoplay === '1';
  const interval = parseInt(carousel.dataset.interval, 10) || 6000;
  const pauseHover = carousel.dataset.pauseHover === '1';

  let index = 0;
  let timer = null;
  let isPaused = false;

  // ============ MEDIA HANDLING ============
  // Lazy-load the active slide's video, play it; pause + reset others.
  function loadSlideMedia(slide) {
    const type = slide.dataset.media;
    if (slide.dataset.loaded === '1' || type === 'image') return;

    // Local video file → inject src once
    const video = slide.querySelector('video.video-bg');
    if (video && video.dataset.src && !video.src) {
      video.src = video.dataset.src;
      video.load();
    }

    // YouTube/Vimeo iframe → inject once
    const wrap = slide.querySelector('.iframe-wrap[data-iframe-src]');
    if (wrap && !wrap.querySelector('iframe')) {
      const iframe = document.createElement('iframe');
      iframe.className = 'iframe-bg';
      iframe.src = wrap.dataset.iframeSrc;
      iframe.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
      iframe.setAttribute('allowfullscreen', '');
      wrap.appendChild(iframe);
      // remove loading shimmer once iframe loads
      iframe.addEventListener('load', () => {
        const loader = slide.querySelector('.video-loading');
        if (loader) loader.style.display = 'none';
      });
    }

    slide.dataset.loaded = '1';
  }

  function playSlideVideo(slide) {
    const video = slide.querySelector('video.video-bg');
    if (video) {
      const p = video.play();
      if (p && typeof p.catch === 'function') p.catch(() => {/* autoplay blocked: silent */});
    }
  }
  function pauseSlideVideo(slide) {
    const video = slide.querySelector('video.video-bg');
    if (video && !video.paused) { try { video.pause(); video.currentTime = 0; } catch(e){} }
  }

  // For HTML5 video slides we want the carousel timer to wait the video's duration.
  // Use the slide's media type to compute "stayTime" dynamically.
  function stayTimeFor(slide) {
    const video = slide.querySelector('video.video-bg');
    if (video && video.duration && isFinite(video.duration) && video.duration > 3) {
      // Use min(video duration, configured interval * 2.5) — cap so very long videos still rotate
      return Math.min(video.duration * 1000, interval * 2.5);
    }
    // YouTube/Vimeo: use 1.5x interval (typical short loop)
    if (slide.dataset.media === 'youtube' || slide.dataset.media === 'vimeo') {
      return Math.max(interval, 10000);
    }
    return interval;
  }

  // ============ NAVIGATION ============
  function go(i) {
    const newIndex = (i + slides.length) % slides.length;
    if (newIndex === index && slides[index].classList.contains('active')) return;

    // For slide transition mode, track exiting slide
    if (carousel.classList.contains('trans-slide')) {
      slides[index].classList.add('exiting');
      setTimeout(() => slides[index]?.classList.remove('exiting'), 1100);
    }

    // Pause OLD slide's video
    pauseSlideVideo(slides[index]);

    index = newIndex;
    slides.forEach((s,n) => s.classList.toggle('active', n === index));
    dots.forEach((d,n) => d.classList.toggle('active', n === index));
    if (current) current.textContent = index + 1;

    // Lazy-load + play NEW slide's media
    loadSlideMedia(slides[index]);
    playSlideVideo(slides[index]);

    // Reset timer based on this slide's stay time
    resetTimer();
  }
  function next() { go(index + 1); }
  function prev() { go(index - 1); }

  function resetTimer() {
    if (timer) clearTimeout(timer);
    if (!autoplay || isPaused) return;
    timer = setTimeout(next, stayTimeFor(slides[index]));
  }

  // ============ EVENT WIRING ============
  document.getElementById('heroNext')?.addEventListener('click', next);
  document.getElementById('heroPrev')?.addEventListener('click', prev);
  dots.forEach(d => d.addEventListener('click', () => go(+d.dataset.go)));

  // Touch swipe
  let touchStartX = 0;
  slidesEl.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, {passive:true});
  slidesEl.addEventListener('touchend',   e => {
    const dx = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(dx) > 50) (dx > 0 ? prev() : next());
  });

  // Pause on hover (desktop only)
  if (pauseHover && window.matchMedia('(hover:hover)').matches) {
    slidesEl.addEventListener('mouseenter', () => { isPaused = true; if (timer) clearTimeout(timer); });
    slidesEl.addEventListener('mouseleave', () => { isPaused = false; resetTimer(); });
  }

  // Pause when tab is not visible (saves battery + bandwidth)
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      isPaused = true;
      if (timer) clearTimeout(timer);
      pauseSlideVideo(slides[index]);
    } else {
      isPaused = false;
      playSlideVideo(slides[index]);
      resetTimer();
    }
  });

  // Keyboard arrows
  document.addEventListener('keydown', e => {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    if (e.key === 'ArrowLeft')  prev();
    if (e.key === 'ArrowRight') next();
  });

  // Initial: load + play the first slide's media
  loadSlideMedia(slides[0]);
  playSlideVideo(slides[0]);
  resetTimer();
})();
</script>

<!-- STATS -->
<section class="stats">
  <div class="container">
    <div class="stats-grid">
      <div class="stat"><h3>2,500+</h3><p><?= e(t('stats_children')) ?></p></div>
      <div class="stat"><h3>15+</h3><p><?= e(t('stats_years')) ?></p></div>
      <div class="stat"><h3><?= (int)$total_programs ?></h3><p><?= e(t('stats_programs')) ?></p></div>
      <div class="stat"><h3>2</h3><p><?= e(t('stats_countries')) ?></p></div>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section id="about">
  <div class="container">
    <div class="grid-2">
      <div class="about-img"></div>
      <div class="about-text">
        <span class="tag"><?= e(t('about_badge')) ?></span>
        <h2><?= e(t('about_title')) ?></h2>
        <p><?= nl2br(e(get_setting('about_short'))) ?></p>
        <div class="verse">"Pure and undefiled religion before God is this: to visit orphans and widows in their trouble, and to keep oneself unspotted from the world." — James 1:27</div>
        <a href="<?= BASE_URL ?>pages/about.php" class="btn btn-primary"><?= e(t('btn_learn_more')) ?></a>
      </div>
    </div>
  </div>
</section>

<!-- MV -->
<section class="mv">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= e(t('mv_badge')) ?></span>
      <h2><?= e(t('mv_title')) ?></h2>
      <p><?= e(t('mv_subtitle')) ?></p>
    </div>
    <div class="mv-grid">
      <div class="mv-card"><div class="ico">🎯</div><h3><?= e(t('mission_label')) ?></h3><p><?= e(get_setting('mission')) ?></p></div>
      <div class="mv-card"><div class="ico">👁️</div><h3><?= e(t('vision_label')) ?></h3><p><?= e(get_setting('vision')) ?></p></div>
      <div class="mv-card"><div class="ico">💝</div><h3><?= e(t('values_label')) ?></h3><p><?= e(get_setting('values_text')) ?></p></div>
    </div>
  </div>
</section>

<!-- PROGRAMS (from DB) -->
<section class="programs" id="programs">
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= e(t('programs_badge')) ?></span>
      <h2><?= e(t('programs_title')) ?></h2>
      <p><?= count($programs) ?></p>
    </div>
    <div class="programs-grid">
      <?php
      $bg_colors = ['linear-gradient(135deg,#2563eb,#f4a261)','linear-gradient(135deg,#5b3a1f,#d4a017)','linear-gradient(135deg,#1a4d6e,#5fa8c9)','linear-gradient(135deg,#e76f51,#f4a261)'];
      foreach ($programs as $i => $p):
        $has_img = !empty($p['image']) && file_exists(__DIR__ . '/' . $p['image']);
      ?>
        <div class="program">
          <div class="program-img" style="<?= $has_img ? "background-image:url('".BASE_URL.e($p['image'])."')" : 'background:'.$bg_colors[$i % 4] ?>">
            <?php if (!$has_img): ?><span style="z-index:1;text-shadow:0 4px 14px rgba(0,0,0,.3)"><?= e($p['icon']) ?></span><?php endif; ?>
            <div class="icon-circle"><?= e($p['icon']) ?></div>
          </div>
          <div class="program-body">
            <h3><?= e(tr_field($p, 'title')) ?></h3>
            <p><?= e(tr_field($p, 'short_desc')) ?></p>
            <a href="<?= BASE_URL ?>pages/programs.php#<?= e($p['slug']) ?>"><?= e(t('btn_learn_more')) ?> →</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- LOCATIONS -->
<section class="locations" id="locations">
  <div class="container">
    <div class="section-head">
      <span class="tag" style="background:rgba(244,162,97,.2);color:var(--accent)"><?= e(t('locations_badge')) ?></span>
      <h2 style="color:#fff"><?= e(t('locations_title')) ?></h2>
      <p><?= e(t('locations_sub')) ?></p>
    </div>
    <div class="loc-grid">
      <div class="loc-card"><div class="flag">🇮🇳</div><h3><?= e(t('india_ops')) ?></h3><p><?= e(get_setting('address_in')) ?></p><ul><li>Child & girl education programs</li><li>Women empowerment & skills training</li><li>Old-age homes & care facilities</li><li>Boys' & girls' hostels</li><li>Acts Bible College</li></ul></div>
      <div class="loc-card"><div class="flag">🇬🇧</div><h3><?= e(t('uk_ops')) ?></h3><p><?= e(get_setting('address_uk')) ?></p><ul><li>Fundraising & sponsorships</li><li>Volunteer & partnership programs</li><li>Local community outreach</li><li>Mission awareness & advocacy</li><li>Prayer & support network</li></ul></div>
    </div>
  </div>
</section>

<!-- DONATE CTA -->
<section class="cta-band">
  <div class="container">
    <h2><?= e(t('donate_band_title')) ?></h2>
    <p><?= e(t('donate_band_text')) ?></p>
    <a href="<?= BASE_URL ?>pages/donate.php" class="btn btn-primary"><?= e(t('btn_donate_now')) ?> ♥</a> &nbsp;
    <a href="<?= BASE_URL ?>pages/partner.php" class="btn btn-outline"><?= e(t('nav_partner')) ?></a>
  </div>
</section>

<!-- TESTIMONIALS (from DB) -->
<?php if ($testimonials): ?>
<section>
  <div class="container">
    <div class="section-head">
      <span class="tag"><?= e(t('testimonials_badge')) ?></span>
      <h2><?= e(t('testimonials_title')) ?></h2>
    </div>
    <div class="test-grid">
      <?php foreach ($testimonials as $tm): ?>
        <div class="test-card">
          <div class="quote">"</div>
          <p><?= e(tr_field($tm, 'message')) ?></p>
          <div class="test-author">
            <div class="avatar"><?= strtoupper(substr(preg_replace('/[^A-Za-z ]/','',$tm['name']),0,2)) ?></div>
            <div><h4><?= e(tr_field($tm, 'name')) ?></h4><span><?= e(tr_field($tm, 'role')) ?></span></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/public_footer.php'; ?>
