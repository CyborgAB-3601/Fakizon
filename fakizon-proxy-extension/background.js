// Fakizon Proxy Switcher - Background Service Worker

const DEFAULT_ORIGINS = [
  'fakizon.vercel.app',
  'victim-shop.vercel.app',
  'checkout-lab.vercel.app'
];

const DEFAULT_CONFIG = {
  enabled: false,
  scheme: 'http',
  host: '127.0.0.1',
  port: '8080',
  proxyLocalhost: true,
  registeredOrigins: DEFAULT_ORIGINS
};

// Fetch live registered origins from ShieldForge dashboard / Supabase
async function fetchRegisteredOrigins() {
  try {
    const res = await fetch('http://localhost:3000/api/snapshot', { cache: 'no-store' })
      .catch(() => fetch('http://127.0.0.1:3000/api/snapshot', { cache: 'no-store' }));
    if (!res || !res.ok) return;
    const data = await res.json();
    if (Array.isArray(data?.origins)) {
      const extracted = data.origins
        .map((o) => {
          try {
            return new URL(o.origin_url).hostname.toLowerCase();
          } catch {
            return null;
          }
        })
        .filter(Boolean);

      const merged = Array.from(new Set([...DEFAULT_ORIGINS, ...extracted]));
      chrome.storage.local.set({ registeredOrigins: merged });
    }
  } catch (e) {
    // Non-fatal if dashboard is starting
  }
}

function getRedirectTargetIfOrigin(url, registeredOrigins) {
  if (!url || typeof url !== 'string') return null;
  if (!url.startsWith('http://') && !url.startsWith('https://')) return null;

  try {
    const parsed = new URL(url);
    const host = parsed.hostname.toLowerCase();

    // Never redirect if already on localhost or loopback
    if (host === 'localhost' || host === '127.0.0.1') return null;

    const list = Array.isArray(registeredOrigins) && registeredOrigins.length > 0
      ? registeredOrigins
      : DEFAULT_ORIGINS;

    const isMatch = list.some((org) => {
      const clean = org.replace(/^https?:\/\//, '').split('/')[0].toLowerCase();
      return host === clean || host.endsWith('.' + clean);
    });

    if (isMatch) {
      return `http://localhost:8000${parsed.pathname}${parsed.search}${parsed.hash}`;
    }
  } catch {}

  return null;
}

function redirectAllOpenOriginTabs() {
  chrome.storage.local.get(['registeredOrigins'], (data) => {
    const origins = data.registeredOrigins || DEFAULT_ORIGINS;
    if (chrome.tabs && chrome.tabs.query) {
      chrome.tabs.query({}, (tabs) => {
        let focused = false;
        let hadOriginTab = false;

        (tabs || []).forEach((tab) => {
          const target = getRedirectTargetIfOrigin(tab.url, origins);
          if (target) {
            hadOriginTab = true;
            chrome.tabs.update(tab.id, { url: target, active: !focused });
            if (!focused && tab.windowId) {
              chrome.windows.update(tab.windowId, { focused: true });
              focused = true;
            }
          }
        });

        // Do not force-create or switch to localhost:8000 if user is browsing non-origin sites
      });
    }
  });
}

function syncDashboard(active) {
  try {
    fetch('http://localhost:3000/api/proxy/status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ active: Boolean(active) })
    }).catch(() => {});
  } catch (e) {}
}

chrome.runtime.onInstalled.addListener(() => {
  chrome.storage.local.get(DEFAULT_CONFIG, (stored) => {
    chrome.storage.local.set(stored);
    updateBadge(stored.enabled);
    syncDashboard(stored.enabled);
    fetchRegisteredOrigins();
  });
});

chrome.runtime.onStartup.addListener(() => {
  chrome.storage.local.get(['enabled'], (result) => {
    const isEn = result.enabled || false;
    updateBadge(isEn);
    syncDashboard(isEn);
    fetchRegisteredOrigins();
  });
});

function updateBadge(enabled) {
  if (enabled) {
    chrome.action.setBadgeText({ text: 'ON' });
    chrome.action.setBadgeBackgroundColor({ color: '#10b981' }); // Vibrant Emerald Green
    chrome.action.setTitle({ title: 'ShieldForge: PROXY ACTIVE' });
  } else {
    chrome.action.setBadgeText({ text: 'OFF' });
    chrome.action.setBadgeBackgroundColor({ color: '#64748b' }); // Muted Slate
    chrome.action.setTitle({ title: 'ShieldForge: DIRECT (No Proxy)' });
  }
}

// Navigation interception via tabs.onUpdated
chrome.tabs.onUpdated.addListener((tabId, changeInfo, tab) => {
  const urlToCheck = changeInfo.url || (tab && tab.url);
  if (!urlToCheck) return;

  chrome.storage.local.get(['enabled', 'registeredOrigins'], (data) => {
    if (!data || !data.enabled) return;

    const origins = data.registeredOrigins || DEFAULT_ORIGINS;
    const target = getRedirectTargetIfOrigin(urlToCheck, origins);
    if (target && target !== tab.url) {
      chrome.tabs.update(tabId, { url: target });
    }
  });
});

// Navigation interception via webNavigation if available
if (chrome.webNavigation && chrome.webNavigation.onBeforeNavigate) {
  chrome.webNavigation.onBeforeNavigate.addListener((details) => {
    if (details.frameId !== 0) return; // Top-level frame navigation only

    chrome.storage.local.get(['enabled', 'registeredOrigins'], (data) => {
      if (!data || !data.enabled) return;

      const origins = data.registeredOrigins || DEFAULT_ORIGINS;
      const target = getRedirectTargetIfOrigin(details.url, origins);
      if (target) {
        chrome.tabs.update(details.tabId, { url: target });
      }
    });
  });
}

// Listen for state change messages and threat reports
chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
  if (message.type === 'UPDATE_STATUS') {
    updateBadge(message.enabled);
    syncDashboard(message.enabled);
    if (message.enabled) {
      fetchRegisteredOrigins().then(() => {
        redirectAllOpenOriginTabs();
      });
    }
    sendResponse({ success: true });
    return false;
  }

  if (message.type === 'REPORT_THREAT') {
    const payloadStr = JSON.stringify(message.payload || {});
    const sendInject = () =>
      fetch('http://localhost:3000/api/demo/inject', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: payloadStr
      }).catch(() =>
        fetch('http://127.0.0.1:3000/api/demo/inject', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: payloadStr
        })
      ).catch((e) => {
        console.warn('[ShieldForge] Threat log error:', e);
      });

    const sendStatus = () =>
      fetch('http://localhost:3000/api/proxy/status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ active: true, anomaly: true })
      }).catch(() =>
        fetch('http://127.0.0.1:3000/api/proxy/status', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ active: true, anomaly: true })
        })
      ).catch(() => {});

    Promise.all([sendInject(), sendStatus()])
      .then(() => {
        sendResponse({ logged: true });
      })
      .catch(() => {
        sendResponse({ logged: false });
      });
    return true;
  }
});

// Periodically synchronize dashboard status and registered origins
setInterval(() => {
  chrome.storage.local.get(['enabled'], (result) => {
    if (result && typeof result.enabled === 'boolean') {
      syncDashboard(result.enabled);
    }
  });
  fetchRegisteredOrigins();
}, 4000);

