<?php require_once __DIR__ . '/layouts/header.php'; ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Debug Devices</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Available Devices</h6>
        </div>
        <div class="card-body">
            <?php if (empty($devices)): ?>
                <div class="alert alert-warning">
                    No devices found. <a href="/devices/create">Add a device</a>
                </div>
            <?php else: ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>IP Address</th>
                            <th>Port</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($devices as $device): ?>
                            <tr>
                                <td><?= $device->id ?></td>
                                <td><?= $device->device_name ?? $device->name ?? 'No Name' ?></td>
                                <td><?= $device->ip_address ?></td>
                                <td><?= $device->port ?></td>
                                <td>
                                    <?php if (isset($device->status)): ?>
                                        <?= $device->status ? 'Active' : 'Inactive' ?>
                                    <?php else: ?>
                                        Unknown
                                    <?php endif; ?>
                                </td>
                                <td><?= $device->created_at ?? 'Unknown' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <h6 class="mt-4 font-weight-bold">Database Table Structure</h6>
            <pre>
<?php
$columns = $db->query("SHOW COLUMNS FROM devices")->fetchAll(PDO::FETCH_ASSOC);
print_r($columns);
?>
            </pre>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?> 