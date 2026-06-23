/* ===================================================================
   PixelNova — Agency Portfolio  |  script.js
   Three.js 3D Scenes · GSAP Scroll Animations · Language Toggle
   =================================================================== */

(function () {
  'use strict';

  // ─── STATE ──────────────────────────────────────────────
  let currentLang = 'tr';
  let mouse = { x: 0, y: 0, nx: 0, ny: 0 };

  // ─── DOM CACHE ──────────────────────────────────────────
  const $ = (s, p = document) => p.querySelector(s);
  const $$ = (s, p = document) => [...p.querySelectorAll(s)];

  // ─── LOADING SCREEN ─────────────────────────────────────
  window.addEventListener('load', () => {
    setTimeout(() => {
      const loader = $('#loading-screen');
      if (loader) loader.classList.add('hidden');
    }, 1200);
  });

  // ─── CURSOR GLOW ───────────────────────────────────────
  const cursorGlow = $('#cursor-glow');
  document.addEventListener('mousemove', (e) => {
    mouse.x = e.clientX;
    mouse.y = e.clientY;
    mouse.nx = (e.clientX / window.innerWidth) * 2 - 1;
    mouse.ny = -(e.clientY / window.innerHeight) * 2 + 1;
    if (cursorGlow) {
      cursorGlow.style.left = e.clientX + 'px';
      cursorGlow.style.top = e.clientY + 'px';
    }
  });

  // ─── NAVBAR ─────────────────────────────────────────────
  const navbar = $('#navbar');
  const navToggle = $('#nav-toggle');
  const navLinks = $('#nav-links');
  const navOverlay = $('#nav-overlay');

  window.addEventListener('scroll', () => {
    if (navbar) {
      navbar.classList.toggle('scrolled', window.scrollY > 60);
    }
  });

  if (navToggle) {
    navToggle.addEventListener('click', () => {
      navToggle.classList.toggle('active');
      navLinks.classList.toggle('open');
      navOverlay.classList.toggle('active');
    });
  }

  // Close mobile menu on link click
  $$('.nav-link').forEach((link) => {
    link.addEventListener('click', () => {
      navToggle.classList.remove('active');
      navLinks.classList.remove('open');
      navOverlay.classList.remove('active');
    });
  });

  if (navOverlay) {
    navOverlay.addEventListener('click', () => {
      navToggle.classList.remove('active');
      navLinks.classList.remove('open');
      navOverlay.classList.remove('active');
    });
  }

  // ─── LANGUAGE TOGGLE ───────────────────────────────────
  const langToggle = $('#lang-toggle');
  const langLabel = $('#lang-label');
  const languages = ['tr', 'en', 'ar'];

  function setLanguage(lang) {
    currentLang = lang;
    
    if (langLabel) {
      if (lang === 'tr') langLabel.textContent = 'EN';
      else if (lang === 'en') langLabel.textContent = 'AR';
      else if (lang === 'ar') langLabel.textContent = 'TR';
    }

    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.lang = lang;

    $$('[data-en][data-tr]').forEach((el) => {
      if (el.hasAttribute(`data-${lang}`)) {
        el.textContent = el.getAttribute(`data-${lang}`);
      }
    });

    // Update placeholders
    $$('[data-placeholder-en][data-placeholder-tr]').forEach((el) => {
      if (el.hasAttribute(`data-placeholder-${lang}`)) {
        el.placeholder = el.getAttribute(`data-placeholder-${lang}`);
      }
    });
  }

  if (langToggle) {
    langToggle.addEventListener('click', () => {
      const currentIndex = languages.indexOf(currentLang);
      const newLang = languages[(currentIndex + 1) % languages.length];
      setLanguage(newLang);
    });
  }

  // Initialize language
  setLanguage('tr');

  // ─── THREE.JS — HERO 3D SCENE ──────────────────────────
  function initHero3D() {
    const canvas = $('#hero-canvas');
    if (!canvas) return;

    const container = canvas.parentElement;
    let w = container.clientWidth;
    let h = container.clientHeight;

    // Renderer
    const renderer = new THREE.WebGLRenderer({
      canvas,
      alpha: true,
      antialias: true,
    });
    renderer.setSize(w, h);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    // Scene & Camera
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, w / h, 0.1, 1000);
    camera.position.z = 6;

    // ── Lights
    const ambientLight = new THREE.AmbientLight(0x404060, 0.6);
    scene.add(ambientLight);

    const pointLight1 = new THREE.PointLight(0x00f0ff, 2, 20);
    pointLight1.position.set(3, 3, 5);
    scene.add(pointLight1);

    const pointLight2 = new THREE.PointLight(0xa855f7, 1.5, 20);
    pointLight2.position.set(-3, -2, 4);
    scene.add(pointLight2);

    const pointLight3 = new THREE.PointLight(0xec4899, 1, 15);
    pointLight3.position.set(0, 4, 3);
    scene.add(pointLight3);

    // ── Create main geometry group
    const mainGroup = new THREE.Group();
    scene.add(mainGroup);

    // ── Central Icosahedron (wireframe)
    const icoGeo = new THREE.IcosahedronGeometry(1.6, 1);
    const icoMat = new THREE.MeshPhongMaterial({
      color: 0x00f0ff,
      wireframe: true,
      transparent: true,
      opacity: 0.35,
      emissive: 0x00f0ff,
      emissiveIntensity: 0.15,
    });
    const ico = new THREE.Mesh(icoGeo, icoMat);
    mainGroup.add(ico);

    // ── Inner solid sphere
    const innerGeo = new THREE.IcosahedronGeometry(0.8, 2);
    const innerMat = new THREE.MeshPhongMaterial({
      color: 0xa855f7,
      transparent: true,
      opacity: 0.2,
      emissive: 0xa855f7,
      emissiveIntensity: 0.3,
      shininess: 100,
    });
    const innerSphere = new THREE.Mesh(innerGeo, innerMat);
    mainGroup.add(innerSphere);

    // ── Outer wireframe ring (torus)
    const torusGeo = new THREE.TorusGeometry(2.6, 0.02, 16, 100);
    const torusMat = new THREE.MeshPhongMaterial({
      color: 0x00f0ff,
      transparent: true,
      opacity: 0.4,
      emissive: 0x00f0ff,
      emissiveIntensity: 0.5,
    });
    const torus1 = new THREE.Mesh(torusGeo, torusMat);
    torus1.rotation.x = Math.PI * 0.5;
    mainGroup.add(torus1);

    const torus2 = torus1.clone();
    torus2.rotation.x = Math.PI * 0.3;
    torus2.rotation.y = Math.PI * 0.4;
    torus2.material = torusMat.clone();
    torus2.material.color.set(0xa855f7);
    torus2.material.emissive.set(0xa855f7);
    torus2.material.opacity = 0.3;
    mainGroup.add(torus2);

    const torus3 = torus1.clone();
    torus3.rotation.x = Math.PI * 0.7;
    torus3.rotation.z = Math.PI * 0.5;
    torus3.material = torusMat.clone();
    torus3.material.color.set(0xec4899);
    torus3.material.emissive.set(0xec4899);
    torus3.material.opacity = 0.25;
    mainGroup.add(torus3);

    // ── Floating particles
    const particleCount = 200;
    const particleGeo = new THREE.BufferGeometry();
    const positions = new Float32Array(particleCount * 3);
    const colors = new Float32Array(particleCount * 3);
    const particleColors = [
      [0, 0.94, 1],      // cyan
      [0.66, 0.33, 0.97], // purple
      [0.93, 0.29, 0.6],  // pink
    ];

    for (let i = 0; i < particleCount; i++) {
      const r = 2.5 + Math.random() * 3;
      const theta = Math.random() * Math.PI * 2;
      const phi = Math.random() * Math.PI;

      positions[i * 3] = r * Math.sin(phi) * Math.cos(theta);
      positions[i * 3 + 1] = r * Math.sin(phi) * Math.sin(theta);
      positions[i * 3 + 2] = r * Math.cos(phi);

      const c = particleColors[Math.floor(Math.random() * particleColors.length)];
      colors[i * 3] = c[0];
      colors[i * 3 + 1] = c[1];
      colors[i * 3 + 2] = c[2];
    }

    particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    particleGeo.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    const particleMat = new THREE.PointsMaterial({
      size: 0.03,
      vertexColors: true,
      transparent: true,
      opacity: 0.7,
      blending: THREE.AdditiveBlending,
      depthWrite: false,
    });
    const particles = new THREE.Points(particleGeo, particleMat);
    mainGroup.add(particles);

    // ── Small floating octahedrons
    const smallShapes = [];
    for (let i = 0; i < 6; i++) {
      const geo = new THREE.OctahedronGeometry(0.15, 0);
      const mat = new THREE.MeshPhongMaterial({
        color: [0x00f0ff, 0xa855f7, 0xec4899][i % 3],
        transparent: true,
        opacity: 0.5,
        emissive: [0x00f0ff, 0xa855f7, 0xec4899][i % 3],
        emissiveIntensity: 0.4,
      });
      const mesh = new THREE.Mesh(geo, mat);
      const angle = (i / 6) * Math.PI * 2;
      const radius = 2.8 + Math.random() * 0.5;
      mesh.position.set(
        Math.cos(angle) * radius,
        (Math.random() - 0.5) * 2,
        Math.sin(angle) * radius
      );
      mesh.userData = {
        angle,
        radius,
        speed: 0.3 + Math.random() * 0.4,
        floatOffset: Math.random() * Math.PI * 2,
      };
      mainGroup.add(mesh);
      smallShapes.push(mesh);
    }

    // ── Animation Loop
    const clock = new THREE.Clock();
    let targetRotX = 0;
    let targetRotY = 0;

    function animate() {
      requestAnimationFrame(animate);
      const t = clock.getElapsedTime();

      // Mouse interaction — smooth follow
      targetRotY = mouse.nx * 0.4;
      targetRotX = -mouse.ny * 0.3;
      mainGroup.rotation.y += (targetRotY - mainGroup.rotation.y) * 0.04;
      mainGroup.rotation.x += (targetRotX - mainGroup.rotation.x) * 0.04;

      // Auto-rotation
      ico.rotation.y += 0.003;
      ico.rotation.x += 0.001;
      innerSphere.rotation.y -= 0.005;
      innerSphere.rotation.x -= 0.002;

      torus1.rotation.z += 0.004;
      torus2.rotation.z -= 0.003;
      torus3.rotation.z += 0.002;

      // Particles rotation
      particles.rotation.y += 0.001;

      // Floating shapes
      smallShapes.forEach((s) => {
        const d = s.userData;
        s.position.y += Math.sin(t * d.speed + d.floatOffset) * 0.003;
        s.rotation.x += 0.01;
        s.rotation.z += 0.008;
      });

      // Pulsing light
      pointLight1.intensity = 2 + Math.sin(t * 1.5) * 0.5;
      pointLight2.intensity = 1.5 + Math.sin(t * 1.2 + 1) * 0.4;

      renderer.render(scene, camera);
    }

    animate();

    // ── Resize
    function onResize() {
      w = container.clientWidth;
      h = container.clientHeight;
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
      renderer.setSize(w, h);
    }

    window.addEventListener('resize', onResize);
  }

  // ─── THREE.JS — SKILLS 3D SCENE ────────────────────────
  function initSkills3D() {
    const canvas = $('#skills-canvas');
    if (!canvas) return;

    const container = canvas.parentElement;
    let w = container.clientWidth;
    let h = container.clientHeight;

    const renderer = new THREE.WebGLRenderer({
      canvas,
      alpha: true,
      antialias: true,
    });
    renderer.setSize(w, h);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(50, w / h, 0.1, 1000);
    camera.position.z = 8;

    // Lights
    const ambient = new THREE.AmbientLight(0x404060, 0.8);
    scene.add(ambient);

    const light1 = new THREE.PointLight(0x00f0ff, 2, 30);
    light1.position.set(5, 5, 5);
    scene.add(light1);

    const light2 = new THREE.PointLight(0xa855f7, 1.5, 30);
    light2.position.set(-5, -3, 4);
    scene.add(light2);

    const skillGroup = new THREE.Group();
    scene.add(skillGroup);

    // Tech data
    const techItems = [
      { name: 'HTML', color: 0xe34f26, geo: 'box' },
      { name: 'CSS', color: 0x1572b6, geo: 'ico' },
      { name: 'JS', color: 0xf0db4f, geo: 'dodeca' },
      { name: 'PHP', color: 0x777bb4, geo: 'octa' },
      { name: 'MySQL', color: 0x00758f, geo: 'tetra' },
    ];

    const meshes = [];
    const orbitRadius = 3;

    techItems.forEach((item, i) => {
      let geometry;
      switch (item.geo) {
        case 'box': geometry = new THREE.BoxGeometry(0.8, 0.8, 0.8); break;
        case 'ico': geometry = new THREE.IcosahedronGeometry(0.5, 0); break;
        case 'dodeca': geometry = new THREE.DodecahedronGeometry(0.5, 0); break;
        case 'octa': geometry = new THREE.OctahedronGeometry(0.55, 0); break;
        case 'tetra': geometry = new THREE.TetrahedronGeometry(0.6, 0); break;
        default: geometry = new THREE.SphereGeometry(0.5, 16, 16);
      }

      const material = new THREE.MeshPhongMaterial({
        color: item.color,
        transparent: true,
        opacity: 0.75,
        emissive: item.color,
        emissiveIntensity: 0.3,
        shininess: 80,
        flatShading: true,
      });

      const mesh = new THREE.Mesh(geometry, material);
      const angle = (i / techItems.length) * Math.PI * 2;
      mesh.position.set(
        Math.cos(angle) * orbitRadius,
        0,
        Math.sin(angle) * orbitRadius
      );
      mesh.userData = {
        angle,
        baseAngle: angle,
        speed: 0.15 + i * 0.03,
        floatPhase: Math.random() * Math.PI * 2,
      };
      skillGroup.add(mesh);
      meshes.push(mesh);

      // Wireframe overlay
      const wireMat = new THREE.MeshPhongMaterial({
        color: item.color,
        wireframe: true,
        transparent: true,
        opacity: 0.15,
      });
      const wireMesh = new THREE.Mesh(geometry.clone(), wireMat);
      wireMesh.scale.set(1.15, 1.15, 1.15);
      mesh.add(wireMesh);
    });

    // Central connector ring
    const ringGeo = new THREE.TorusGeometry(orbitRadius, 0.015, 8, 80);
    const ringMat = new THREE.MeshPhongMaterial({
      color: 0x00f0ff,
      transparent: true,
      opacity: 0.2,
      emissive: 0x00f0ff,
      emissiveIntensity: 0.4,
    });
    const ring = new THREE.Mesh(ringGeo, ringMat);
    ring.rotation.x = Math.PI * 0.5;
    skillGroup.add(ring);

    // Second ring
    const ring2 = ring.clone();
    ring2.material = ringMat.clone();
    ring2.material.color.set(0xa855f7);
    ring2.material.emissive.set(0xa855f7);
    ring2.material.opacity = 0.12;
    ring2.rotation.x = Math.PI * 0.35;
    ring2.rotation.z = Math.PI * 0.2;
    ring2.scale.set(1.2, 1.2, 1.2);
    skillGroup.add(ring2);

    // Particles
    const pCount = 100;
    const pGeo = new THREE.BufferGeometry();
    const pPos = new Float32Array(pCount * 3);
    for (let i = 0; i < pCount; i++) {
      const r = 1.5 + Math.random() * 4;
      const th = Math.random() * Math.PI * 2;
      const ph = Math.random() * Math.PI;
      pPos[i * 3] = r * Math.sin(ph) * Math.cos(th);
      pPos[i * 3 + 1] = r * Math.sin(ph) * Math.sin(th);
      pPos[i * 3 + 2] = r * Math.cos(ph);
    }
    pGeo.setAttribute('position', new THREE.BufferAttribute(pPos, 3));
    const pMat = new THREE.PointsMaterial({
      size: 0.025,
      color: 0x00f0ff,
      transparent: true,
      opacity: 0.4,
      blending: THREE.AdditiveBlending,
      depthWrite: false,
    });
    const pPoints = new THREE.Points(pGeo, pMat);
    skillGroup.add(pPoints);

    const clock = new THREE.Clock();

    function animate() {
      requestAnimationFrame(animate);
      const t = clock.getElapsedTime();

      // Orbit tech items
      meshes.forEach((m) => {
        const d = m.userData;
        const a = d.baseAngle + t * d.speed;
        m.position.x = Math.cos(a) * orbitRadius;
        m.position.z = Math.sin(a) * orbitRadius;
        m.position.y = Math.sin(t * 0.8 + d.floatPhase) * 0.5;
        m.rotation.x += 0.012;
        m.rotation.y += 0.018;
      });

      // Mouse interaction
      skillGroup.rotation.y += (mouse.nx * 0.3 - skillGroup.rotation.y) * 0.03;
      skillGroup.rotation.x += (-mouse.ny * 0.15 - skillGroup.rotation.x) * 0.03;

      ring.rotation.z += 0.002;
      ring2.rotation.z -= 0.003;
      pPoints.rotation.y += 0.0008;

      renderer.render(scene, camera);
    }

    animate();

    function onResize() {
      w = container.clientWidth;
      h = container.clientHeight;
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
      renderer.setSize(w, h);
    }

    window.addEventListener('resize', onResize);
  }

  // ─── GSAP SCROLL ANIMATIONS ─────────────────────────────
  function initGSAP() {
    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

    gsap.registerPlugin(ScrollTrigger);

    // Reveal animations
    $$('.reveal').forEach((el) => {
      gsap.fromTo(
        el,
        { opacity: 0, y: 50 },
        {
          opacity: 1,
          y: 0,
          duration: 0.9,
          ease: 'power3.out',
          scrollTrigger: {
            trigger: el,
            start: 'top 85%',
            end: 'bottom 20%',
            toggleActions: 'play none none none',
          },
        }
      );
    });

    // Hero content entrance
    const heroContent = $('.hero-content');
    if (heroContent) {
      const tl = gsap.timeline({ delay: 1.4 });
      tl.fromTo('.hero-badge', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' })
        .fromTo('.hero-title', { opacity: 0, y: 30 }, { opacity: 1, y: 0, duration: 0.7, ease: 'power3.out' }, '-=0.3')
        .fromTo('.hero-subtitle', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' }, '-=0.3')
        .fromTo('.hero-cta-group', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.5, ease: 'power3.out' }, '-=0.2')
        .fromTo('.hero-stats', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.5, ease: 'power3.out' }, '-=0.2');
    }

    // Project cards stagger
    gsap.fromTo(
      '.project-card',
      { opacity: 0, y: 60 },
      {
        opacity: 1,
        y: 0,
        duration: 0.7,
        stagger: 0.15,
        ease: 'power3.out',
        scrollTrigger: {
          trigger: '.projects-grid',
          start: 'top 80%',
        },
      }
    );

    // Skill cards stagger
    gsap.fromTo(
      '.skill-card',
      { opacity: 0, y: 40, scale: 0.9 },
      {
        opacity: 1,
        y: 0,
        scale: 1,
        duration: 0.5,
        stagger: 0.1,
        ease: 'back.out(1.4)',
        scrollTrigger: {
          trigger: '.skills-grid',
          start: 'top 80%',
        },
      }
    );

    // Skill bars animation
    $$('.skill-bar').forEach((bar) => {
      const width = bar.dataset.width;
      ScrollTrigger.create({
        trigger: bar,
        start: 'top 90%',
        onEnter: () => {
          bar.style.width = width + '%';
        },
      });
    });

    // Navbar links — active state on scroll
    const sections = ['hero', 'projects', 'skills', 'contact'];
    sections.forEach((id) => {
      const section = $(`#${id}`);
      if (section) {
        ScrollTrigger.create({
          trigger: section,
          start: 'top center',
          end: 'bottom center',
          onToggle: (self) => {
            if (self.isActive) {
              $$('.nav-link').forEach((l) => l.style.color = '');
              const activeLink = $(`.nav-link[href="#${id}"]`);
              if (activeLink) activeLink.style.color = '#00f0ff';
            }
          },
        });
      }
    });
  }

  // ─── CONTACT FORM (AJAX + WhatsApp) ─────────────────────
  const contactForm = $('#contact-form');
  if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const name = $('#form-name').value.trim();
      const email = $('#form-email').value.trim();
      const message = $('#form-message').value.trim();

      if (!name || !email || !message) return;

      const btn = $('#btn-send');
      const originalText = btn.querySelector('span').textContent;

      // Disable button while submitting
      btn.disabled = true;
      btn.querySelector('span').textContent = currentLang === 'tr' ? 'Gönderiliyor...' : 'Sending...';

      // Submit to backend via AJAX
      const formData = new FormData();
      formData.append('name', name);
      formData.append('email', email);
      formData.append('message', message);

      fetch('submit_message.php', {
        method: 'POST',
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            // Success feedback
            btn.querySelector('span').textContent = currentLang === 'tr' ? 'Gönderildi ✓' : 'Sent ✓';
            btn.style.background = 'linear-gradient(135deg, #22d3ee, #10b981)';

            // Also open WhatsApp for instant contact
            const text = encodeURIComponent(
              `Merhaba PixelNova! Ben ${name}.\n\n${message}\n\nE-posta: ${email}`
            );
            window.open(`https://wa.me/905000000000?text=${text}`, '_blank');

            setTimeout(() => {
              btn.querySelector('span').textContent = originalText;
              btn.style.background = '';
              btn.disabled = false;
              contactForm.reset();
            }, 3000);
          } else {
            // Error feedback
            btn.querySelector('span').textContent = currentLang === 'tr' ? 'Hata! Tekrar deneyin' : 'Error! Try again';
            btn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
            setTimeout(() => {
              btn.querySelector('span').textContent = originalText;
              btn.style.background = '';
              btn.disabled = false;
            }, 3000);
          }
        })
        .catch(() => {
          // Network error — still send via WhatsApp as fallback
          const text = encodeURIComponent(
            `Merhaba PixelNova! Ben ${name}.\n\n${message}\n\nE-posta: ${email}`
          );
          window.open(`https://wa.me/905000000000?text=${text}`, '_blank');

          btn.querySelector('span').textContent = currentLang === 'tr' ? 'WhatsApp ile gönderildi' : 'Sent via WhatsApp';
          btn.style.background = 'linear-gradient(135deg, #25d366, #128c7e)';
          setTimeout(() => {
            btn.querySelector('span').textContent = originalText;
            btn.style.background = '';
            btn.disabled = false;
            contactForm.reset();
          }, 3000);
        });
    });
  }

  // ─── SMOOTH SCROLL FOR ANCHOR LINKS ────────────────────
  $$('a[href^="#"]').forEach((link) => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const target = $(link.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // ─── INIT ──────────────────────────────────────────────
  initHero3D();
  initSkills3D();

  // Delay GSAP init to ensure DOM is painted
  setTimeout(initGSAP, 100);

})();
