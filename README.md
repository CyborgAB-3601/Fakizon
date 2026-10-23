# Fakizon - Amazon-Style PHP Shopping Target & Testing Lab

**Fakizon** is an Amazon-inspired mock e-commerce shopping website built in pure PHP and SQLite, designed for testing web application vulnerabilities (SQL Injection, XSS, IDOR, File Upload, Command Injection) with security testing tools such as Burp Suite and OWASP ZAP.

---

## 🚀 Quick Start

### 1. Start the PHP Server
Double-click `start-server.bat` or run:
```powershell
php -S 0.0.0.0:8000
```
Then visit **`http://localhost:8000`** in your browser.

---

## 🔌 Fakizon Proxy Switcher (Chrome Extension)

Included in this repository is a custom Chrome extension to toggle your proxy **ON** and **OFF** in 1 click for intercepting traffic in **Burp Suite** or **OWASP ZAP**.

### Installation (3 Steps):
1. Open Google Chrome and go to `chrome://extensions`.
2. Toggle on **Developer mode** in the top right.
3. Click **Load unpacked** and select the folder:
   ```
   D:\ExploitZero\fakizon-proxy-extension
   ```
4. Click the extension icon in Chrome to toggle between **Proxy ON** (`127.0.0.1:8080`) and **Proxy OFF** (Direct connection).

---

## 🔑 Demo Accounts

| Username | Password | Role | Description |
| :--- | :--- | :--- | :--- |
| `admin` | `admin123` | Administrator | Full access to inventory, sales analytics & diagnostics |
| `alex` | `alex123` | Customer | Verified Prime buyer with order history |
| `jane` | `jane123` | Customer | Secondary customer account for IDOR testing |

---

## 🧪 Vulnerability Testing Points

The website is naturally vulnerable to common web vulnerabilities:

| # | Vulnerability Category | Endpoint | Notes |
| :--- | :--- | :--- | :--- |
| **1** | **SQL Injection (SQLi)** | `search.php?q=...`<br>`login.php` | Search parameter and login form allow auth bypass (`admin' --`) and query manipulation. |
| **2** | **Reflected XSS** | `search.php?q=...` | Search query is echoed into the results header without HTML encoding. |
| **3** | **Stored XSS** | `product.php?id=1` | Customer reviews are stored in the database and rendered raw to all viewers. |
| **4** | **IDOR** | `orders.php?id=1` | Order records can be accessed by ID without verifying customer session ownership. |
| **5** | **File Upload** | `profile.php` | Profile picture upload saves arbitrary extensions directly into `assets/uploads/`. |
| **6** | **Command Injection** | `admin.php` | Supplier Ping Tool executes user-supplied hostnames directly in `shell_exec()`. |

---

## 🛠 Project Structure

```
d:\ExploitZero\
├── db.php                     # SQLite PDO database connection & auto-seeding
├── index.php                  # Amazon-style storefront, hero banner, product grid
├── search.php                 # Search results (SQLi and Reflected XSS)
├── product.php                # Product detail & reviews (Stored XSS)
├── cart.php                   # Shopping cart & subtotal calculations
├── checkout.php               # Simulated checkout & order placement
├── orders.php                 # Order history & invoice view (IDOR)
├── login.php                  # Sign-In (SQL Injection Auth Bypass)
├── register.php               # Customer registration
├── profile.php                # Customer profile & avatar upload (File Upload)
├── admin.php                  # Operations console & ping diagnostic (Command Injection)
├── start-server.bat           # Windows 1-click launcher
├── assets/                    # CSS styling and local SVG image assets
└── fakizon-proxy-extension/   # Chrome Extension (Manifest V3) to toggle proxy ON/OFF
    ├── manifest.json
    ├── background.js
    ├── popup.html
    ├── popup.js
    └── icons/
```
