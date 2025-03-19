<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Real-time Attendance Monitoring</h1>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Start Live Capture</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="deviceSelector">Select Device:</label>
                        <select id="deviceSelector" class="form-control">
                            <option value="">-- Select a device --</option>
                            <?php if (!empty($devices)): ?>
                                <?php foreach ($devices as $device): ?>
                                    <option value="<?= $device->id ?>">
                                        <?= htmlspecialchars($device->device_name ?? $device->name ?? 'Device ' . $device->id) ?> 
                                        (<?= htmlspecialchars($device->ip_address) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No devices available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="captureTime">Capture Duration (seconds):</label>
                        <input type="number" id="captureTime" class="form-control" value="60" min="10" max="3600">
                    </div>
                    <button id="startLiveCapture" class="btn btn-primary">
                        <i class="fas fa-play-circle"></i> Start Live Capture
                    </button>
                    <button id="stopLiveCapture" class="btn btn-danger" style="display:none;">
                        <i class="fas fa-stop-circle"></i> Stop Capture
                    </button>
                    <div id="liveStatus" class="mt-3"></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Attendance Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Present Today</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="presentCount">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Absent Today</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="absentCount">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-times fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Late Today</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="lateCount">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Employees</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalEmployees">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-id-card fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Recent Attendance</h6>
            <div class="dropdown no-arrow">
                <button class="btn btn-sm btn-primary shadow-sm" id="refreshAttendance">
                    <i class="fas fa-sync fa-sm text-white-50"></i> Refresh
                </button>
                <button class="btn btn-sm btn-success shadow-sm" id="exportAttendance">
                    <i class="fas fa-download fa-sm text-white-50"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="attendanceTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Device</th>
                            <th>Timestamp</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                        <!-- Attendance records will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Event Log</h6>
        </div>
        <div class="card-body">
            <div id="syncLogContainer" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody id="syncLogBody">
                        <tr>
                            <td><?= date('H:i:s') ?></td>
                            <td><span class="badge badge-info">System</span></td>
                            <td>Ready to capture attendance events.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    console.log('Realtime page loaded');
    
    // Debug info for device list
    var deviceCount = $('#deviceSelector option').length - 1; // Subtract the placeholder option
    console.log('Loaded ' + deviceCount + ' devices');
    $('#deviceSelector option').each(function() {
        console.log('Device option: ' + $(this).text() + ', value: ' + $(this).val());
    });
    
    // Initialize variables
    var pollingInterval = null;
    var captureId = null;
    var latestTimestamp = null;
    var consecutiveErrors = 0;
    var maxConsecutiveErrors = 5;
    
    // Enable the start button only when a device is selected
    $('#deviceSelector').on('change', function() {
        console.log('Device selected: ' + $(this).val());
        if ($(this).val()) {
            $('#startLiveCapture').prop('disabled', false);
        } else {
            $('#startLiveCapture').prop('disabled', true);
        }
    });
    
    // Initially disable start button if no device is selected
    if (!$('#deviceSelector').val()) {
        $('#startLiveCapture').prop('disabled', true);
    }
    
    // Start live capture
    $('#startLiveCapture').on('click', function() {
        var deviceId = $('#deviceSelector').val();
        var captureTime = $('#captureTime').val();
        
        if (!deviceId) {
            showAlert('danger', 'Please select a device first.');
            return;
        }
        
        console.log('Starting live capture for device: ' + deviceId + ', duration: ' + captureTime);
        
        // Show loading indicator
        $('#liveStatus').html('<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div> Connecting to device...');
        
        // Disable form controls
        $('#deviceSelector, #captureTime, #startLiveCapture').prop('disabled', true);
        
        // Start monitoring
        startMonitoring(deviceId, captureTime);
    });
    
    // Stop live capture
    $('#stopLiveCapture').on('click', function() {
        stopMonitoring();
    });
    
    // Function to start monitoring
    function startMonitoring(deviceId, duration) {
        // Add event log
        addEventLog('System', 'info', 'Starting live capture for device ID: ' + deviceId);
        
        // Start the capture
        $.ajax({
            url: '/devices/start-live-capture',
            type: 'POST',
            data: {
                device_id: deviceId,
                duration: duration
            },
            dataType: 'json',
            success: function(response) {
                console.log('Start live capture response:', response);
                
                if (response.success) {
                    captureId = response.capture_id;
                    
                    // Update UI
                    $('#liveStatus').html('<div class="alert alert-success">Live capture started. Monitoring for attendance events...</div>');
                    $('#startLiveCapture').hide();
                    $('#stopLiveCapture').show();
                    
                    // Add event log
                    addEventLog('System', 'success', 'Live capture started successfully. Capture ID: ' + captureId);
                    
                    // Start polling for events
                    pollingInterval = setInterval(function() {
                        pollForEvents(deviceId);
                    }, 3000); // Poll every 3 seconds
                    
                    // Set timeout to stop monitoring after the specified duration
                    setTimeout(function() {
                        if (pollingInterval) {
                            stopMonitoring();
                        }
                    }, duration * 1000);
                } else {
                    // Update UI
                    $('#liveStatus').html('<div class="alert alert-danger">Failed to start live capture: ' + response.message + '</div>');
                    
                    // Re-enable form controls
                    $('#deviceSelector, #captureTime, #startLiveCapture').prop('disabled', false);
                    
                    // Add event log
                    addEventLog('System', 'error', 'Failed to start live capture: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error starting live capture:', error);
                
                // Update UI
                $('#liveStatus').html('<div class="alert alert-danger">Error starting live capture: ' + error + '</div>');
                
                // Re-enable form controls
                $('#deviceSelector, #captureTime, #startLiveCapture').prop('disabled', false);
                
                // Add event log
                addEventLog('System', 'error', 'Error starting live capture: ' + error);
            }
        });
    }
    
    // Function to stop monitoring
    function stopMonitoring() {
        // Clear polling interval
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
        
        // Update UI
        $('#liveStatus').html('<div class="alert alert-info">Live capture stopped.</div>');
        $('#stopLiveCapture').hide();
        $('#startLiveCapture').show();
        
        // Re-enable form controls
        $('#deviceSelector, #captureTime, #startLiveCapture').prop('disabled', false);
        
        // Add event log
        addEventLog('System', 'info', 'Live capture stopped.');
        
        // If we have a capture ID, stop it on the server
        if (captureId) {
            $.ajax({
                url: '/devices/stop-live-capture',
                type: 'POST',
                data: {
                    capture_id: captureId
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Stop live capture response:', response);
                    
                    if (response.success) {
                        // Add event log
                        addEventLog('System', 'success', 'Live capture stopped successfully.');
                    } else {
                        // Add event log
                        addEventLog('System', 'warning', 'Failed to stop live capture: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error stopping live capture:', error);
                    
                    // Add event log
                    addEventLog('System', 'error', 'Error stopping live capture: ' + error);
                }
            });
        }
    }
    
    // Function to poll for events
    function pollForEvents(deviceId) {
        $.ajax({
            url: '/devices/monitor-device',
            type: 'GET',
            data: {
                device_id: deviceId
            },
            dataType: 'json',
            timeout: 10000, // 10 second timeout
            success: function(response) {
                console.log('Poll response:', response);
                
                if (response.success) {
                    // Process any new events
                    if (response.events && response.events.length > 0) {
                        // Play notification sound
                        playNotificationSound();
                        
                        // Add events to the log
                        $.each(response.events, function(index, event) {
                            var eventType = event.type || 'check-in';
                            var employeeName = event.employee_name || 'Unknown';
                            var timestamp = event.timestamp || new Date().toISOString();
                            
                            addEventLog('Device', 'info', employeeName + ' ' + eventType + ' at ' + formatTimestamp(timestamp));
                        });
                        
                        // Refresh the attendance table
                        refreshAttendanceTable();
                    }
                } else {
                    console.log('Error in poll response:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.log('Error polling for events:', status);
                console.log('Status:', status);
                console.log('Response:', error);
                
                // Don't show error for timeout - this is expected behavior
                if (status !== 'timeout') {
                    addEventLog('System', 'error', 'Error polling for events: ' + error);
                }
            }
        });
    }
    
    // Function to process a record
    function processRecord(record) {
        console.log('Processing record:', record);
        
        // Add to attendance table
        var employeeName = record.employee_name || 'Unknown';
        var deviceName = record.device_name || 'Unknown';
        var timestamp = record.timestamp || new Date().toISOString();
        var type = record.type || 'check-in';
        var status = record.status || 'present';
        
        var row = '<tr>' +
            '<td>' + employeeName + '</td>' +
            '<td>' + deviceName + '</td>' +
            '<td>' + formatTimestamp(timestamp) + '</td>' +
            '<td>' + capitalizeFirstLetter(type) + '</td>' +
            '<td>' + getStatusBadge(status) + '</td>' +
            '</tr>';
        
        $('#attendanceTableBody').prepend(row);
        
        // Add event log
        addEventLog('Attendance', 'success', employeeName + ' ' + type + ' at ' + formatTimestamp(timestamp));
        
        // Play notification sound
        playNotificationSound();
    }
    
    // Function to refresh attendance table
    function refreshAttendanceTable() {
        $.ajax({
            url: '/devices/get-today-attendance',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Refresh attendance response:', response);
                
                if (response.success) {
                    // Clear the table
                    $('#attendanceTableBody').empty();
                    
                    // Add records
                    if (response.records && response.records.length > 0) {
                        $.each(response.records, function(index, record) {
                            var employeeName = record.employee_name || 'Unknown';
                            var deviceName = record.device_name || 'Unknown';
                            var timestamp = record.timestamp || '';
                            var type = record.type || 'check-in';
                            var status = record.status || 'present';
                            
                            var row = '<tr>' +
                                '<td>' + employeeName + '</td>' +
                                '<td>' + deviceName + '</td>' +
                                '<td>' + formatTimestamp(timestamp) + '</td>' +
                                '<td>' + capitalizeFirstLetter(type) + '</td>' +
                                '<td>' + getStatusBadge(status) + '</td>' +
                                '</tr>';
                            
                            $('#attendanceTableBody').append(row);
                        });
                    } else {
                        $('#attendanceTableBody').html('<tr><td colspan="5" class="text-center">No data available in table</td></tr>');
                    }
                    
                    // Update summary if available
                    if (response.summary) {
                        updateAttendanceSummary(response.summary);
                    }
                } else {
                    showAlert('danger', 'Error refreshing attendance: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error refreshing attendance:', error);
                showAlert('danger', 'Error refreshing attendance: ' + error);
            }
        });
    }
    
    // Function to update attendance summary
    function updateAttendanceSummary(summary) {
        $('#presentCount').text(summary.present || 0);
        $('#absentCount').text(summary.absent || 0);
        $('#lateCount').text(summary.late || 0);
        $('#totalEmployees').text(summary.total || 0);
    }
    
    // Function to add event log
    function addEventLog(source, type, message) {
        var now = new Date();
        var timeString = now.toLocaleTimeString();
        
        var statusBadge = '<span class="badge badge-' + type + '">' + source + '</span>';
        
        var logEntry = '<tr>' +
            '<td>' + timeString + '</td>' +
            '<td>' + statusBadge + '</td>' +
            '<td>' + message + '</td>' +
            '</tr>';
        
        $('#syncLogBody').prepend(logEntry);
        
        // Scroll to top of log container
        $('#syncLogContainer').scrollTop(0);
    }
    
    // Helper functions
    function formatTimestamp(timestamp) {
        if (!timestamp) return '';
        
        var date = new Date(timestamp);
        return date.toLocaleString();
    }
    
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    
    function getStatusBadge(status) {
        var badgeClass = 'secondary';
        
        switch (status.toLowerCase()) {
            case 'present':
                badgeClass = 'success';
                break;
            case 'absent':
                badgeClass = 'danger';
                break;
            case 'late':
                badgeClass = 'warning';
                break;
        }
        
        return '<span class="badge badge-' + badgeClass + '">' + capitalizeFirstLetter(status) + '</span>';
    }
    
    function showAlert(type, message) {
        var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>';
        
        $('#liveStatus').html(alertHtml);
    }
    
    function playNotificationSound() {
        // Create an audio element
        var audio = new Audio('/assets/sounds/notification.mp3');
        audio.play().catch(function(error) {
            console.log('Error playing notification sound:', error);
        });
    }
    
    // Initialize the page
    refreshAttendanceTable();
    
    // Handle refresh button click
    $('#refreshAttendance').click(function(e) {
        e.preventDefault();
        refreshAttendanceTable();
    });
    
    // Handle export button click
    $('#exportAttendance').click(function(e) {
        e.preventDefault();
        
        // Get the current date for the filename
        var today = new Date();
        var dateString = today.getFullYear() + '-' + 
                        ('0' + (today.getMonth() + 1)).slice(-2) + '-' + 
                        ('0' + today.getDate()).slice(-2);
        
        // Export attendance data to CSV
        $.ajax({
            url: '/devices/export-attendance?date=' + dateString,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    // Create a download link
                    var downloadLink = document.createElement('a');
                    downloadLink.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(response.csv);
                    downloadLink.download = 'attendance_' + dateString + '.csv';
                    
                    // Append to the document, click it, and remove it
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);
                    
                    addEventLog('System', 'success', 'Attendance data exported successfully');
                } else {
                    showAlert('danger', 'Error exporting data: ' + response.message);
                    addEventLog('System', 'error', 'Failed to export attendance data: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error exporting attendance data:', error);
                showAlert('danger', 'Error exporting data: ' + error);
                addEventLog('System', 'error', 'Failed to export attendance data: ' + error);
            }
        });
    });
    
    // Handle window unload event to stop monitoring when the page is closed
    $(window).on('beforeunload', function() {
        if (pollingInterval) {
            stopMonitoring();
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 