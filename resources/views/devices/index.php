<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Device Management</h1>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['success']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Device List</h6>
            <div>
                <a href="/devices/realtime" class="btn btn-info btn-sm">
                    <i class="fas fa-clock"></i> Real-time Monitoring
                </a>
                <a href="/devices/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Device
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Device Name</th>
                            <th>Type</th>
                            <th>IP Address</th>
                            <th>Port</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($devices)): ?>
                            <?php foreach ($devices as $device): ?>
                                <?php $deviceStatus = isset($device->status) ? $device->status : 'unknown'; ?>
                                <tr>
                                    <td><?= htmlspecialchars($device->device_name ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars(ucfirst($device->device_type ?? 'N/A')) ?></td>
                                    <td><?= htmlspecialchars($device->ip_address ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($device->port ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($device->location ?? 'N/A') ?></td>
                                    <td>
                                        <?php if ($deviceStatus === 'active'): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php elseif ($deviceStatus === 'inactive'): ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php elseif ($deviceStatus === 'maintenance'): ?>
                                            <span class="badge badge-warning">Maintenance</span>
                                        <?php elseif ($deviceStatus === 'syncing'): ?>
                                            <span class="badge badge-warning">Syncing</span>
                                        <?php elseif ($deviceStatus === 'error'): ?>
                                            <span class="badge badge-danger">Error</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?= htmlspecialchars($deviceStatus) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/devices/edit/<?= $device->id ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/devices/test-connection/<?= $device->id ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-plug"></i>
                                            </a>
                                            <div class="dropdown">
                                                <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="syncDropdown<?= $device->id ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-sync"></i>
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="syncDropdown<?= $device->id ?>">
                                                    <a class="dropdown-item" href="/devices/sync/<?= $device->id ?>">
                                                        <i class="fas fa-sync"></i> Sync All
                                                    </a>
                                                    <a class="dropdown-item sync-by-date-btn" href="#" data-device-id="<?= $device->id ?>">
                                                        <i class="fas fa-calendar-alt"></i> Sync by Date
                                                    </a>
                                                </div>
                                            </div>
                                            <a href="/devices/delete/<?= $device->id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this device?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No devices found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Sync by Date Modal -->
<div class="modal fade" id="syncByDateModal" tabindex="-1" role="dialog" aria-labelledby="syncByDateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="syncByDateModalLabel">Sync Attendance by Date Range</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="syncByDateForm" action="/devices/sync-by-date/" method="post">
                <div class="modal-body">
                    <div id="syncProgressContainer" style="display: none;">
                        <div class="text-center mb-3">
                            <h5 id="syncStatusMessage">Starting sync process...</h5>
                        </div>
                        <div class="progress mb-3">
                            <div id="syncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <div class="row text-center mb-3">
                            <div class="col-3">
                                <div class="font-weight-bold">Total</div>
                                <div id="syncTotalRecords">0</div>
                            </div>
                            <div class="col-3">
                                <div class="font-weight-bold">Processed</div>
                                <div id="syncProcessedRecords">0</div>
                            </div>
                            <div class="col-3">
                                <div class="font-weight-bold">New</div>
                                <div id="syncNewRecords">0</div>
                            </div>
                            <div class="col-3">
                                <div class="font-weight-bold">Skipped</div>
                                <div id="syncSkippedRecords">0</div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Sync Log</h6>
                            </div>
                            <div class="card-body p-0">
                                <div id="syncLogContainer" style="max-height: 200px; overflow-y: auto;">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Status</th>
                                                <th>Message</th>
                                            </tr>
                                        </thead>
                                        <tbody id="syncLogBody">
                                            <!-- Log entries will be added here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="syncDateRangeContainer">
                        <div class="form-group">
                            <label for="start_date">Start Date:</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= date('Y-m-d', strtotime('-7 days')) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">End Date:</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="syncCancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="syncSubmitBtn">Sync</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "order": [[0, "asc"]],
        "pageLength": 10
    });
    
    // Handle Sync by Date button click
    $('.sync-by-date-btn').click(function(e) {
        e.preventDefault();
        var deviceId = $(this).data('device-id');
        $('#syncByDateForm').attr('action', '/devices/sync-by-date/' + deviceId);
        
        // Reset the form
        $('#syncProgressContainer').hide();
        $('#syncDateRangeContainer').show();
        $('#syncCancelBtn').text('Cancel');
        $('#syncSubmitBtn').show();
        $('#syncLogBody').empty();
        
        $('#syncByDateModal').modal('show');
    });
    
    // Handle form submission
    $('#syncByDateForm').submit(function(e) {
        e.preventDefault();
        
        var form = $(this);
        var url = form.attr('action');
        var deviceId = url.split('/').pop();
        
        // Show progress container
        $('#syncDateRangeContainer').hide();
        $('#syncProgressContainer').show();
        $('#syncSubmitBtn').hide();
        $('#syncCancelBtn').text('Close');
        
        // Reset progress
        updateSyncProgress(0, 'Starting sync process...');
        addLogEntry('Starting', 'info', 'Initializing sync process');
        
        // Submit the form
        $.ajax({
            type: 'POST',
            url: url,
            data: form.serialize(),
            success: function(response) {
                // Start polling for progress updates
                startProgressPolling(deviceId);
            },
            error: function(xhr, status, error) {
                updateSyncProgress(100, 'Error: ' + error, 'bg-danger');
                addLogEntry('Error', 'danger', 'Failed to start sync: ' + error);
                console.error('Error syncing attendance:', error);
            }
        });
    });
    
    // Function to start polling for progress updates
    function startProgressPolling(deviceId) {
        var lastStatus = '';
        var lastMessage = '';
        var pollInterval = setInterval(function() {
            $.ajax({
                type: 'GET',
                url: '/devices/sync-by-date/' + deviceId + '?check_progress=1',
                dataType: 'json',
                success: function(response) {
                    // Update progress UI
                    updateSyncProgress(
                        response.progress,
                        response.message,
                        response.status === 'error' ? 'bg-danger' : (response.status === 'completed' ? 'bg-success' : '')
                    );
                    
                    // Update counters
                    $('#syncTotalRecords').text(response.total_records);
                    $('#syncProcessedRecords').text(response.processed_records);
                    $('#syncNewRecords').text(response.new_records);
                    $('#syncSkippedRecords').text(response.skipped_records);
                    
                    // Add log entry if status or message changed
                    if (response.status !== lastStatus || response.message !== lastMessage) {
                        var logType = 'info';
                        if (response.status === 'error') {
                            logType = 'danger';
                        } else if (response.status === 'completed') {
                            logType = 'success';
                        } else if (response.status === 'connecting') {
                            logType = 'primary';
                        } else if (response.status === 'processing') {
                            logType = 'warning';
                        }
                        
                        addLogEntry(response.status, logType, response.message);
                        
                        lastStatus = response.status;
                        lastMessage = response.message;
                    }
                    
                    // If completed or error, stop polling
                    if (response.status === 'completed' || response.status === 'error') {
                        clearInterval(pollInterval);
                        
                        // If completed, reload the page after a delay
                        if (response.status === 'completed') {
                            addLogEntry('Success', 'success', 'Sync completed successfully. Reloading page...');
                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error checking progress:', error);
                    addLogEntry('Error', 'danger', 'Failed to check progress: ' + error);
                }
            });
        }, 1000); // Poll every second
    }
    
    // Function to update the progress UI
    function updateSyncProgress(progress, message, additionalClass) {
        $('#syncProgressBar').css('width', progress + '%').attr('aria-valuenow', progress).text(progress + '%');
        $('#syncStatusMessage').text(message);
        
        // Reset classes
        $('#syncProgressBar').removeClass('bg-danger bg-success');
        
        // Add additional class if provided
        if (additionalClass) {
            $('#syncProgressBar').addClass(additionalClass);
        }
    }
    
    // Function to add a log entry
    function addLogEntry(status, type, message) {
        var now = new Date();
        var timeString = now.toLocaleTimeString();
        
        var statusBadge = '<span class="badge badge-' + type + '">' + status + '</span>';
        
        var logEntry = '<tr>' +
            '<td>' + timeString + '</td>' +
            '<td>' + statusBadge + '</td>' +
            '<td>' + message + '</td>' +
            '</tr>';
        
        $('#syncLogBody').prepend(logEntry);
        
        // Scroll to top of log container
        $('#syncLogContainer').scrollTop(0);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 