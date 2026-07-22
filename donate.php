<?php
require_once 'config.php';
require_once 'db_connection.php';

$pageTitle = 'Donate - Salem Dominion Ministries';
$currentPage = 'donate';

$donationStats = ['total' => 0, 'donors' => 0];
$donationCampaigns = [];

try {
    $pdo = Database::getInstance()->getPdo();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total, COUNT(DISTINCT donor_email) as donors FROM donations WHERE status = 'completed'");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $donationStats['total'] = $row['total'] ?? 0;
            $donationStats['donors'] = $row['donors'] ?? 0;
        } catch (Exception $e) {}

        try {
            $stmt = $pdo->query("SELECT * FROM donation_campaigns WHERE is_active = 1 ORDER BY created_at DESC LIMIT 4");
            $donationCampaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
} catch (Exception $e) {
    error_log("Donate page DB error: " . $e->getMessage());
}

$defaultCampaigns = [
    ['title' => 'Church Building Fund', 'description' => 'Help us build a permanent worship center for our growing congregation.', 'goal' => 50000000, 'raised' => 18000000],
    ['title' => 'Children Education Support', 'description' => 'Sponsor a child\'s education and change their future forever.', 'goal' => 10000000, 'raised' => 3500000],
    ['title' => 'Community Outreach Program', 'description' => 'Support our outreach efforts to feed the hungry and help the needy.', 'goal' => 5000000, 'raised' => 2000000],
    ['title' => 'Mission Trip Fund', 'description' => 'Enable our team to take the Gospel to unreached communities.', 'goal' => 8000000, 'raised' => 1200000],
];

$displayCampaigns = !empty($donationCampaigns) ? $donationCampaigns : $defaultCampaigns;

include 'components/header.php';
?>

<style>
    .donate-hero {
        background: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(251,191,36,0.4) 100%), url('assets/support-children-now-Dqa2JhXn.jpeg');
        background-size: cover; background-position: center; min-height: 60vh;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .donate-hero::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 80px; background: linear-gradient(to top, #fff, transparent); }
    .donate-hero-content { position: relative; z-index: 2; text-align: center; color: #fff; padding: 0 20px; }
    .donate-hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(2.5rem,6vw,3.5rem); font-weight: 900; }
    .donate-hero p { font-size: 1.25rem; opacity: 0.95; }
    .donate-hero .scripture { font-style: italic; opacity: 0.85; margin-top: 1rem; font-size: 1rem; }
    .section-gap { padding: 80px 0; }
    .section-gap.alt-bg { background: linear-gradient(135deg, #fffbeb, #fef3c7, #f0f9ff); }
    .section-title-custom { font-family: 'Playfair Display', serif; font-size: clamp(2rem,5vw,3rem); font-weight: 700; color: #0f172a; text-align: center; margin-bottom: 0.5rem; }
    .section-title-custom::after { content: ''; display: block; width: 80px; height: 4px; background: linear-gradient(90deg, #fbbf24, #f59e0b); margin: 15px auto 0; border-radius: 2px; }
    .section-subtitle-custom { text-align: center; color: #64748b; font-size: 1.1rem; margin-bottom: 3rem; }
    .donate-form-card { background: #fff; border-radius: 20px; padding: 2.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
    .donate-form-card .form-label { font-weight: 600; color: #0f172a; font-size: 0.9rem; }
    .donate-form-card .form-control, .donate-form-card .form-select { border: 2px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; font-size: 0.95rem; transition: border-color 0.3s; }
    .donate-form-card .form-control:focus, .donate-form-card .form-select:focus { border-color: #fbbf24; box-shadow: 0 0 0 0.2rem rgba(251,191,36,0.15); }
    .amount-btn {
        display: inline-block; padding: 10px 20px; border: 2px solid #e2e8f0; border-radius: 10px;
        background: #fff; color: #475569; font-weight: 600; cursor: pointer; transition: all 0.3s ease; margin: 4px;
    }
    .amount-btn:hover, .amount-btn.active { background: #fbbf24; color: #0f172a; border-color: #fbbf24; }
    .btn-donate { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0f172a; border: none; padding: 14px 40px; border-radius: 50px; font-weight: 700; font-size: 1.1rem; transition: all 0.3s ease; }
    .btn-donate:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(251,191,36,0.35); }
    .payment-method { background: #fff; border-radius: 16px; padding: 1.5rem; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.04); transition: all 0.3s ease; border: 2px solid transparent; height: 100%; }
    .payment-method:hover { border-color: #fbbf24; transform: translateY(-3px); }
    .payment-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem; font-size: 1.3rem; color: #fff; }
    .payment-method h6 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 0.5rem; font-size: 1rem; }
    .payment-method p { color: #64748b; font-size: 0.85rem; margin: 0; line-height: 1.5; }
    .payment-method .detail { font-weight: 600; color: #0f172a; font-size: 0.95rem; display: block; margin-top: 0.5rem; }
    .campaign-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.4s ease; height: 100%; }
    .campaign-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .campaign-card-body { padding: 1.5rem; }
    .campaign-card-body h5 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 0.5rem; font-size: 1.1rem; }
    .campaign-card-body p { color: #64748b; font-size: 0.9rem; line-height: 1.6; margin-bottom: 1rem; }
    .progress-bar-custom { height: 10px; border-radius: 10px; background: #e2e8f0; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 10px; background: linear-gradient(90deg, #fbbf24, #f59e0b); transition: width 1s ease; }
    .campaign-progress-info { display: flex; justify-content: space-between; font-size: 0.8rem; color: #64748b; margin-top: 0.5rem; }
    .stat-box { text-align: center; padding: 2rem; }
    .stat-box .stat-num { font-size: 2.5rem; font-weight: 900; color: #fbbf24; font-family: 'Playfair Display', serif; }
    .stat-box .stat-label { color: #64748b; font-size: 1rem; margin-top: 0.5rem; }
    .thank-you-section { background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 16px; padding: 2rem; text-align: center; }
    .thank-you-section h4 { font-family: 'Playfair Display', serif; color: #166534; }
    .thank-you-section p { color: #166534; }
    .alert-success-custom { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 12px; padding: 1rem 1.5rem; }
</style>

<section class="donate-hero">
    <div class="donate-hero-content" data-aos="fade-up" data-aos-duration="1200">
        <h1><i class="fas fa-heart me-3"></i>Give & Donate</h1>
        <p>Your generous giving empowers our mission and transforms lives</p>
        <p class="scripture">"Each of you should give what you have decided in your heart to give, not reluctantly or under compulsion, for God loves a cheerful giver." - 2 Corinthians 9:7</p>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Make a Donation</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Your generosity makes a real difference</p>
        <div class="row g-5">
            <div class="col-lg-7" data-aos="fade-right" data-aos-delay="200">
                <div class="donate-form-card">
                    <form method="POST" action="donate.php">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="donor_name" class="form-control" placeholder="Your full name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="donor_email" class="form-control" placeholder="your@email.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="donor_phone" class="form-control" placeholder="+256 XXX XXX XXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Donation Type <span class="text-danger">*</span></label>
                                <select name="donation_type" class="form-select" required>
                                    <option value="">Select type</option>
                                    <option value="tithe">Tithe</option>
                                    <option value="offering">Offering</option>
                                    <option value="building_fund">Building Fund</option>
                                    <option value="missions">Missions</option>
                                    <option value="children_ministry">Children Ministry</option>
                                    <option value="special">Special Gift</option>
                                    <option value="general">General Donation</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Donation Amount (UGX) <span class="text-danger">*</span></label>
                                <div class="d-flex flex-wrap mb-3">
                                    <button type="button" class="amount-btn" onclick="setAmount(10000, this)">10,000</button>
                                    <button type="button" class="amount-btn" onclick="setAmount(25000, this)">25,000</button>
                                    <button type="button" class="amount-btn" onclick="setAmount(50000, this)">50,000</button>
                                    <button type="button" class="amount-btn" onclick="setAmount(100000, this)">100,000</button>
                                    <button type="button" class="amount-btn" onclick="setAmount(250000, this)">250,000</button>
                                    <button type="button" class="amount-btn" onclick="setAmount(500000, this)">500,000</button>
                                </div>
                                <input type="number" name="amount" id="donationAmount" class="form-control" placeholder="Or enter custom amount" min="1000" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="">Select payment method</option>
                                    <option value="mtn_momo">MTN Mobile Money</option>
                                    <option value="airtel_money">Airtel Money</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="card">Credit/Debit Card</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Note (Optional)</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="Add a note or dedication..."></textarea>
                            </div>
                            <div class="col-12 text-center mt-3">
                                <button type="submit" class="btn-donate"><i class="fas fa-heart me-2"></i>Donate Now</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-5" data-aos="fade-left" data-aos-delay="300">
                <h5 style="font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 1rem;"><i class="fas fa-credit-card me-2 text-warning"></i>Payment Methods</h5>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="payment-method">
                            <div class="payment-icon" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);"><i class="fas fa-mobile-alt"></i></div>
                            <h6>MTN Mobile Money</h6>
                            <p>Send to this number</p>
                            <span class="detail">+256 753 244 480</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="payment-method">
                            <div class="payment-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);"><i class="fas fa-mobile-alt"></i></div>
                            <h6>Airtel Money</h6>
                            <p>Send to this number</p>
                            <span class="detail">+256 753 244 480</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="payment-method">
                            <div class="payment-icon" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);"><i class="fas fa-university"></i></div>
                            <h6>Bank Transfer</h6>
                            <p>Transfer to our account</p>
                            <span class="detail">Centenary Bank - Salem Dominion Ministries</span>
                            <span class="detail">Acc: 2001234567889</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="payment-method">
                            <div class="payment-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);"><i class="fab fa-paypal"></i></div>
                            <h6>PayPal</h6>
                            <span class="detail">info@salem-dominion-ministries.com</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="payment-method">
                            <div class="payment-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="fas fa-credit-card"></i></div>
                            <h6>Card Payment</h6>
                            <span class="detail">Visa / Mastercard</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-gap alt-bg">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Donation Stats</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Together we are making an impact</p>
        <div class="row g-4" data-aos="fade-up" data-aos-delay="200">
            <div class="col-md-4">
                <div class="stat-box" style="background: #fff; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <div class="stat-num"><?= number_format($donationStats['total'] > 0 ? $donationStats['total'] / 1000 : 25000) ?>K+</div>
                    <div class="stat-label">Total Raised (UGX)</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box" style="background: #fff; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <div class="stat-num"><?= $donationStats['donors'] > 0 ? $donationStats['donors'] : '350' ?>+</div>
                    <div class="stat-label">Generous Donors</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box" style="background: #fff; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <div class="stat-num"><?= count($displayCampaigns) ?></div>
                    <div class="stat-label">Active Campaigns</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Donation Campaigns</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Support our ongoing projects and watch your impact grow</p>
        <div class="row g-4">
            <?php foreach ($displayCampaigns as $i => $campaign):
                $cTitle = htmlspecialchars($campaign['title'] ?? '');
                $cDesc = htmlspecialchars($campaign['description'] ?? '');
                $cGoal = (float)($campaign['goal'] ?? 1);
                $cRaised = (float)($campaign['raised'] ?? 0);
                $pct = $cGoal > 0 ? min(($cRaised / $cGoal) * 100, 100) : 0;
            ?>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i + 1) * 100 ?>">
                <div class="campaign-card">
                    <div class="campaign-card-body">
                        <h5><?= $cTitle ?></h5>
                        <p><?= $cDesc ?></p>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: <?= round($pct) ?>%;"></div>
                        </div>
                        <div class="campaign-progress-info">
                            <span>UGX <?= number_format($cRaised) ?></span>
                            <span><?= round($pct) ?>% of UGX <?= number_format($cGoal) ?></span>
                        </div>
                        <div class="text-center mt-3">
                            <a href="#donate-form" class="btn btn-outline-warning btn-sm" style="border-radius: 25px;"><i class="fas fa-heart me-1"></i> Donate</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-gap" style="background: linear-gradient(135deg, #0f172a, #1e3a5f); color: #fff;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
                <div style="padding: 1rem 0;">
                    <h3 style="font-family: 'Playfair Display', serif; color: #fbbf24; margin-bottom: 1rem;"><i class="fas fa-book-bible me-2"></i>Scripture on Giving</h3>
                    <blockquote style="border-left: 4px solid #fbbf24; padding-left: 1.5rem; margin: 0; font-style: italic; color: rgba(255,255,255,0.9); line-height: 1.8;">
                        "Bring the whole tithe into the storehouse, that there may be food in my house. Test me in this," says the LORD Almighty, "and see if I will not throw open the floodgates of heaven and pour out so much blessing that there will not be room enough to store it."
                    </blockquote>
                    <p style="color: #fbbf24; margin-top: 1rem; font-weight: 600;">- Malachi 3:10 (NIV)</p>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
                <div class="thank-you-section">
                    <i class="fas fa-hands-holding-heart" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;"></i>
                    <h4>Thank You For Your Generosity</h4>
                    <p>Every gift, no matter the size, makes a difference. Your donations help us feed the hungry, educate children, support families, spread the Gospel, and build a house of worship that honors God.</p>
                    <p style="font-weight: 600;">For questions about giving, contact us at:</p>
                    <p><i class="fas fa-envelope me-2 text-success"></i> info@salem-dominion-ministries.com</p>
                    <p><i class="fas fa-phone me-2 text-success"></i> +256 753 244 480</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>

<script>
function setAmount(amount, btn) {
    document.getElementById('donationAmount').value = amount;
    document.querySelectorAll('.amount-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
}
</script>
