<?php include_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Application Settings</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="/settings/update" method="POST">
                        <?php if (isset($settings) && is_array($settings)): ?>
                            <?php foreach ($settings as $setting): ?>
                                <div class="form-group mb-3">
                                    <label for="<?php echo $setting->setting_key; ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $setting->setting_key)); ?>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="<?php echo $setting->setting_key; ?>" 
                                        name="<?php echo $setting->setting_key; ?>" 
                                        value="<?php echo htmlspecialchars($setting->value); ?>"
                                    >
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No settings found. Add settings to the database to manage them here.</p>
                        <?php endif; ?>
                        
                        <button type="submit" name="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?> 