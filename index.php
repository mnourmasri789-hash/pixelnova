<?php
/**
 * PixelNova Portfolio — Dynamic Frontend
 *
 * Fetches projects from the database and renders them dynamically.
 * The contact form submits via AJAX to submit_message.php.
 * All frontend design, 3D elements, and text remain identical.
 */

require_once __DIR__ . '/db_config.php';

// Fetch active projects ordered by sort_order
$projects = [];
try {
    $db = getDB();
    $stmt = $db->query('SELECT * FROM projects WHERE is_active = 1 ORDER BY sort_order ASC');
    $projects = $stmt->fetchAll();
} catch (PDOException $e) {
    // If DB is unavailable, page still renders with empty projects
    $projects = [];
}

$projectCount = count($projects);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PixelNova - Web Development Agency. E-ticaretten İş Otomasyonlarına kadar akıllı web çözümleri üretiyoruz.">
  <meta name="keywords" content="Web Development Agency, Full-Stack Development, PHP, JavaScript, E-commerce, Web Automation, PixelNova">
  <meta name="author" content="PixelNova">

  <title>PixelNova | Web Development Agency</title>

  <!-- Styles -->
  <link rel="stylesheet" href="style.css">

  <!-- Three.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

  <!-- GSAP -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
</head>
<body>

  <!-- Loading Screen -->
  <div id="loading-screen">
    <div class="loader">
      <div class="loader-ring"></div>
      <div class="loader-text" data-en="Loading..." data-tr="Yükleniyor..." data-ar="جاري التحميل...">Yükleniyor...</div>
    </div>
  </div>

  <!-- Background Effects -->
  <div class="bg-grid"></div>
  <div class="bg-glow bg-glow-1"></div>
  <div class="bg-glow bg-glow-2"></div>
  <div class="bg-glow bg-glow-3"></div>

  <!-- Cursor Glow -->
  <div class="cursor-glow" id="cursor-glow"></div>

  <!-- Navigation -->
  <nav id="navbar">
    <div class="nav-container">
      <a href="#hero" class="nav-logo">PixelNova<span>.</span></a>

      <div class="nav-overlay" id="nav-overlay"></div>

      <ul class="nav-links" id="nav-links">
        <li><a href="#hero" class="nav-link" data-en="Home" data-tr="Ana Sayfa" data-ar="الرئيسية">Ana Sayfa</a></li>
        <li><a href="#projects" class="nav-link" data-en="Projects" data-tr="Projeler" data-ar="المشاريع">Projeler</a></li>
        <li><a href="#skills" class="nav-link" data-en="Skills" data-tr="Yetenekler" data-ar="المهارات">Yetenekler</a></li>
        <li><a href="#contact" class="nav-link" data-en="Contact" data-tr="İletişim" data-ar="اتصل بنا">İletişim</a></li>
        <li>
          <button class="nav-lang-toggle" id="lang-toggle">
            <span class="globe-icon">🌐</span>
            <span id="lang-label">EN</span>
          </button>
        </li>
      </ul>

      <button class="nav-menu-toggle" id="nav-toggle" aria-label="Toggle Menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
  </nav>

  <!-- ===== HERO SECTION ===== -->
  <section id="hero">
    <div class="hero-container">
      <div class="hero-content">
        <div class="hero-badge">
          <span class="dot"></span>
          <span data-en="Open to new projects" data-tr="Yeni projelere açığız" data-ar="متاحون لمشاريع جديدة">Yeni projelere açığız</span>
        </div>

        <h1 class="hero-title">
          <span data-en="Hi, We are" data-tr="Merhaba, Biz" data-ar="مرحباً، نحن">Merhaba, Biz</span><br>
          <span class="highlight">PixelNova</span><br>
          <span data-en="Web Development Agency" data-tr="Web Geliştirme Ajansı" data-ar="وكالة تطوير ويب">Web Geliştirme Ajansı</span>
        </h1>

        <p class="hero-subtitle" data-en="We build smart web solutions, from E-commerce to Business Automation." data-tr="E-ticaretten İş Otomasyonlarına kadar akıllı web çözümleri üretiyoruz." data-ar="نحن نبني حلول ويب ذكية، من التجارة الإلكترونية إلى أتمتة الأعمال.">
          E-ticaretten İş Otomasyonlarına kadar akıllı web çözümleri üretiyoruz.
        </p>

        <div class="hero-cta-group">
          <a href="#projects" class="btn-primary" id="cta-projects">
            <span data-en="View Projects" data-tr="Projelerimizi İncele" data-ar="عرض المشاريع">Projelerimizi İncele</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </a>
          <a href="#contact" class="btn-secondary" id="cta-contact">
            <span data-en="Get in Touch" data-tr="Bize Ulaşın" data-ar="تواصل معنا">Bize Ulaşın</span>
          </a>
        </div>

        <div class="hero-stats">
          <div class="stat-item">
            <span class="stat-number" id="stat-projects"><?= $projectCount ?>+</span>
            <span class="stat-label" data-en="Projects" data-tr="Proje" data-ar="مشروع">Proje</span>
          </div>
          <div class="stat-item">
            <span class="stat-number" id="stat-tech">5+</span>
            <span class="stat-label" data-en="Technologies" data-tr="Teknoloji" data-ar="تقنيات">Teknoloji</span>
          </div>
          <div class="stat-item">
            <span class="stat-number" id="stat-clients">10+</span>
            <span class="stat-label" data-en="Happy Clients" data-tr="Mutlu Müşteri" data-ar="عملاء سعداء">Mutlu Müşteri</span>
          </div>
        </div>
      </div>

      <div class="hero-3d">
        <canvas id="hero-canvas"></canvas>
      </div>
    </div>
  </section>

  <!-- ===== PROJECTS SECTION ===== -->
  <section id="projects">
    <div class="container">
      <div class="section-header reveal">
        <span class="section-label" data-en="Portfolio" data-tr="Portföy" data-ar="محفظة الأعمال">Portföy</span>
        <h2 class="section-title" data-en="Featured Projects" data-tr="Öne Çıkan Projeler" data-ar="مشاريع مميزة">Öne Çıkan Projeler</h2>
        <p class="section-subtitle" data-en="A selection of projects we've built, from e-commerce to business automation systems." data-tr="E-ticaretten iş otomasyon sistemlerine kadar geliştirdiğimiz projelerden seçmeler." data-ar="مجموعة مختارة من المشاريع التي قمنا ببنائها، من التجارة الإلكترونية إلى أنظمة أتمتة الأعمال.">E-ticaretten iş otomasyon sistemlerine kadar geliştirdiğimiz projelerden seçmeler.</p>
      </div>

      <div class="projects-grid">
        <?php if (!empty($projects)): ?>
          <?php foreach ($projects as $project): ?>
            <?php
              $techTags = array_map('trim', explode(',', $project['tech_stack']));
              $slug = strtolower(str_replace(' ', '-', $project['title']));
            ?>
            <div class="project-card reveal" id="project-<?= esc($slug) ?>">
              <div class="project-image">
                <img src="<?= esc($project['image']) ?>" alt="<?= esc($project['title']) ?>" loading="lazy">
                <div class="project-image-overlay"></div>
                <span class="project-badge <?= esc($project['badge_class']) ?>"
                      data-en="<?= esc($project['badge_text_en']) ?>"
                      data-tr="<?= esc($project['badge_text_tr']) ?>"
                      data-ar="<?= esc($project['badge_text_en']) ?>"><?= esc($project['badge_text_tr']) ?></span>
              </div>
              <div class="project-info">
                <h3 class="project-title"><?= esc($project['title']) ?></h3>
                <p class="project-description"
                   data-en="<?= esc($project['description_en']) ?>"
                   data-tr="<?= esc($project['description_tr']) ?>"
                   data-ar="<?= esc($project['description_en']) ?>"><?= esc($project['description_tr']) ?></p>
                <div class="project-tech-stack">
                  <?php foreach ($techTags as $tag): ?>
                    <span class="tech-tag"><?= esc($tag) ?></span>
                  <?php endforeach; ?>
                </div>
                <?php if (!empty($project['project_url'])): ?>
                  <a href="<?= esc($project['project_url']) ?>" target="_blank" class="btn-primary" style="margin-top: 20px; padding: 8px 16px; font-size: 14px; width: 100%; justify-content: center; text-decoration: none;">
                    <span data-en="Visit Website" data-tr="Siteyi Ziyaret Et" data-ar="زيارة الموقع">Siteyi Ziyaret Et</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 6px;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- Fallback: static cards if DB is unavailable -->
          <div class="project-card reveal" id="project-modaren">
            <div class="project-image">
              <img src="images/modaren.png" alt="MODAREN E-commerce Platform" loading="lazy">
              <div class="project-image-overlay"></div>
              <span class="project-badge ecommerce">E-Commerce</span>
            </div>
            <div class="project-info">
              <h3 class="project-title">MODAREN</h3>
              <p class="project-description" data-en="A full-featured e-commerce platform with product management, secure checkout, and responsive storefront design." data-tr="Ürün yönetimi, güvenli ödeme ve duyarlı vitrin tasarımına sahip tam özellikli bir e-ticaret platformu." data-ar="منصة تجارة إلكترونية كاملة الميزات مع إدارة المنتجات، ودفع آمن، وتصميم واجهة متجاوب.">Ürün yönetimi, güvenli ödeme ve duyarlı vitrin tasarımına sahip tam özellikli bir e-ticaret platformu.</p>
              <div class="project-tech-stack">
                <span class="tech-tag">PHP</span>
                <span class="tech-tag">MySQL</span>
                <span class="tech-tag">JavaScript</span>
                <span class="tech-tag">CSS</span>
              </div>
            </div>
          </div>

          <div class="project-card reveal" id="project-ada-kunefe">
            <div class="project-image">
              <img src="images/ada-kunefe.png" alt="Ada Künefe Digital QR Menu" loading="lazy">
              <div class="project-image-overlay"></div>
              <span class="project-badge menu" data-en="QR Menu" data-tr="QR Menü" data-ar="قائمة QR">QR Menü</span>
            </div>
            <div class="project-info">
              <h3 class="project-title">Ada Künefe</h3>
              <p class="project-description" data-en="A digital QR menu system for restaurants with dynamic category filtering, beautiful food presentation, and instant ordering." data-tr="Dinamik kategori filtreleme, güzel yemek sunumu ve anlık sipariş özellikleriyle restoranlar için dijital QR menü sistemi." data-ar="نظام قائمة QR رقمي للمطاعم مع تصفية ديناميكية للفئات، وعرض جميل للطعام، وطلب فوري.">Dinamik kategori filtreleme, güzel yemek sunumu ve anlık sipariş özellikleriyle restoranlar için dijital QR menü sistemi.</p>
              <div class="project-tech-stack">
                <span class="tech-tag">HTML</span>
                <span class="tech-tag">CSS</span>
                <span class="tech-tag">JavaScript</span>
              </div>
            </div>
          </div>

          <div class="project-card reveal" id="project-smartwash">
            <div class="project-image">
              <img src="images/smartwash.png" alt="SmartWash Business Automation" loading="lazy">
              <div class="project-image-overlay"></div>
              <span class="project-badge automation" data-en="Automation" data-tr="Otomasyon" data-ar="أتمتة">Otomasyon</span>
            </div>
            <div class="project-info">
              <h3 class="project-title">SmartWash</h3>
              <p class="project-description" data-en="A business automation system for laundry management with scheduling, customer tracking, and analytics dashboard." data-tr="Planlama, müşteri takibi ve analiz paneline sahip çamaşırhane iş otomasyon sistemi." data-ar="نظام أتمتة أعمال لإدارة المغاسل مع الجدولة، وتتبع العملاء، ولوحة تحليلات.">Planlama, müşteri takibi ve analiz paneline sahip çamaşırhane iş otomasyon sistemi.</p>
              <div class="project-tech-stack">
                <span class="tech-tag">PHP</span>
                <span class="tech-tag">MySQL</span>
                <span class="tech-tag">JavaScript</span>
                <span class="tech-tag">CSS</span>
              </div>
            </div>
          </div>

          <div class="project-card reveal" id="project-digital-invites">
            <div class="project-image">
              <img src="images/digital-invites.png" alt="Digital Invites RSVP System" loading="lazy">
              <div class="project-image-overlay"></div>
              <span class="project-badge rsvp" data-en="RSVP System" data-tr="LCV Sistemi" data-ar="نظام دعوات">LCV Sistemi</span>
            </div>
            <div class="project-info">
              <h3 class="project-title" data-en="Digital Invites" data-tr="Dijital Davetiye" data-ar="دعوات رقمية">Dijital Davetiye</h3>
              <p class="project-description" data-en="A smart digital invitation and RSVP management system with personalized links, guest tracking, and real-time confirmations." data-tr="Kişiselleştirilmiş bağlantılar, misafir takibi ve gerçek zamanlı onaylarla akıllı dijital davetiye ve LCV yönetim sistemi." data-ar="نظام ذكي لإدارة الدعوات الرقمية مع روابط شخصية، وتتبع للضيوف، وتأكيدات في الوقت الفعلي.">Kişiselleştirilmiş bağlantılar, misafir takibi ve gerçek zamanlı onaylarla akıllı dijital davetiye ve LCV yönetim sistemi.</p>
              <div class="project-tech-stack">
                <span class="tech-tag">PHP</span>
                <span class="tech-tag">MySQL</span>
                <span class="tech-tag">JavaScript</span>
                <span class="tech-tag">CSS</span>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ===== SKILLS SECTION ===== -->
  <section id="skills">
    <div class="container">
      <div class="section-header reveal">
        <span class="section-label" data-en="Tech Stack" data-tr="Teknoloji Yığını" data-ar="حزمة التقنيات">Teknoloji Yığını</span>
        <h2 class="section-title" data-en="Skills &amp; Technologies" data-tr="Yetenekler &amp; Teknolojiler" data-ar="المهارات والتقنيات">Yetenekler &amp; Teknolojiler</h2>
        <p class="section-subtitle" data-en="The core technologies we use to build modern, scalable web applications." data-tr="Modern, ölçeklenebilir web uygulamaları oluşturmak için kullandığımız temel teknolojiler." data-ar="التقنيات الأساسية التي نستخدمها لبناء تطبيقات ويب حديثة وقابلة للتطوير.">Modern, ölçeklenebilir web uygulamaları oluşturmak için kullandığımız temel teknolojiler.</p>
      </div>

      <!-- 3D Skills Orbit -->
      <div class="skills-orbit reveal">
        <canvas id="skills-canvas"></canvas>
      </div>

      <!-- Skill Cards -->
      <div class="skills-grid reveal">
        <div class="skill-card" id="skill-html">
          <div class="skill-icon html">
            <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor"><path d="M1.5 0h21l-1.91 21.563L11.977 24l-8.564-2.438L1.5 0zm7.031 9.75l-.232-2.718 10.059.003.076-.757.076-.771.076-.758H6.792l.227 2.716.183 2.224h8.68l-.35 3.78-2.02.602-2.046-.602-.13-1.434H9.2l.265 2.716 3.046.86.015-.003.015.003 3.046-.86.396-4.06H8.531z"/></svg>
          </div>
          <span class="skill-name">HTML5</span>
          <div class="skill-bar-container"><div class="skill-bar" data-width="95"></div></div>
        </div>

        <div class="skill-card" id="skill-css">
          <div class="skill-icon css">
            <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor"><path d="M1.5 0h21l-1.91 21.563L11.977 24l-8.565-2.438L1.5 0zm17.09 4.413L5.41 4.41l.213 2.622 10.125.002-.255 2.716h-6.64l.24 2.573h6.182l-.366 3.523-2.91.804-2.956-.81-.188-2.11H6.61l.33 4.171L12 19.351l5.085-1.47L18.59 4.414z"/></svg>
          </div>
          <span class="skill-name">CSS3</span>
          <div class="skill-bar-container"><div class="skill-bar" data-width="92"></div></div>
        </div>

        <div class="skill-card" id="skill-js">
          <div class="skill-icon js">
            <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor"><path d="M0 0h24v24H0V0zm22.034 18.276c-.175-1.095-.888-2.015-3.003-2.873-.736-.345-1.554-.585-1.797-1.14-.091-.33-.105-.51-.046-.705.15-.646.915-.84 1.515-.66.39.12.75.42.976.9 1.034-.676 1.034-.676 1.755-1.125-.27-.42-.405-.6-.586-.78-.63-.705-1.469-1.065-2.834-1.034l-.705.089c-.676.165-1.32.525-1.71 1.005-1.14 1.291-.811 3.541.569 4.471 1.365 1.02 3.361 1.244 3.616 2.205.24 1.17-.87 1.545-1.966 1.41-.811-.18-1.26-.586-1.755-1.336l-1.83 1.051c.21.48.45.689.81 1.109 1.74 1.756 6.09 1.666 6.871-1.004.029-.09.24-.705.074-1.65l.046.067zm-8.983-7.245h-2.248c0 1.938-.009 3.864-.009 5.805 0 1.232.063 2.363-.138 2.711-.33.689-1.18.601-1.566.48-.396-.196-.597-.466-.83-.855-.063-.105-.11-.196-.127-.196l-1.825 1.125c.305.63.75 1.172 1.324 1.517.855.51 2.004.675 3.207.405.783-.226 1.458-.691 1.811-1.411.51-.93.402-2.07.397-3.346.012-2.054 0-4.109 0-6.179l.004-.056z"/></svg>
          </div>
          <span class="skill-name">JavaScript</span>
          <div class="skill-bar-container"><div class="skill-bar" data-width="88"></div></div>
        </div>

        <div class="skill-card" id="skill-php">
          <div class="skill-icon php">
            <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor"><path d="M7.01 10.207h-.944l-.515 2.648h.838c.556 0 .97-.105 1.242-.314.272-.21.455-.559.55-1.049.092-.47.05-.802-.124-.995-.175-.193-.523-.29-1.047-.29zM12 5.688C5.373 5.688 0 8.514 0 12s5.373 6.313 12 6.313S24 15.486 24 12c0-3.486-5.373-6.312-12-6.312zm-3.26 7.451c-.261.25-.575.438-.917.551-.336.108-.765.164-1.285.164H5.357l-.327 1.681H3.652l1.23-6.326h2.65c.797 0 1.378.209 1.744.628.366.418.476 1.002.33 1.752a2.836 2.836 0 01-.349.89 2.77 2.77 0 01-.607.66zm5.044-2.108c-.069.265-.158.559-.243.769H10.91l-.072.371h2.075l-.216 1.108H10.62l-.26 1.336H9.01l1.23-6.326h4.12l-.215 1.108h-2.742l-.199 1.025h2.632l-.002.609zm5.765 1.86c-.073.222-.21.46-.397.707-.187.248-.407.442-.66.583-.303.168-.67.291-1.102.369a7.476 7.476 0 01-1.348.115h-.674l-.327 1.681h-1.378l1.23-6.326h2.649c.797 0 1.378.209 1.744.628.366.418.476 1.002.33 1.752a2.907 2.907 0 01-.067.491zm-1.588-.488c.092-.47.05-.802-.124-.995-.175-.193-.523-.29-1.047-.29h-.944l-.515 2.648h.838c.556 0 .97-.105 1.242-.314.272-.21.455-.559.55-1.049z"/></svg>
          </div>
          <span class="skill-name">PHP</span>
          <div class="skill-bar-container"><div class="skill-bar" data-width="85"></div></div>
        </div>

        <div class="skill-card" id="skill-mysql">
          <div class="skill-icon mysql">
            <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor"><path d="M16.405 5.501c-.115 0-.193.014-.274.033v.013h.014c.054.104.146.18.214.273.054.107.1.214.154.32l.014-.015c.094-.066.14-.172.14-.333-.04-.047-.046-.094-.08-.14-.04-.067-.126-.1-.18-.153zM5.77 18.695h-.927a50.854 50.854 0 00-.27-4.41h-.008l-1.41 4.41H2.45l-1.4-4.41h-.01c-.087 1.465-.143 2.945-.169 4.41H0c.055-1.966.162-3.93.322-5.89h1.24l1.29 3.96h.008l1.307-3.96h1.174c.178 2.09.282 4.02.35 5.89zM8.92 15.27c0 .467 0 .802-.01 1.006-.007.201-.025.356-.054.463-.035.118-.093.202-.174.254-.08.053-.2.08-.353.08-.12 0-.225-.022-.313-.066a.672.672 0 01-.218-.2l-.298.347c.116.12.266.212.45.276.185.064.384.096.596.096.328 0 .577-.094.748-.282.17-.187.256-.473.256-.857V13.34h-.63v1.93zm2.27 3.526c-.327 0-.592-.082-.794-.248a.862.862 0 01-.322-.671c0-.342.125-.614.374-.814.25-.2.618-.302 1.106-.302.18 0 .37.016.566.048v-.166c0-.455-.228-.682-.684-.682-.19 0-.39.033-.598.098a2.42 2.42 0 00-.485.2l-.17-.496c.18-.102.396-.19.65-.264.252-.074.502-.11.748-.11.378 0 .654.096.83.29.176.193.264.483.264.867v2.75h-.51l-.038-.378h-.014c-.135.12-.29.22-.463.296-.174.076-.364.114-.572.114l.012-.332zm.24-.504c.162 0 .308-.035.44-.104a1.1 1.1 0 00.35-.294v-.788a2.628 2.628 0 00-.45-.042c-.276 0-.48.054-.61.16-.13.11-.196.26-.196.454 0 .164.054.29.162.378.108.088.26.132.454.132l-.15.104zm3.352.536c-.246 0-.47-.046-.672-.14a1.525 1.525 0 01-.52-.388 1.76 1.76 0 01-.338-.58 2.12 2.12 0 01-.12-.728c0-.274.042-.528.126-.762.083-.234.2-.437.35-.607.148-.17.325-.302.53-.398.203-.096.426-.144.668-.144.246 0 .47.046.672.14.2.092.377.224.52.394.146.17.26.374.338.61.08.237.12.494.12.772 0 .266-.042.514-.126.742-.083.23-.2.428-.35.596-.148.168-.325.3-.53.394a1.58 1.58 0 01-.668.144v-.045zm.033-.52c.272 0 .48-.114.624-.342.144-.228.216-.534.216-.918 0-.39-.072-.702-.216-.936-.144-.234-.352-.35-.624-.35-.278 0-.49.116-.636.35-.147.234-.22.546-.22.936 0 .384.073.69.22.918.145.228.358.342.636.342zm2.312.484V13.34h.628v1.782h.014a1 1 0 01.364-.338.92.92 0 01.496-.138c.296 0 .528.088.694.262.166.174.25.452.25.834v2.753h-.626v-2.58c0-.536-.218-.804-.654-.804-.16 0-.312.06-.458.176a.942.942 0 00-.31.46v2.748h-.628l.23-.155zM2.073 11.157h1.624c.62 0 1.073.17 1.36.512.286.342.394.796.322 1.36-.04.296-.13.573-.268.828-.14.256-.326.47-.558.646-.233.174-.506.31-.822.408-.315.096-.67.144-1.063.144H1.548l.525-3.898zm.535 3.392h.38c.554 0 1.01-.164 1.366-.492.354-.328.574-.786.66-1.372.052-.386.01-.696-.124-.928-.135-.232-.442-.348-.92-.348h-.464l-.898 3.14zM8.238 11.07c.303 0 .564.067.783.2.22.135.39.32.512.558.12.238.168.515.142.832-.074.554-.303 1.006-.686 1.358-.384.352-.844.528-1.38.528-.295 0-.55-.065-.768-.198a1.294 1.294 0 01-.506-.556c-.118-.236-.164-.51-.14-.824.068-.512.282-.948.642-1.306.36-.358.83-.537 1.41-.537l-.009-.055zm-.059.516c-.24 0-.46.078-.66.232-.198.156-.34.378-.424.67-.088.3-.08.55.024.75.104.2.31.3.62.3.244 0 .466-.078.666-.234.2-.156.34-.382.42-.678.082-.292.073-.54-.028-.744-.1-.202-.314-.304-.642-.304l.024.008z"/></svg>
          </div>
          <span class="skill-name">MySQL</span>
          <div class="skill-bar-container"><div class="skill-bar" data-width="82"></div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== CONTACT SECTION ===== -->
  <section id="contact">
    <div class="container">
      <div class="section-header reveal">
        <span class="section-label" data-en="Get in Touch" data-tr="İletişim" data-ar="تواصل معنا">İletişim</span>
        <h2 class="section-title" data-en="Let's Work Together" data-tr="Birlikte Çalışalım" data-ar="دعنا نعمل معاً">Birlikte Çalışalım</h2>
        <p class="section-subtitle" data-en="Have a project in mind? We'd love to hear about it." data-tr="Aklınızda bir proje mi var? Duymak isteriz." data-ar="هل لديك مشروع في ذهنك؟ نود أن نسمع عنه.">Aklınızda bir proje mi var? Duymak isteriz.</p>
      </div>

      <div class="contact-grid">
        <div class="contact-info reveal">
          <h3 class="contact-info-title" data-en="Let's create something amazing together." data-tr="Birlikte harika bir şey yaratalım." data-ar="دعنا نصنع شيئاً رائعاً معاً.">Birlikte harika bir şey yaratalım.</h3>
          <p class="contact-info-subtitle" data-en="Whether you need a complete e-commerce platform, a business automation system, or a sleek digital menu — we're here to bring your vision to life." data-tr="İster eksiksiz bir e-ticaret platformu, ister bir iş otomasyon sistemi, isterse şık bir dijital menü olsun — vizyonunuzu hayata geçirmek için buradayız." data-ar="سواء كنت بحاجة إلى منصة تجارة إلكترونية متكاملة، نظام أتمتة أعمال، أو قائمة رقمية أنيقة — نحن هنا لتحويل رؤيتك إلى واقع.">İster eksiksiz bir e-ticaret platformu, ister bir iş otomasyon sistemi, isterse şık bir dijital menü olsun — vizyonunuzu hayata geçirmek için buradayız.</p>

          <div class="contact-methods">
            <div class="contact-method">
              <div class="contact-method-icon email">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
              </div>
              <div class="contact-method-text">
                <span class="contact-method-label">Email</span>
                <span class="contact-method-value">pixelnovatr.com</span>
              </div>
            </div>

            <a href="https://wa.me/905340226594" target="_blank" class="contact-method" id="whatsapp-link">
              <div class="contact-method-icon whatsapp">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              </div>
              <div class="contact-method-text">
                <span class="contact-method-label">WhatsApp</span>
                <span class="contact-method-value" data-en="Quick Contact" data-tr="Hızlı İletişim" data-ar="تواصل سريع">Hızlı İletişim</span>
              </div>
            </a>

            <a href="tel:05340226594" class="contact-method" id="phone-link">
              <div class="contact-method-icon phone">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              </div>
              <div class="contact-method-text">
                <span class="contact-method-label" data-en="Phone" data-tr="Telefon" data-ar="الهاتف">Telefon</span>
                <span class="contact-method-value">05340226594</span>
              </div>
            </a>

            <a href="https://www.instagram.com/pixelnovatr/" target="_blank" class="contact-method" id="instagram-link">
              <div class="contact-method-icon instagram">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
              </div>
              <div class="contact-method-text">
                <span class="contact-method-label">Instagram</span>
                <span class="contact-method-value" data-en="Follow Us" data-tr="Takip Et" data-ar="تابعنا">Takip Et</span>
              </div>
            </a>

            <div class="contact-method">
              <div class="contact-method-icon location">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
              </div>
              <div class="contact-method-text">
                <span class="contact-method-label" data-en="Location" data-tr="Konum" data-ar="الموقع">Konum</span>
                <span class="contact-method-value" data-en="Turkey" data-tr="Türkiye" data-ar="تركيا">Türkiye</span>
              </div>
            </div>
          </div>
        </div>

        <div class="contact-form-wrapper reveal">
          <form class="contact-form" id="contact-form">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="form-name" data-en="Name" data-tr="İsim" data-ar="الاسم">İsim</label>
                <input class="form-input" type="text" id="form-name" name="name" placeholder="" data-placeholder-en="Your name" data-placeholder-tr="Adınız" data-placeholder-ar="اسمك" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="form-email" data-en="Email" data-tr="E-posta" data-ar="البريد الإلكتروني">E-posta</label>
                <input class="form-input" type="email" id="form-email" name="email" placeholder="" data-placeholder-en="Your email" data-placeholder-tr="E-posta adresiniz" data-placeholder-ar="بريدك الإلكتروني" required>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="form-message" data-en="Message" data-tr="Mesaj" data-ar="الرسالة">Mesaj</label>
              <textarea class="form-textarea" id="form-message" name="message" placeholder="" data-placeholder-en="Tell us about your project..." data-placeholder-tr="Projeniz hakkında bilgi verin..." data-placeholder-ar="أخبرنا عن مشروعك..." required></textarea>
            </div>
            <button type="submit" class="btn-submit" id="btn-send">
              <span data-en="Send Message" data-tr="Mesaj Gönder" data-ar="إرسال الرسالة">Mesaj Gönder</span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== FOOTER ===== -->
  <footer id="footer">
    <div class="container">
      <div class="footer-content">
        <p class="footer-text">
          © 2026 PixelNova. <span data-en="Made with" data-tr="Sevgiyle yapıldı" data-ar="صُنع بحب">Sevgiyle yapıldı</span> <span class="heart">♥</span>
        </p>
        <div class="footer-right">
          <div class="footer-links">
          <a href="#hero" class="footer-link" data-en="Home" data-tr="Ana Sayfa" data-ar="الرئيسية">Ana Sayfa</a>
          <a href="#projects" class="footer-link" data-en="Projects" data-tr="Projeler" data-ar="المشاريع">Projeler</a>
          <a href="#contact" class="footer-link" data-en="Contact" data-tr="İletişim" data-ar="اتصل بنا">İletişim</a>
        </div>
        <div class="footer-socials">
          <a href="tel:05340226594" class="footer-social-link phone" aria-label="Phone" title="05340226594">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          </a>
          <a href="https://www.instagram.com/pixelnovatr/" target="_blank" class="footer-social-link instagram" aria-label="Instagram">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          </a>
        </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- WhatsApp Floating Button -->
  <a href="https://wa.me/905000000000" target="_blank" class="whatsapp-float" id="whatsapp-float" aria-label="WhatsApp Contact">
    <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
  </a>

  <!-- Main Script -->
  <script src="script.js"></script>
</body>
</html>
