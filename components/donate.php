<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken()) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $name = trim($_POST['donor_name'] ?? '');
        $email = trim($_POST['donor_email'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        $type = trim($_POST['donation_type'] ?? 'general');
        $phone = trim($_POST['donor_phone'] ?? '');
        $method = trim($_POST['payment_method'] ?? 'cash');

        if (empty($name) || $amount <= 0) {
            $error = 'Name and a valid amount are required.';
        } else {
            $db = Database::getInstance();
            $reference = 'DON-' . strtoupper(bin2hex(random_bytes(6))) . '-' . date('ymd');
            $db->insert('donations', [
                'donor_name'     => $name,
                'donor_email'    => $email,
                'donor_phone'    => $phone,
                'amount'         => $amount,
                'donation_type'  => $type,
                'payment_method' => $method,
                'transaction_id' => $reference,
                'status'         => 'pending',
                'ip_address'     => $_SERVER['REMOTE_ADDR'] ?? '',
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
            $msg = urlencode("Praise God Pastor! I want to give a donation.\n\nName: $name\nAmount: UGX $amount\nType: $type\nMethod: $method\nPhone: $phone");
            header("Location: https://wa.me/256753244480?text=$msg");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Give - Salem Dominion Ministries</title>
    <link rel="icon" href="public/logo-icon.jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@300;600&display=swap" rel="stylesheet">
    <style>
        :root { --navy: #0f172a; --gold: #fbbf24; }
        body { background: #f8fafc; font-family: 'Montserrat', sans-serif; }
        .donate-card { border: none; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); overflow: hidden; }
        .bible-texture { background: linear-gradient(135deg, var(--navy), #1e293b); color: white; padding: 60px 20px; text-align: center; }
        .btn-gold { background: var(--gold); color: var(--navy); font-weight: bold; border-radius: 50px; padding: 15px 30px; border: none; }
        .form-control { border-radius: 15px; padding: 12px; border: 2px solid #e2e8f0; }
    </style>
</head>
<body>
    <?php require_once 'components/universal_nav_perfect.php'; ?>
    
    <div class="bible-texture">
        <h1 class="display-4 font-playfair">Generous Giving</h1>
        <p class="lead">"God loves a cheerful giver." - 2 Corinthians 9:7</p>
    </div>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card donate-card p-4">
                    <form method="POST">
                        <?= csrfField() ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="donor_name" class="form-control" required placeholder="Enter your name">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="donor_email" class="form-control" placeholder="Optional">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Phone (WhatsApp)</label>
                                <input type="text" name="donor_phone" class="form-control" required placeholder="+256...">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Amount (UGX)</label>
                            <input type="number" name="amount" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Offering Type</label>
                            <select name="donation_type" class="form-select form-control">
                                <option value="tithe">Tithe</option>
                                <option value="offering">Sunday Offering</option>
                                <option value="building_fund">Building Fund</option>
                                <option value="missions">Missions</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Payment Method</label>
                            <select name="payment_method" class="form-select form-control">
                                <option value="mobile_money">Mobile Money (MTN/Airtel)</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-gold w-100">Proceed to Pastor's WhatsApp</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'components/ultimate_footer_clean.php'; ?>
</body>
</html>