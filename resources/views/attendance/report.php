<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Attendance Report</h1>
        <a href="/attendance" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Attendance
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filter Report</h5>
        </div>
        <div class="card-body">
            <form action="/attendance/report" method="GET">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="employee_id">Employee</label>
                        <select class="form-control" id="employee_id" name="employee_id">
                            <option value="">All Employees</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?= $employee->id ?>" <?= (isset($_GET['employee_id']) && $_GET['employee_id'] == $employee->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($employee->employee_id . ' - ' . $employee->first_name . ' ' . $employee->last_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? date('Y-m-01')) ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? date('Y-m-t')) ?>">
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-block">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Attendance Records</h5>
            <div>
                <button id="exportPDF" class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
                <button id="exportCSV" class="btn btn-sm btn-success">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="reportTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($attendances)): ?>
                            <?php foreach ($attendances as $attendance): ?>
                                <tr>
                                    <td><?= htmlspecialchars($attendance->date) ?></td>
                                    <td><?= htmlspecialchars($attendance->emp_id) ?></td>
                                    <td><?= htmlspecialchars($attendance->first_name . ' ' . $attendance->last_name) ?></td>
                                    <td><?= htmlspecialchars($attendance->time_in ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($attendance->time_out ?? 'N/A') ?></td>
                                    <td>
                                        <?php if ($attendance->status === 'present'): ?>
                                            <span class="badge badge-success">Present</span>
                                        <?php elseif ($attendance->status === 'absent'): ?>
                                            <span class="badge badge-danger">Absent</span>
                                        <?php elseif ($attendance->status === 'late'): ?>
                                            <span class="badge badge-warning">Late</span>
                                        <?php elseif ($attendance->status === 'half_day'): ?>
                                            <span class="badge badge-info">Half Day</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?= htmlspecialchars(ucfirst($attendance->status)) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($attendance->note ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No attendance records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#reportTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            order: [[0, 'desc']]
        });
        
        $('#exportPDF').click(function(e) {
            e.preventDefault();
            $('#reportTable').DataTable().button('3').trigger();
        });
        
        $('#exportCSV').click(function(e) {
            e.preventDefault();
            $('#reportTable').DataTable().button('1').trigger();
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 