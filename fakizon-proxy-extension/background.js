// Fakizon Proxy Switcher - Background Service Worker

const DEFAULT_CONFIG = {
  enabled: false,
  scheme: 'http',
  host: '127.0.0.1',
  port: '8080',
  proxyLocalhost: true
};

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
  });
});

chrome.runtime.onStartup.addListener(() => {
  chrome.storage.local.get(['enabled'], (result) => {
    const isEn = result.enabled || false;
    updateBadge(isEn);
    syncDashboard(isEn);
  });
});

function updateBadge(enabled) {
  if (enabled) {
    chrome.action.setBadgeText({ text: 'ON' });
    chrome.action.setBadgeBackgroundColor({ color: '#10b981' }); // Vibrant Emerald Green
    chrome.action.setTitle({ title: 'Exploit Zero: PROXY ACTIVE' });
  } else {
    chrome.action.setBadgeText({ text: 'OFF' });
    chrome.action.setBadgeBackgroundColor({ color: '#64748b' }); // Muted Slate
    chrome.action.setTitle({ title: 'Exploit Zero: DIRECT (No Proxy)' });
  }
}

// Listen for state change messages from popup
chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
  if (message.type === 'UPDATE_STATUS') {
    updateBadge(message.enabled);
    syncDashboard(message.enabled);
    sendResponse({ success: true });
  }
});

// Keep dashboard synchronized with current extension state
setInterval(() => {
  chrome.storage.local.get(['enabled'], (result) => {
    if (result && typeof result.enabled === 'boolean') {
      syncDashboard(result.enabled);
    }
  });
}, 2000);

