<?php
// Developer WhatsApp Floating Button - Opposite Side (Bottom-Left)
require_once 'config.php';

// Developer WhatsApp configuration
$dev_whatsapp_config = [
    'developer_number' => '+256753244480',
    'message' => 'Hello! I need help with Salem Dominion Ministries website development.',
    'position' => 'bottom-left',
    'show_label' => true,
    'animation' => 'pulse',
    'badge_text' => 'DEV',
    'badge_color' => '#dc2626',
    'size' => 'medium'
];
?>

<!-- Developer WhatsApp Floating Button - Bottom-Left (Opposite Side) -->
<style>
/* Developer WhatsApp Button - Bottom-Left */
.dev-whatsapp-float {
    position: fixed;
    bottom: 25px;
    left: 25px;  <!-- Opposite side from typical bottom-right -->
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 9999;
    box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
    transition: all 0.3s ease;
    text-decoration: none;
    font-size: 28px;
}

.dev-whatsapp-float:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 35px rgba(37, 211, 102, 0.5);
    background: linear-gradient(135deg, #128C7E 0%, #25D366 100%);
}

/* Pulse Animation for Visibility */
@keyframes dev-whatsapp-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
    }
    70% {
        box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
    }
}

.dev-whatsapp-float.pulse {
    animation: dev-whatsapp-pulse 2s infinite;
}

/* Developer Badge - Clearly Identified */
.dev-whatsapp-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc2626;
    color: white;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
    border: 2px solid white;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
    z-index: 1;
}

/* Developer Label - Clearly Identified */
.dev-whatsapp-label {
    position: fixed;
    bottom: 90px;
    left: 25px;
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    color: white;
    padding: 12px 18px;
    border-radius: 25px;
    font-size: 0.85rem;
    font-weight: 600;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 9998;
    max-width: 200px;
    text-align: center;
    border: 2px solid #25D366;
}

.dev-whatsapp-float:hover + .dev-whatsapp-label {
    opacity: 1;
    visibility: visible;
    transform: translateY(-5px);
}

/* Developer Tooltip */
.dev-whatsapp-tooltip {
    position: fixed;
    bottom: 90px;
    left: 90px;
    background: #1e293b;
    color: white;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 0.8rem;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 9997;
    pointer-events: none;
}

.dev-whatsapp-tooltip::before {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 10px;
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #1e293b;
}

.dev-whatsapp-float:hover ~ .dev-whatsapp-tooltip {
    opacity: 1;
    visibility: visible;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .dev-whatsapp-float {
        width: 50px;
        height: 50px;
        font-size: 24px;
        bottom: 20px;
        left: 20px;
    }
    
    .dev-whatsapp-badge {
        width: 18px;
        height: 18px;
        font-size: 9px;
    }
    
    .dev-whatsapp-label {
        bottom: 80px;
        left: 20px;
        font-size: 0.8rem;
        padding: 10px 15px;
        max-width: 180px;
    }
    
    .dev-whatsapp-tooltip {
        bottom: 80px;
        left: 75px;
        font-size: 0.75rem;
    }
}

@media (max-width: 480px) {
    .dev-whatsapp-float {
        width: 45px;
        height: 45px;
        font-size: 20px;
        bottom: 15px;
        left: 15px;
    }
    
    .dev-whatsapp-badge {
        width: 16px;
        height: 16px;
        font-size: 8px;
    }
    
    .dev-whatsapp-label {
        bottom: 70px;
        left: 15px;
        font-size: 0.75rem;
        padding: 8px 12px;
        max-width: 150px;
    }
    
    .dev-whatsapp-tooltip {
        bottom: 70px;
        left: 65px;
        font-size: 0.7rem;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .dev-whatsapp-label {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    }
}

/* Accessibility */
.dev-whatsapp-float:focus {
    outline: 3px solid #25D366;
    outline-offset: 2px;
}

/* Animation Classes */
.dev-whatsapp-float.bounce {
    animation: dev-whatsapp-bounce 1s ease-in-out;
}

@keyframes dev-whatsapp-bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

/* Developer Icon Integration */
.dev-whatsapp-float i {
    position: relative;
    z-index: 2;
}

/* Special Developer Identification */
.dev-whatsapp-float::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(220, 38, 38, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
    z-index: 1;
}

.dev-whatsapp-float:hover::before {
    background: linear-gradient(135deg, rgba(220, 38, 38, 0.2) 0%, rgba(220, 38, 38, 0.1) 100%);
}
</style>

<!-- Developer WhatsApp Floating Button - Bottom-Left (Opposite Side) -->
<a href="https://wa.me/<?php echo str_replace(['+', '-', ' '], '', $dev_whatsapp_config['developer_number']); ?>?text=<?php echo urlencode($dev_whatsapp_config['message']); ?>" 
   class="dev-whatsapp-float pulse" 
   target="_blank" 
   rel="noopener noreferrer"
   title="Chat with Developer on WhatsApp">
    <i class="fab fa-whatsapp"></i>
    <div class="dev-whatsapp-badge"><?php echo $dev_whatsapp_config['badge_text']; ?></div>
</a>

<!-- Developer WhatsApp Label - Clearly Identified -->
<div class="dev-whatsapp-label">
    <i class="fas fa-code"></i> Developer Support
</div>

<!-- Developer WhatsApp Tooltip -->
<div class="dev-whatsapp-tooltip">
    Chat with our developer
</div>

<script>
// Developer WhatsApp functionality
document.addEventListener('DOMContentLoaded', function() {
    const devWhatsappFloat = document.querySelector('.dev-whatsapp-float');
    const devWhatsappLabel = document.querySelector('.dev-whatsapp-label');
    
    // Add bounce animation on page load
    setTimeout(() => {
        devWhatsappFloat.classList.add('bounce');
        setTimeout(() => {
            devWhatsappFloat.classList.remove('bounce');
        }, 1000);
    }, 2000);
    
    // Track WhatsApp clicks for analytics (optional)
    devWhatsappFloat.addEventListener('click', function() {
        // Log WhatsApp interaction
        if (typeof gtag !== 'undefined') {
            gtag('event', 'developer_whatsapp_click', {
                'event_category': 'Contact',
                'event_label': 'Developer Support',
                'value': 1
            });
        }
        
        // Show confirmation (optional)
        console.log('Developer WhatsApp clicked');
        
        // Store click in session for analytics
        sessionStorage.setItem('dev_whatsapp_clicked', 'true');
    });
    
    // Show label on hover for better UX
    let labelTimeout;
    
    devWhatsappFloat.addEventListener('mouseenter', function() {
        clearTimeout(labelTimeout);
        devWhatsappLabel.style.opacity = '1';
        devWhatsappLabel.style.visibility = 'visible';
    });
    
    devWhatsappFloat.addEventListener('mouseleave', function() {
        labelTimeout = setTimeout(() => {
            devWhatsappLabel.style.opacity = '0';
            devWhatsappLabel.style.visibility = 'hidden';
        }, 500);
    });
    
    // Keyboard accessibility
    devWhatsappFloat.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            window.open(this.href, '_blank');
        }
    });
    
    // Show developer identification more clearly
    let pulseInterval = setInterval(() => {
        devWhatsappFloat.classList.add('pulse');
        setTimeout(() => {
            devWhatsappFloat.classList.remove('pulse');
        }, 3000);
    }, 30000);
    
    // Stop pulsing after first click
    if (sessionStorage.getItem('dev_whatsapp_clicked') === 'true') {
        clearInterval(pulseInterval);
        devWhatsappFloat.classList.remove('pulse');
    }
    
    // Add developer identification on first visit
    if (!sessionStorage.getItem('dev_whatsapp_first_visit')) {
        setTimeout(() => {
            devWhatsappFloat.classList.add('bounce');
            setTimeout(() => {
                devWhatsappFloat.classList.remove('bounce');
            }, 2000);
        }, 5000);
        sessionStorage.setItem('dev_whatsapp_first_visit', 'true');
    }
    
    // Developer WhatsApp tracking
    function trackDeveloperWhatsApp(type, message) {
        // Track different types of developer WhatsApp interactions
        const data = {
            type: type,
            message: message,
            timestamp: new Date().toISOString(),
            page: window.location.pathname,
            userAgent: navigator.userAgent,
            isDeveloper: true
        };
        
        // Send to analytics or backend (optional)
        console.log('Developer WhatsApp contact tracked:', data);
        
        // You can send this data to your server for analytics
        // fetch('/api/developer-whatsapp-tracking', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/json' },
        //     body: JSON.stringify(data)
        // });
    }
    
    // Add developer-specific hover effects
    devWhatsappFloat.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-3px) scale(1.05) rotate(5deg)';
    });
    
    devWhatsappFloat.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1) rotate(0deg)';
    });
    
    // Developer identification animation
    function showDeveloperIdentification() {
        devWhatsappFloat.style.background = 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)';
        setTimeout(() => {
            devWhatsappFloat.style.background = 'linear-gradient(135deg, #25D366 0%, #128C7E 100%)';
        }, 2000);
    }
    
    // Show developer identification periodically
    setInterval(showDeveloperIdentification, 60000);
    
    // Add developer badge animation
    setInterval(() => {
        const badge = document.querySelector('.dev-whatsapp-badge');
        badge.style.transform = 'scale(1.2)';
        setTimeout(() => {
            badge.style.transform = 'scale(1)';
        }, 200);
    }, 15000);
});

// Developer WhatsApp share functionality (optional)
function shareWithDeveloper(message, url) {
    const devMessage = encodeURIComponent('Developer Support: ' + message + ' ' + url);
    const whatsappUrl = 'https://wa.me/<?php echo str_replace(['+', '-', ' '], '', $dev_whatsapp_config['developer_number']); ?>?text=' + devMessage;
    window.open(whatsappUrl, '_blank');
}

// Developer WhatsApp contact tracking (optional)
function trackDeveloperWhatsAppContact(type, message) {
    // Track different types of developer WhatsApp interactions
    const data = {
        type: type,
        message: message,
        timestamp: new Date().toISOString(),
        page: window.location.pathname,
        userAgent: navigator.userAgent,
        isDeveloper: true,
        session_id: sessionStorage.getItem('session_id') || 'unknown'
    };
    
    // Send to analytics or backend (optional)
    console.log('Developer WhatsApp contact tracked:', data);
    
    // You can send this data to your server for analytics
    // fetch('/api/developer-whatsapp-tracking', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify(data)
    // });
}
</script>
