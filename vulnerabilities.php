<?php
$pageTitle = "Security Testing Lab & Vulnerability Matrix";
require_once __DIR__ . '/header.php';
?>

<div style="margin-top: 24px; max-width: 1100px; margin-left: auto; margin-right: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 800;">
                <i class="fa-solid fa-flask" style="color: #ef4444;"></i> Fakizon Vulnerability Testing Lab
            </h1>
            <p style="color: #64748b; font-size: 0.95rem;">
                A hands-on testing ground designed for web vulnerability scanners, penetration testing labs, and defensive code analysis.
            </p>
        </div>
        <div>
            <a href="toggle_security.php?ref=vulnerabilities.php" class="btn <?= $isVuln ? 'btn-buy-now' : 'btn-add-cart' ?>" style="width: auto; padding: 10px 20px; font-weight: 700;">
                <i class="fa-solid fa-arrows-rotate"></i> <?= $isVuln ? 'Switch to SECURE Mode' : 'Switch to VULNERABLE Mode' ?>
            </a>
        </div>
    </div>

    <!-- Mode Banner -->
    <div style="background: <?= $isVuln ? '#fef2f2' : '#f0fdf4' ?>; border: 1px solid <?= $isVuln ? '#fca5a5' : '#86efac' ?>; padding: 16px 20px; border-radius: 6px; margin-bottom: 28px;">
        <div style="display: flex; align-items: center; gap: 10px; font-size: 1.05rem; font-weight: 700; color: <?= $isVuln ? '#b91c1c' : '#15803d' ?>;">
            <i class="fa-solid <?= $isVuln ? 'fa-triangle-exclamation' : 'fa-shield-check' ?>"></i>
            Current Status: <?= $isVuln ? 'VULNERABLE (Lab Mode Enabled)' : 'SECURE (Remediation Mode Enabled)' ?>
        </div>
        <p style="font-size: 0.88rem; color: #475569; margin-top: 6px;">
            <?= $isVuln 
                ? 'All intentional security flaws are currently UNPATCHED. Vulnerability scanners (like OWASP ZAP, Burp Suite, Nuclei) will identify these findings.' 
                : 'Defensive patches, input validations, parameter binding, and output encodings are ACTIVE. Test payloads should fail or be safely neutralized.' ?>
        </p>
    </div>

    <!-- Vulnerability 1: SQL Injection (SQLi) -->
    <div class="lab-card <?= $isVuln ? 'vuln' : 'secure' ?>">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">OWASP A03:2021 - Injection</span>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin: 6px 0;">1. SQL Injection (SQLi)</h3>
            </div>
            <a href="search.php?q=Echo" class="btn btn-secondary" style="width: auto; padding: 4px 12px; font-size: 0.8rem;">Test on Search &rarr;</a>
        </div>
        <p style="font-size: 0.88rem; color: #334155; margin: 8px 0;">
            <strong>Flaw Location:</strong> <code>search.php?q=...</code> and <code>login.php</code><br>
            <strong>Underlying Mechanism:</strong> Unsanitized user inputs are directly concatenated into SQL queries (<code>SELECT * FROM products WHERE name LIKE '%$q%'</code>).
        </p>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; margin-top: 10px;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #475569;">Sample Test Payloads:</div>
            <ul style="font-size: 0.85rem; padding-left: 20px; margin-top: 4px; color: #0f1111;">
                <li>Search Query: <code>' OR 1=1 --</code> (Dumps all catalog items)</li>
                <li>Login Auth Bypass: Username: <code>admin' --</code> Password: <code>anything</code></li>
            </ul>
        </div>
        <div style="margin-top: 10px; font-size: 0.85rem;">
            <strong>Defensive Remediation:</strong> Use PDO prepared statements with parameter binding:
            <div class="code-snippet">$stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE :query");<br>$stmt->execute([':query' => "%" . $query . "%"]);</div>
        </div>
    </div>

    <!-- Vulnerability 2: Reflected XSS -->
    <div class="lab-card <?= $isVuln ? 'vuln' : 'secure' ?>">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">OWASP A03:2021 - Injection</span>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin: 6px 0;">2. Reflected Cross-Site Scripting (Reflected XSS)</h3>
            </div>
            <a href="search.php?q=%3Cscript%3Ealert(%27XSS%27)%3C%2Fscript%3E" class="btn btn-secondary" style="width: auto; padding: 4px 12px; font-size: 0.8rem;">Test Reflected XSS &rarr;</a>
        </div>
        <p style="font-size: 0.88rem; color: #334155; margin: 8px 0;">
            <strong>Flaw Location:</strong> <code>search.php?q=...</code><br>
            <strong>Underlying Mechanism:</strong> The GET parameter <code>$_GET['q']</code> is directly echoed back into the HTML markup without escaping.
        </p>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; margin-top: 10px;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #475569;">Sample Test Payloads:</div>
            <ul style="font-size: 0.85rem; padding-left: 20px; margin-top: 4px; color: #0f1111;">
                <li><code>&lt;script&gt;alert('Fakizon XSS')&lt;/script&gt;</code></li>
                <li><code>&lt;img src=x onerror=alert(document.domain)&gt;</code></li>
            </ul>
        </div>
        <div style="margin-top: 10px; font-size: 0.85rem;">
            <strong>Defensive Remediation:</strong> Always escape contextually before echoing to HTML:
            <div class="code-snippet">&lt;?= htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8') ?&gt;</div>
        </div>
    </div>

    <!-- Vulnerability 3: Stored XSS -->
    <div class="lab-card <?= $isVuln ? 'vuln' : 'secure' ?>">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">OWASP A03:2021 - Injection</span>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin: 6px 0;">3. Stored Cross-Site Scripting (Persistent XSS)</h3>
            </div>
            <a href="product.php?id=1#write-review" class="btn btn-secondary" style="width: auto; padding: 4px 12px; font-size: 0.8rem;">Test on Product Review &rarr;</a>
        </div>
        <p style="font-size: 0.88rem; color: #334155; margin: 8px 0;">
            <strong>Flaw Location:</strong> <code>product.php?id=X</code> (Customer Reviews section)<br>
            <strong>Underlying Mechanism:</strong> User reviews submitted through POST are written to the database and later rendered raw to all customers viewing that product.
        </p>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; margin-top: 10px;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #475569;">Sample Test Payloads:</div>
            <ul style="font-size: 0.85rem; padding-left: 20px; margin-top: 4px; color: #0f1111;">
                <li>Title: <code>&lt;b&gt;Great sound&lt;/b&gt;</code></li>
                <li>Comment: <code>&lt;script&gt;console.log('Stored review payload executed')&lt;/script&gt; Excellent item!</code></li>
            </ul>
        </div>
        <div style="margin-top: 10px; font-size: 0.85rem;">
            <strong>Defensive Remediation:</strong> HTML entity encode stored strings when rendering into the DOM:
            <div class="code-snippet">&lt;?= nl2br(htmlspecialchars($review['comment'], ENT_QUOTES, 'UTF-8')) ?&gt;</div>
        </div>
    </div>

    <!-- Vulnerability 4: Insecure Direct Object References (IDOR) -->
    <div class="lab-card <?= $isVuln ? 'vuln' : 'secure' ?>">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">OWASP A01:2021 - Broken Access Control</span>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin: 6px 0;">4. Insecure Direct Object References (IDOR)</h3>
            </div>
            <a href="orders.php?id=2" class="btn btn-secondary" style="width: auto; padding: 4px 12px; font-size: 0.8rem;">Test Order IDOR &rarr;</a>
        </div>
        <p style="font-size: 0.88rem; color: #334155; margin: 8px 0;">
            <strong>Flaw Location:</strong> <code>orders.php?id=X</code><br>
            <strong>Underlying Mechanism:</strong> Order records are retrieved solely by query parameter <code>id</code> without verifying that the order belongs to the authenticated user's session.
        </p>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; margin-top: 10px;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #475569;">Sample Test:</div>
            <div style="font-size: 0.85rem; color: #0f1111; margin-top: 4px;">
                Log in as <code>alex</code> (or guest) and visit <code>orders.php?id=2</code>. In vulnerable mode, you can see Jane Doe's full order, shipping address, and items!
            </div>
        </div>
        <div style="margin-top: 10px; font-size: 0.85rem;">
            <strong>Defensive Remediation:</strong> Enforce session authorization in the database query:
            <div class="code-snippet">$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :order_id AND user_id = :session_user_id");</div>
        </div>
    </div>

    <!-- Vulnerability 5: Unrestricted File Upload -->
    <div class="lab-card <?= $isVuln ? 'vuln' : 'secure' ?>">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">OWASP A04:2021 - Insecure Design</span>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin: 6px 0;">5. Unrestricted File Upload</h3>
            </div>
            <a href="profile.php" class="btn btn-secondary" style="width: auto; padding: 4px 12px; font-size: 0.8rem;">Test File Upload &rarr;</a>
        </div>
        <p style="font-size: 0.88rem; color: #334155; margin: 8px 0;">
            <strong>Flaw Location:</strong> <code>profile.php</code> (Avatar upload)<br>
            <strong>Underlying Mechanism:</strong> In vulnerable mode, the file upload script accepts any file extension without validating MIME-types, saving it into the publicly-accessible <code>assets/uploads/</code> folder.
        </p>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; margin-top: 10px;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #475569;">Sample Test:</div>
            <div style="font-size: 0.85rem; color: #0f1111; margin-top: 4px;">
                Upload a test script like <code>test.html</code> or <code>info.php</code> as your avatar, then navigate to <code>assets/uploads/test.html</code> to verify execution.
            </div>
        </div>
        <div style="margin-top: 10px; font-size: 0.85rem;">
            <strong>Defensive Remediation:</strong> Enforce whitelist extensions, verify magic bytes/MIME type with <code>finfo</code>, and store files outside document root or disable script execution via <code>.htaccess</code>.
        </div>
    </div>

    <!-- Vulnerability 6: OS Command Injection -->
    <div class="lab-card <?= $isVuln ? 'vuln' : 'secure' ?>">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">OWASP A03:2021 - Command Injection</span>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin: 6px 0;">6. Remote OS Command Injection</h3>
            </div>
            <a href="admin.php" class="btn btn-secondary" style="width: auto; padding: 4px 12px; font-size: 0.8rem;">Test Command Injection &rarr;</a>
        </div>
        <p style="font-size: 0.88rem; color: #334155; margin: 8px 0;">
            <strong>Flaw Location:</strong> <code>admin.php</code> (Supplier Network Diagnostics Tool)<br>
            <strong>Underlying Mechanism:</strong> The ping utility builds a shell command by string concatenation and passes it to <code>shell_exec()</code>.
        </p>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; margin-top: 10px;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #475569;">Sample Test Payloads:</div>
            <ul style="font-size: 0.85rem; padding-left: 20px; margin-top: 4px; color: #0f1111;">
                <li>Windows: <code>127.0.0.1 & whoami</code></li>
                <li>Windows: <code>127.0.0.1 & dir</code></li>
                <li>Linux: <code>127.0.0.1 ; uname -a</code></li>
            </ul>
        </div>
        <div style="margin-top: 10px; font-size: 0.85rem;">
            <strong>Defensive Remediation:</strong> Validate IP format with <code>filter_var($ip, FILTER_VALIDATE_IP)</code> and escape arguments using <code>escapeshellarg()</code>.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
