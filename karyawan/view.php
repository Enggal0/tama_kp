<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Detail - Kaon Employee Dashboard</title>
    <link rel="stylesheet" href="../css/karyawan/style-view.css" />
</head>
<body>
    <div class="container">
        <!-- Header with Back Button -->
        <div class="detail-header">
            <a href="mytasks.html" class="back-btn">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
                Back to Tasks
            </a>
            <h1 class="page-title">Task Details</h1>
        </div>

        <!-- Main Content -->
        <div class="detail-content">
            <!-- Task Information Card -->
            <div class="task-info-card">
                <div class="task-type-badge">KPI Alignment</div>
                <h2 class="task-title">Daily Work Order Alignment</h2>

                <div class="task-description">
                    Complete 50 Work Orders (WO) per day to maintain web access quality and ensure optimal customer service. This task is part of the daily target to support Telkom's network operations.
                </div>

                <div class="task-details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Daily Target</div>
                        <div class="detail-value">50 WO/day</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Deadline</div>
                        <div class="detail-value">June 30, 2025</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Category</div>
                        <div class="detail-value">KPI Alignment</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Assigned By</div>
                        <div class="detail-value">Operations Manager</div>
                    </div>
                </div>

                <div class="progress-section">
                    <div class="progress-label">
                        <span class="progress-title">Progress Completion</span>
                        <span class="progress-percentage">65%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 65%"></div>
                    </div>
                </div>
            </div>

            <!-- Status and Actions Card -->
            <div class="status-card">
                <div class="status-header">
                    <h3 class="status-title">Task Status</h3>
                    <div class="status-badge-large status-pending">
                        <div class="status-indicator"></div>
                        Pending
                    </div>
                </div>

                <div class="priority-indicator">
                    <div class="priority-badge priority-high">High Priority</div>
                </div>

                <div class="action-buttons">
                    <button class="action-btn btn-report" onclick="openReportModal()">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 102 0V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" clip-rule="evenodd"/>
                        </svg>
                        Report Progress
                    </button>
                </div>
            </div>
        </div>

        <!-- Timeline Section -->
        <div class="timeline-section">
            <h3 class="timeline-title">Activity Timeline</h3>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-date">June 29, 2025 - 09:00</div>
                    <div class="timeline-content">
                        <strong>Task Created</strong><br>
                        Daily Work Order Alignment task created and assigned to John Doe.
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-date">June 29, 2025 - 10:30</div>
                    <div class="timeline-content">
                        <strong>Progress Update</strong><br>
                        Completed 15 WOs out of 50 target (30% progress)
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-date">June 29, 2025 - 14:15</div>
                    <div class="timeline-content">
                        <strong>Progress Update</strong><br>
                        Completed 32 WOs out of 50 target (65% progress)
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-date">June 29, 2025 - 16:00</div>
                    <div class="timeline-content">
                        <strong>Status: Pending</strong><br>
                        Still in progress. 18 WOs remaining to reach the daily target.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal-custom" id="reportModal">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <h5 class="modal-title">Submit Task Report</h5>
                <button class="close-btn" onclick="closeReportModal()">×</button>
            </div>
            <div class="modal-body-custom">
                <div class="task-info">
                    <strong>Current Task:</strong>
                    <p>Daily Work Order Alignment</p>
                </div>
                <form id="reportForm" onsubmit="submitReport(event)">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="achieved" class="form-label">Achieved</label>
                            <input type="number" id="achieved" name="achieved" class="form-control" placeholder="e.g. 50" min="0" required>
                            <div class="help-text">Amount of work done</div>
                        </div>
                        <div class="col-md-6">
                            <label for="target" class="form-label">Target</label>
                            <input type="number" id="target" name="target" class="form-control" placeholder="e.g. 50" min="0" required>
                            <div class="help-text">Target specified</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="">-- Select Status --</option>
                            <option value="achieve">✅ Achieved</option>
                            <option value="non-achieve">❌ Non Achieved</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Submit Report</button>
                </form>
            </div>
        </div>
    </div>
        <script src="../js/karyawan/view.js"></script>
</body>
</html>
