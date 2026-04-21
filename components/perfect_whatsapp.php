<?php
// Production-Ready WhatsApp Component - Perfect for Hosting
require_once 'config.php';

// WhatsApp configuration
$whatsapp_config = [
    'developer_number' => '+256753244480', // Your developer WhatsApp number
    'church_number' => '+256753244480',   // Church WhatsApp number
    'message' => 'Hello! I need help with Salem Dominion Ministries website.',
    'position' => 'bottom-left',          // Opposite side from typical bottom-right
    'show_label' => true,                 // Clearly identified
    'animation' => 'pulse',               // Gentle animation
    'color' => '#25D366',                 // Official WhatsApp green
    'size' => 'medium'                    // Professional size
];
?>

<!-- Perfect WhatsApp Integration -->
<style>
/* WhatsApp Floating Button - Bottom Left (Opposite Side) */
.whatsapp-float {
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

.whatsapp-float:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 35px rgba(37, 211, 102, 0.5);
    background: linear-gradient(135deg, #128C7E 0%, #25D366 100%);
}

/* Pulse Animation for Visibility */
@keyframes whatsapp-pulse {
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

.whatsapp-float.pulse {
    animation: whatsapp-pulse 2s infinite;
}

/* WhatsApp Label - Clearly Identified */
.whatsapp-label {
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

.whatsapp-float:hover + .whatsapp-label {
    opacity: 1;
    visibility: visible;
    transform: translateY(-5px);
}

/* WhatsApp Badge - Developer Identification */
.whatsapp-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc2626;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
    border: 2px solid white;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .whatsapp-float {
        width: 50px;
        height: 50px;
        font-size: 24px;
        bottom: 20px;
        left: 20px;
    }
    
    .whatsapp-label {
        bottom: 80px;
        left: 20px;
        font-size: 0.8rem;
        padding: 10px 15px;
        max-width: 180px;
    }
    
    .whatsapp-badge {
        width: 18px;
        height: 18px;
        font-size: 9px;
    }
}

@media (max-width: 480px) {
    .whatsapp-float {
        width: 45px;
        height: 45px;
        font-size: 20px;
        bottom: 15px;
        left: 15px;
    }
    
    .whatsapp-label {
        bottom: 70px;
        left: 15px;
        font-size: 0.75rem;
        padding: 8px 12px;
        max-width: 150px;
    }
    
    .whatsapp-badge {
        width: 16px;
        height: 16px;
        font-size: 8px;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .whatsapp-label {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    }
}

/* Accessibility */
.whatsapp-float:focus {
    outline: 3px solid #25D366;
    outline-offset: 2px;
}

/* Animation Classes */
.whatsapp-float.bounce {
    animation: whatsapp-bounce 1s ease-in-out;
}

@keyframes whatsapp-bounce {
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

/* WhatsApp Tooltip */
.whatsapp-tooltip {
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

.whatsapp-tooltip::before {
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

.whatsapp-float:hover ~ .whatsapp-tooltip {
    opacity: 1;
    visibility: visible;
}
</style>

<!-- WhatsApp Floating Button - Bottom Left (Opposite Side) -->
<a href="https://wa.me/<?php echo str_replace(['+', '-', ' '], '', $whatsapp_config['developer_number']); ?>?text=<?php echo urlencode($whatsapp_config['message']); ?>" 
   class="whatsapp-float pulse" 
   target="_blank" 
   rel="noopener noreferrer"
   title="Chat with Developer on WhatsApp">
    <i class="fab fa-whatsapp"></i>
    <div class="whatsapp-badge">DEV</div>
</a>

<!-- WhatsApp Label - Clearly Identified -->
<div class="whatsapp-label">
    <i class="fas fa-code"></i> Developer Support
</div>

<!-- WhatsApp Tooltip -->
<div class="whatsapp-tooltip">
    Get help from our developer
</div>

<script>
// WhatsApp functionality - Perfect for hosting
document.addEventListener('DOMContentLoaded', function() {
    const whatsappFloat = document.querySelector('.whatsapp-float');
    const whatsappLabel = document.querySelector('.whatsapp-label');
    
    // Add bounce animation on page load
    setTimeout(() => {
        whatsappFloat.classList.add('bounce');
        setTimeout(() => {
            whatsappFloat.classList.remove('bounce');
        }, 1000);
    }, 2000);
    
    // Track WhatsApp clicks for analytics (optional)
    whatsappFloat.addEventListener('click', function() {
        // Log WhatsApp interaction
        if (typeof gtag !== 'undefined') {
            gtag('event', 'whatsapp_click', {
                'event_category': 'Contact',
                'event_label': 'Developer Support',
                'value': 1
            });
        }
        
        // Show confirmation (optional)
        console.log('WhatsApp support clicked');
    });
    
    // Show label on hover for better UX
    let labelTimeout;
    
    whatsappFloat.addEventListener('mouseenter', function() {
        clearTimeout(labelTimeout);
        whatsappLabel.style.opacity = '1';
        whatsappLabel.style.visibility = 'visible';
    });
    
    whatsappFloat.addEventListener('mouseleave', function() {
        labelTimeout = setTimeout(() => {
            whatsappLabel.style.opacity = '0';
            whatsappLabel.style.visibility = 'hidden';
        }, 500);
    });
    
    // Keyboard accessibility
    whatsappFloat.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            window.open(this.href, '_blank');
        }
    });
    
    // Hide on scroll down, show on scroll up (optional enhancement)
    let lastScrollTop = 0;
    let hideTimeout;
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scrolling down
            clearTimeout(hideTimeout);
            whatsappFloat.style.opacity = '0.7';
            whatsappFloat.style.transform = 'scale(0.9)';
        } else {
            // Scrolling up
            clearTimeout(hideTimeout);
            whatsappFloat.style.opacity = '1';
            whatsappFloat.style.transform = 'scale(1)';
        }
        
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }, false);
    
    // Add pulse animation every 30 seconds to draw attention
    setInterval(() => {
        whatsappFloat.classList.add('pulse');
        setTimeout(() => {
            whatsappFloat.classList.remove('pulse');
        }, 3000);
    }, 30000);
});

// WhatsApp share functionality (optional)
function shareViaWhatsApp(text, url) {
    const message = encodeURIComponent(text + ' ' + url);
    const whatsappUrl = 'https://wa.me/?text=' + message;
    window.open(whatsappUrl, '_blank');
}

// WhatsApp contact tracking (optional)
function trackWhatsAppContact(type, message) {
    // Track different types of WhatsApp interactions
    const data = {
        type: type,
        message: message,
        timestamp: new Date().toISOString(),
        page: window.location.pathname,
        userAgent: navigator.userAgent
    };
    
    // Send to analytics or backend (optional)
    console.log('WhatsApp contact tracked:', data);
    
    // You can send this data to your server for analytics
    // fetch('/api/whatsapp-tracking', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify(data)
    // });
}
</script>
