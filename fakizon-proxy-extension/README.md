# Fakizon Proxy Switcher (Chrome Extension - Manifest V3)

A lightweight, 1-click Chrome extension designed to toggle your browser proxy **ON** and **OFF** for security testing with **Burp Suite**, **OWASP ZAP**, **Fiddler**, or custom proxies.

---

## 🚀 How to Install in Google Chrome (or Brave / Edge)

You can load this extension in less than 30 seconds:

1. Open **Google Chrome** and navigate to:
   ```
   chrome://extensions
   ```
2. Enable **Developer mode** using the toggle switch in the top-right corner.
3. Click the **Load unpacked** button in the top-left corner.
4. Select the folder:
   ```
   D:\ExploitZero\fakizon-proxy-extension
   ```
5. Click the puzzle icon (Extensions) in your Chrome toolbar and **Pin** 📌 **Fakizon Proxy Switcher**.

---

## ⚡ How to Use

1. Click the **Fakizon Proxy Switcher** icon in your Chrome toolbar.
2. Click the **toggle switch**:
   - 🟢 **ON:** All browser HTTP/HTTPS traffic is routed through your proxy (`127.0.0.1:8080`). The extension badge will display **`ON`** in green.
   - ⚫ **OFF:** Browser traffic returns immediately to **Direct (Normal internet connection)**. The extension badge will display **`OFF`**.

---

## 🛠 Features

- **1-Click Presets:**
  - **Burp Suite:** Sets HTTP proxy to `127.0.0.1:8080`.
  - **OWASP ZAP:** Sets HTTP proxy to `127.0.0.1:8080`.
  - **Fiddler:** Sets HTTP proxy to `127.0.0.1:8888`.
- **Localhost Interception:**
  - By default, Chrome bypasses local loopback traffic (`localhost` / `127.0.0.1`).
  - This extension includes an automatic **"Proxy localhost / 127.0.0.1 traffic"** setting (using `<-loopback>`) so you can intercept and test requests sent to your local Fakizon server (`http://localhost:8000`) without manual browser flag hacks!
- **Dynamic Status Badge:** Instantly see if your proxy is active or direct directly from the Chrome toolbar.

---

## 📂 Extension Files

```
d:\ExploitZero\fakizon-proxy-extension\
├── manifest.json       # Manifest V3 extension configuration
├── background.js       # Background service worker & badge manager
├── popup.html          # Extension popup user interface
├── popup.js            # Proxy control & chrome.proxy API bindings
├── icons/              # Extension icons (16px, 48px, 128px)
└── README.md           # Instructions
```
