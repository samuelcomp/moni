<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Attendance</h1>
        <a href="/attendance" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Attendance
        </a>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Attendance Form</h5>
        </div>
        <div class="card-body">
            <form action="/attendance/update/<?= $attendance->id ?>" method="POST">
                <div class="form-group">
                    <label for="employee_id">Employee</label>
                    <select class="form-control" id="employee_id" name="employee_id" required>
                        <option value="">Select Employee</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= $employee->id ?>" <?= ($attendance->employee_id == $employee->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($employee->employee_id . ' - ' . $employee->first_name . ' ' . $employee->last_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($attendance->date) ?>" required>
                </div>
                <div class="form-group">
                    <label for="time_in">Time In</label>
                    <input type="time" class="form-control" id="time_in" name="time_in" value="<?= htmlspecialchars($attendance->time_in ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="time_out">Time Out</label>
                    <input type="time" class="form-control" id="time_out" name="time_out" value="<?= htmlspecialchars($attendance->time_out ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="present" <?= ($attendance->status == 'present') ? 'selected' : '' ?>>Present</option>
                        <option value="absent" <?= ($attendance->status == 'absent') ? 'selected' : '' ?>>Absent</option>
                        <option value="late" <?= ($attendance->status == 'late') ? 'selected' : '' ?>>Late</option>
                        <option value="half_day" <?= ($attendance->status == 'half_day') ? 'selected' : '' ?>>Half Day</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="note">Note</label>
                    <textarea class="form-control" id="note" name="note" rows="3"><?= htmlspecialchars($attendance->note ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="/attendance" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 