<div class="container-fluid mt-4">
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
                            <?php foreach ($devices as $device): ?>
                                <option value="<?= $device->id ?>"><?= $device->name ?> (<?= $device->ip_address ?>)</option>
                            <?php endforeach; ?>
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
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalCount">0</div>
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
    
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Attendance</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Actions:</div>
                            <a class="dropdown-item" href="#" id="refreshAttendance"><i class="fas fa-sync fa-sm fa-fw mr-2 text-gray-400"></i>Refresh</a>
                            <a class="dropdown-item" href="#" id="exportAttendance"><i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i>Export</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="attendanceTable" class="table table-bordered table-striped" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Device</th>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentAttendance)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No attendance records found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentAttendance as $attendance): ?>
                                        <tr>
                                            <td>
                                                <?php if (isset($attendance->employee_name)): ?>
                                                    <?= $attendance->employee_name ?>
                                                <?php else: ?>
                                                    <?= $attendance->biometric_id ?? 'Unknown' ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($attendance->device_name)): ?>
                                                    <?= $attendance->device_name ?>
                                                <?php else: ?>
                                                    <?= $attendance->device_id ?? 'Unknown' ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $timestamp = $attendance->timestamp ?? $attendance->check_time ?? $attendance->datetime ?? $attendance->created_at ?? 'Unknown';
                                                    echo $timestamp;
                                                ?>
                                            </td>
                                            <td><?= $attendance->type ?? 'Check-in' ?></td>
                                            <td><?= $attendance->status ?? 'Present' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Live Events Log -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Live Events Log</h6>
                </div>
                <div class="card-body">
                    <div id="liveEventsContainer" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User ID</th>
                                    <th>Event</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody id="liveEventsBody">
                                <!-- Live events will be added here -->
                                <tr>
                                    <td colspan="4" class="text-center">No events yet. Start live capture to see events.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var attendanceTable = $('#attendanceTable').DataTable({
        "order": [[2, "desc"]],
        "pageLength": 10,
        "language": {
            "emptyTable": "No attendance records found"
        }
    });
    
    // Variables for live capture
    var liveCapturePollId = null;
    var isCapturing = false;
    var captureStartTime = null;
    var eventCount = 0;
    
    // Function to start live capture
    function startLiveCapture() {
        var deviceId = $('#deviceSelector').val();
        var captureTime = $('#captureTime').val();
        
        if (!deviceId) {
            showAlert('danger', 'Please select a device');
            return;
        }
        
        console.log('Starting live capture for device ID:', deviceId, 'for', captureTime, 'seconds');
        
        // Show the stop button and hide the start button
        $('#startLiveCapture').hide();
        $('#stopLiveCapture').show();
        
        // Show a loading message
        showAlert('info', 'Connecting to device...');
        
        // Start the countdown
        var remainingTime = parseInt(captureTime);
        var countdownInterval = setInterval(function() {
            remainingTime--;
            updateStatusWithCountdown(remainingTime);
            
            if (remainingTime <= 0) {
                clearInterval(countdownInterval);
                stopLiveCapture();
            }
        }, 1000);
        
        // Store the interval ID for later
        window.countdownInterval = countdownInterval;
        
        // Make the AJAX request to start live capture
        $.ajax({
            url: '/devices/realtime?live=1&device_id=' + deviceId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Live capture response:', response);
                
                if (!response.success) {
                    showAlert('danger', 'Error: ' + response.message);
                    stopLiveCapture();
                    return;
                }
                
                // Start polling for events
                startEventPolling(deviceId);
            },
            error: function(xhr, status, error) {
                console.error('Error starting live capture:', error);
                console.error('Response:', xhr.responseText);
                showAlert('danger', 'Error starting live capture: ' + error);
                stopLiveCapture();
            }
        });
    }
    
    // Function to stop live capture
    function stopLiveCapture() {
        console.log('Stopping live capture');
        
        // Clear the countdown interval
        if (window.countdownInterval) {
            clearInterval(countdownInterval);
        }
        
        // Clear the polling interval
        if (window.pollingInterval) {
            clearInterval(window.pollingInterval);
        }
        
        // Show the start button and hide the stop button
        $('#stopLiveCapture').hide();
        $('#startLiveCapture').show();
        
        // Show a stopped message
        showAlert('warning', 'Live capture stopped');
    }
    
    // Function to start polling for events
    function startEventPolling(deviceId) {
        console.log('Starting event polling for device ID:', deviceId);
        
        // Add initial log entry
        addEventLog('System', 'info', 'Started monitoring attendance events');
        
        // Set up polling interval
        var pollingInterval = setInterval(function() {
            $.ajax({
                url: '/devices/realtime?live=1&poll=1&device_id=' + deviceId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Polling response:', response);
                    
                    if (response.events && response.events.length > 0) {
                        // Process each event
                        response.events.forEach(function(event) {
                            processEvent(event);
                        });
                    }
                    
                    // Update summary if available
                    if (response.summary) {
                        updateAttendanceSummary(response.summary);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error polling for events:', error);
                    console.error('Response:', xhr.responseText);
                    addEventLog('System', 'error', 'Error polling for events: ' + error);
                }
            });
        }, 2000); // Poll every 2 seconds
        
        // Store the interval ID for later
        window.pollingInterval = pollingInterval;
    }
    
    // Function to process events
    function processEvents(events) {
        events.forEach(function(event) {
            eventCount++;
            
            // Add to the events log
            var eventType = event.status || 'check-in';
            var details = 'Time: ' + event.timestamp;
            if (event.employee_name) {
                details += ', Employee: ' + event.employee_name;
            }
            
            addEventLog(event.uid, eventType, details);
            
            // Refresh the attendance table
            refreshAttendanceTable();
        });
    }
    
    // Function to add an event log entry
    function addEventLog(userId, eventType, details) {
        var now = new Date();
        var timeString = now.toLocaleTimeString();
        
        var eventClass = '';
        if (eventType.toLowerCase() === 'error') {
            eventClass = 'table-danger';
        } else if (eventType.toLowerCase() === 'warning') {
            eventClass = 'table-warning';
        } else if (eventType.toLowerCase() === 'check-in') {
            eventClass = 'table-success';
        } else if (eventType.toLowerCase() === 'check-out') {
            eventClass = 'table-info';
        }
        
        var logEntry = '<tr class="' + eventClass + '">' +
            '<td>' + timeString + '</td>' +
            '<td>' + userId + '</td>' +
            '<td>' + eventType + '</td>' +
            '<td>' + details + '</td>' +
            '</tr>';
        
        // If this is the first real entry, clear the "no events" message
        if ($('#liveEventsBody tr').length === 1 && 
            $('#liveEventsBody tr td').length === 1 && 
            $('#liveEventsBody tr td').text().includes('No events yet')) {
            $('#liveEventsBody').empty();
        }
        
        // Add the new entry at the top
        $('#liveEventsBody').prepend(logEntry);
    }
    
    // Function to update status with countdown
    function updateStatusWithCountdown(seconds) {
        var minutes = Math.floor(seconds / 60);
        var remainingSeconds = seconds % 60;
        var timeString = minutes + ':' + (remainingSeconds < 10 ? '0' : '') + remainingSeconds;
        
        showAlert('info', 'Live capture in progress. Time remaining: ' + timeString);
    }
    
    // Function to show an alert
    function showAlert(type, message) {
        $('#liveStatus').html('<div class="alert alert-' + type + '">' + message + '</div>');
    }
    
    // Function to refresh the attendance table
    function refreshAttendanceTable() {
        $.ajax({
            url: '/devices/get_recent_attendance',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Clear the table
                    attendanceTable.clear();
                    
                    // Add new data
                    response.attendance.forEach(function(item) {
                        var employeeName = item.employee_name || item.biometric_id || 'Unknown';
                        var deviceName = item.device_name || item.device_id || 'Unknown';
                        var timestamp = item.timestamp || item.check_time || item.datetime || item.created_at || 'Unknown';
                        var type = item.type || 'Check-in';
                        var status = item.status || 'Present';
                        
                        attendanceTable.row.add([
                            employeeName,
                            deviceName,
                            timestamp,
                            type,
                            status
                        ]);
                    });
                    
                    // Redraw the table
                    attendanceTable.draw();
                    
                    // Update summary if available
                    if (response.summary) {
                        updateAttendanceSummary(response.summary);
                    }
                }
            }
        });
    }
    
    // Function to update attendance summary
    function updateAttendanceSummary(summary) {
        $('#presentCount').text(summary.present || 0);
        $('#absentCount').text(summary.absent || 0);
        $('#lateCount').text(summary.late || 0);
        $('#totalCount').text(summary.total || 0);
    }
    
    // Handle start button click
    $('#startLiveCapture').click(function() {
        startLiveCapture();
    });
    
    // Handle stop button click
    $('#stopLiveCapture').click(function() {
        stopLiveCapture();
    });
    
    // Handle refresh button click
    $('#refreshAttendance').click(function(e) {
        e.preventDefault();
        refreshAttendanceTable();
    });
    
    // Handle export button click
    $('#exportAttendance').click(function(e) {
        e.preventDefault();
        // Implement export functionality here
        alert('Export functionality will be implemented soon.');
    });
    
    // Initialize - load attendance data and summary
    refreshAttendanceTable();
});
</script> 