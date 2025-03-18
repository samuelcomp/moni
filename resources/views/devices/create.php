<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add Device</h1>
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
            <form action="/devices/store" method="post">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="device_name">Device Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="device_name" name="device_name" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="device_type">Device Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="device_type" name="device_type" required>
                            <option value="zkteco">ZKTeco</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="ip_address">IP Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ip_address" name="ip_address" placeholder="192.168.1.201" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="port">Port <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="port" name="port" value="4370" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="device_id">Device ID</label>
                        <input type="text" class="form-control" id="device_id" name="device_id">
                        <small class="form-text text-muted">Optional identifier for the device</small>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="location">Location</label>
                        <input type="text" class="form-control" id="location" name="location">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="test_connection" name="test_connection" value="1" checked>
                        <label class="custom-control-label" for="test_connection">Test connection before saving</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Device
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 