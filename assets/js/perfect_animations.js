// Perfect Animations System for Salem Dominion Ministries
// Error-free, performance-optimized, responsive animations

class PerfectAnimations {
    constructor() {
        this.isInitialized = false;
        this.animationFrame = null;
        this.observers = new Map();
        this.timers = new Map();
        this.sounds = new Map();
        this.config = {
            enabled: true,
            reducedMotion: false,
            touchDevice: false,
            performanceMode: 'auto'
        };
        
        this.init();
    }
    
    init() {
        try {
            this.detectCapabilities();
            this.setupEventListeners();
            this.initializeObservers();
            this.startAnimations();
            this.isInitialized = true;
            console.log('Perfect Animations initialized successfully');
        } catch (error) {
            console.error('Failed to initialize animations:', error);
            this.fallbackMode();
        }
    }
    
    detectCapabilities() {
        // Detect user preferences
        this.config.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.config.touchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        
        // Detect performance capabilities
        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (connection) {
            const effectiveType = connection.effectiveType;
            if (effectiveType === 'slow-2g' || effectiveType === '2g') {
                this.config.performanceMode = 'low';
            } else if (effectiveType === '3g') {
                this.config.performanceMode = 'medium';
            } else {
                this.config.performanceMode = 'high';
            }
        }
        
        // Detect browser capabilities
        this.supportsIntersectionObserver = 'IntersectionObserver' in window;
        this.supportsWebAudio = 'AudioContext' in window || 'webkitAudioContext' in window;
        this.supportsRequestAnimationFrame = 'requestAnimationFrame' in window;
    }
    
    setupEventListeners() {
        // Page visibility
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseAnimations();
            } else {
                this.resumeAnimations();
            }
        });
        
        // Window resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                this.handleResize();
            }, 250);
        });
        
        // Touch events for mobile
        if (this.config.touchDevice) {
            this.setupTouchEvents();
        }
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                this.handleKeyboardNavigation(e);
            }
        });
    }
    
    initializeObservers() {
        if (!this.supportsIntersectionObserver) {
            this.fallbackObserver();
            return;
        }
        
        // Animate elements on scroll
        const scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateElement(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '50px'
        });
        
        // Observe all animatable elements
        const animatableElements = document.querySelectorAll('[data-aos], .card-custom, .service-card, .stat-card, .floating-angel');
        animatableElements.forEach(el => {
            scrollObserver.observe(el);
        });
        
        this.observers.set('scroll', scrollObserver);
    }
    
    animateElement(element) {
        if (this.config.reducedMotion) {
            this.simpleAnimate(element);
            return;
        }
        
        const animationType = this.getAnimationType(element);
        
        switch (animationType) {
            case 'fade-up':
                this.fadeUp(element);
                break;
            case 'fade-in':
                this.fadeIn(element);
                break;
            case 'slide-left':
                this.slideLeft(element);
                break;
            case 'slide-right':
                this.slideRight(element);
                break;
            case 'scale-up':
                this.scaleUp(element);
                break;
            case 'float':
                this.floatAnimation(element);
                break;
            case 'pulse':
                this.pulseAnimation(element);
                break;
            default:
                this.fadeIn(element);
        }
    }
    
    getAnimationType(element) {
        // Check data attributes
        if (element.dataset.aos) {
            return element.dataset.aos;
        }
        
        // Check classes
        if (element.classList.contains('hero-title')) return 'fade-up';
        if (element.classList.contains('hero-subtitle')) return 'fade-up';
        if (element.classList.contains('card-custom')) return 'fade-up';
        if (element.classList.contains('service-card')) return 'scale-up';
        if (element.classList.contains('stat-card')) return 'scale-up';
        if (element.classList.contains('floating-angel')) return 'float';
        if (element.classList.contains('hero-angel')) return 'pulse';
        if (element.classList.contains('service-icon')) return 'pulse';
        
        return 'fade-in';
    }
    
    fadeUp(element, duration = 1000) {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = `opacity ${duration}ms ease, transform ${duration}ms ease`;
        
        // Force reflow
        element.offsetHeight;
        
        element.style.opacity = '1';
        element.style.transform = 'translateY(0)';
        
        // Clean up after animation
        setTimeout(() => {
            element.style.transition = '';
        }, duration);
    }
    
    fadeIn(element, duration = 1000) {
        element.style.opacity = '0';
        element.style.transition = `opacity ${duration}ms ease`;
        
        // Force reflow
        element.offsetHeight;
        
        element.style.opacity = '1';
        
        // Clean up after animation
        setTimeout(() => {
            element.style.transition = '';
        }, duration);
    }
    
    slideLeft(element, duration = 1000) {
        element.style.opacity = '0';
        element.style.transform = 'translateX(-30px)';
        element.style.transition = `opacity ${duration}ms ease, transform ${duration}ms ease`;
        
        // Force reflow
        element.offsetHeight;
        
        element.style.opacity = '1';
        element.style.transform = 'translateX(0)';
        
        // Clean up after animation
        setTimeout(() => {
            element.style.transition = '';
        }, duration);
    }
    
    slideRight(element, duration = 1000) {
        element.style.opacity = '0';
        element.style.transform = 'translateX(30px)';
        element.style.transition = `opacity ${duration}ms ease, transform ${duration}ms ease`;
        
        // Force reflow
        element.offsetHeight;
        
        element.style.opacity = '1';
        element.style.transform = 'translateX(0)';
        
        // Clean up after animation
        setTimeout(() => {
            element.style.transition = '';
        }, duration);
    }
    
    scaleUp(element, duration = 1000) {
        element.style.opacity = '0';
        element.style.transform = 'scale(0.8)';
        element.style.transition = `opacity ${duration}ms ease, transform ${duration}ms ease`;
        
        // Force reflow
        element.offsetHeight;
        
        element.style.opacity = '1';
        element.style.transform = 'scale(1)';
        
        // Clean up after animation
        setTimeout(() => {
            element.style.transition = '';
        }, duration);
    }
    
    floatAnimation(element) {
        if (this.config.reducedMotion) return;
        
        const duration = 3000 + Math.random() * 2000;
        const distance = 10 + Math.random() * 20;
        
        element.style.animation = `float ${duration}ms ease-in-out infinite`;
        
        // Add keyframes if not already added
        if (!document.querySelector('#float-keyframes')) {
            const style = document.createElement('style');
            style.id = 'float-keyframes';
            style.textContent = `
                @keyframes float {
                    0%, 100% { transform: translateY(0px) rotate(0deg); }
                    25% { transform: translateY(-${distance}px) rotate(2deg); }
                    50% { transform: translateY(-${distance * 1.5}px) rotate(0deg); }
                    75% { transform: translateY(-${distance}px) rotate(-2deg); }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    pulseAnimation(element) {
        if (this.config.reducedMotion) return;
        
        const duration = 2000 + Math.random() * 1000;
        
        element.style.animation = `pulse ${duration}ms ease-in-out infinite`;
        
        // Add keyframes if not already added
        if (!document.querySelector('#pulse-keyframes')) {
            const style = document.createElement('style');
            style.id = 'pulse-keyframes';
            style.textContent = `
                @keyframes pulse {
                    0%, 100% { transform: scale(1); opacity: 0.8; }
                    50% { transform: scale(1.05); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    simpleAnimate(element) {
        // Fallback for reduced motion
        element.style.opacity = '1';
        element.style.transform = 'none';
    }
    
    setupTouchEvents() {
        // Add touch feedback for mobile devices
        const touchElements = document.querySelectorAll('.btn, .card-custom, .service-card, .stat-card');
        
        touchElements.forEach(element => {
            element.addEventListener('touchstart', (e) => {
                element.style.transform = 'scale(0.98)';
            });
            
            element.addEventListener('touchend', (e) => {
                element.style.transform = '';
            });
        });
    }
    
    handleKeyboardNavigation(e) {
        // Add focus animations for keyboard navigation
        const focusedElement = document.activeElement;
        
        if (focusedElement.classList.contains('btn') || 
            focusedElement.classList.contains('nav-link') ||
            focusedElement.classList.contains('card-custom')) {
            
            focusedElement.style.transform = 'scale(1.05)';
            focusedElement.style.boxShadow = '0 0 0 3px rgba(255, 215, 0, 0.5)';
            
            // Remove focus styles when focus is lost
            focusedElement.addEventListener('blur', () => {
                focusedElement.style.transform = '';
                focusedElement.style.boxShadow = '';
            }, { once: true });
        }
    }
    
    handleResize() {
        // Recalculate animations on resize
        const floatingAngels = document.querySelectorAll('.floating-angel');
        floatingAngels.forEach(angel => {
            this.updateAngelPosition(angel);
        });
    }
    
    updateAngelPosition(angel) {
        // Update floating angel positions based on screen size
        const screenWidth = window.innerWidth;
        
        if (screenWidth < 768) {
            // Mobile positioning
            if (angel.classList.contains('angel-1')) {
                angel.style.left = '2%';
                angel.style.top = '10%';
            } else if (angel.classList.contains('angel-2')) {
                angel.style.right = '2%';
                angel.style.top = '70%';
            } else {
                angel.style.display = 'none';
            }
        } else {
            // Desktop positioning
            angel.style.display = 'block';
            if (angel.classList.contains('angel-1')) {
                angel.style.left = '5%';
                angel.style.top = '20%';
            } else if (angel.classList.contains('angel-2')) {
                angel.style.right = '5%';
                angel.style.top = '60%';
            } else if (angel.classList.contains('angel-3')) {
                angel.style.left = '10%';
                angel.style.top = '40%';
            }
        }
    }
    
    startAnimations() {
        // Start continuous animations
        this.startFloatingAngels();
        this.startShimmerEffects();
        this.startRotatingElements();
    }
    
    startFloatingAngels() {
        const angels = document.querySelectorAll('.floating-angel');
        angels.forEach((angel, index) => {
            this.animateAngel(angel, index);
        });
    }
    
    animateAngel(angel, index) {
        if (this.config.reducedMotion) return;
        
        const baseDelay = index * 1000;
        const interval = 3000 + Math.random() * 2000;
        
        const animate = () => {
            const randomX = (Math.random() - 0.5) * 20;
            const randomY = (Math.random() - 0.5) * 20;
            const randomRotate = (Math.random() - 0.5) * 10;
            
            angel.style.transition = `all ${interval}ms ease-in-out`;
            angel.style.transform = `translate(${randomX}px, ${randomY}px) rotate(${randomRotate}deg)`;
            
            this.timers.set(`angel-${index}`, setTimeout(animate, interval));
        };
        
        setTimeout(animate, baseDelay);
    }
    
    startShimmerEffects() {
        const shimmerElements = document.querySelectorAll('.booking-cta, .hero-section::before');
        
        shimmerElements.forEach(element => {
            if (element.classList.contains('booking-cta')) {
                this.shimmerAnimation(element);
            }
        });
    }
    
    shimmerAnimation(element) {
        if (this.config.reducedMotion) return;
        
        // Add shimmer animation
        if (!document.querySelector('#shimmer-keyframes')) {
            const style = document.createElement('style');
            style.id = 'shimmer-keyframes';
            style.textContent = `
                @keyframes shimmer {
                    0% { transform: translateX(-100%); }
                    100% { transform: translateX(100%); }
                }
            `;
            document.head.appendChild(style);
        }
        
        element.style.animation = 'shimmer 3s infinite';
    }
    
    startRotatingElements() {
        const rotatingElements = document.querySelectorAll('.loader-icon');
        
        rotatingElements.forEach(element => {
            this.rotateAnimation(element);
        });
    }
    
    rotateAnimation(element) {
        if (this.config.reducedMotion) return;
        
        element.style.animation = 'spin 2s linear infinite';
        
        // Add keyframes if not already added
        if (!document.querySelector('#spin-keyframes')) {
            const style = document.createElement('style');
            style.id = 'spin-keyframes';
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    pauseAnimations() {
        // Pause all animations when page is not visible
        document.querySelectorAll('[style*="animation"]').forEach(element => {
            element.style.animationPlayState = 'paused';
        });
        
        // Clear timers
        this.timers.forEach(timer => clearTimeout(timer));
    }
    
    resumeAnimations() {
        // Resume all animations when page becomes visible
        document.querySelectorAll('[style*="animation"]').forEach(element => {
            element.style.animationPlayState = 'running';
        });
        
        // Restart animations
        this.startAnimations();
    }
    
    fallbackObserver() {
        // Fallback for browsers without IntersectionObserver
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('[data-aos], .card-custom, .service-card, .stat-card');
            
            elements.forEach(element => {
                const rect = element.getBoundingClientRect();
                const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
                
                if (isVisible && !element.classList.contains('animated')) {
                    this.animateElement(element);
                    element.classList.add('animated');
                }
            });
        };
        
        window.addEventListener('scroll', animateOnScroll);
        animateOnScroll(); // Initial check
    }
    
    fallbackMode() {
        // Fallback mode for when animations fail
        console.log('Running in fallback mode');
        document.body.classList.add('no-animations');
        
        // Remove all animation classes
        document.querySelectorAll('[data-aos]').forEach(element => {
            element.style.opacity = '1';
            element.style.transform = 'none';
        });
    }
    
    // Public API methods
    animate(element, type = 'fade-in') {
        if (!this.isInitialized) return;
        
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            this.animateElement(element);
        }
    }
    
    addAnimation(element, type) {
        if (!this.isInitialized) return;
        
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            element.dataset.aos = type;
        }
    }
    
    removeAnimation(element) {
        if (!this.isInitialized) return;
        
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            element.style.animation = '';
            element.style.transition = '';
            element.style.transform = '';
            element.style.opacity = '';
        }
    }
    
    destroy() {
        // Clean up all animations and observers
        this.observers.forEach(observer => observer.disconnect());
        this.timers.forEach(timer => clearTimeout(timer));
        
        // Remove event listeners
        document.removeEventListener('visibilitychange', this.pauseAnimations);
        window.removeEventListener('resize', this.handleResize);
        
        // Clear references
        this.observers.clear();
        this.timers.clear();
        this.sounds.clear();
        
        this.isInitialized = false;
    }
}

// Performance monitoring
class PerformanceMonitor {
    constructor() {
        this.metrics = {
            fps: 0,
            memoryUsage: 0,
            animationCount: 0
        };
        
        if (window.performance && window.performance.memory) {
            this.startMonitoring();
        }
    }
    
    startMonitoring() {
        const measureFPS = () => {
            let lastTime = performance.now();
            let frames = 0;
            
            const checkFPS = (currentTime) => {
                frames++;
                
                if (currentTime >= lastTime + 1000) {
                    this.metrics.fps = Math.round(frames * 1000 / (currentTime - lastTime));
                    frames = 0;
                    lastTime = currentTime;
                    
                    // Adjust animation quality based on performance
                    this.adjustAnimationQuality();
                }
                
                requestAnimationFrame(checkFPS);
            };
            
            requestAnimationFrame(checkFPS);
        };
        
        measureFPS();
        
        // Monitor memory usage
        setInterval(() => {
            this.metrics.memoryUsage = performance.memory.usedJSHeapSize / 1048576; // MB
        }, 5000);
    }
    
    adjustAnimationQuality() {
        const animations = window.perfectAnimations;
        
        if (!animations || !animations.isInitialized) return;
        
        if (this.metrics.fps < 30) {
            // Reduce animation quality
            animations.config.performanceMode = 'low';
            document.body.classList.add('reduce-animations');
        } else if (this.metrics.fps > 50) {
            // Increase animation quality
            animations.config.performanceMode = 'high';
            document.body.classList.remove('reduce-animations');
        }
    }
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    try {
        // Initialize animations
        window.perfectAnimations = new PerfectAnimations();
        
        // Initialize performance monitor
        window.performanceMonitor = new PerformanceMonitor();
        
        // Add global error handling
        window.addEventListener('error', (e) => {
            console.error('Animation error:', e.error);
            if (window.perfectAnimations) {
                window.perfectAnimations.fallbackMode();
            }
        });
        
        console.log('Perfect animations system loaded successfully');
        
    } catch (error) {
        console.error('Failed to load animations:', error);
        document.body.classList.add('no-animations');
    }
});

// Export for external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { PerfectAnimations, PerformanceMonitor };
}
