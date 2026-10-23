<?php
// Generates clean, crisp, self-contained SVG product images for Fakizon
$imgDir = __DIR__ . '/assets/images/';
if (!file_exists($imgDir)) {
    mkdir($imgDir, 0777, true);
}

$images = [
    'echo-dot.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="100%" height="100%">
  <defs>
    <radialGradient id="dotGrad" cx="50%" cy="40%" r="60%">
      <stop offset="0%" stop-color="#475569" />
      <stop offset="70%" stop-color="#1e293b" />
      <stop offset="100%" stop-color="#0f172a" />
    </radialGradient>
    <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur stdDeviation="8" result="blur" />
      <feComposite in="SourceGraphic" in2="blur" operator="over" />
    </filter>
  </defs>
  <rect width="400" height="400" fill="#f8fafc" rx="16"/>
  <!-- Base Shadow -->
  <ellipse cx="200" cy="330" rx="120" ry="25" fill="#cbd5e1" opacity="0.6"/>
  <!-- Echo Sphere Body -->
  <circle cx="200" cy="200" r="110" fill="url(#dotGrad)"/>
  <!-- Fabric Mesh Texture Details -->
  <circle cx="200" cy="200" r="105" fill="none" stroke="#64748b" stroke-width="2" stroke-dasharray="3,3" opacity="0.4"/>
  <!-- Alexa Glowing Light Ring at base -->
  <ellipse cx="200" cy="290" rx="90" ry="16" fill="none" stroke="#00f7ff" stroke-width="8" filter="url(#glow)"/>
  <ellipse cx="200" cy="290" rx="88" ry="15" fill="none" stroke="#0088ff" stroke-width="4"/>
  <!-- Top Buttons -->
  <circle cx="200" cy="120" r="14" fill="#334155"/>
  <circle cx="170" cy="135" r="11" fill="#334155"/>
  <circle cx="230" cy="135" r="11" fill="#334155"/>
  <!-- Plus / Minus symbols -->
  <text x="170" y="140" font-family="sans-serif" font-weight="bold" font-size="16" fill="#94a3b8" text-anchor="middle">-</text>
  <text x="230" y="140" font-family="sans-serif" font-weight="bold" font-size="16" fill="#94a3b8" text-anchor="middle">+</text>
  <circle cx="200" cy="120" r="4" fill="#00f7ff"/>
  <!-- Badge -->
  <rect x="20" y="20" width="85" height="26" rx="4" fill="#febd69"/>
  <text x="62" y="37" font-family="sans-serif" font-weight="bold" font-size="11" fill="#111" text-anchor="middle">ECHO DOT</text>
</svg>
SVG,

    'kindle.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="100%" height="100%">
  <defs>
    <linearGradient id="kindleBody" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1e293b"/>
      <stop offset="100%" stop-color="#0f172a"/>
    </linearGradient>
  </defs>
  <rect width="400" height="400" fill="#f8fafc" rx="16"/>
  <!-- Drop Shadow -->
  <rect x="105" y="45" width="200" height="300" rx="14" fill="#94a3b8" opacity="0.3"/>
  <!-- Outer Bezel -->
  <rect x="100" y="40" width="200" height="300" rx="14" fill="url(#kindleBody)"/>
  <!-- E-Ink Display Screen -->
  <rect x="116" y="60" width="168" height="236" rx="4" fill="#f1f5f9"/>
  <!-- E-Ink Book Text Simulation -->
  <text x="200" y="90" font-family="Georgia, serif" font-weight="bold" font-size="13" fill="#1e293b" text-anchor="middle">Chapter 1: The Odyssey</text>
  <line x1="130" y1="102" x2="270" y2="102" stroke="#94a3b8" stroke-width="1"/>
  <rect x="130" y="115" width="140" height="6" rx="2" fill="#64748b"/>
  <rect x="130" y="130" width="135" height="6" rx="2" fill="#64748b"/>
  <rect x="130" y="145" width="138" height="6" rx="2" fill="#64748b"/>
  <rect x="130" y="160" width="120" height="6" rx="2" fill="#64748b"/>
  <rect x="130" y="180" width="140" height="6" rx="2" fill="#64748b"/>
  <rect x="130" y="195" width="132" height="6" rx="2" fill="#64748b"/>
  <rect x="130" y="210" width="136" height="6" rx="2" fill="#64748b"/>
  <rect x="130" y="225" width="90" height="6" rx="2" fill="#64748b"/>
  <!-- Page Counter -->
  <text x="200" y="278" font-family="sans-serif" font-size="9" fill="#94a3b8" text-anchor="middle">Page 42 of 380 &bull; 68%</text>
  <!-- Kindle Logo at Bottom Bezel -->
  <text x="200" y="324" font-family="sans-serif" font-weight="600" font-size="12" fill="#64748b" text-anchor="middle">kindle</text>
</svg>
SVG,

    'laptop.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="100%" height="100%">
  <defs>
    <linearGradient id="lapScreen" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#0284c7"/>
      <stop offset="50%" stop-color="#3b82f6"/>
      <stop offset="100%" stop-color="#6366f1"/>
    </linearGradient>
  </defs>
  <rect width="400" height="400" fill="#f8fafc" rx="16"/>
  <!-- Display Shadow -->
  <rect x="75" y="85" width="250" height="160" rx="8" fill="#94a3b8" opacity="0.3"/>
  <!-- Screen Lid -->
  <rect x="70" y="80" width="260" height="165" rx="8" fill="#1e293b"/>
  <!-- Screen Glass & Wallpaper -->
  <rect x="80" y="90" width="240" height="145" rx="4" fill="url(#lapScreen)"/>
  <!-- Screen App Windows -->
  <rect x="95" y="105" width="100" height="60" rx="4" fill="rgba(255,255,255,0.2)"/>
  <rect x="140" y="130" width="120" height="80" rx="4" fill="rgba(15,23,42,0.6)"/>
  <!-- Laptop Notch / Camera -->
  <circle cx="200" cy="85" r="2.5" fill="#475569"/>
  <!-- Base Chassis -->
  <polygon points="50,250 350,250 365,270 35,270" fill="#94a3b8"/>
  <polygon points="53,248 347,248 345,260 55,260" fill="#cbd5e1"/>
  <!-- Trackpad Cutout -->
  <rect x="175" y="254" width="50" height="8" rx="2" fill="#94a3b8"/>
  <!-- Bottom Lip -->
  <rect x="35" y="270" width="330" height="6" rx="3" fill="#64748b"/>
</svg>
SVG,

    'headphones.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="100%" height="100%">
  <defs>
    <linearGradient id="hpGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#334155"/>
      <stop offset="100%" stop-color="#0f172a"/>
    </linearGradient>
  </defs>
  <rect width="400" height="400" fill="#f8fafc" rx="16"/>
  <!-- Headband Arc -->
  <path d="M 120 220 A 90 90 0 0 1 280 220" fill="none" stroke="url(#hpGrad)" stroke-width="20" stroke-linecap="round"/>
  <!-- Cushion Padding -->
  <path d="M 140 180 A 70 70 0 0 1 260 180" fill="none" stroke="#1e293b" stroke-width="12" stroke-linecap="round"/>
  <!-- Ear Cups -->
  <ellipse cx="115" cy="235" rx="32" ry="48" fill="url(#hpGrad)"/>
  <ellipse cx="115" cy="235" rx="22" ry="38" fill="#1e293b"/>
  <ellipse cx="285" cy="235" rx="32" ry="48" fill="url(#hpGrad)"/>
  <ellipse cx="285" cy="235" rx="22" ry="38" fill="#1e293b"/>
  <!-- Gold Accent Ring -->
  <circle cx="115" cy="235" r="10" fill="none" stroke="#f59e0b" stroke-width="2"/>
  <circle cx="285" cy="235" r="10" fill="none" stroke="#f59e0b" stroke-width="2"/>
  <text x="200" y="320" font-family="sans-serif" font-weight="bold" font-size="14" fill="#475569" text-anchor="middle">ACTIVE NOISE CANCELING</text>
</svg>
SVG,

    'console.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="100%" height="100%">
  <defs>
    <linearGradient id="conGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#ffffff"/>
      <stop offset="100%" stop-color="#e2e8f0"/>
    </linearGradient>
  </defs>
  <rect width="400" height="400" fill="#f8fafc" rx="16"/>
  <!-- PS5 Style Vertical Console Shape -->
  <path d="M 150 70 Q 140 200 135 320 L 185 320 Q 190 200 195 70 Z" fill="url(#conGrad)" stroke="#cbd5e1" stroke-width="2"/>
  <path d="M 158 90 L 150 310 L 175 310 L 182 90 Z" fill="#0f172a"/>
  <!-- Blue LED Vent Line -->
  <line x1="160" y1="100" x2="155" y2="300" stroke="#00d4ff" stroke-width="3"/>
  <!-- Game Controller next to it -->
  <path d="M 230 200 C 230 180 270 170 300 170 C 330 170 370 180 370 200 C 370 240 350 280 340 280 C 330 280 320 250 300 250 C 280 250 270 280 260 280 C 250 280 230 240 230 200 Z" fill="#ffffff" stroke="#cbd5e1" stroke-width="3"/>
  <!-- Thumbsticks -->
  <circle cx="280" cy="225" r="14" fill="#334155"/>
  <circle cx="320" cy="225" r="14" fill="#334155"/>
  <!-- D-Pad and Buttons -->
  <circle cx="265" cy="195" r="5" fill="#64748b"/>
  <circle cx="335" cy="195" r="5" fill="#3b82f6"/>
</svg>
SVG,

    'camera.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="100%" height="100%">
  <rect width="400" height="400" fill="#f8fafc" rx="16"/>
  <!-- Camera Body -->
  <rect x="110" y="100" width="180" height="180" rx="36" fill="#1e293b"/>
  <!-- Inner Ring -->
  <circle cx="200" cy="190" r="60" fill="#0f172a" stroke="#334155" stroke-width="4"/>
  <!-- Camera Lens Elements -->
  <circle cx="200" cy="190" r="40" fill="#0284c7"/>
  <circle cx="200" cy="190" r="24" fill="#0c4a6e"/>
  <circle cx="190" cy="180" r="8" fill="#38bdf8" opacity="0.6"/>
  <!-- IR Night Vision Sensor & Blue Status LED -->
  <circle cx="200" cy="125" r="5" fill="#3b82f6"/>
  <circle cx="245" cy="140" r="4" fill="#ef4444" opacity="0.7"/>
  <text x="200" y="320" font-family="sans-serif" font-weight="700" font-size="13" fill="#64748b" text-anchor="middle">1080p HD LIVE VIEW</text>
</svg>
SVG,

    'mouse.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="100%" height="100%">
  <rect width="400" height="400" fill="#f8fafc" rx="16"/>
  <!-- Mouse Body Oval -->
  <path d="M 200 80 C 145 80 135 150 135 240 C 135 310 165 330 200 330 C 235 330 265 310 265 240 C 265 150 255 80 200 80 Z" fill="#1e293b" stroke="#334155" stroke-width="4"/>
  <!-- Center Split Line -->
  <line x1="200" y1="80" x2="200" y2="180" stroke="#0f172a" stroke-width="3"/>
  <!-- Scroll Wheel with Metal Accent -->
  <rect x="193" y="110" width="14" height="34" rx="6" fill="#94a3b8" stroke="#f59e0b" stroke-width="2"/>
  <!-- Thumb Rest Wing -->
  <path d="M 135 200 C 105 220 105 260 135 270 Z" fill="#0f172a"/>
  <text x="200" y="360" font-family="sans-serif" font-weight="600" font-size="12" fill="#64748b" text-anchor="middle">Ergonomic Wireless</text>
</svg>
SVG,

    'security-book.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="100%" height="100%">
  <rect width="400" height="400" fill="#f8fafc" rx="16"/>
  <!-- Book Cover Shadow -->
  <rect x="115" y="65" width="190" height="270" rx="8" fill="#94a3b8" opacity="0.3"/>
  <!-- Book Cover (Cyber Security Black/Amber) -->
  <rect x="110" y="60" width="190" height="270" rx="8" fill="#0f172a"/>
  <!-- Book Spine -->
  <rect x="110" y="60" width="22" height="270" rx="4" fill="#1e293b"/>
  <!-- Security Shield Graphic -->
  <path d="M 205 130 L 245 145 L 245 185 C 245 210 205 230 205 230 C 205 230 165 210 165 185 L 165 145 Z" fill="#ef4444"/>
  <!-- Keyhole in Shield -->
  <circle cx="205" cy="170" r="8" fill="#fff"/>
  <polygon points="202,170 208,170 211,190 199,190" fill="#fff"/>
  <!-- Book Titles -->
  <text x="205" y="105" font-family="sans-serif" font-weight="800" font-size="11" fill="#f59e0b" text-anchor="middle">WEB APPLICATION</text>
  <text x="205" y="120" font-family="sans-serif" font-weight="800" font-size="13" fill="#ffffff" text-anchor="middle">SECURITY LAB</text>
  <text x="205" y="270" font-family="sans-serif" font-weight="bold" font-size="10" fill="#94a3b8" text-anchor="middle">Finding &amp; Patching Flaws</text>
  <text x="205" y="295" font-family="sans-serif" font-size="9" fill="#64748b" text-anchor="middle">Defensive Edition</text>
</svg>
SVG,

    'placeholder.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="100%" height="100%">
  <rect width="400" height="400" fill="#f1f5f9" rx="16"/>
  <circle cx="200" cy="180" r="60" fill="#e2e8f0"/>
  <path d="M 180 180 L 195 160 L 210 180 L 220 170 L 235 190 L 165 190 Z" fill="#94a3b8"/>
  <circle cx="180" cy="155" r="7" fill="#fbbf24"/>
  <text x="200" y="270" font-family="sans-serif" font-weight="600" font-size="16" fill="#64748b" text-anchor="middle">Fakizon Certified Product</text>
</svg>
SVG,

    'hero-banner.svg' => <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 400" width="100%" height="100%">
  <defs>
    <linearGradient id="heroGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#0f172a"/>
      <stop offset="50%" stop-color="#1e293b"/>
      <stop offset="100%" stop-color="#232f3e"/>
    </linearGradient>
  </defs>
  <rect width="1600" height="400" fill="url(#heroGrad)"/>
  <!-- Decorative Grid lines -->
  <line x1="0" y1="100" x2="1600" y2="100" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>
  <line x1="0" y1="200" x2="1600" y2="200" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>
  <line x1="0" y1="300" x2="1600" y2="300" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>
  <circle cx="1300" cy="200" r="180" fill="#f08804" opacity="0.1"/>
  <circle cx="1400" cy="160" r="100" fill="#00d4ff" opacity="0.15"/>
</svg>
SVG
];

foreach ($images as $filename => $svgContent) {
    file_put_contents($imgDir . $filename, $svgContent);
    echo "Generated: assets/images/{$filename}\n";
}

echo "All local image assets generated successfully.\n";
