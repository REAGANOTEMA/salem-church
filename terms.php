<?php
$pageTitle = 'Terms of Service | Salem Dominion Ministries';
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
        <h1 data-aos="fade-up"><i class="fas fa-file-contract me-2"></i>Terms of Service</h1>
        <p data-aos="fade-up" data-delay="100">Please read these terms carefully</p>
    </div>
</section>

<section class="policy-content" data-aos="fade-up">
    <p class="last-updated"><i class="fas fa-calendar me-1"></i> Last updated: <?= date('F j, Y') ?></p>

    <h2>1. Acceptance of Terms</h2>
    <p>By accessing and using the website of Salem Dominion Ministries ("we," "our," or "us") at <strong><?= CHURCH_WEBSITE ?></strong>, you accept and agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our website.</p>
    <p>These terms apply to all visitors, users, and others who access or use our website.</p>

    <h2>2. Use of Website</h2>
    <p>Our website is provided for informational purposes related to Salem Dominion Ministries, including our programs, events, services, and community activities. You agree to use our website only for lawful purposes and in accordance with these terms.</p>
    <p>You agree not to:</p>
    <ul>
        <li>Use the website in any way that violates applicable laws or regulations</li>
        <li>Attempt to gain unauthorized access to any portion of the website or its systems</li>
        <li>Use the website to transmit harmful, abusive, or inappropriate content</li>
        <li>Interfere with or disrupt the website or servers connected to the website</li>
        <li>Use automated systems (bots, scrapers) to access the website without our written permission</li>
        <li>Impersonate any person or entity associated with Salem Dominion Ministries</li>
    </ul>

    <h2>3. Donations and Financial Contributions</h2>
    <p>Salem Dominion Ministries accepts donations and financial contributions to support our ministry activities. By making a donation through our website:</p>
    <ul>
        <li>You confirm that you are authorized to make the donation and use the selected payment method</li>
        <li>All donations are voluntary and non-refundable, except in cases of demonstrable error</li>
        <li>Donation receipts will be provided for tax purposes as required by applicable law</li>
        <li>We use secure, third-party payment processors. We do not store your payment card details</li>
        <li>Recurring donations can be cancelled at any time by contacting us</li>
    </ul>
    <p>If you believe a donation was made in error, please contact us within 30 days at <?= CHURCH_EMAIL ?>.</p>

    <h2>4. Events and Registration</h2>
    <p>Our website may allow you to register for church events and programs. By registering:</p>
    <ul>
        <li>You agree to provide accurate and current information</li>
        <li>You understand that event availability may be limited</li>
        <li>Cancellation policies may apply to specific events and will be communicated during registration</li>
        <li>We reserve the right to modify or cancel events due to unforeseen circumstances</li>
        <li>Photos and videos may be taken at events for church communications and promotional purposes</li>
    </ul>

    <h2>5. Intellectual Property</h2>
    <p>All content on this website, including but not limited to text, graphics, logos, images, audio, video, software, and other materials, is the property of Salem Dominion Ministries or its content suppliers and is protected by copyright, trademark, and other intellectual property laws.</p>
    <p>You may view and access content for personal, non-commercial use. You may not:</p>
    <ul>
        <li>Reproduce, distribute, or create derivative works from our content without written permission</li>
        <li>Use our logo, branding, or trademarks for any purpose without authorization</li>
        <li>Reupload or redistribute sermon recordings, event materials, or church media</li>
        <li>Use content from our website for commercial purposes</li>
    </ul>
    <p>For permission to use our content, please contact <?= CHURCH_EMAIL ?>.</p>

    <h2>6. User-Generated Content</h2>
    <p>Our website may allow users to submit content such as comments, prayer requests, testimonials, or contact forms. By submitting content:</p>
    <ul>
        <li>You grant Salem Dominion Ministries a non-exclusive, royalty-free license to use, display, and moderate your content</li>
        <li>You represent that you own or have the necessary rights to the content you submit</li>
        <li>You agree not to submit content that is unlawful, defamatory, or infringes on others' rights</li>
        <li>We reserve the right to remove any content that violates these terms</li>
    </ul>

    <h2>7. Limitation of Liability</h2>
    <p>To the fullest extent permitted by law, Salem Dominion Ministries shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of or relating to:</p>
    <ul>
        <li>Your use of or inability to use our website</li>
        <li>Any unauthorized access to or alteration of your data</li>
        <li>Any errors or omissions in website content</li>
        <li>The conduct of any user or third party on or through the website</li>
    </ul>
    <p>Our website is provided "as is" and "as available" without warranties of any kind, either express or implied.</p>

    <h2>8. Privacy</h2>
    <p>Your use of our website is also governed by our <a href="privacy.php" style="color:#0ea5e9;">Privacy Policy</a>, which is incorporated into these terms by reference. Please review it to understand our practices regarding your personal information.</p>

    <h2>9. Links to Third-Party Websites</h2>
    <p>Our website may contain links to third-party websites or services (such as social media platforms, YouTube, or payment processors). We are not responsible for the content, privacy practices, or availability of these third-party sites. Accessing them is at your own risk.</p>

    <h2>10. Modifications to Terms</h2>
    <p>We reserve the right to modify these Terms of Service at any time. Changes will be effective immediately upon posting on this page. Your continued use of the website after any changes constitutes your acceptance of the new terms.</p>

    <h2>11. Governing Law</h2>
    <p>These Terms of Service shall be governed by and construed in accordance with the laws of the Republic of Uganda. Any disputes arising from these terms shall be subject to the exclusive jurisdiction of the courts of Uganda.</p>

    <h2>12. Severability</h2>
    <p>If any provision of these terms is found to be unenforceable or invalid, that provision will be limited or eliminated to the minimum extent necessary, and the remaining provisions will remain in full force and effect.</p>

    <div class="contact-box">
        <h3><i class="fas fa-envelope me-2 text-primary"></i>Contact for Questions</h3>
        <p>If you have any questions about these Terms of Service, please contact us:</p>
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
