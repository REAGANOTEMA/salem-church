<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$db = Database::getNamed('admin');
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

$backupDir = UPLOADS_PATH . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    switch ($action) {
        case 'create_backup':
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupDir . '/' . $filename;

            try {
                $pdo = $db->getPdo();
                $tables = [];
                $stmt = $pdo->query("SHOW TABLES");
                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    $tables[] = $row[0];
                }

                $output = "-- Salem Dominion Ministries Database Backup\n";
                $output .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
                $output .= "-- Database: " . DB_NAME . "\n\n";
                $output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

                foreach ($tables as $table) {
                    $output .= "-- Table: {$table}\n";
                    $output .= "DROP TABLE IF EXISTS `{$table}`;\n";

                    $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
                    $createRow = $createStmt->fetch(PDO::FETCH_NUM);
                    $output .= $createRow[1] . ";\n\n";

                    $dataStmt = $pdo->query("SELECT * FROM `{$table}`");
                    $rows = $dataStmt->fetchAll(PDO::FETCH_NUM);

                    if (!empty($rows)) {
                        $colStmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
                        $columns = $colStmt->fetchAll(PDO::FETCH_NUM);
                        $colNames = array_map(fn($c) => "`{$c[0]}`", $columns);

                        foreach ($rows as $row) {
                            $values = array_map(function ($v) {
                                return $v === null ? 'NULL' : $pdo->quote($v);
                            }, $row);
                            $output .= "INSERT INTO `{$table}` (" . implode(', ', $colNames) . ") VALUES (" . implode(', ', $values) . ");\n";
                        }
                        $output .= "\n";
                    }
                }

                $output .= "SET FOREIGN_KEY_CHECKS = 1;\n";

                file_put_contents($filepath, $output);
                logActivity($db, 'created', 'backups', $_SESSION['admin_id'], "Created backup: {$filename}");

                if ($isAjax) jsonSuccess(['filename' => $filename], 'Backup created successfully');
                setFlash('success', 'Backup created successfully: ' . $filename);
            } catch (Exception $e) {
                error_log("Backup failed: " . $e->getMessage());
                if ($isAjax) jsonError('Backup failed: ' . $e->getMessage());
                setFlash('error', 'Backup failed: ' . $e->getMessage());
            }
            redirect(BASE_URL . '/admin/dashboard.php?section=backups');
            break;

        case 'delete_backup':
            $filename = $_POST['filename'] ?? '';
            if ($filename && preg_match('/^backup_[\d\-_]+\.sql$/', $filename)) {
                $fullPath = $backupDir . '/' . $filename;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    logActivity($db, 'deleted', 'backups', $_SESSION['admin_id'], "Deleted backup: {$filename}");
                }
            }
            if ($isAjax) jsonSuccess([], 'Backup deleted');
            setFlash('success', 'Backup deleted');
            redirect(BASE_URL . '/admin/dashboard.php?section=backups');
            break;
    }
}

if ($action === 'download' && !empty($_GET['file'])) {
    $filename = $_GET['file'];
    if (preg_match('/^backup_[\d\-_]+\.sql$/', $filename)) {
        $fullPath = $backupDir . '/' . $filename;
        if (file_exists($fullPath)) {
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($fullPath));
            header('Cache-Control: no-cache, must-revalidate');
            readfile($fullPath);
            exit;
        }
    }
    setFlash('error', 'Backup file not found');
    redirect(BASE_URL . '/admin/dashboard.php?section=backups');
}

$backups = [];
$files = glob($backupDir . '/backup_*.sql');
if ($files) {
    usort($files, function ($a, $b) { return filemtime($b) - filemtime($a); });
    foreach ($files as $file) {
        $backups[] = [
            'filename' => basename($file),
            'size' => filesize($file),
            'date' => date('Y-m-d H:i:s', filemtime($file)),
        ];
    }
}

$totalBackupSize = array_sum(array_column($backups, 'size'));

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-database me-2"></i>Database Backups</h4>
    <form method="POST" class="d-inline">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="create_backup">
        <button type="submit" class="btn btn-primary" onclick="this.disabled=true;this.innerHTML='<i class=\'fas fa-spinner fa-spin me-1\'></i> Creating...'">
            <i class="fas fa-download me-1"></i> Create Backup
        </button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-database fa-2x text-primary mb-2"></i>
                <h3 class="mb-0"><?= count($backups) ?></h3>
                <small class="text-muted">Total Backups</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-hdd fa-2x text-info mb-2"></i>
                <h3 class="mb-0"><?= number_format($totalBackupSize / 1024, 1) ?> KB</h3>
                <small class="text-muted">Total Size</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x text-success mb-2"></i>
                <h3 class="mb-0"><?= !empty($backups) ? $backups[0]['date'] : 'Never' ?></h3>
                <small class="text-muted">Last Backup</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Backup Files</h5></div>
    <div class="card-body">
        <?php if (empty($backups)): ?>
            <div class="text-center py-5">
                <i class="fas fa-database fa-3x text-muted mb-3"></i>
                <p class="text-muted">No backups found. Create your first backup!</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td>
                                <i class="fas fa-file-code text-primary me-2"></i>
                                <code><?= sanitize($backup['filename']) ?></code>
                            </td>
                            <td><small><?= number_format($backup['size'] / 1024, 1) ?> KB</small></td>
                            <td><small><?= $backup['date'] ?></small></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="?action=download&file=<?= urlencode($backup['filename']) ?>" class="btn btn-outline-success" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this backup permanently?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete_backup">
                                        <input type="hidden" name="filename" value="<?= sanitize($backup['filename']) ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h6><i class="fas fa-info-circle me-1"></i> Backup Notes</h6>
        <ul class="small text-muted mb-0">
            <li>Backups are stored as SQL dump files in the uploads/backups directory.</li>
            <li>Backups contain the complete database structure and data.</li>
            <li>Download backups regularly and store them in a safe location.</li>
            <li>To restore a backup, import the SQL file using phpMyAdmin or MySQL command line.</li>
        </ul>
    </div>
</div>
