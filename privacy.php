<?php
$pageTitle = 'Privacy Policy | Salem Dominion Ministries';
$currentPage = 'other';

require_once 'config.php';
require_once 'db_connection.php';

include 'components/header.php';
?>

<style>
.policy-hero {
    background: linear-gradient(135deg, rgba(15,23,42,0.9), rgba(14,165,233,0.7));
    padding: 100px 0 50px;
    color: #fff;
    text-align: center;
}
.policy-hero h1 { font-family: 'Playfair Display', serif; font-size: 2.5rem; font-weight: 700; }
.policy-hero p { font-family: 'Montserrat', sans-serif; font-size: 1rem; opacity: 0.8; }

.policy-content { max-width: 900px; margin: 0 auto; padding: 40px 20px 80px; }
.policy-content h2 { font-family: 'Playfair Display', serif; color: #0f172a; font-size: 1.5rem; margin: 35px 0 15px; padding-bottom: 10px; border-bottom: 3px solid #fbbf24; display: inline-block; }
.policy-content h3 { font-family: 'Playfair Display', serif; color: #1e293b; font-size: 1.2rem; margin: 25px 0 10px; }
.policy-content p { color: #475569; line-height: 1.8; font-family: 'Montserrat', sans-serif; font-size: 0.95rem; }
.policy-content ul { color: #475569; line-height: 1.8; font-family: 'Montserrat', sans-serif; font-size: 0.95rem; padding-left: 25px; }
.policy-content ul li { margin-bottom: 8px; }
.policy-content .last-updated { color: #94a3b8; font-size: 0.85rem; margin-bottom: 30px; font-family: 'Montserrat', sans-serif; }
.policy-content .contact-box { background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 14px; padding: 25px; border-left: 4px solid #0ea5e9; margin: 30px 0; }
.policy-content .contact-box p { margin: 0; }
.back-link { font-family: 'Montserrat', sans-serif; font-size: 0.9rem; }
.back-link a { color: #0ea5e9; text-decoration: none; transition: color 0.3s; }
.back-link a:hover { color: #0284c7; }

@media(max-width:768px) { .policy-hero h1 { font-size: 2rem; } .policy-content { padding: 20px 15px 60px; } }
</style>

<section class="policy-hero" data-aos="fade-in">
    <div class="container">
        <h1 data-aos="fade-up"><i class="fas fa-shield-alt me-2"></i>Privacy Policy</h1>
        <p data-aos="fade-up" data-delay="100">Your privacy is important to us</p>
    </div>
</section>

<section class="policy-content" data-aos="fade-up">
    <p class="last-updated"><i class="fas fa-calendar me-1"></i> Last updated: <?= date('F j, Y') ?></p>

    <h2>1. Introduction</h2>
    <p>Salem Dominion Ministries ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website, <strong><?= CHURCH_WEBSITE ?></strong>.</p>
    <p>By accessing or using our website, you agree to the collection and use of information in accordance with this policy. If you do not agree, please do not use our website.</p>

    <h2>2. Information We Collect</h2>
    <h3>2.1 Personal Information</h3>
    <p>We may collect personally identifiable information that you voluntarily provide to us when you:</p>
    <ul>
        <li>Fill out a contact form (name, email address, phone number, and message)</li>
        <li>Register for events or programs</li>
        <li>Submit a prayer request</li>
        <li>Subscribe to our newsletter</li>
        <li>Make a donation or financial contribution</li>
        <li>Create an account on our website</li>
    </ul>

    <h3>2.2 Automatically Collected Information</h3>
    <p>When you visit our website, we may automatically collect certain information, including:</p>
    <ul>
        <li>IP address and browser type</li>
        <li>Operating system and device information</li>
        <li>Pages visited and time spent on our website</li>
        <li>Referring website or source</li>
        <li>Date and time of your visit</li>
    </ul>

    <h2>3. How We Use Your Information</h2>
    <p>We use the information we collect to:</p>
    <ul>
        <li>Respond to your inquiries and provide customer support</li>
        <li>Send you information about church events, services, and programs</li>
        <li>Process donations and provide tax receipts</li>
        <li>Manage event registrations and participation</li>
        <li>Improve our website and ministry services</li>
        <li>Send periodic emails about church news (only if you subscribe)</li>
        <li>Ensure the security and integrity of our website</li>
    </ul>

    <h2>4. Data Protection</h2>
    <p>We implement appropriate technical and organizational security measures to protect your personal information, including:</p>
    <ul>
        <li>SSL/TLS encryption for data in transit</li>
        <li>Secure database storage with access controls</li>
        <li>Regular security audits and updates</li>
        <li>Limited access to personal information on a need-to-know basis</li>
    </ul>
    <p>However, no method of electronic transmission or storage is 100% secure, and we cannot guarantee absolute security.</p>

    <h2>5. Cookies and Tracking</h2>
    <p>Our website may use cookies and similar tracking technologies to enhance your experience. Cookies are small files stored on your device that help us understand how you use our website. You can control cookie preferences through your browser settings.</p>

    <h2>6. Third-Party Services</h2>
    <p>We may use third-party services (such as Google Maps, YouTube, and payment processors) that collect information. These third parties have their own privacy policies, and we encourage you to review them. We are not responsible for the practices of these third-party services.</p>

    <h2>7. Children's Privacy</h2>
    <p>We are committed to protecting children's privacy. We do not knowingly collect personal information from children under 13 without parental consent. If you are a parent and believe your child has provided us with personal information, please contact us so we can delete it.</p>

    <h2>8. Sharing Your Information</h2>
    <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only:</p>
    <ul>
        <li>With your explicit consent</li>
        <li>To comply with legal obligations or church governance requirements</li>
        <li>To protect our rights, safety, or property</li>
        <li>With service providers who assist in operating our website (under strict confidentiality)</li>
    </ul>

    <h2>9. Your Rights</h2>
    <p>You have the right to:</p>
    <ul>
        <li>Access the personal information we hold about you</li>
        <li>Request correction of inaccurate information</li>
        <li>Request deletion of your personal information</li>
        <li>Opt out of receiving communications from us</li>
        <li>Lodge a complaint with a relevant authority</li>
    </ul>

    <h2>10. Changes to This Policy</h2>
    <p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated revision date. We encourage you to review this policy periodically.</p>

    <div class="contact-box">
        <h3><i class="fas fa-envelope me-2 text-primary"></i>Questions About This Policy?</h3>
        <p>If you have any questions about this Privacy Policy, please contact us:</p>
        <p class="mt-2">
            <strong>Email:</strong> <a href="mailto:<?= CHURCH_EMAIL ?>" style="color:#0ea5e9;"><?= CHURCH_EMAIL ?></a><br>
            <strong>Phone:</strong> <a href="tel:<?= CHURCH_PHONE ?>" style="color:#0ea5e9;"><?= CHURCH_PHONE ?></a><br>
            <strong>Address:</strong> <?= CHURCH_ADDRESS ?>
        </p>
    </div>

    <div class="back-link mt-4">
        <a href="index.php"><i class="fas fa-arrow-left me-1"></i>Back to Home</a>
    </div>
</section>

<?php include 'components/footer.php'; ?>
