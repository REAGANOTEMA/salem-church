<?php
// Reusable Footer Component for Salem Dominion Ministries
// Include this file in any page to display the footer
?>
<footer class="footer">
    <div class="container">
        <div class="row">
            <!-- Church Info -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="footer-section">
                    <h4 class="footer-title">
                        <i class="fas fa-church me-2"></i>Salem Dominion Ministries
                    </h4>
                    <p class="footer-text">
                        A vibrant community of believers dedicated to spreading the love of God 
                        and transforming lives through the power of the Holy Spirit.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="footer-section">
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-home me-2"></i>Home</a></li>
                        <li><a href="about.php"><i class="fas fa-info-circle me-2"></i>About Us</a></li>
                        <li><a href="news.php"><i class="fas fa-newspaper me-2"></i>News</a></li>
                        <li><a href="sermons.php"><i class="fas fa-bible me-2"></i>Sermons</a></li>
                        <li><a href="events.php"><i class="fas fa-calendar me-2"></i>Events</a></li>
                        <li><a href="gallery.php"><i class="fas fa-images me-2"></i>Gallery</a></li>
                        <li><a href="contact.php"><i class="fas fa-envelope me-2"></i>Contact</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="footer-section">
                    <h4 class="footer-title">Contact Us</h4>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt me-3"></i>
                            <span>Iganga, Uganda</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone me-3"></i>
                            <span>+256 123 456 789</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope me-3"></i>
                            <span>info@salem-dominion-ministries.org</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock me-3"></i>
                            <span>Sunday Service: 9:00 AM - 12:00 PM</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Newsletter Signup -->
        <div class="row">
            <div class="col-12">
                <div class="newsletter-section">
                    <h4 class="newsletter-title">Stay Connected</h4>
                    <p class="newsletter-text">Subscribe to our newsletter for latest updates and events</p>
                    <form class="newsletter-form" onsubmit="subscribeNewsletter(event)">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Enter your email" required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane me-2"></i>Subscribe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="row">
            <div class="col-12">
                <div class="footer-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <p class="copyright">
                                &copy; <?php echo date('Y'); ?> Salem Dominion Ministries. All rights reserved.
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="footer-bottom-links">
                                <a href="#">Privacy Policy</a>
                                <a href="#">Terms of Service</a>
                                <a href="#">Cookie Policy</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Newsletter Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="newsletterToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-envelope text-primary me-2"></i>
            <strong class="me-auto">Newsletter</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            Successfully subscribed to newsletter!
        </div>
    </div>
</div>

<style>
/* Footer Styles */
.footer {
    background: linear-gradient(135deg, var(--primary-color), #1a2942);
    color: var(--white);
    padding: 60px 0 0;
    margin-top: 80px;
    position: relative;
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--accent-color), var(--secondary-color));
}

.footer-section {
    margin-bottom: 30px;
}

.footer-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    color: var(--white);
    position: relative;
    padding-bottom: 10px;
}

.footer-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: var(--accent-color);
}

.footer-text {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
    margin-bottom: 20px;
}

.social-links {
    display: flex;
    gap: 15px;
}

.social-link {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: var(--secondary-color);
    color: var(--white);
    transform: translateY(-3px);
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.footer-links a:hover {
    color: var(--white);
    transform: translateX(5px);
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.contact-item {
    display: flex;
    align-items: center;
    color: rgba(255, 255, 255, 0.8);
}

.contact-item i {
    width: 20px;
    text-align: center;
    color: var(--accent-color);
}

.newsletter-section {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: 30px;
    text-align: center;
    margin: 40px 0;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.newsletter-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    margin-bottom: 15px;
    color: var(--white);
}

.newsletter-text {
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 25px;
}

.newsletter-form .input-group {
    max-width: 500px;
    margin: 0 auto;
}

.newsletter-form .form-control {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: var(--white);
    padding: 12px 20px;
}

.newsletter-form .form-control::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.newsletter-form .form-control:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: var(--secondary-color);
    color: var(--white);
    box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 30px;
    margin-top: 40px;
}

.copyright {
    color: rgba(255, 255, 255, 0.6);
    margin: 0;
}

.footer-bottom-links {
    display: flex;
    gap: 20px;
    justify-content: flex-end;
}

.footer-bottom-links a {
    color: rgba(255, 255, 255, 0.6);
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover {
    color: var(--white);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .footer {
        padding: 40px 0 0;
        margin-top: 60px;
    }
    
    .footer-title {
        font-size: 1.3rem;
    }
    
    .social-links {
        justify-content: center;
    }
    
    .newsletter-section {
        padding: 20px;
        margin: 30px 0;
    }
    
    .newsletter-title {
        font-size: 1.5rem;
    }
    
    .footer-bottom-links {
        justify-content: center;
        margin-top: 15px;
    }
    
    .footer-bottom .row {
        text-align: center;
    }
}

@media (max-width: 480px) {
    .footer {
        padding: 30px 0 0;
    }
    
    .footer-section {
        margin-bottom: 25px;
    }
    
    .footer-title {
        font-size: 1.2rem;
    }
    
    .newsletter-section {
        padding: 15px;
    }
    
    .newsletter-title {
        font-size: 1.3rem;
    }
    
    .footer-bottom-links {
        flex-direction: column;
        gap: 10px;
        align-items: center;
    }
}
</style>

<script>
// Newsletter subscription function
function subscribeNewsletter(event) {
    event.preventDefault();
    
    const form = event.target;
    const email = form.querySelector('input[type="email"]').value;
    const toastEl = document.getElementById('newsletterToast');
    const toastMessage = document.getElementById('toastMessage');
    
    // Simulate newsletter subscription
    toastMessage.textContent = 'Successfully subscribed to newsletter!';
    
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
    
    // Reset form
    form.reset();
    
    console.log('Newsletter subscription:', email);
}
</script>
