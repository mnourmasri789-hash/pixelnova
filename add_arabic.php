<?php
// Script to inject Arabic translations into index.php
$file = __DIR__ . '/index.php';
$content = file_get_contents($file);

$dictionary = [
    // UI Elements
    "Loading..." => "جاري التحميل...",
    "Home" => "الرئيسية",
    "Projects" => "المشاريع",
    "Skills" => "المهارات",
    "Contact" => "اتصل بنا",
    "Open to new projects" => "متاحون لمشاريع جديدة",
    "Hi, We are" => "مرحباً، نحن",
    "Web Development Agency" => "وكالة تطوير ويب",
    "We build smart web solutions, from E-commerce to Business Automation." => "نحن نبني حلول ويب ذكية، من التجارة الإلكترونية إلى أتمتة الأعمال.",
    "View Projects" => "عرض المشاريع",
    "Get in Touch" => "تواصل معنا",
    "Project" => "مشروع",
    "Projects" => "المشاريع",
    "Technologies" => "تقنية",
    "Happy Clients" => "عملاء سعداء",
    "Portfolio" => "محفظة الأعمال",
    "Featured Projects" => "مشاريع مميزة",
    "A selection of projects we've built, from e-commerce to business automation systems." => "مجموعة مختارة من المشاريع التي قمنا ببنائها، من أنظمة التجارة الإلكترونية إلى أنظمة أتمتة الأعمال.",
    "Visit Website" => "زيارة الموقع",
    "Tech Stack" => "حزمة التقنيات",
    "Skills & Technologies" => "المهارات والتقنيات",
    "The core technologies we use to build modern, scalable web applications." => "التقنيات الأساسية التي نستخدمها لبناء تطبيقات ويب حديثة وقابلة للتطوير.",
    "Let's Work Together" => "دعنا نعمل معاً",
    "Have a project in mind? We'd love to hear about it." => "هل لديك مشروع في ذهنك؟ نود أن نسمع عنه.",
    "Let's create something amazing together." => "دعنا نصنع شيئاً رائعاً معاً.",
    "Whether you need a complete e-commerce platform, a business automation system, or a sleek digital menu — we're here to bring your vision to life." => "سواء كنت بحاجة إلى منصة تجارة إلكترونية متكاملة، نظام أتمتة أعمال، أو قائمة رقمية أنيقة — نحن هنا لتحويل رؤيتك إلى واقع.",
    "Quick Contact" => "تواصل سريع",
    "Location" => "الموقع",
    "Turkey" => "تركيا",
    "Name" => "الاسم",
    "Email" => "البريد الإلكتروني",
    "Message" => "الرسالة",
    "Send Message" => "إرسال الرسالة",
    "Made with" => "صُنع بحب",
    "QR Menu" => "قائمة QR",
    "Automation" => "أتمتة",
    "RSVP System" => "نظام دعوات",
    "Digital Invites" => "دعوات رقمية",
    // Placeholders
    "Your name" => "اسمك",
    "Your email" => "بريدك الإلكتروني",
    "Tell us about your project..." => "أخبرنا عن مشروعك..."
];

// Add data-ar to existing translations
$content = preg_replace_callback('/data-en="(.*?)" data-tr="(.*?)"/', function($matches) use ($dictionary) {
    $en = $matches[1];
    $tr = $matches[2];
    $en_clean = html_entity_decode($en, ENT_QUOTES, 'UTF-8');
    $ar = $dictionary[$en_clean] ?? $dictionary[$en] ?? "Translation missing";
    $ar_escaped = htmlspecialchars($ar, ENT_QUOTES, 'UTF-8');
    return "data-en=\"$en\" data-tr=\"$tr\" data-ar=\"$ar_escaped\"";
}, $content);

// Add data-placeholder-ar to existing placeholders
$content = preg_replace_callback('/data-placeholder-en="(.*?)" data-placeholder-tr="(.*?)"/', function($matches) use ($dictionary) {
    $en = $matches[1];
    $tr = $matches[2];
    $en_clean = html_entity_decode($en, ENT_QUOTES, 'UTF-8');
    $ar = $dictionary[$en_clean] ?? $dictionary[$en] ?? "Translation missing";
    $ar_escaped = htmlspecialchars($ar, ENT_QUOTES, 'UTF-8');
    return "data-placeholder-en=\"$en\" data-placeholder-tr=\"$tr\" data-placeholder-ar=\"$ar_escaped\"";
}, $content);

file_put_contents($file, $content);
echo "Injected Arabic translations.";
