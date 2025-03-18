<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Device Connection Test</h1>
        <a href="/devices" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Devices
        </a>
    </div>

    <?php if (isset($device)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Testing Connection to <?= htmlspecialchars($device->device_name ?? 'Device') ?></h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Device Name:</strong> <?= htmlspecialchars($device->device_name ?? 'N/A') ?>
                    </div>
                    <div class="col-md-4">
                        <strong>IP Address:</strong> <?= htmlspecialchars($device->ip_address ?? 'N/A') ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Port:</strong> <?= htmlspecialchars($device->port ?? 'N/A') ?>
                    </div>
                </div>

                <?php if (isset($result)): ?>
                    <?php if ($result['success']): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Connection successful!
                        </div>
                        
                        <?php if (isset($result['info']) && !empty($result['info'])): ?>
                            <h5 class="mt-4">Device Information</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <?php foreach ($result['info'] as $key => $value): ?>
                                        <tr>
                                            <th><?= htmlspecialchars($key) ?></th>
                                            <td><?= htmlspecialchars($value) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i> Connection failed: <?= htmlspecialchars($result['message']) ?>
                        </div>
                        <div class="mt-3">
                            <h5>Troubleshooting Tips:</h5>
                            <ul>
                                <li>Verify the device is powered on and connected to the network</li>
                                <li>Check that the IP address and port are correct</li>
                                <li>Ensure there are no firewalls blocking the connection</li>
                                <li>Confirm the device supports the ZKTeco protocol</li>
                            </ul>
                        </div>
                        <?php if (isset($result['debug'])): ?>
                            <div class="mt-3">
                                <h5>Debug Information:</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <?php foreach ($result['debug'] as $key => $value): ?>
                                                <tr>
                                                    <th><?= htmlspecialchars($key) ?></th>
                                                    <td><?= htmlspecialchars(is_bool($value) ? ($value ? 'true' : 'false') : $value) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-spinner fa-spin"></i> Testing connection...
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="/devices" class="btn btn-primary">
                    <i class="fas fa-list"></i> Back to Device List
                </a>
                <a href="/devices/test-connection/<?= $device->id ?>" class="btn btn-info ml-2">
                    <i class="fas fa-sync"></i> Test Again
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Device not found
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 