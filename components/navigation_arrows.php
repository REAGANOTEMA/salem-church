<?php
/**
 * Navigation Arrows Component
 * Provides up arrow and home button for easy navigation
 */

// Get current page for tooltip text
$current_page = basename($_SERVER['PHP_SELF']);
$page_name = ucfirst(pathinfo($current_page, PATHINFO_FILENAME));

// Determine if we're on the home page
$is_home = $current_page === 'index.php' || $current_page === '/';
?>

<!-- Heavenly Navigation Arrows -->
<div class="heavenly-navigation">
    <!-- Up Arrow -->
    <a href="#" class="nav-arrow nav-arrow-up" 
       onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;" 
       data-tooltip="Scroll to top"
       aria-label="Scroll to top">
        <i class="fas fa-arrow-up"></i>
    </a>
    
    <!-- Home Button -->
    <a href="index.php" class="nav-arrow nav-arrow-home" 
       data-tooltip="Go to home"
       aria-label="Go to home">
        <i class="fas fa-home"></i>
    </a>
    
    <!-- Back Button (if not on home) -->
    <?php if (!$is_home): ?>
    <a href="javascript:history.back()" class="nav-arrow" 
       data-tooltip="Go back"
       aria-label="Go back to previous page">
        <i class="fas fa-arrow-left"></i>
    </a>
    <?php endif; ?>
    
    <!-- Page Indicator -->
    <div class="page-indicator" title="<?php echo htmlspecialchars($page_name); ?>">
        <i class="fas fa-map-marker-alt"></i>
    </div>
</div>

<style>
/* Page Indicator */
.page-indicator {
    width: 40px;
    height: 40px;
    background: var(--gradient-celestial);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1a1a2e;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 10px 25px rgba(255, 215, 0, 0.2);
    position: relative;
}

.page-indicator:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(255, 215, 0, 0.3);
}

.page-indicator::after {
    content: attr(title);
    position: absolute;
    bottom: 50px;
    right: 50%;
    transform: translateX(50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 0.7rem;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

.page-indicator:hover::after {
    opacity: 1;
}

/* Responsive Navigation Arrows */
@media (max-width: 768px) {
    .page-indicator {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .page-indicator {
        width: 30px;
        height: 30px;
        font-size: 0.8rem;
    }
}
</style>

<script>
// Add smooth scroll behavior for up arrow
document.addEventListener('DOMContentLoaded', function() {
    const upArrow = document.querySelector('.nav-arrow-up');
    if (upArrow) {
        upArrow.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Show/hide up arrow based on scroll position
    window.addEventListener('scroll', function() {
        const upArrow = document.querySelector('.nav-arrow-up');
        if (upArrow) {
            if (window.pageYOffset > 200) {
                upArrow.style.opacity = '1';
                upArrow.style.pointerEvents = 'auto';
            } else {
                upArrow.style.opacity = '0.5';
                upArrow.style.pointerEvents = 'none';
            }
        }
    });
    
    // Initialize up arrow state
    const upArrow = document.querySelector('.nav-arrow-up');
    if (upArrow && window.pageYOffset <= 200) {
        upArrow.style.opacity = '0.5';
        upArrow.style.pointerEvents = 'none';
    }
});
</script>
