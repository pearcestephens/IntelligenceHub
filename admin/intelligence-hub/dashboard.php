<?php
$pageTitle = 'Intelligence Hub';
$currentPage = $_GET['page'] ?? 'overview';
require_once __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">

                <!-- AI Command Input -->
                <div class="card mb-4">
                    <div class="card__header">
                        <h3 class="card__title">
                            <i class="fas fa-robot"></i> AI Command Center
                        </h3>
                    </div>
                    <div class="card__body">
                        <div class="ai-command-wrapper">
                            <div class="ai-command-input-group">
                                <i class="fas fa-microphone ai-command-icon"></i>
                                <input type="text" class="form-control form-control--lg" id="aiCommandInput"
                                       placeholder="Ask anything: What needs my attention? Show low stock products..."
                                       aria-label="AI Command">
                                <button class="btn btn--primary btn--lg" type="button" id="sendCommand">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                                <button class="btn btn--secondary btn--lg" type="button" id="voiceCommand">
                                    <i class="fas fa-microphone"></i>
                                </button>
                            </div>
                            <div id="aiResponse" class="ai-response d-none">
                                <div class="alert alert--info">
                                    <strong><i class="fas fa-robot"></i> AI Response:</strong>
                                    <p class="mb-0" id="aiResponseText"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Quick Stats -->
                <div class="metrics-grid mb-4">
                    <div class="metric-card metric-card--primary">
                        <div class="metric-card__icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="metric-card__content">
                            <div class="metric-card__value" id="activeAgents">0</div>
                            <div class="metric-card__label">Active Agents</div>
                            <div class="metric-card__sublabel">9 total agents</div>
                        </div>
                    </div>

                    <div class="metric-card metric-card--success">
                        <div class="metric-card__icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="metric-card__content">
                            <div class="metric-card__value" id="tasksCompleted">0</div>
                            <div class="metric-card__label">Tasks Completed</div>
                            <div class="metric-card__sublabel">Last 24 hours</div>
                        </div>
                    </div>

                    <div class="metric-card metric-card--warning">
                        <div class="metric-card__icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="metric-card__content">
                            <div class="metric-card__value" id="pendingApprovals">0</div>
                            <div class="metric-card__label">Pending Approvals</div>
                            <div class="metric-card__sublabel">Require attention</div>
                        </div>
                    </div>

                    <div class="metric-card metric-card--info">
                        <div class="metric-card__icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="metric-card__content">
                            <div class="metric-card__value" id="costSavings">$0</div>
                            <div class="metric-card__label">Cost Savings</div>
                            <div class="metric-card__sublabel">This month</div>
                        </div>
                    </div>
                </div>


                <!-- AI Recommendations Panel -->
                <div class="card mb-4">
                    <div class="card__header card__header--warning">
                        <h3 class="card__title">
                            <i class="fas fa-lightbulb"></i> AI Recommendations
                        </h3>
                    </div>
                    <div class="card__body">
                        <div id="recommendationsList">
                            <p class="text--muted">Loading recommendations...</p>
                        </div>
                    </div>
                </div>

                <!-- Active Agents Status -->
                <div class="card mb-4">
                    <div class="card__header">
                        <h3 class="card__title">
                            <i class="fas fa-robot"></i> Agent Status
                        </h3>
                    </div>
                    <div class="card__body">
                        <div class="agent-grid" id="agentStatusGrid">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="grid grid--2-col">
                    <div class="card">
                        <div class="card__header">
                            <h3 class="card__title">
                                <i class="fas fa-history"></i> Recent Activity
                            </h3>
                        </div>
                        <div class="card__body">
                            <div class="activity-list" id="recentActivityList">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card__header card__header--danger">
                            <h3 class="card__title">
                                <i class="fas fa-exclamation-triangle"></i> Alerts
                            </h3>
                        </div>
                        <div class="card__body">
                            <div class="alert-list" id="alertsList">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal for Approval Requests -->
    <div class="modal" id="approvalModal">
        <div class="modal__backdrop"></div>
        <div class="modal__dialog">
            <div class="modal__content">
                <div class="modal__header">
                    <h3 class="modal__title">
                        <i class="fas fa-question-circle"></i> Approval Required
                    </h3>
                    <button type="button" class="modal__close" data-modal-close>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal__body" id="approvalModalBody">
                    <!-- Will be populated dynamically -->
                </div>
                <div class="modal__footer">
                    <button type="button" class="btn btn--secondary" data-modal-close">
                        <i class="fas fa-times-circle"></i> Decline
                    </button>
                    <button type="button" class="btn btn--success" id="approveButton">
                        <i class="fas fa-check-circle"></i> Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
