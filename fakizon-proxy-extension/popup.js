// Exploit Zero Proxy Switcher - Interactive Logic with Draggable & Snapping Toggle

document.addEventListener('DOMContentLoaded', () => {
  // Elements
  const body = document.body;
  const mainToggleBtn = document.getElementById('mainToggleBtn');
  const toggleKnob = document.getElementById('toggleKnob');
  const trackBgOn = document.querySelector('.toggle-track-bg-on');
  const trackBgOff = document.querySelector('.toggle-track-bg-off');
  const labelConnected = document.getElementById('labelConnected');
  const labelDisconnected = document.getElementById('labelDisconnected');
  const orbToggleBtn = document.getElementById('orbToggleBtn');
  const statusHeading = document.getElementById('statusHeading');
  const statusSub = document.getElementById('statusSub');
  const lockIcon = document.getElementById('lockIcon');
  
  // Stats
  const statIp = document.getElementById('statIp');
  const statDns = document.getElementById('statDns');
  const statLoc = document.getElementById('statLoc');
  const statLat = document.getElementById('statLat');
  const latencyVal = document.getElementById('latencyVal');

  // Node selector
  const nodePill = document.getElementById('nodePill');
  const nodeName = document.getElementById('nodeName');
  const nodeSubtitle = document.getElementById('nodeSubtitle');
  const btnPresets = document.getElementById('btnPresets');

  // State
  let isProxyEnabled = true;

  // Node Presets with actual server IP address
  const SERVER_IP = '10.68.225.33';
  const presets = [
    { name: SERVER_IP, subtitle: 'Secure Node (Port 8000)', host: '127.0.0.1', port: '8080', latency: '< 1 ms' },
    { name: '127.0.0.1', subtitle: 'Localhost Node (Port 8000)', host: '127.0.0.1', port: '8080', latency: '< 1 ms' },
    { name: 'Burp Suite', subtitle: 'Interceptor (127.0.0.1:8080)', host: '127.0.0.1', port: '8080', latency: '1 ms' },
    { name: 'OWASP ZAP', subtitle: 'Security Proxy (127.0.0.1:8080)', host: '127.0.0.1', port: '8080', latency: '1 ms' }
  ];
  let currentPresetIndex = 0;

  function syncDashboard(active) {
    try {
      fetch('http://localhost:3000/api/proxy/status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ active: !!active })
      }).catch(() => {});
    } catch (e) {}
  }

  // Load Saved Configuration
  chrome.storage.local.get({
    enabled: false,
    scheme: 'http',
    host: '127.0.0.1',
    port: '8080',
    presetIndex: 0
  }, (data) => {
    currentPresetIndex = data.presetIndex || 0;
    isProxyEnabled = !!data.enabled;

    applyPresetUI(presets[currentPresetIndex]);
    updateUI(isProxyEnabled);
    syncDashboard(isProxyEnabled);
  });

  // ================= Drag & Slide Logic with Snapping =================
  const MIN_X = 6;    // Position when OFF (px)
  const MAX_X = 142;  // Position when ON (px)
  const MID_X = (MIN_X + MAX_X) / 2; // 74px midpoint threshold

  let isDragging = false;
  let startPointerX = 0;
  let startKnobX = 0;
  let currentKnobX = MAX_X;
  let hasMoved = false;

  mainToggleBtn.addEventListener('pointerdown', (e) => {
    isDragging = true;
    hasMoved = false;
    startPointerX = e.clientX;
    startKnobX = isProxyEnabled ? MAX_X : MIN_X;
    currentKnobX = startKnobX;

    mainToggleBtn.setPointerCapture(e.pointerId);
    mainToggleBtn.classList.add('dragging');

    // Remove transition during direct dragging for zero lag
    toggleKnob.style.transition = 'none';
  });

  mainToggleBtn.addEventListener('pointermove', (e) => {
    if (!isDragging) return;

    const deltaX = e.clientX - startPointerX;
    if (Math.abs(deltaX) > 3) {
      hasMoved = true;
    }

    let nextX = startKnobX + deltaX;
    nextX = Math.max(MIN_X, Math.min(MAX_X, nextX));
    currentKnobX = nextX;

    // Direct knob transform
    toggleKnob.style.transform = `translateX(${nextX}px)`;

    // Calculate progress 0 (off) -> 1 (on)
    const progress = (nextX - MIN_X) / (MAX_X - MIN_X);

    // Live background & label interpolation
    if (trackBgOn) trackBgOn.style.opacity = progress;
    if (trackBgOff) trackBgOff.style.opacity = 1 - progress;

    if (labelConnected) {
      labelConnected.style.opacity = progress;
      labelConnected.style.transform = `translateX(${(1 - progress) * -8}px)`;
    }
    if (labelDisconnected) {
      labelDisconnected.style.opacity = 1 - progress;
      labelDisconnected.style.transform = `translateX(${progress * 8}px)`;
    }
  });

  function finishDrag() {
    if (!isDragging) return;
    isDragging = false;
    mainToggleBtn.classList.remove('dragging');

    // Restore smooth spring cubic-bezier transition
    toggleKnob.style.transition = 'transform 0.38s cubic-bezier(0.175, 0.885, 0.32, 1.275), background-color 0.3s ease';

    // Clear inline drag styles
    if (trackBgOn) trackBgOn.style.opacity = '';
    if (trackBgOff) trackBgOff.style.opacity = '';
    if (labelConnected) {
      labelConnected.style.opacity = '';
      labelConnected.style.transform = '';
    }
    if (labelDisconnected) {
      labelDisconnected.style.opacity = '';
      labelDisconnected.style.transform = '';
    }
    toggleKnob.style.transform = '';

    if (hasMoved) {
      // Snapped based on where user released knob
      const targetState = currentKnobX > MID_X;
      if (targetState !== isProxyEnabled) {
        if (targetState) {
          enableProxy();
        } else {
          disableProxy();
        }
      } else {
        // Snap back to original position
        updateUI(isProxyEnabled);
      }
    } else {
      // Tap / Click without dragging -> Smooth Toggle
      if (isProxyEnabled) {
        disableProxy();
      } else {
        enableProxy();
      }
    }
  }

  mainToggleBtn.addEventListener('pointerup', finishDrag);
  mainToggleBtn.addEventListener('pointercancel', finishDrag);

  // Click on the central orb also triggers smooth toggle
  orbToggleBtn.addEventListener('click', () => {
    if (isProxyEnabled) {
      disableProxy();
    } else {
      enableProxy();
    }
  });

  // ================= Proxy Activation API =================
  function enableProxy() {
    isProxyEnabled = true;
    updateUI(true);
    syncDashboard(true);

    const preset = presets[currentPresetIndex];
    const host = preset.host || '127.0.0.1';
    const port = parseInt(preset.port, 10) || 8080;
    const scheme = 'http';

    // Scoped PAC script: Intercept Fakizon/localhost targets without cutting off WAN internet
    const pacData = `
      function FindProxyForURL(url, host) {
        if (url.indexOf(":3000") !== -1 || url.indexOf(":8080") !== -1) {
          return "DIRECT";
        }
        if (host === "127.0.0.1" || host === "localhost" || host === "${SERVER_IP}") {
          return "PROXY ${host}:${port}; DIRECT";
        }
        return "DIRECT";
      }
    `;

    const config = {
      mode: 'pac_script',
      pacScript: { data: pacData }
    };

    chrome.proxy.settings.set({ value: config, scope: 'regular' }, () => {
      chrome.storage.local.set({ enabled: true, host, port, scheme, presetIndex: currentPresetIndex }, () => {
        chrome.runtime.sendMessage({ type: 'UPDATE_STATUS', enabled: true });
      });
    });
  }

  function disableProxy() {
    isProxyEnabled = false;
    updateUI(false);
    syncDashboard(false);

    const config = { mode: 'direct' };

    chrome.proxy.settings.set({ value: config, scope: 'regular' }, () => {
      chrome.storage.local.set({ enabled: false }, () => {
        chrome.runtime.sendMessage({ type: 'UPDATE_STATUS', enabled: false });
      });
    });
  }

  function updateUI(enabled) {
    isProxyEnabled = enabled;
    const preset = presets[currentPresetIndex];

    if (enabled) {
      body.classList.remove('proxy-off');
      statusHeading.innerHTML = 'Proxy is <span class="status-highlight-on">ON</span>';
      statusSub.textContent = 'Your connection is secure and private';

      // Locked Icon
      lockIcon.innerHTML = `
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
      `;

      statIp.textContent = 'Yes';
      statDns.textContent = 'None';
      statLoc.textContent = preset.name;
      statLat.textContent = preset.latency;
      latencyVal.textContent = preset.latency;
    } else {
      body.classList.add('proxy-off');
      statusHeading.innerHTML = 'Proxy is <span class="status-highlight-off">OFF</span>';
      statusSub.textContent = 'Direct connection (No interception)';

      // Unlocked Icon
      lockIcon.innerHTML = `
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
        <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
      `;

      statIp.textContent = 'Direct';
      statDns.textContent = 'Exposed';
      statLoc.textContent = 'Direct';
      statLat.textContent = '--';
      latencyVal.textContent = '--';
    }
  }

  // ================= Node Pill Preset Cycling =================
  function cyclePreset() {
    currentPresetIndex = (currentPresetIndex + 1) % presets.length;
    const p = presets[currentPresetIndex];
    applyPresetUI(p);

    chrome.storage.local.set({
      host: p.host,
      port: p.port,
      presetIndex: currentPresetIndex
    });

    // If currently enabled, re-apply with new preset
    if (isProxyEnabled) {
      enableProxy();
    }
  }

  nodePill.addEventListener('click', cyclePreset);
  if (btnPresets) {
    btnPresets.addEventListener('click', cyclePreset);
  }

  function applyPresetUI(p) {
    nodeName.textContent = p.name;
    nodeSubtitle.textContent = p.subtitle;
    statLoc.textContent = p.name;
    statLat.textContent = p.latency;
    latencyVal.textContent = p.latency;
  }

  // Vulnerability Cards click to open test points
  const vulnCards = document.querySelectorAll('.vuln-card-item');
  vulnCards.forEach(card => {
    card.addEventListener('click', () => {
      const url = card.getAttribute('data-url');
      if (url) {
        chrome.tabs.create({ url });
      }
    });
  });
});
