<?php
/**
 * Support Page V2
 * Contact forms, FAQ, system status, and help resources
 *
 * Features:
 * - Contact form with validation and file attachment
 * - Ticket submission system
 * - FAQ accordion with search
 * - System status dashboard with uptime monitoring
 * - Feedback form with star rating
 * - Help resources and quick links
 * - Live chat widget placeholder
 * - Recent tickets table
 * - Knowledge base search
 * - Support hours and response times
 *
 * @package CIS_Intelligence_Dashboard
 * @subpackage Support
 * @version 2.0.0
 */

declare(strict_types=1);

// Page configuration
$page_title = 'Support & Help Center';
$page_subtitle = 'Get help, submit tickets, and check system status';
$current_page = 'support';

// Get system status (in production, these would be real checks)
$system_status = [
    'overall' => 'operational', // operational, degraded, outage
    'components' => [
        ['name' => 'Dashboard', 'status' => 'operational', 'uptime' => '99.9%'],
        ['name' => 'Code Scanner', 'status' => 'operational', 'uptime' => '99.7%'],
        ['name' => 'Database', 'status' => 'operational', 'uptime' => '100%'],
        ['name' => 'API Endpoints', 'status' => 'operational', 'uptime' => '99.8%'],
        ['name' => 'File Storage', 'status' => 'operational', 'uptime' => '99.9%']
    ],
    'last_check' => date('Y-m-d H:i:s')
];

// Get recent tickets for current user (if any)
$recent_tickets_query = $pdo->prepare("
    SELECT id, subject, status, priority, created_at
    FROM support_tickets
    WHERE project_id = ? AND created_by = ?
    ORDER BY created_at DESC
    LIMIT 5
");
// For demo, use empty array if table doesn't exist
try {
    $recent_tickets_query->execute([$current_project_id, $_SESSION['user_id'] ?? 0]);
    $recent_tickets = $recent_tickets_query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_tickets = [];
}

?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">
                <i class="fas fa-life-ring"></i>
                <?= htmlspecialchars($page_title) ?>
            </h1>
            <p class="page-subtitle"><?= htmlspecialchars($page_subtitle) ?></p>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-primary" onclick="openTicketModal()">
                <i class="fas fa-ticket-alt"></i>
                Submit Ticket
            </button>
            <a href="?page=documentation" class="btn btn-outline-primary">
                <i class="fas fa-book"></i>
                Documentation
            </a>
        </div>
    </div>
</div>

<!-- System Status Overview -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-heartbeat me-2"></i>
                System Status
            </h5>
            <small class="text-muted">
                Last checked: <?= htmlspecialchars($system_status['last_check']) ?>
            </small>
        </div>
    </div>
    <div class="card-body">
        <!-- Overall Status -->
        <div class="alert alert-<?= $system_status['overall'] === 'operational' ? 'success' : 'warning' ?> mb-4">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <?php if ($system_status['overall'] === 'operational'): ?>
                        <i class="fas fa-check-circle fa-2x"></i>
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    <?php endif; ?>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h5 class="alert-heading mb-1">
                        <?= $system_status['overall'] === 'operational' ? 'All Systems Operational' : 'Experiencing Issues' ?>
                    </h5>
                    <p class="mb-0">
                        <?= $system_status['overall'] === 'operational'
                            ? 'All services are running normally'
                            : 'Some services may be experiencing degraded performance' ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Component Status -->
        <div class="row g-3">
            <?php foreach ($system_status['components'] as $component): ?>
            <div class="col-md-4">
                <div class="border rounded p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0"><?= htmlspecialchars($component['name']) ?></h6>
                        <?php
                        $status_class = 'success';
                        $status_icon = 'check-circle';
                        if ($component['status'] === 'degraded') {
                            $status_class = 'warning';
                            $status_icon = 'exclamation-circle';
                        } elseif ($component['status'] === 'outage') {
                            $status_class = 'danger';
                            $status_icon = 'times-circle';
                        }
                        ?>
                        <span class="badge bg-<?= $status_class ?>">
                            <i class="fas fa-<?= $status_icon ?> me-1"></i>
                            <?= ucfirst($component['status']) ?>
                        </span>
                    </div>
                    <div class="text-muted small">
                        <i class="fas fa-chart-line me-1"></i>
                        Uptime: <strong><?= htmlspecialchars($component['uptime']) ?></strong>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-3">
            <a href="#" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-history me-1"></i>
                View Status History
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Contact & Feedback -->
    <div class="col-lg-8">
        <!-- Quick Contact Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-envelope me-2"></i>
                    Quick Contact
                </h5>
            </div>
            <div class="card-body">
                <form id="quickContactForm" onsubmit="submitQuickContact(event)">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Your Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                name="name"
                                required
                                placeholder="John Doe"
                            >
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input
                                type="email"
                                class="form-control"
                                name="email"
                                required
                                placeholder="john@example.com"
                            >
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                Subject <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                name="subject"
                                required
                                placeholder="Brief description of your inquiry"
                            >
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                Message <span class="text-danger">*</span>
                            </label>
                            <textarea
                                class="form-control"
                                name="message"
                                rows="5"
                                required
                                placeholder="Please provide details about your question or issue..."
                            ></textarea>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i>
                                For urgent issues, please submit a ticket instead.
                            </small>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>
                                Send Message
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i>
                                Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Feedback Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-comment-dots me-2"></i>
                    Feedback & Suggestions
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Help us improve! Rate your experience and share your thoughts.</p>

                <form id="feedbackForm" onsubmit="submitFeedback(event)">
                    <div class="mb-3">
                        <label class="form-label">
                            Overall Satisfaction <span class="text-danger">*</span>
                        </label>
                        <div class="rating-stars" id="ratingStars">
                            <i class="far fa-star" data-rating="1" onclick="setRating(1)"></i>
                            <i class="far fa-star" data-rating="2" onclick="setRating(2)"></i>
                            <i class="far fa-star" data-rating="3" onclick="setRating(3)"></i>
                            <i class="far fa-star" data-rating="4" onclick="setRating(4)"></i>
                            <i class="far fa-star" data-rating="5" onclick="setRating(5)"></i>
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" required>
                        <small class="form-text text-muted" id="ratingText">Click to rate</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Feedback Type</label>
                        <select class="form-select" name="feedback_type">
                            <option value="general">General Feedback</option>
                            <option value="feature">Feature Request</option>
                            <option value="bug">Bug Report</option>
                            <option value="performance">Performance Issue</option>
                            <option value="ui">UI/UX Suggestion</option>
                            <option value="documentation">Documentation</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Your Feedback <span class="text-danger">*</span>
                        </label>
                        <textarea
                            class="form-control"
                            name="feedback_message"
                            rows="4"
                            required
                            placeholder="Tell us what you think..."
                        ></textarea>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="follow_up" id="followUp">
                        <label class="form-check-label" for="followUp">
                            I'd like to receive follow-up about this feedback
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i>
                        Submit Feedback
                    </button>
                </form>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Frequently Asked Questions
                    </h5>
                    <input
                        type="text"
                        class="form-control form-control-sm"
                        style="width: 250px;"
                        placeholder="Search FAQ..."
                        id="faqSearch"
                        onkeyup="searchFAQ()"
                    >
                </div>
            </div>
            <div class="card-body">
                <div class="accordion" id="supportFAQ">
                    <div class="accordion-item faq-item" data-keywords="password login access">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I reset my password?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#supportFAQ">
                            <div class="accordion-body">
                                Click the "Forgot Password" link on the login page. Enter your email address and you'll receive a password reset link. For security reasons, the link expires after 1 hour.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item faq-item" data-keywords="scan error failed">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Why is my scan failing?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#supportFAQ">
                            <div class="accordion-body">
                                Common reasons include: insufficient file permissions, excluded directories, large file size limits, or timeout issues. Check the scan logs in the Scan History page for detailed error messages. Ensure your scan configuration has the correct paths and patterns.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item faq-item" data-keywords="upgrade features plan">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                How do I upgrade my account?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#supportFAQ">
                            <div class="accordion-body">
                                Contact our sales team at sales@intelligencehub.com or click the "Upgrade" button in Settings. We offer flexible plans based on your team size and scan volume requirements.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item faq-item" data-keywords="team members invite users">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Can I add team members to my project?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#supportFAQ">
                            <div class="accordion-body">
                                Yes! Go to Business Units page and use the "Manage Team" feature to add members to organizational units. Then map those units to your projects for collaborative access.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item faq-item" data-keywords="export data download">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Can I export my data?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#supportFAQ">
                            <div class="accordion-body">
                                Absolutely! Most pages have an "Export" button that allows you to download data in JSON or CSV format. You can also use our API to programmatically export data.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item faq-item" data-keywords="api integration webhook">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                How do I integrate with my CI/CD pipeline?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#supportFAQ">
                            <div class="accordion-body">
                                Use our REST API endpoints to trigger scans and retrieve results. See the <a href="?page=documentation#api-reference">API Reference</a> in the documentation. You can also configure webhooks in Settings → Integrations.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item faq-item" data-keywords="billing invoice payment">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                How does billing work?
                            </button>
                        </h2>
                        <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#supportFAQ">
                            <div class="accordion-body">
                                We bill monthly based on your plan. Invoices are sent via email on the 1st of each month. You can update payment methods and view billing history in Settings → Billing.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item faq-item" data-keywords="support hours response time">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                What are your support hours?
                            </button>
                        </h2>
                        <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#supportFAQ">
                            <div class="accordion-body">
                                Our support team is available Monday-Friday, 9 AM - 6 PM EST. Priority tickets receive response within 4 hours, standard tickets within 24 hours. Emergency support is available 24/7 for Enterprise plans.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="?page=documentation#faq" class="btn btn-outline-primary btn-sm">
                        View All FAQs in Documentation
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Support Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Support Information
                </h5>
            </div>
            <div class="card-body">
                <h6 class="fw-bold">Contact Methods</h6>
                <ul class="list-unstyled mb-3">
                    <li class="mb-2">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <a href="mailto:support@intelligencehub.com">support@intelligencehub.com</a>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone text-success me-2"></i>
                        <a href="tel:+18005551234">+1 (800) 555-1234</a>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-comments text-info me-2"></i>
                        <a href="#" onclick="openLiveChat()">Live Chat</a>
                        <span class="badge bg-success ms-1">Online</span>
                    </li>
                </ul>

                <h6 class="fw-bold mt-4">Support Hours</h6>
                <table class="table table-sm table-borderless mb-3">
                    <tr>
                        <td><strong>Monday - Friday:</strong></td>
                        <td>9 AM - 6 PM EST</td>
                    </tr>
                    <tr>
                        <td><strong>Saturday:</strong></td>
                        <td>10 AM - 4 PM EST</td>
                    </tr>
                    <tr>
                        <td><strong>Sunday:</strong></td>
                        <td>Closed</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-muted small">
                            <i class="fas fa-clock me-1"></i>
                            Emergency support 24/7 for Enterprise
                        </td>
                    </tr>
                </table>

                <h6 class="fw-bold mt-4">Response Times</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <span class="badge bg-danger">Critical</span>
                        <span class="ms-2">1 hour</span>
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-warning">High</span>
                        <span class="ms-2">4 hours</span>
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-info">Normal</span>
                        <span class="ms-2">24 hours</span>
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-secondary">Low</span>
                        <span class="ms-2">48 hours</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-link me-2"></i>
                    Quick Links
                </h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="?page=documentation" class="list-group-item list-group-item-action">
                    <i class="fas fa-book text-primary me-2"></i>
                    Documentation
                </a>
                <a href="?page=documentation#getting-started" class="list-group-item list-group-item-action">
                    <i class="fas fa-play-circle text-success me-2"></i>
                    Getting Started Guide
                </a>
                <a href="?page=documentation#api-reference" class="list-group-item list-group-item-action">
                    <i class="fas fa-plug text-info me-2"></i>
                    API Reference
                </a>
                <a href="?page=documentation#troubleshooting" class="list-group-item list-group-item-action">
                    <i class="fas fa-wrench text-warning me-2"></i>
                    Troubleshooting
                </a>
                <a href="?page=documentation#video-tutorials" class="list-group-item list-group-item-action">
                    <i class="fas fa-video text-danger me-2"></i>
                    Video Tutorials
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-users text-secondary me-2"></i>
                    Community Forum
                </a>
                <a href="?page=privacy" class="list-group-item list-group-item-action">
                    <i class="fas fa-shield-alt text-dark me-2"></i>
                    Privacy Policy
                </a>
            </div>
        </div>

        <!-- Recent Tickets -->
        <?php if (count($recent_tickets) > 0): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-ticket-alt me-2"></i>
                    Your Recent Tickets
                </h5>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($recent_tickets as $ticket): ?>
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <h6 class="mb-1"><?= htmlspecialchars($ticket['subject']) ?></h6>
                        <?php
                        $status_class = [
                            'open' => 'primary',
                            'in_progress' => 'info',
                            'resolved' => 'success',
                            'closed' => 'secondary'
                        ][$ticket['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $status_class ?>"><?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?></span>
                    </div>
                    <p class="mb-1 small text-muted">
                        <i class="fas fa-clock me-1"></i>
                        <?= date('M j, Y', strtotime($ticket['created_at'])) ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="card-footer text-center">
                <button class="btn btn-sm btn-outline-primary" onclick="viewAllTickets()">
                    View All Tickets
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Live Chat Widget -->
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-comments fa-3x mb-3"></i>
                <h5>Need Immediate Help?</h5>
                <p class="mb-3">Chat with our support team in real-time</p>
                <button class="btn btn-light" onclick="openLiveChat()">
                    <i class="fas fa-comment-dots me-1"></i>
                    Start Live Chat
                </button>
                <div class="mt-2">
                    <span class="badge bg-success">
                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                        3 agents online
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Ticket Modal -->
<div class="modal fade" id="submitTicketModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-ticket-alt me-2"></i>
                    Submit Support Ticket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="ticketForm" onsubmit="submitTicket(event)">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Priority <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="priority" required>
                                <option value="">Select priority...</option>
                                <option value="low">Low - General question</option>
                                <option value="normal" selected>Normal - Non-urgent issue</option>
                                <option value="high">High - Blocking work</option>
                                <option value="critical">Critical - Service down</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="category" required>
                                <option value="">Select category...</option>
                                <option value="technical">Technical Issue</option>
                                <option value="billing">Billing</option>
                                <option value="feature">Feature Request</option>
                                <option value="bug">Bug Report</option>
                                <option value="account">Account Access</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                Subject <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                name="subject"
                                required
                                placeholder="Brief summary of the issue"
                            >
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                Description <span class="text-danger">*</span>
                            </label>
                            <textarea
                                class="form-control"
                                name="description"
                                rows="6"
                                required
                                placeholder="Please provide detailed information:
- What were you trying to do?
- What happened instead?
- Steps to reproduce the issue
- Any error messages received"
                            ></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                Attachment (Optional)
                            </label>
                            <input
                                type="file"
                                class="form-control"
                                name="attachment"
                                accept=".png,.jpg,.jpeg,.pdf,.txt,.log"
                            >
                            <small class="form-text text-muted">
                                Accepted formats: PNG, JPG, PDF, TXT, LOG (max 5MB)
                            </small>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Tip:</strong> Include screenshots and detailed steps to help us resolve your issue faster.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="ticketForm" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i>
                    Submit Ticket
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Support Page JavaScript
const DashboardApp = DashboardApp || {};

// Submit quick contact form
function submitQuickContact(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    // In production, this would send to API
    console.log('Contact form data:', Object.fromEntries(formData));

    // Show success message
    alert('Thank you for contacting us! We\'ll respond within 24 hours.');
    form.reset();
}

// Submit feedback form
function submitFeedback(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    // Validate rating
    const rating = document.getElementById('ratingValue').value;
    if (!rating) {
        alert('Please provide a rating');
        return;
    }

    // In production, this would send to API
    console.log('Feedback data:', Object.fromEntries(formData));

    // Show success message
    alert('Thank you for your feedback! We appreciate your input.');
    form.reset();
    resetRating();
}

// Rating system
let currentRating = 0;

function setRating(rating) {
    currentRating = rating;
    document.getElementById('ratingValue').value = rating;

    const stars = document.querySelectorAll('#ratingStars i');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('far');
            star.classList.add('fas', 'text-warning');
        } else {
            star.classList.remove('fas', 'text-warning');
            star.classList.add('far');
        }
    });

    const ratingTexts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
    document.getElementById('ratingText').textContent = ratingTexts[rating];
}

function resetRating() {
    currentRating = 0;
    document.getElementById('ratingValue').value = '';
    document.querySelectorAll('#ratingStars i').forEach(star => {
        star.classList.remove('fas', 'text-warning');
        star.classList.add('far');
    });
    document.getElementById('ratingText').textContent = 'Click to rate';
}

// FAQ search
function searchFAQ() {
    const query = document.getElementById('faqSearch').value.toLowerCase();
    const items = document.querySelectorAll('.faq-item');
    let visibleCount = 0;

    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        const keywords = item.dataset.keywords || '';

        if (text.includes(query) || keywords.includes(query)) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    if (visibleCount === 0 && query !== '') {
        // Could show "no results" message here
    }
}

// Open ticket modal
function openTicketModal() {
    const modal = new bootstrap.Modal(document.getElementById('submitTicketModal'));
    modal.show();
}

// Submit ticket
function submitTicket(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    // In production, this would send to API with file upload
    console.log('Ticket data:', Object.fromEntries(formData));

    // Show success message
    alert('Ticket submitted successfully! You\'ll receive a confirmation email shortly.');

    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('submitTicketModal')).hide();
    form.reset();
}

// Open live chat
function openLiveChat() {
    // In production, this would open live chat widget
    alert('Live chat widget would open here. Integration with services like Intercom, Drift, or Zendesk Chat.');
}

// View all tickets
function viewAllTickets() {
    // In production, would navigate to tickets page
    alert('Would navigate to full tickets list page');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof DashboardApp.init === 'function') {
        DashboardApp.init();
    }
});
</script>

<style>
.rating-stars {
    font-size: 2rem;
    cursor: pointer;
}
.rating-stars i {
    transition: all 0.2s ease;
}
.rating-stars i:hover {
    transform: scale(1.2);
}
</style>

<?php // Layout handled by index.php ?>
