<?php
/**
 * Privacy Policy Page V2
 * Comprehensive privacy policy with GDPR compliance
 *
 * Features:
 * - Table of contents with anchor navigation
 * - Data collection and usage transparency
 * - User rights and data protection (GDPR compliant)
 * - Cookie policy and tracking information
 * - Security measures documentation
 * - Data retention and deletion policies
 * - International data transfers
 * - Contact information for privacy inquiries
 * - Print and download options
 * - Last updated timestamp
 * - Version history tracking
 * - Acceptance acknowledgment system
 *
 * @package CIS_Intelligence_Dashboard
 * @subpackage Legal
 * @version 2.0.0
 */

declare(strict_types=1);

// Page configuration
$page_title = 'Privacy Policy';
$page_subtitle = 'How we collect, use, and protect your data';
$current_page = 'privacy';

// Privacy policy metadata
$policy_version = '2.0.0';
$effective_date = '2025-01-01';
$last_updated = '2025-10-31';

require_once __DIR__ . '/../includes-v2/header.php';
?>

<!-- Page Header -->
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><?= htmlspecialchars($page_title) ?></h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($page_subtitle) ?></p>
            <small class="text-muted">
                <i class="fas fa-calendar-alt me-1"></i> Effective Date: <?= date('F j, Y', strtotime($effective_date)) ?>
                <span class="mx-2">|</span>
                <i class="fas fa-clock me-1"></i> Last Updated: <?= date('F j, Y', strtotime($last_updated)) ?>
                <span class="mx-2">|</span>
                Version <?= htmlspecialchars($policy_version) ?>
            </small>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print Policy
            </button>
            <button class="btn btn-outline-secondary" onclick="downloadPolicy()">
                <i class="fas fa-download me-2"></i>Download PDF
            </button>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sidebar TOC -->
    <div class="col-lg-3 mb-4">
        <div class="card sticky-top" style="top: 80px;">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Table of Contents</h5>
            </div>
            <div class="card-body p-0">
                <nav id="privacy-toc" class="nav flex-column">
                    <a class="nav-link active" href="#introduction">1. Introduction</a>
                    <a class="nav-link" href="#data-collection">2. Data We Collect</a>
                    <a class="nav-link" href="#data-usage">3. How We Use Your Data</a>
                    <a class="nav-link" href="#data-sharing">4. Data Sharing & Disclosure</a>
                    <a class="nav-link" href="#data-security">5. Security Measures</a>
                    <a class="nav-link" href="#cookies">6. Cookies & Tracking</a>
                    <a class="nav-link" href="#user-rights">7. Your Rights (GDPR)</a>
                    <a class="nav-link" href="#data-retention">8. Data Retention</a>
                    <a class="nav-link" href="#international">9. International Transfers</a>
                    <a class="nav-link" href="#children">10. Children's Privacy</a>
                    <a class="nav-link" href="#updates">11. Policy Updates</a>
                    <a class="nav-link" href="#contact">12. Contact Us</a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body p-4">

                <!-- Introduction -->
                <section id="introduction" class="mb-5">
                    <h2 class="section-title">1. Introduction</h2>
                    <p class="lead">Welcome to the Intelligence Hub Privacy Policy. This document explains how we collect, use, protect, and share your personal information.</p>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Scope:</strong> This Privacy Policy applies to all users of the Intelligence Hub platform, including administrators, developers, and team members with access to the code quality dashboard.
                    </div>

                    <p>By using our services, you agree to the collection and use of information in accordance with this policy. If you do not agree with our policies and practices, please do not use our services.</p>

                    <p><strong>Key Points:</strong></p>
                    <ul>
                        <li>We are committed to protecting your privacy and data security</li>
                        <li>We only collect data necessary to provide and improve our services</li>
                        <li>We never sell your personal information to third parties</li>
                        <li>You have full control over your data and can request deletion at any time</li>
                        <li>We comply with GDPR, CCPA, and other major privacy regulations</li>
                    </ul>
                </section>

                <!-- Data Collection -->
                <section id="data-collection" class="mb-5">
                    <h2 class="section-title">2. Data We Collect</h2>
                    <p>We collect several types of information to provide and improve our services:</p>

                    <h4 class="mt-4"><i class="fas fa-user text-primary me-2"></i>Personal Information</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Data Type</th>
                                    <th>What We Collect</th>
                                    <th>Purpose</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Account Information</strong></td>
                                    <td>Name, email address, username, password (encrypted), organization name</td>
                                    <td>Account creation, authentication, communication</td>
                                </tr>
                                <tr>
                                    <td><strong>Profile Data</strong></td>
                                    <td>Job title, department, avatar image, preferences</td>
                                    <td>Personalization, team collaboration</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact Details</strong></td>
                                    <td>Email, phone (optional), time zone</td>
                                    <td>Notifications, support, system alerts</td>
                                </tr>
                                <tr>
                                    <td><strong>Billing Information</strong></td>
                                    <td>Company name, billing address, VAT number</td>
                                    <td>Invoicing (payment data processed by Stripe)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h4 class="mt-4"><i class="fas fa-code text-success me-2"></i>Usage Data</h4>
                    <ul>
                        <li><strong>Code Analysis:</strong> Source code files (processed locally, not stored permanently), scan results, violation reports, code metrics</li>
                        <li><strong>Activity Logs:</strong> Login times, page views, feature usage, actions performed (non-personal)</li>
                        <li><strong>Performance Metrics:</strong> Scan duration, file sizes processed, query execution times</li>
                        <li><strong>Team Collaboration:</strong> Comments, issue assignments, rule customizations</li>
                    </ul>

                    <h4 class="mt-4"><i class="fas fa-server text-warning me-2"></i>Technical Data</h4>
                    <ul>
                        <li><strong>Device Information:</strong> Browser type and version, operating system, screen resolution</li>
                        <li><strong>Connection Data:</strong> IP address (anonymized after 90 days), ISP, approximate location (city/country only)</li>
                        <li><strong>Session Data:</strong> Session tokens, authentication cookies, CSRF tokens</li>
                        <li><strong>Error Logs:</strong> JavaScript errors, API failures, system diagnostics (anonymized)</li>
                    </ul>

                    <div class="alert alert-success mt-3">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Data Minimization:</strong> We only collect data that is necessary for service operation and user experience improvement. We do not collect sensitive personal data (race, religion, health, etc.) unless explicitly required and consented.
                    </div>
                </section>

                <!-- Data Usage -->
                <section id="data-usage" class="mb-5">
                    <h2 class="section-title">3. How We Use Your Data</h2>
                    <p>We use collected data for the following purposes:</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card h-100 border-primary">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-cogs text-primary me-2"></i>Service Provision</h5>
                                    <ul class="mb-0">
                                        <li>Authenticate users and manage accounts</li>
                                        <li>Process code scans and generate reports</li>
                                        <li>Store and retrieve scan history</li>
                                        <li>Enable team collaboration features</li>
                                        <li>Send notifications and alerts</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-success">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-chart-line text-success me-2"></i>Service Improvement</h5>
                                    <ul class="mb-0">
                                        <li>Analyze usage patterns to improve features</li>
                                        <li>Identify and fix bugs</li>
                                        <li>Optimize performance and speed</li>
                                        <li>Develop new features based on usage data</li>
                                        <li>Conduct A/B testing (anonymized)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-info">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-headset text-info me-2"></i>Customer Support</h5>
                                    <ul class="mb-0">
                                        <li>Respond to support inquiries</li>
                                        <li>Troubleshoot technical issues</li>
                                        <li>Provide onboarding assistance</li>
                                        <li>Process account changes/deletions</li>
                                        <li>Send service announcements</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-warning">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-shield-alt text-warning me-2"></i>Security & Compliance</h5>
                                    <ul class="mb-0">
                                        <li>Detect and prevent fraud</li>
                                        <li>Monitor for security threats</li>
                                        <li>Enforce terms of service</li>
                                        <li>Comply with legal obligations</li>
                                        <li>Conduct security audits</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Marketing Communications:</strong> We may send you product updates and promotional emails. You can opt-out at any time using the unsubscribe link in emails or in your account settings. Transactional emails (security alerts, password resets) cannot be opted out.
                    </div>
                </section>

                <!-- Data Sharing -->
                <section id="data-sharing" class="mb-5">
                    <h2 class="section-title">4. Data Sharing & Disclosure</h2>
                    <p class="lead">We do not sell your personal information. We only share data in the following circumstances:</p>

                    <h4 class="mt-4"><i class="fas fa-handshake text-primary me-2"></i>Service Providers</h4>
                    <p>We work with trusted third-party vendors who help us operate our services. All vendors sign data processing agreements (DPAs) and are contractually obligated to protect your data:</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Provider</th>
                                    <th>Service</th>
                                    <th>Data Shared</th>
                                    <th>Privacy Policy</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>AWS</strong></td>
                                    <td>Cloud hosting, data storage</td>
                                    <td>All data (encrypted at rest)</td>
                                    <td><a href="https://aws.amazon.com/privacy/" target="_blank">View Policy</a></td>
                                </tr>
                                <tr>
                                    <td><strong>Stripe</strong></td>
                                    <td>Payment processing</td>
                                    <td>Billing info, email (no card data stored by us)</td>
                                    <td><a href="https://stripe.com/privacy" target="_blank">View Policy</a></td>
                                </tr>
                                <tr>
                                    <td><strong>SendGrid</strong></td>
                                    <td>Email delivery</td>
                                    <td>Email address, name, email content</td>
                                    <td><a href="https://www.twilio.com/legal/privacy" target="_blank">View Policy</a></td>
                                </tr>
                                <tr>
                                    <td><strong>Sentry</strong></td>
                                    <td>Error monitoring</td>
                                    <td>Error logs (anonymized), user IDs (hashed)</td>
                                    <td><a href="https://sentry.io/privacy/" target="_blank">View Policy</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h4 class="mt-4"><i class="fas fa-balance-scale text-danger me-2"></i>Legal Requirements</h4>
                    <p>We may disclose your information when required by law or to:</p>
                    <ul>
                        <li>Comply with legal process (subpoena, court order, warrant)</li>
                        <li>Enforce our Terms of Service</li>
                        <li>Protect the rights, property, or safety of Intelligence Hub, our users, or the public</li>
                        <li>Prevent fraud or security threats</li>
                        <li>Cooperate with law enforcement investigations</li>
                    </ul>

                    <h4 class="mt-4"><i class="fas fa-building text-info me-2"></i>Business Transfers</h4>
                    <p>If Intelligence Hub is involved in a merger, acquisition, or sale of assets, your data may be transferred. We will notify you via email and/or prominent notice on our website before any transfer, and your data will remain subject to this Privacy Policy.</p>

                    <div class="alert alert-danger">
                        <i class="fas fa-ban me-2"></i>
                        <strong>We Never:</strong> Sell your personal information to data brokers, advertisers, or third parties. Your data is yours, and we respect that.
                    </div>
                </section>

                <!-- Security -->
                <section id="data-security" class="mb-5">
                    <h2 class="section-title">5. Security Measures</h2>
                    <p class="lead">We implement industry-standard security measures to protect your data:</p>

                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded h-100">
                                <i class="fas fa-lock fa-3x text-primary mb-3"></i>
                                <h5>Encryption</h5>
                                <p class="small mb-0">All data encrypted in transit (TLS 1.3) and at rest (AES-256)</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded h-100">
                                <i class="fas fa-user-shield fa-3x text-success mb-3"></i>
                                <h5>Access Control</h5>
                                <p class="small mb-0">Role-based access, 2FA available, password hashing (bcrypt)</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded h-100">
                                <i class="fas fa-server fa-3x text-info mb-3"></i>
                                <h5>Infrastructure</h5>
                                <p class="small mb-0">SOC 2 compliant hosting, regular backups, DDoS protection</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded h-100">
                                <i class="fas fa-bug fa-3x text-warning mb-3"></i>
                                <h5>Monitoring</h5>
                                <p class="small mb-0">24/7 security monitoring, intrusion detection, vulnerability scanning</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded h-100">
                                <i class="fas fa-graduation-cap fa-3x text-danger mb-3"></i>
                                <h5>Training</h5>
                                <p class="small mb-0">Staff security awareness training, background checks, NDAs</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded h-100">
                                <i class="fas fa-clipboard-check fa-3x text-secondary mb-3"></i>
                                <h5>Audits</h5>
                                <p class="small mb-0">Annual security audits, penetration testing, compliance reviews</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Data Breach Response:</strong> In the unlikely event of a data breach, we will notify affected users within 72 hours and report to relevant authorities as required by GDPR and other regulations.
                    </div>
                </section>

                <!-- Cookies -->
                <section id="cookies" class="mb-5">
                    <h2 class="section-title">6. Cookies & Tracking Technologies</h2>
                    <p>We use cookies and similar technologies to improve your experience:</p>

                    <h4 class="mt-4">Types of Cookies We Use:</h4>
                    <div class="accordion" id="cookiesAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#essential">
                                    <i class="fas fa-check-circle text-success me-2"></i> Essential Cookies (Required)
                                </button>
                            </h2>
                            <div id="essential" class="accordion-collapse collapse show" data-bs-parent="#cookiesAccordion">
                                <div class="accordion-body">
                                    <p><strong>Purpose:</strong> These cookies are necessary for the website to function and cannot be disabled.</p>
                                    <ul>
                                        <li><code>session_id</code> - Maintains your login session (expires on browser close)</li>
                                        <li><code>csrf_token</code> - Protects against cross-site request forgery attacks</li>
                                        <li><code>auth_token</code> - Remembers your authentication state (7 days)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#analytics">
                                    <i class="fas fa-chart-bar text-primary me-2"></i> Analytics Cookies (Optional)
                                </button>
                            </h2>
                            <div id="analytics" class="accordion-collapse collapse" data-bs-parent="#cookiesAccordion">
                                <div class="accordion-body">
                                    <p><strong>Purpose:</strong> Help us understand how users interact with our platform (anonymized data).</p>
                                    <ul>
                                        <li><code>_ga</code> - Google Analytics user ID (anonymized, 2 years)</li>
                                        <li><code>_gid</code> - Google Analytics session ID (24 hours)</li>
                                        <li><code>usage_stats</code> - Feature usage tracking (anonymized, 1 year)</li>
                                    </ul>
                                    <p class="mb-0"><strong>Control:</strong> You can opt-out in <a href="/dashboard/settings">Settings</a> or use browser Do Not Track.</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#preferences">
                                    <i class="fas fa-sliders-h text-info me-2"></i> Preference Cookies (Optional)
                                </button>
                            </h2>
                            <div id="preferences" class="accordion-collapse collapse" data-bs-parent="#cookiesAccordion">
                                <div class="accordion-body">
                                    <p><strong>Purpose:</strong> Remember your settings and preferences.</p>
                                    <ul>
                                        <li><code>theme</code> - UI theme preference (light/dark, 1 year)</li>
                                        <li><code>language</code> - Language preference (1 year)</li>
                                        <li><code>sidebar_collapsed</code> - Sidebar state (1 year)</li>
                                        <li><code>table_page_size</code> - Table rows per page (1 year)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Managing Cookies:</h5>
                        <p>You can control cookie settings in your browser. Note that disabling essential cookies may affect website functionality:</p>
                        <ul>
                            <li><strong>Chrome:</strong> Settings → Privacy and Security → Cookies and other site data</li>
                            <li><strong>Firefox:</strong> Options → Privacy & Security → Cookies and Site Data</li>
                            <li><strong>Safari:</strong> Preferences → Privacy → Manage Website Data</li>
                            <li><strong>Edge:</strong> Settings → Cookies and site permissions</li>
                        </ul>
                    </div>
                </section>

                <!-- User Rights (GDPR) -->
                <section id="user-rights" class="mb-5">
                    <h2 class="section-title">7. Your Rights (GDPR Compliance)</h2>
                    <p class="lead">Under GDPR and other privacy laws, you have the following rights:</p>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Right</th>
                                    <th>Description</th>
                                    <th style="width: 20%;">How to Exercise</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong><i class="fas fa-eye text-primary me-2"></i>Right to Access</strong></td>
                                    <td>Request a copy of all personal data we hold about you</td>
                                    <td><button class="btn btn-sm btn-outline-primary" onclick="requestDataExport()">Request Export</button></td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-edit text-success me-2"></i>Right to Rectification</strong></td>
                                    <td>Correct inaccurate or incomplete personal data</td>
                                    <td><a href="/dashboard/settings">Update in Settings</a></td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-trash text-danger me-2"></i>Right to Erasure</strong></td>
                                    <td>Request deletion of your personal data ("right to be forgotten")</td>
                                    <td><button class="btn btn-sm btn-outline-danger" onclick="requestDataDeletion()">Request Deletion</button></td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-ban text-warning me-2"></i>Right to Restriction</strong></td>
                                    <td>Limit how we process your data in certain circumstances</td>
                                    <td><a href="/dashboard/admin/pages-v2/support.php">Contact Support</a></td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-exchange-alt text-info me-2"></i>Right to Portability</strong></td>
                                    <td>Receive your data in machine-readable format (JSON/CSV)</td>
                                    <td><button class="btn btn-sm btn-outline-info" onclick="requestDataExport('portable')">Export Data</button></td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-hand-paper text-secondary me-2"></i>Right to Object</strong></td>
                                    <td>Object to processing based on legitimate interests or direct marketing</td>
                                    <td><a href="/dashboard/settings">Opt-out in Settings</a></td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-robot text-dark me-2"></i>Automated Decisions</strong></td>
                                    <td>Object to decisions made solely by automated processing</td>
                                    <td><em>We don't use automated decision-making</em></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-success">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Response Time:</strong> We will respond to all requests within 30 days (GDPR requirement). For complex requests, we may extend this by an additional 60 days and will inform you.
                    </div>

                    <h5 class="mt-4">Supervisory Authority:</h5>
                    <p>You have the right to lodge a complaint with your local data protection authority if you believe we have violated your privacy rights:</p>
                    <ul>
                        <li><strong>EU:</strong> Contact your national Data Protection Authority (<a href="https://edpb.europa.eu/about-edpb/board/members_en" target="_blank">Find your DPA</a>)</li>
                        <li><strong>UK:</strong> Information Commissioner's Office (ICO) - <a href="https://ico.org.uk" target="_blank">ico.org.uk</a></li>
                        <li><strong>California:</strong> California Attorney General - <a href="https://oag.ca.gov" target="_blank">oag.ca.gov</a></li>
                    </ul>
                </section>

                <!-- Data Retention -->
                <section id="data-retention" class="mb-5">
                    <h2 class="section-title">8. Data Retention</h2>
                    <p>We retain your data only as long as necessary for the purposes outlined in this policy:</p>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Data Type</th>
                                    <th>Retention Period</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Active account data</td>
                                    <td>Duration of account + 30 days after closure</td>
                                    <td>Service provision, user convenience</td>
                                </tr>
                                <tr>
                                    <td>Scan results & code metrics</td>
                                    <td>2 years (or until project deleted)</td>
                                    <td>Historical analysis, trend tracking</td>
                                </tr>
                                <tr>
                                    <td>Activity logs</td>
                                    <td>90 days (anonymized after 30 days)</td>
                                    <td>Security monitoring, debugging</td>
                                </tr>
                                <tr>
                                    <td>Billing records</td>
                                    <td>7 years</td>
                                    <td>Legal and tax requirements</td>
                                </tr>
                                <tr>
                                    <td>Support tickets</td>
                                    <td>3 years</td>
                                    <td>Service improvement, dispute resolution</td>
                                </tr>
                                <tr>
                                    <td>Marketing data (if opted in)</td>
                                    <td>Until opt-out + 30 days</td>
                                    <td>Compliance with unsubscribe requests</td>
                                </tr>
                                <tr>
                                    <td>Backup data</td>
                                    <td>90 days (encrypted)</td>
                                    <td>Disaster recovery</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-4">Data Deletion Process:</h5>
                    <ol>
                        <li><strong>Request Submission:</strong> Submit deletion request via Settings or Support</li>
                        <li><strong>Verification:</strong> We verify your identity (2-3 business days)</li>
                        <li><strong>Grace Period:</strong> 30-day grace period (can cancel deletion request)</li>
                        <li><strong>Deletion:</strong> All data permanently deleted from production systems</li>
                        <li><strong>Backups:</strong> Data removed from backups within 90 days</li>
                        <li><strong>Confirmation:</strong> Email confirmation sent upon completion</li>
                    </ol>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> Some data may be retained for longer periods if required by law (e.g., financial records) or for legitimate legal purposes (e.g., defending against lawsuits).
                    </div>
                </section>

                <!-- International Transfers -->
                <section id="international" class="mb-5">
                    <h2 class="section-title">9. International Data Transfers</h2>
                    <p>Intelligence Hub operates globally. Your data may be transferred to and processed in countries other than your own:</p>

                    <h5>Primary Data Locations:</h5>
                    <ul>
                        <li><strong>United States:</strong> AWS us-east-1 (primary hosting)</li>
                        <li><strong>European Union:</strong> AWS eu-west-1 (for EU customers, optional)</li>
                        <li><strong>United Kingdom:</strong> AWS eu-west-2 (for UK customers, optional)</li>
                    </ul>

                    <h5 class="mt-4">EU Data Protection:</h5>
                    <p>For transfers from the EU/EEA, we rely on the following mechanisms:</p>
                    <ul>
                        <li><strong>Standard Contractual Clauses (SCCs):</strong> We use EU-approved SCCs with all service providers</li>
                        <li><strong>Adequacy Decisions:</strong> We transfer data only to countries deemed adequate by the European Commission where possible</li>
                        <li><strong>Additional Safeguards:</strong> Encryption in transit and at rest, access controls, regular audits</li>
                    </ul>

                    <div class="alert alert-info">
                        <i class="fas fa-globe me-2"></i>
                        <strong>EU Customers:</strong> You can request that your data be stored exclusively in EU data centers. Contact support to enable EU-only hosting.
                    </div>
                </section>

                <!-- Children's Privacy -->
                <section id="children" class="mb-5">
                    <h2 class="section-title">10. Children's Privacy</h2>
                    <p>Intelligence Hub is not intended for use by individuals under the age of 16 (or the applicable age of digital consent in your jurisdiction).</p>

                    <div class="alert alert-danger">
                        <i class="fas fa-child me-2"></i>
                        <strong>Age Restriction:</strong> We do not knowingly collect personal information from children under 16. If you believe we have inadvertently collected such information, please contact us immediately at <a href="mailto:privacy@intelligencehub.com">privacy@intelligencehub.com</a>, and we will delete it within 72 hours.
                    </div>

                    <p><strong>For Parents/Guardians:</strong> If you discover that your child has provided personal information to us, you have the right to request deletion. We will verify your identity and relationship to the child before processing the request.</p>
                </section>

                <!-- Policy Updates -->
                <section id="updates" class="mb-5">
                    <h2 class="section-title">11. Policy Updates</h2>
                    <p>We may update this Privacy Policy from time to time to reflect changes in our practices or legal requirements.</p>

                    <h5>How We Notify You:</h5>
                    <ul>
                        <li><strong>Email Notification:</strong> For material changes, we'll send an email 30 days before the new policy takes effect</li>
                        <li><strong>In-App Notification:</strong> Prominent banner on the dashboard</li>
                        <li><strong>Website Notice:</strong> Updated "Last Modified" date at the top of this page</li>
                        <li><strong>Version History:</strong> <button class="btn btn-sm btn-outline-secondary" onclick="showVersionHistory()">View Change History</button></li>
                    </ul>

                    <p><strong>Your Options:</strong> If you disagree with the updated policy, you may close your account before the changes take effect. Continued use after the effective date constitutes acceptance of the new terms.</p>

                    <h5 class="mt-4">Recent Changes:</h5>
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Version 2.0.0 - October 31, 2025</h6>
                                <small class="text-muted">Current</small>
                            </div>
                            <p class="mb-1">Added GDPR compliance details, enhanced cookie policy, expanded data retention section</p>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Version 1.5.0 - June 15, 2025</h6>
                                <small class="text-muted">Previous</small>
                            </div>
                            <p class="mb-1">Updated third-party service providers list, clarified data sharing practices</p>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Version 1.0.0 - January 1, 2025</h6>
                                <small class="text-muted">Initial</small>
                            </div>
                            <p class="mb-1">Initial privacy policy published</p>
                        </div>
                    </div>
                </section>

                <!-- Contact -->
                <section id="contact" class="mb-5">
                    <h2 class="section-title">12. Contact Us</h2>
                    <p>If you have questions, concerns, or requests regarding this Privacy Policy or your personal data:</p>

                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                                    <h5>Data Protection Officer</h5>
                                    <p class="mb-0"><a href="mailto:dpo@intelligencehub.com">dpo@intelligencehub.com</a></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-envelope fa-3x text-success mb-3"></i>
                                    <h5>Privacy Inquiries</h5>
                                    <p class="mb-0"><a href="mailto:privacy@intelligencehub.com">privacy@intelligencehub.com</a></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-headset fa-3x text-info mb-3"></i>
                                    <h5>General Support</h5>
                                    <p class="mb-0"><a href="/dashboard/admin/pages-v2/support.php">Support Center</a></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-4">Mailing Address:</h5>
                    <address>
                        <strong>Intelligence Hub, Inc.</strong><br>
                        Attn: Privacy Department<br>
                        123 Tech Boulevard, Suite 500<br>
                        San Francisco, CA 94105<br>
                        United States
                    </address>

                    <p><strong>Response Time:</strong> We typically respond to privacy inquiries within 3 business days, with full resolution within 30 days as required by GDPR.</p>
                </section>

                <!-- Acknowledgment Section -->
                <section id="acknowledgment" class="mt-5 pt-4 border-top">
                    <h3 class="mb-3">Policy Acknowledgment</h3>
                    <div class="alert alert-light border">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="privacyAcknowledge" onchange="enableAcceptButton()">
                            <label class="form-check-label" for="privacyAcknowledge">
                                I have read and understood the Intelligence Hub Privacy Policy (Version <?= htmlspecialchars($policy_version) ?>)
                            </label>
                        </div>
                        <button id="acceptPolicyBtn" class="btn btn-primary mt-3" disabled onclick="acceptPolicy()">
                            <i class="fas fa-check me-2"></i>Accept Privacy Policy
                        </button>
                        <small class="text-muted d-block mt-2">Last accepted: <span id="lastAcceptedDate">Never</span></small>
                    </div>
                </section>

            </div>
        </div>
    </div>
</div>

<script>
// Smooth scroll for TOC
document.querySelectorAll('#privacy-toc a').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Update active state
            document.querySelectorAll('#privacy-toc a').forEach(a => a.classList.remove('active'));
            this.classList.add('active');
        }
    });
});

// Highlight active section on scroll
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('section[id]');
    let currentSection = '';

    sections.forEach(section => {
        const sectionTop = section.offsetTop - 100;
        if (window.pageYOffset >= sectionTop) {
            currentSection = section.getAttribute('id');
        }
    });

    document.querySelectorAll('#privacy-toc a').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + currentSection) {
            link.classList.add('active');
        }
    });
});

// Enable accept button
function enableAcceptButton() {
    document.getElementById('acceptPolicyBtn').disabled = !document.getElementById('privacyAcknowledge').checked;
}

// Accept policy
function acceptPolicy() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/api/accept-privacy-policy', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const now = new Date().toLocaleString();
            document.getElementById('lastAcceptedDate').textContent = now;
            DashboardApp.showAlert('success', 'Privacy policy acceptance recorded. Thank you!');
        }
    };
    xhr.send(JSON.stringify({ version: '<?= $policy_version ?>' }));
}

// Request data export
function requestDataExport(format = 'full') {
    if (confirm('Request a copy of all your personal data? You will receive a download link via email within 24 hours.')) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/request-data-export', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onload = function() {
            if (xhr.status === 200) {
                DashboardApp.showAlert('success', 'Data export request submitted. Check your email in 24 hours.');
            }
        };
        xhr.send(JSON.stringify({ format: format }));
    }
}

// Request data deletion
function requestDataDeletion() {
    if (confirm('WARNING: This will permanently delete your account and all associated data after a 30-day grace period. This action cannot be undone. Continue?')) {
        if (confirm('Are you absolutely sure? Type DELETE in the next prompt to confirm.')) {
            const confirmation = prompt('Type DELETE to confirm account deletion:');
            if (confirmation === 'DELETE') {
                window.location.href = '/dashboard/settings/delete-account';
            }
        }
    }
}

// Download policy as PDF
function downloadPolicy() {
    DashboardApp.showAlert('info', 'PDF generation in progress...');
    // In production, this would trigger PDF generation
    setTimeout(() => {
        window.print(); // Fallback to browser print
    }, 500);
}

// Show version history
function showVersionHistory() {
    DashboardApp.showAlert('info', 'Full version history available in our <a href="/docs/privacy-changelog" target="_blank">Privacy Policy Changelog</a>');
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Check if user has previously accepted
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '/api/check-privacy-acceptance', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const data = JSON.parse(xhr.responseText);
            if (data.accepted) {
                document.getElementById('lastAcceptedDate').textContent = new Date(data.accepted_at).toLocaleString();
            }
        }
    };
    xhr.send();

    // Initialize DashboardApp if available
    if (typeof DashboardApp !== 'undefined' && typeof DashboardApp.init === 'function') {
        DashboardApp.init();
    }
});
</script>

<?php require_once __DIR__ . '/../includes-v2/footer.php'; ?>
