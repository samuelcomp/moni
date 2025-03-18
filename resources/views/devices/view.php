<!-- Add this modal for date-based sync -->
<div class="modal fade" id="syncByDateModal" tabindex="-1" role="dialog" aria-labelledby="syncByDateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="syncByDateModalLabel">Sync Attendance by Date Range</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/devices/sync-by-date/<?= $device->id ?>" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= date('Y-m-d', strtotime('-7 days')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date:</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Sync</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add this button next to the regular sync button -->
<a href="#" class="btn btn-info" data-toggle="modal" data-target="#syncByDateModal">
    <i class="fas fa-calendar-alt"></i> Sync by Date
</a> 