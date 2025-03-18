<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Device</h1>
        <a href="/devices" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Devices
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
            <h5 class="card-title mb-0">Device Information</h5>
        </div>
        <div class="card-body">
            <form action="/devices/update/<?= $device->id ?>" method="post">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="device_name">Device Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="device_name" name="device_name" value="<?= htmlspecialchars($device->device_name ?? '') ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="device_type">Device Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="device_type" name="device_type" required>
                            <option value="zkteco" <?= ($device->device_type === 'zkteco') ? 'selected' : '' ?>>ZKTeco</option>
                            <option value="other" <?= ($device->device_type === 'other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="ip_address">IP Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ip_address" name="ip_address" value="<?= htmlspecialchars($device->ip_address ?? '') ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="port">Port <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="port" name="port" value="<?= htmlspecialchars($device->port ?? '4370') ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="device_id">Device ID</label>
                        <input type="text" class="form-control" id="device_id" name="device_id" value="<?= htmlspecialchars($device->device_id ?? '') ?>">
                        <small class="form-text text-muted">Optional identifier for the device</small>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="location">Location</label>
                        <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($device->location ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active" <?= ($device->status === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($device->status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            <option value="maintenance" <?= ($device->status === 'maintenance') ? 'selected' : '' ?>>Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($device->notes ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Device
                </button>
            </form>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Device Actions</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <a href="/devices/test-connection/<?= $device->id ?>" class="btn btn-info btn-block mb-3">
                        <i class="fas fa-plug"></i> Test Connection
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="/devices/sync/<?= $device->id ?>" class="btn btn-success btn-block mb-3">
                        <i class="fas fa-sync"></i> Sync Attendance
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="/devices/sync/<?= $device->id ?>?clear=1" class="btn btn-warning btn-block mb-3" onclick="return confirm('Are you sure you want to sync and clear attendance data from the device?')">
                        <i class="fas fa-eraser"></i> Sync & Clear
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 