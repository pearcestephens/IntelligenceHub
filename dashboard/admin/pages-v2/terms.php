<?php
/**
 * Terms of Service Page V2
 * Comprehensive terms and conditions for platform usage
 *
 * Features:
 * - Table of contents with anchor navigation
 * - Service description and scope
 * - User rights and responsibilities
 * - Account terms and acceptable use policy
 * - Intellectual property rights
 * - Liability limitations and disclaimers
 * - Termination and suspension policies
 * - Dispute resolution and governing law
 * - Agreement acceptance tracking
 * - Version history and change log
 * - Print and download options
 * - Digital signature flow
 *
 * @package CIS_Intelligence_Dashboard
 * @subpackage Legal
 * @version 2.0.0
 */

declare(strict_types=1);

// Page configuration
$page_title = 'Terms of Service';
$page_subtitle = 'Legal agreement governing your use of Intelligence Hub';
$current_page = 'terms';

// Terms metadata
$terms_version = '2.0.0';
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
                Version <?= htmlspecialchars($terms_version) ?>
            </small>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print Terms
            </button>
            <button class="btn btn-outline-secondary" onclick="downloadTerms()">
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
                <nav id="terms-toc" class="nav flex-column">
                    <a class="nav-link active" href="#acceptance">1. Acceptance of Terms</a>
                    <a class="nav-link" href="#service-description">2. Service Description</a>
                    <a class="nav-link" href="#account">3. Account Registration</a>
                    <a class="nav-link" href="#usage-terms">4. Usage Terms</a>
                    <a class="nav-link" href="#user-responsibilities">5. User Responsibilities</a>
                    <a class="nav-link" href="#intellectual-property">6. Intellectual Property</a>
                    <a class="nav-link" href="#service-availability">7. Service Availability</a>
                    <a class="nav-link" href="#fees">8. Fees & Payment</a>
                    <a class="nav-link" href="#warranties">9. Warranties & Disclaimers</a>
                    <a class="nav-link" href="#liability">10. Limitation of Liability</a>
                    <a class="nav-link" href="#indemnification">11. Indemnification</a>
                    <a class="nav-link" href="#termination">12. Termination</a>
                    <a class="nav-link" href="#dispute-resolution">13. Dispute Resolution</a>
                    <a class="nav-link" href="#modifications">14. Modifications to Terms</a>
                    <a class="nav-link" href="#miscellaneous">15. Miscellaneous</a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body p-4">

                <!-- Acceptance -->
                <section id="acceptance" class="mb-5">
                    <h2 class="section-title">1. Acceptance of Terms</h2>
                    <p class="lead">Welcome to Intelligence Hub. By accessing or using our platform, you agree to be bound by these Terms of Service.</p>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Binding Agreement:</strong> These Terms constitute a legally binding agreement between you ("User," "you," or "your") and Intelligence Hub, Inc. ("Intelligence Hub," "we," "us," or "our"). If you do not agree to these Terms, you may not access or use our services.
                    </div>

                    <h5 class="mt-4">Who May Use Our Services:</h5>
                    <ul>
                        <li><strong>Age Requirement:</strong> You must be at least 16 years old (or the age of legal consent in your jurisdiction) to use Intelligence Hub</li>
                        <li><strong>Legal Capacity:</strong> You must have the legal authority to enter into this agreement</li>
                        <li><strong>Business Accounts:</strong> If registering on behalf of an organization, you represent that you have authority to bind that entity to these Terms</li>
                        <li><strong>Compliance:</strong> You must not be prohibited from receiving services under applicable laws</li>
                    </ul>

                    <h5 class="mt-4">Agreement to Terms:</h5>
                    <p>By creating an account, clicking "I Agree," or using any part of our services, you acknowledge that you have read, understood, and agree to be bound by:</p>
                    <ol>
                        <li>These Terms of Service</li>
                        <li>Our <a href="/dashboard/admin/pages-v2/privacy.php">Privacy Policy</a></li>
                        <li>Our Acceptable Use Policy (embedded in Section 4)</li>
                        <li>Any additional terms specific to certain features or services</li>
                    </ol>
                </section>

                <!-- Service Description -->
                <section id="service-description" class="mb-5">
                    <h2 class="section-title">2. Service Description</h2>
                    <p>Intelligence Hub is a code quality and intelligence platform that provides:</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-search text-primary me-2"></i>Code Analysis</h5>
                                    <ul class="mb-0">
                                        <li>Automated code scanning and quality checks</li>
                                        <li>Rule-based validation and best practices</li>
                                        <li>Violation detection and reporting</li>
                                        <li>Code metrics and health scoring</li>
                                        <li>Dependency analysis</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-chart-line text-success me-2"></i>Intelligence Dashboard</h5>
                                    <ul class="mb-0">
                                        <li>Real-time metrics and visualizations</li>
                                        <li>Historical trend analysis</li>
                                        <li>Team collaboration tools</li>
                                        <li>Customizable reporting</li>
                                        <li>API access for integrations</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-info h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-users text-info me-2"></i>Team Management</h5>
                                    <ul class="mb-0">
                                        <li>Multi-user access with role-based permissions</li>
                                        <li>Project organization and segmentation</li>
                                        <li>Activity tracking and audit logs</li>
                                        <li>Business unit categorization</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-warning h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-cog text-warning me-2"></i>Configuration</h5>
                                    <ul class="mb-0">
                                        <li>Custom rule creation and modification</li>
                                        <li>Scan scheduling and automation</li>
                                        <li>Threshold and alert configuration</li>
                                        <li>Export and integration capabilities</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Service Evolution:</strong> We continuously improve Intelligence Hub and may add, modify, or discontinue features. We will provide reasonable notice for significant changes that materially affect your use of the service.
                    </div>

                    <h5 class="mt-4">What We Do NOT Provide:</h5>
                    <ul>
                        <li>Source code hosting or version control (use GitHub, GitLab, etc.)</li>
                        <li>Code compilation or execution environments</li>
                        <li>Legal advice regarding code licensing or compliance</li>
                        <li>Guaranteed detection of all security vulnerabilities</li>
                        <li>Automated code fixes (we identify issues, you fix them)</li>
                    </ul>
                </section>

                <!-- Account Registration -->
                <section id="account" class="mb-5">
                    <h2 class="section-title">3. Account Registration & Security</h2>

                    <h5>Account Creation:</h5>
                    <p>To use Intelligence Hub, you must create an account by providing:</p>
                    <ul>
                        <li>Valid email address</li>
                        <li>Secure password (minimum 12 characters, must include uppercase, lowercase, number, and symbol)</li>
                        <li>Full name and organization (if applicable)</li>
                        <li>Acceptance of these Terms and Privacy Policy</li>
                    </ul>

                    <h5 class="mt-4">Account Security - Your Responsibilities:</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Requirement</th>
                                    <th>Your Obligation</th>
                                    <th>Consequences of Non-Compliance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Password Protection</strong></td>
                                    <td>Keep password confidential, do not share with anyone</td>
                                    <td>You are liable for all activities under your account</td>
                                </tr>
                                <tr>
                                    <td><strong>Accurate Information</strong></td>
                                    <td>Provide truthful, accurate, and complete information</td>
                                    <td>Account suspension or termination</td>
                                </tr>
                                <tr>
                                    <td><strong>Account Monitoring</strong></td>
                                    <td>Notify us immediately of unauthorized access</td>
                                    <td>Delayed notification may limit liability protection</td>
                                </tr>
                                <tr>
                                    <td><strong>One Account Per User</strong></td>
                                    <td>Do not create multiple accounts without authorization</td>
                                    <td>All duplicate accounts may be terminated</td>
                                </tr>
                                <tr>
                                    <td><strong>Two-Factor Authentication</strong></td>
                                    <td>Enable 2FA when available (strongly recommended)</td>
                                    <td>Enhanced security; may be required for Enterprise plans</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Important:</strong> You are responsible for all activities that occur under your account, even if performed by others. If you suspect unauthorized access, change your password immediately and contact <a href="mailto:security@intelligencehub.com">security@intelligencehub.com</a>.
                    </div>

                    <h5 class="mt-4">Account Types & Eligibility:</h5>
                    <ul>
                        <li><strong>Individual Accounts:</strong> For personal use or small projects</li>
                        <li><strong>Team Accounts:</strong> For organizations with multiple users</li>
                        <li><strong>Enterprise Accounts:</strong> For large organizations with advanced needs (requires contract)</li>
                    </ul>

                    <p><strong>Account Transfer:</strong> Accounts are non-transferable except with our prior written consent or as part of a business transfer (merger, acquisition).</p>
                </section>

                <!-- Usage Terms -->
                <section id="usage-terms" class="mb-5">
                    <h2 class="section-title">4. Acceptable Use Policy</h2>
                    <p class="lead">You agree to use Intelligence Hub only for lawful purposes and in accordance with these Terms.</p>

                    <h5>Permitted Uses:</h5>
                    <ul class="text-success">
                        <li><i class="fas fa-check-circle me-2"></i>Analyze code you own or have permission to analyze</li>
                        <li><i class="fas fa-check-circle me-2"></i>Generate reports and metrics for your projects</li>
                        <li><i class="fas fa-check-circle me-2"></i>Collaborate with authorized team members</li>
                        <li><i class="fas fa-check-circle me-2"></i>Integrate with other services via our API</li>
                        <li><i class="fas fa-check-circle me-2"></i>Export data for backup and analysis purposes</li>
                    </ul>

                    <h5 class="mt-4">Prohibited Activities:</h5>
                    <p>You may NOT:</p>
                    <div class="accordion" id="prohibitedAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#prohibited-illegal">
                                    <i class="fas fa-ban text-danger me-2"></i> Illegal or Harmful Activities
                                </button>
                            </h2>
                            <div id="prohibited-illegal" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Violate any applicable laws or regulations</li>
                                        <li>Upload or analyze code containing malware, viruses, or malicious code</li>
                                        <li>Use the service to facilitate illegal activities</li>
                                        <li>Attempt to gain unauthorized access to other users' accounts or data</li>
                                        <li>Harass, threaten, or harm others</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#prohibited-ip">
                                    <i class="fas fa-copyright text-danger me-2"></i> Intellectual Property Violations
                                </button>
                            </h2>
                            <div id="prohibited-ip" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Analyze code you don't have rights or permission to use</li>
                                        <li>Infringe on others' intellectual property rights</li>
                                        <li>Reverse engineer, decompile, or disassemble our software</li>
                                        <li>Remove or modify any proprietary notices or labels</li>
                                        <li>Use our trademarks without written permission</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#prohibited-abuse">
                                    <i class="fas fa-exclamation-triangle text-danger me-2"></i> Service Abuse
                                </button>
                            </h2>
                            <div id="prohibited-abuse" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Exceed rate limits or usage quotas for your plan tier</li>
                                        <li>Use automated scripts to create accounts or generate requests (except via our API)</li>
                                        <li>Attempt to overload, crash, or disrupt our servers</li>
                                        <li>Probe, scan, or test vulnerabilities without authorization</li>
                                        <li>Interfere with other users' access to the service</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#prohibited-commercial">
                                    <i class="fas fa-dollar-sign text-danger me-2"></i> Unauthorized Commercial Use
                                </button>
                            </h2>
                            <div id="prohibited-commercial" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Resell or redistribute our services without authorization</li>
                                        <li>Use free tier for commercial purposes requiring paid plan</li>
                                        <li>Scrape or extract data for competitive analysis</li>
                                        <li>Build competing products using our service</li>
                                        <li>Share account credentials among multiple users (except authorized team members)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-gavel me-2"></i>
                        <strong>Enforcement:</strong> Violations of this Acceptable Use Policy may result in immediate account suspension or termination without refund, and may expose you to legal liability.
                    </div>
                </section>

                <!-- User Responsibilities -->
                <section id="user-responsibilities" class="mb-5">
                    <h2 class="section-title">5. User Responsibilities</h2>

                    <h5>Your Code & Data:</h5>
                    <ul>
                        <li><strong>Ownership:</strong> You retain all rights to code you upload or analyze</li>
                        <li><strong>Licensing:</strong> You are responsible for ensuring you have rights to analyze all code</li>
                        <li><strong>Backups:</strong> Maintain your own backups; we are not a backup service</li>
                        <li><strong>Accuracy:</strong> Verify scan results independently; our service is a tool, not a guarantee</li>
                        <li><strong>Compliance:</strong> Ensure your use complies with all applicable laws and regulations</li>
                    </ul>

                    <h5 class="mt-4">Team Management:</h5>
                    <p>If you manage a team account, you are responsible for:</p>
                    <ul>
                        <li>Actions of all team members under your account</li>
                        <li>Proper permission assignment and access control</li>
                        <li>Promptly removing access for departing team members</li>
                        <li>Monitoring team activity and ensuring compliance with these Terms</li>
                        <li>Payment for all authorized and unauthorized uses under your account</li>
                    </ul>

                    <h5 class="mt-4">Third-Party Integrations:</h5>
                    <p>If you integrate Intelligence Hub with third-party services:</p>
                    <ul>
                        <li>You are responsible for configuring integrations securely</li>
                        <li>We are not responsible for third-party service availability or security</li>
                        <li>Review third-party terms and privacy policies separately</li>
                        <li>Revoke integration access when no longer needed</li>
                    </ul>

                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Best Practice:</strong> Regularly review team member access, rotate API keys, and audit integration permissions to maintain security.
                    </div>
                </section>

                <!-- Intellectual Property -->
                <section id="intellectual-property" class="mb-5">
                    <h2 class="section-title">6. Intellectual Property Rights</h2>

                    <h5>Our Intellectual Property:</h5>
                    <p>Intelligence Hub and all related materials (software, documentation, logos, design) are owned by Intelligence Hub, Inc. and protected by:</p>
                    <ul>
                        <li>Copyright laws</li>
                        <li>Trademark laws</li>
                        <li>Patent laws</li>
                        <li>Trade secret laws</li>
                        <li>Other intellectual property rights</li>
                    </ul>

                    <h5 class="mt-4">License Grant to You:</h5>
                    <p>Subject to your compliance with these Terms, we grant you a limited, non-exclusive, non-transferable, revocable license to:</p>
                    <ul>
                        <li>Access and use Intelligence Hub for your internal business purposes</li>
                        <li>Use our API in accordance with our API Terms</li>
                        <li>Display our logo/badge on your website (with attribution)</li>
                    </ul>

                    <p><strong>This license does NOT permit you to:</strong></p>
                    <ul>
                        <li>Modify, adapt, or create derivative works</li>
                        <li>Reverse engineer or decompile our software</li>
                        <li>Rent, lease, sell, or sublicense the service</li>
                        <li>Remove or alter any proprietary notices</li>
                    </ul>

                    <h5 class="mt-4">Your Content - License Grant to Us:</h5>
                    <p>By uploading code or content to Intelligence Hub, you grant us a limited license to:</p>
                    <ul>
                        <li>Process and analyze your code to provide the service</li>
                        <li>Store and transmit your content as necessary for service operation</li>
                        <li>Create anonymized, aggregated data for service improvement</li>
                        <li>Display scan results and metrics to authorized users</li>
                    </ul>

                    <div class="alert alert-success">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Your Code Stays Yours:</strong> We do not claim ownership of your code. The license you grant us is solely for providing and improving our services. We will never use your code for any other purpose without your consent.
                    </div>

                    <h5 class="mt-4">Feedback & Suggestions:</h5>
                    <p>If you provide feedback, suggestions, or ideas for improvements ("Feedback"), you grant us an unrestricted, perpetual license to use that Feedback for any purpose without compensation or attribution. We may (but are not obligated to) implement Feedback in future versions.</p>

                    <h5 class="mt-4">Copyright Infringement Claims (DMCA):</h5>
                    <p>If you believe content on Intelligence Hub infringes your copyright, please contact our designated agent at <a href="mailto:dmca@intelligencehub.com">dmca@intelligencehub.com</a> with:</p>
                    <ul>
                        <li>Identification of the copyrighted work</li>
                        <li>Location of the infringing material</li>
                        <li>Your contact information</li>
                        <li>Statement of good faith belief</li>
                        <li>Statement of accuracy under penalty of perjury</li>
                        <li>Physical or electronic signature</li>
                    </ul>
                </section>

                <!-- Service Availability -->
                <section id="service-availability" class="mb-5">
                    <h2 class="section-title">7. Service Availability & SLA</h2>

                    <h5>Uptime Targets:</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Plan Tier</th>
                                    <th>Target Uptime</th>
                                    <th>Scheduled Maintenance</th>
                                    <th>Support Response</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Free</strong></td>
                                    <td>Best effort (no guarantee)</td>
                                    <td>Unscheduled, with notice</td>
                                    <td>Community forum only</td>
                                </tr>
                                <tr>
                                    <td><strong>Pro</strong></td>
                                    <td>99.5% monthly</td>
                                    <td>Announced 48h in advance</td>
                                    <td>24-48 hours</td>
                                </tr>
                                <tr>
                                    <td><strong>Team</strong></td>
                                    <td>99.8% monthly</td>
                                    <td>Announced 72h in advance</td>
                                    <td>12-24 hours</td>
                                </tr>
                                <tr>
                                    <td><strong>Enterprise</strong></td>
                                    <td>99.95% monthly (SLA)</td>
                                    <td>Scheduled maintenance windows</td>
                                    <td>1-4 hours (priority)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-4">Maintenance & Downtime:</h5>
                    <ul>
                        <li><strong>Scheduled Maintenance:</strong> We perform regular maintenance, typically during low-traffic hours (weekends, late nights UTC)</li>
                        <li><strong>Emergency Maintenance:</strong> Unscheduled maintenance may occur for security patches or critical issues</li>
                        <li><strong>Notification:</strong> We will notify users via email and status page (<a href="https://status.intelligencehub.com" target="_blank">status.intelligencehub.com</a>)</li>
                        <li><strong>Downtime Credits:</strong> Enterprise customers may be eligible for service credits per SLA agreement</li>
                    </ul>

                    <h5 class="mt-4">Service Modifications:</h5>
                    <p>We reserve the right to:</p>
                    <ul>
                        <li>Modify, suspend, or discontinue any feature or service</li>
                        <li>Impose usage limits or restrictions</li>
                        <li>Update software and infrastructure</li>
                        <li>Change API endpoints or data formats (with deprecation notice)</li>
                    </ul>

                    <div class="alert alert-warning">
                        <i class="fas fa-tools me-2"></i>
                        <strong>No Guarantee:</strong> While we strive for high availability, we do not guarantee uninterrupted access. The service is provided "as is" and "as available" (see Section 9 for warranty disclaimers).
                    </div>

                    <h5 class="mt-4">Data Backup & Recovery:</h5>
                    <ul>
                        <li>We perform regular backups of user data</li>
                        <li>Backup retention: 30 days (90 days for Enterprise)</li>
                        <li>Data recovery requests may incur fees</li>
                        <li><strong>You are responsible for maintaining your own backups</strong></li>
                    </ul>
                </section>

                <!-- Fees & Payment -->
                <section id="fees" class="mb-5">
                    <h2 class="section-title">8. Fees & Payment Terms</h2>

                    <h5>Subscription Plans:</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Free</h5>
                                    <h3 class="text-primary">$0</h3>
                                    <p class="small">1 project, 10 scans/month</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Pro</h5>
                                    <h3 class="text-success">$49</h3>
                                    <p class="small">10 projects, unlimited scans</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Team</h5>
                                    <h3 class="text-info">$199</h3>
                                    <p class="small">50 projects, 10 users</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Enterprise</h5>
                                    <h3 class="text-warning">Custom</h3>
                                    <p class="small">Unlimited, dedicated support</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="mt-3"><small class="text-muted">Prices shown are monthly rates (USD). Annual billing available at 20% discount.</small></p>

                    <h5 class="mt-4">Payment Terms:</h5>
                    <ul>
                        <li><strong>Billing Cycle:</strong> Monthly or annual, billed in advance</li>
                        <li><strong>Payment Methods:</strong> Credit card, PayPal, bank transfer (Enterprise only)</li>
                        <li><strong>Automatic Renewal:</strong> Subscriptions renew automatically unless cancelled</li>
                        <li><strong>Failed Payments:</strong> Service may be suspended after 3 days; account terminated after 14 days</li>
                        <li><strong>Currency:</strong> All prices in USD; currency conversion fees may apply</li>
                        <li><strong>Taxes:</strong> Prices exclude applicable sales tax, VAT, or GST (added at checkout)</li>
                    </ul>

                    <h5 class="mt-4">Refund Policy:</h5>
                    <ul>
                        <li><strong>Free Plan:</strong> No refunds (free service)</li>
                        <li><strong>Monthly Subscriptions:</strong> Prorated refund within 7 days of charge</li>
                        <li><strong>Annual Subscriptions:</strong> Prorated refund within 30 days of initial charge only</li>
                        <li><strong>Mid-Month Cancellation:</strong> Service continues until end of billing period, no prorated refund</li>
                        <li><strong>Refund Processing:</strong> 5-10 business days to original payment method</li>
                    </ul>

                    <h5 class="mt-4">Price Changes:</h5>
                    <ul>
                        <li>We may change prices with 30 days' notice</li>
                        <li>Existing subscribers grandfathered at current price for one billing cycle</li>
                        <li>You may cancel before price increase takes effect</li>
                    </ul>

                    <div class="alert alert-info">
                        <i class="fas fa-credit-card me-2"></i>
                        <strong>Payment Processing:</strong> Payments are processed securely by Stripe. We do not store your full credit card information.
                    </div>
                </section>

                <!-- Warranties & Disclaimers -->
                <section id="warranties" class="mb-5">
                    <h2 class="section-title">9. Warranties & Disclaimers</h2>

                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>DISCLAIMER OF WARRANTIES</h5>
                        <p class="mb-0">INTELLIGENCE HUB IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT ANY WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO:</p>
                    </div>

                    <ul class="text-danger">
                        <li>WARRANTIES OF MERCHANTABILITY</li>
                        <li>FITNESS FOR A PARTICULAR PURPOSE</li>
                        <li>NON-INFRINGEMENT</li>
                        <li>TITLE</li>
                        <li>ACCURACY OR RELIABILITY OF RESULTS</li>
                        <li>UNINTERRUPTED OR ERROR-FREE OPERATION</li>
                        <li>SECURITY OR VIRUS-FREE OPERATION</li>
                    </ul>

                    <h5 class="mt-4">What This Means:</h5>
                    <ul>
                        <li><strong>No Guarantee of Results:</strong> We do not guarantee that our analysis will detect all issues, vulnerabilities, or bugs in your code</li>
                        <li><strong>False Positives/Negatives:</strong> Scan results may include false positives or miss actual issues</li>
                        <li><strong>No Security Guarantee:</strong> Use of Intelligence Hub does not guarantee your code is secure or compliant</li>
                        <li><strong>No Professional Advice:</strong> Our service is not a substitute for professional code review or security audit</li>
                        <li><strong>No Warranty on Uptime:</strong> Despite best efforts, we cannot guarantee 100% uptime or availability</li>
                        <li><strong>Third-Party Services:</strong> We are not responsible for third-party integrations or services</li>
                    </ul>

                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-balance-scale me-2"></i>
                        <strong>Legal Context:</strong> Some jurisdictions do not allow disclaimer of implied warranties. In such jurisdictions, these disclaimers may not apply to you, and you may have additional rights.
                    </div>

                    <h5 class="mt-4">Your Responsibilities:</h5>
                    <p>You acknowledge and agree that:</p>
                    <ul>
                        <li>You use Intelligence Hub at your own risk</li>
                        <li>You are responsible for verifying all scan results independently</li>
                        <li>You should not rely solely on our analysis for production deployments</li>
                        <li>You must implement your own security measures and testing</li>
                        <li>You should maintain regular backups of your code and data</li>
                    </ul>
                </section>

                <!-- Limitation of Liability -->
                <section id="liability" class="mb-5">
                    <h2 class="section-title">10. Limitation of Liability</h2>

                    <div class="alert alert-danger">
                        <h5><i class="fas fa-shield-alt me-2"></i>LIMITATION OF LIABILITY</h5>
                        <p>TO THE MAXIMUM EXTENT PERMITTED BY LAW, INTELLIGENCE HUB SHALL NOT BE LIABLE FOR:</p>
                    </div>

                    <ol class="text-danger">
                        <li><strong>INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES</strong></li>
                        <li><strong>LOSS OF PROFITS, REVENUE, DATA, OR USE</strong></li>
                        <li><strong>BUSINESS INTERRUPTION OR LOST BUSINESS OPPORTUNITIES</strong></li>
                        <li><strong>COST OF SUBSTITUTE SERVICES</strong></li>
                        <li><strong>DAMAGES ARISING FROM YOUR CODE OR DATA</strong></li>
                        <li><strong>UNAUTHORIZED ACCESS TO YOUR ACCOUNT</strong></li>
                        <li><strong>ERRORS OR INACCURACIES IN SCAN RESULTS</strong></li>
                        <li><strong>THIRD-PARTY CLAIMS RELATED TO YOUR USE</strong></li>
                    </ol>

                    <h5 class="mt-4">Liability Cap:</h5>
                    <p>In no event shall Intelligence Hub's total liability to you for all claims arising from or related to these Terms or the service exceed the greater of:</p>
                    <ul>
                        <li>The amount you paid to Intelligence Hub in the 12 months preceding the claim, OR</li>
                        <li>$100 USD</li>
                    </ul>

                    <h5 class="mt-4">Exceptions:</h5>
                    <p>The limitations above do NOT apply to:</p>
                    <ul>
                        <li>Liability for death or personal injury caused by our negligence</li>
                        <li>Liability for fraud or fraudulent misrepresentation</li>
                        <li>Any liability that cannot be excluded by law</li>
                    </ul>

                    <h5 class="mt-4">Basis of the Bargain:</h5>
                    <p class="small text-muted">You acknowledge that these limitations of liability are an essential element of these Terms and that Intelligence Hub would not provide the service at the current pricing without these limitations.</p>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Enterprise SLA:</strong> Enterprise customers with signed Service Level Agreements may have different liability provisions as specified in their contracts.
                    </div>
                </section>

                <!-- Indemnification -->
                <section id="indemnification" class="mb-5">
                    <h2 class="section-title">11. Indemnification</h2>
                    <p class="lead">You agree to defend, indemnify, and hold harmless Intelligence Hub and its officers, directors, employees, and agents from and against any claims, liabilities, damages, losses, and expenses arising out of or related to:</p>

                    <ul>
                        <li><strong>Your Use of the Service:</strong> Any use or misuse of Intelligence Hub by you or anyone using your account</li>
                        <li><strong>Your Content:</strong> Any code, data, or content you upload, analyze, or store</li>
                        <li><strong>Violation of Terms:</strong> Your breach of these Terms or any applicable law</li>
                        <li><strong>Infringement Claims:</strong> Claims that your content infringes third-party intellectual property rights</li>
                        <li><strong>Third-Party Claims:</strong> Claims by third parties arising from your actions</li>
                        <li><strong>Team Members:</strong> Actions of team members or users under your account</li>
                    </ul>

                    <h5 class="mt-4">Indemnification Process:</h5>
                    <ol>
                        <li>We will promptly notify you of any claim subject to indemnification</li>
                        <li>You will assume control of the defense (with our cooperation)</li>
                        <li>You will not settle any claim without our prior written consent</li>
                        <li>We reserve the right to participate in defense at our expense</li>
                    </ol>

                    <p><strong>Costs Covered:</strong> Your indemnification obligations include reasonable attorneys' fees, court costs, expert witness fees, and settlement amounts.</p>

                    <div class="alert alert-warning">
                        <i class="fas fa-gavel me-2"></i>
                        <strong>Example:</strong> If you upload stolen code and the rightful owner sues Intelligence Hub, you would be responsible for defending us and covering any damages.
                    </div>
                </section>

                <!-- Termination -->
                <section id="termination" class="mb-5">
                    <h2 class="section-title">12. Termination & Suspension</h2>

                    <h5>Termination by You:</h5>
                    <p>You may terminate your account at any time by:</p>
                    <ul>
                        <li>Using the account deletion option in Settings</li>
                        <li>Contacting support at <a href="mailto:support@intelligencehub.com">support@intelligencehub.com</a></li>
                        <li>Cancelling your subscription (service continues until end of billing period)</li>
                    </ul>

                    <h5 class="mt-4">Termination by Us:</h5>
                    <p>We may suspend or terminate your account immediately if:</p>
                    <ul>
                        <li>You violate these Terms or our Acceptable Use Policy</li>
                        <li>Your account is inactive for 12+ months</li>
                        <li>Payment fails and is not resolved within 14 days</li>
                        <li>We are required to do so by law</li>
                        <li>You engage in fraudulent or illegal activities</li>
                        <li>Your actions pose a security risk to other users</li>
                    </ul>

                    <h5 class="mt-4">Effect of Termination:</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>What Happens</th>
                                    <th>Timeline</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Service Access</strong></td>
                                    <td>Immediate termination of access</td>
                                    <td>Within minutes</td>
                                </tr>
                                <tr>
                                    <td><strong>Data Export</strong></td>
                                    <td>30-day grace period to export data</td>
                                    <td>30 days</td>
                                </tr>
                                <tr>
                                    <td><strong>Data Deletion</strong></td>
                                    <td>All data permanently deleted</td>
                                    <td>After 30 days</td>
                                </tr>
                                <tr>
                                    <td><strong>Refunds</strong></td>
                                    <td>Prorated refund (if eligible)</td>
                                    <td>5-10 business days</td>
                                </tr>
                                <tr>
                                    <td><strong>Billing</strong></td>
                                    <td>Automatic cancellation of subscription</td>
                                    <td>Immediate</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-4">Survival of Terms:</h5>
                    <p>The following sections survive termination:</p>
                    <ul>
                        <li>Intellectual Property Rights (Section 6)</li>
                        <li>Warranties & Disclaimers (Section 9)</li>
                        <li>Limitation of Liability (Section 10)</li>
                        <li>Indemnification (Section 11)</li>
                        <li>Dispute Resolution (Section 13)</li>
                        <li>Any provisions that by their nature should survive</li>
                    </ul>

                    <div class="alert alert-danger">
                        <i class="fas fa-database me-2"></i>
                        <strong>Data Retention Warning:</strong> After 30 days, your data is permanently deleted and cannot be recovered. Export all important data before termination!
                    </div>
                </section>

                <!-- Dispute Resolution -->
                <section id="dispute-resolution" class="mb-5">
                    <h2 class="section-title">13. Dispute Resolution & Governing Law</h2>

                    <h5>Governing Law:</h5>
                    <p>These Terms are governed by the laws of the State of California, United States, without regard to its conflict of law provisions.</p>

                    <h5 class="mt-4">Dispute Resolution Process:</h5>

                    <h6 class="mt-3">Step 1: Informal Negotiation (Required)</h6>
                    <p>Before filing any formal action, you must contact us at <a href="mailto:legal@intelligencehub.com">legal@intelligencehub.com</a> and attempt to resolve the dispute informally for at least 30 days.</p>

                    <h6 class="mt-3">Step 2: Binding Arbitration</h6>
                    <p>If informal resolution fails, any disputes will be resolved through binding arbitration by the American Arbitration Association (AAA) under its Commercial Arbitration Rules.</p>
                    <ul>
                        <li><strong>Location:</strong> San Francisco, California (or mutually agreed location)</li>
                        <li><strong>Arbitrator:</strong> Single arbitrator selected per AAA rules</li>
                        <li><strong>Costs:</strong> Each party pays their own costs unless arbitrator decides otherwise</li>
                        <li><strong>Award:</strong> Arbitrator's decision is final and binding</li>
                        <li><strong>Enforcement:</strong> Judgment may be entered in any court of competent jurisdiction</li>
                    </ul>

                    <h6 class="mt-3">Exceptions to Arbitration:</h6>
                    <p>The following may be brought in court:</p>
                    <ul>
                        <li>Claims in small claims court (if within jurisdiction limits)</li>
                        <li>Intellectual property disputes</li>
                        <li>Requests for injunctive or equitable relief</li>
                    </ul>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-users-slash me-2"></i>CLASS ACTION WAIVER</h6>
                        <p class="mb-0">YOU AND INTELLIGENCE HUB AGREE THAT DISPUTES WILL BE RESOLVED INDIVIDUALLY. YOU WAIVE ANY RIGHT TO PARTICIPATE IN A CLASS ACTION, COLLECTIVE ACTION, OR REPRESENTATIVE ACTION. THERE WILL BE NO CLASS ARBITRATION.</p>
                    </div>

                    <h5 class="mt-4">Jurisdiction & Venue:</h5>
                    <p>For matters not subject to arbitration, you agree to exclusive jurisdiction of the federal and state courts located in San Francisco County, California.</p>

                    <h5 class="mt-4">Limitation on Time to File:</h5>
                    <p>Any claim arising from these Terms must be filed within <strong>one (1) year</strong> of the date the claim arose. After one year, the claim is permanently barred.</p>

                    <h5 class="mt-4">Opt-Out of Arbitration:</h5>
                    <p>You may opt-out of arbitration by sending written notice to <a href="mailto:legal@intelligencehub.com">legal@intelligencehub.com</a> within <strong>30 days of account creation</strong>. Opt-out notice must include your name, email, and statement that you opt-out of arbitration.</p>
                </section>

                <!-- Modifications to Terms -->
                <section id="modifications" class="mb-5">
                    <h2 class="section-title">14. Modifications to Terms</h2>
                    <p>We may update these Terms from time to time. When we make material changes, we will notify you by:</p>

                    <ul>
                        <li><strong>Email:</strong> Notice sent to your registered email address (30 days before effective date for material changes)</li>
                        <li><strong>In-App Banner:</strong> Prominent notification on the dashboard</li>
                        <li><strong>Updated Date:</strong> "Last Updated" date at the top of this page</li>
                    </ul>

                    <h5 class="mt-4">Your Options:</h5>
                    <ul>
                        <li><strong>Accept:</strong> Continue using the service after the effective date (constitutes acceptance)</li>
                        <li><strong>Reject:</strong> Stop using the service and terminate your account before the effective date</li>
                    </ul>

                    <p><strong>Material Changes Definition:</strong> Changes that significantly affect your rights or obligations, such as:</p>
                    <ul>
                        <li>Changes to dispute resolution or arbitration</li>
                        <li>Significant changes to intellectual property rights</li>
                        <li>Material changes to liability limitations</li>
                        <li>Changes to data usage or privacy practices</li>
                        <li>Price increases exceeding 20%</li>
                    </ul>

                    <h5 class="mt-4">Version History:</h5>
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Version 2.0.0 - October 31, 2025</h6>
                                <span class="badge bg-success">Current</span>
                            </div>
                            <p class="mb-1">Major update: Added detailed SLA terms, enhanced dispute resolution, clarified IP rights</p>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Version 1.5.0 - June 15, 2025</h6>
                                <span class="badge bg-secondary">Previous</span>
                            </div>
                            <p class="mb-1">Updated payment terms, added annual billing options</p>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Version 1.0.0 - January 1, 2025</h6>
                                <span class="badge bg-secondary">Initial</span>
                            </div>
                            <p class="mb-1">Initial Terms of Service published</p>
                        </div>
                    </div>
                </section>

                <!-- Miscellaneous -->
                <section id="miscellaneous" class="mb-5">
                    <h2 class="section-title">15. Miscellaneous Provisions</h2>

                    <h5>Entire Agreement:</h5>
                    <p>These Terms, together with our Privacy Policy and any additional agreements, constitute the entire agreement between you and Intelligence Hub regarding the service and supersede all prior agreements.</p>

                    <h5 class="mt-4">Severability:</h5>
                    <p>If any provision of these Terms is found to be unenforceable, the remaining provisions will remain in full force and effect. The unenforceable provision will be modified to the minimum extent necessary to make it enforceable.</p>

                    <h5 class="mt-4">Waiver:</h5>
                    <p>Our failure to enforce any right or provision does not constitute a waiver of that right or provision. Any waiver must be in writing and signed by an authorized representative.</p>

                    <h5 class="mt-4">Assignment:</h5>
                    <p>You may not assign or transfer these Terms or your account without our written consent. We may assign these Terms to any affiliate or in connection with a merger, acquisition, or sale of assets.</p>

                    <h5 class="mt-4">Force Majeure:</h5>
                    <p>We are not liable for failures or delays caused by events beyond our reasonable control, including but not limited to: natural disasters, war, terrorism, riots, embargoes, acts of government, pandemic, internet failures, or hacker attacks.</p>

                    <h5 class="mt-4">Export Controls:</h5>
                    <p>You agree not to export or re-export Intelligence Hub or any data obtained from the service in violation of U.S. export control laws or the laws of the jurisdiction in which you obtained the service.</p>

                    <h5 class="mt-4">Government Use:</h5>
                    <p>If you are a U.S. government entity, Intelligence Hub is a "commercial item" and is licensed with only those rights as granted to all other users under these Terms.</p>

                    <h5 class="mt-4">Relationship:</h5>
                    <p>No agency, partnership, joint venture, or employment relationship is created between you and Intelligence Hub. You may not make commitments on behalf of Intelligence Hub.</p>

                    <h5 class="mt-4">Contact for Legal Notices:</h5>
                    <address class="mt-3">
                        <strong>Intelligence Hub, Inc.</strong><br>
                        Attn: Legal Department<br>
                        123 Tech Boulevard, Suite 500<br>
                        San Francisco, CA 94105<br>
                        United States<br>
                        Email: <a href="mailto:legal@intelligencehub.com">legal@intelligencehub.com</a>
                    </address>

                    <h5 class="mt-4">Questions About These Terms?</h5>
                    <p>Contact us at <a href="/dashboard/admin/pages-v2/support.php">Support Center</a> or email <a href="mailto:legal@intelligencehub.com">legal@intelligencehub.com</a></p>
                </section>

                <!-- Agreement Section -->
                <section id="agreement" class="mt-5 pt-4 border-top">
                    <h3 class="mb-3">Terms Agreement</h3>
                    <div class="alert alert-light border">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="termsAgree" onchange="enableAgreeButton()">
                            <label class="form-check-label" for="termsAgree">
                                I have read, understood, and agree to be bound by the Intelligence Hub Terms of Service (Version <?= htmlspecialchars($terms_version) ?>)
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="privacyAgree">
                            <label class="form-check-label" for="privacyAgree">
                                I have read and agree to the <a href="/dashboard/admin/pages-v2/privacy.php" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                        <button id="agreeTermsBtn" class="btn btn-primary mt-3" disabled onclick="agreeToTerms()">
                            <i class="fas fa-file-signature me-2"></i>I Agree to Terms & Privacy Policy
                        </button>
                        <small class="text-muted d-block mt-2">
                            Agreement Date: <span id="agreementDate">Not yet agreed</span><br>
                            <span id="signatureInfo"></span>
                        </small>
                    </div>
                </section>

            </div>
        </div>
    </div>
</div>

<script>
// Smooth scroll for TOC
document.querySelectorAll('#terms-toc a').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Update active state
            document.querySelectorAll('#terms-toc a').forEach(a => a.classList.remove('active'));
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

    document.querySelectorAll('#terms-toc a').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + currentSection) {
            link.classList.add('active');
        }
    });
});

// Enable agree button
function enableAgreeButton() {
    const termsChecked = document.getElementById('termsAgree').checked;
    const privacyChecked = document.getElementById('privacyAgree').checked;
    document.getElementById('agreeTermsBtn').disabled = !(termsChecked && privacyChecked);
}

// Agree to terms
function agreeToTerms() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/api/accept-terms', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const now = new Date();
            document.getElementById('agreementDate').textContent = now.toLocaleString();
            document.getElementById('signatureInfo').innerHTML =
                '<i class="fas fa-check-circle text-success me-1"></i>Digital signature recorded';
            DashboardApp.showAlert('success', 'Terms acceptance recorded. Thank you!');
        }
    };
    xhr.send(JSON.stringify({
        version: '<?= $terms_version ?>',
        timestamp: new Date().toISOString(),
        ip_address: 'client_ip' // Would be captured server-side
    }));
}

// Download terms as PDF
function downloadTerms() {
    DashboardApp.showAlert('info', 'PDF generation in progress...');
    setTimeout(() => {
        window.print();
    }, 500);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Check if user has previously accepted
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '/api/check-terms-acceptance', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const data = JSON.parse(xhr.responseText);
            if (data.accepted) {
                document.getElementById('agreementDate').textContent = new Date(data.accepted_at).toLocaleString();
                document.getElementById('signatureInfo').innerHTML =
                    '<i class="fas fa-check-circle text-success me-1"></i>Digital signature on file';
            }
        }
    };
    xhr.send();

    // Add change listener for privacy checkbox
    document.getElementById('privacyAgree').addEventListener('change', enableAgreeButton);

    // Initialize DashboardApp if available
    if (typeof DashboardApp !== 'undefined' && typeof DashboardApp.init === 'function') {
        DashboardApp.init();
    }
});
</script>

<?php require_once __DIR__ . '/../includes-v2/footer.php'; ?>
