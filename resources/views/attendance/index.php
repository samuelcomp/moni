<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Attendance Management</h1>
        <a href="/attendance/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Attendance
        </a>
    </div>

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

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Today's Attendance</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($todayAttendance)): ?>
                            <?php foreach ($todayAttendance as $attendance): ?>
                                <tr>
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
                                    <td>
                                        <a href="/attendance/edit/<?= $attendance->id ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal" data-id="<?= $attendance->id ?>">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No attendance records found for today.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this attendance record?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" id="deleteButton" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#deleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var deleteUrl = '/attendance/delete/' + id;
            
            var modal = $(this);
            modal.find('#deleteButton').attr('href', deleteUrl);
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 