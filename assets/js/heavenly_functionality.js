/**
 * Heavenly Functionality System
 * Perfect functionality with heavenly experience
 */

class HeavenlySystem {
    constructor() {
        this.init();
    }

    init() {
        this.createHeavenlyBackground();
        this.addFloatingParticles();
        this.addDivineLightRays();
        this.initializeLogo();
        this.setupHeavenlyInteractions();
        this.addHeavenlySounds();
        this.setupScrollEffects();
        this.initializeParallax();
        this.addHeavenlyCursor();
        this.setupLoadingAnimations();
    }

    createHeavenlyBackground() {
        // Create heavenly background
        const heavenlyBg = document.createElement('div');
        heavenlyBg.className = 'heavenly-bg';
        document.body.appendChild(heavenlyBg);

        // Add dynamic gradient animation
        this.animateHeavenlyBackground(heavenlyBg);
    }

    animateHeavenlyBackground(element) {
        const gradients = [
            'radial-gradient(circle at 20% 20%, rgba(255, 215, 0, 0.1) 0%, transparent 50%)',
            'radial-gradient(circle at 80% 80%, rgba(255, 182, 193, 0.1) 0%, transparent 50%)',
            'radial-gradient(circle at 50% 50%, rgba(135, 206, 235, 0.05) 0%, transparent 70%)',
            'linear-gradient(135deg, rgba(255, 215, 0, 0.02) 0%, rgba(255, 182, 193, 0.02) 25%, rgba(135, 206, 235, 0.02) 50%, rgba(147, 112, 219, 0.02) 75%, rgba(255, 165, 0, 0.02) 100%)'
        ];

        let currentGradient = 0;
        setInterval(() => {
            element.style.background = gradients[currentGradient];
            currentGradient = (currentGradient + 1) % gradients.length;
        }, 5000);
    }

    addFloatingParticles() {
        const particlesContainer = document.createElement('div');
        particlesContainer.className = 'heavenly-particles';
        document.body.appendChild(particlesContainer);

        // Create multiple particles
        for (let i = 0; i < 10; i++) {
            const particle = document.createElement('div');
            particle.className = 'heavenly-particle';
            particle.style.animationDelay = `${i * 2}s`;
            particle.style.animationDuration = `${12 + Math.random() * 6}s`;
            particlesContainer.appendChild(particle);
        }
    }

    addDivineLightRays() {
        const raysContainer = document.createElement('div');
        raysContainer.className = 'divine-light-rays';
        document.body.appendChild(raysContainer);

        // Create divine light rays
        for (let i = 0; i < 6; i++) {
            const ray = document.createElement('div');
            ray.className = 'divine-ray';
            ray.style.animationDelay = `${i * 3}s`;
            ray.style.animationDuration = `${18 + Math.random() * 4}s`;
            raysContainer.appendChild(ray);
        }
    }

    initializeLogo() {
        // Find and enhance all logos
        const logos = document.querySelectorAll('.navbar-brand, .heavenly-logo');
        logos.forEach(logo => {
            logo.classList.add('heavenly-logo');
            
            // Add logo image if not present
            if (!logo.querySelector('img')) {
                const logoImg = document.createElement('img');
                logoImg.src = 'public/images/logo.png'; // User's logo
                logoImg.alt = 'Salem Dominion Ministries';
                logoImg.style.display = 'none'; // Hide initially
                logo.appendChild(logoImg);
                
                // Show logo when loaded
                logoImg.onload = () => {
                    logoImg.style.display = 'inline-block';
                    logo.style.fontSize = '0'; // Hide text when image loads
                };
                
                // Fallback to text if image fails
                logoImg.onerror = () => {
                    logoImg.style.display = 'none';
                    logo.style.fontSize = '2rem'; // Show text fallback
                };
            }
        });
    }

    setupHeavenlyInteractions() {
        // Add hover effects to all interactive elements
        const interactiveElements = document.querySelectorAll('button, a, .heavenly-card, .heavenly-btn');
        interactiveElements.forEach(element => {
            element.addEventListener('mouseenter', () => this.addHeavenlyGlow(element));
            element.addEventListener('mouseleave', () => this.removeHeavenlyGlow(element));
        });
    }

    addHeavenlyGlow(element) {
        element.style.boxShadow = '0 0 30px rgba(255, 215, 0, 0.6)';
        element.style.transform = 'scale(1.05)';
    }

    removeHeavenlyGlow(element) {
        element.style.boxShadow = '';
        element.style.transform = '';
    }

    addHeavenlySounds() {
        // Create heavenly sound system
        this.heavenlySounds = {
            chime: new Audio('assets/sounds/heavenly-chime.mp3'),
            bell: new Audio('assets/sounds/church-bell.mp3'),
            whisper: new Audio('assets/sounds/divine-whisper.mp3')
        };

        // Set volume and loop
        Object.values(this.heavenlySounds).forEach(sound => {
            sound.volume = 0.3;
            sound.loop = false;
        });

        // Add sound triggers
        this.setupSoundTriggers();
    }

    setupSoundTriggers() {
        // Play sound on logo hover
        const logos = document.querySelectorAll('.heavenly-logo');
        logos.forEach(logo => {
            logo.addEventListener('mouseenter', () => {
                if (this.heavenlySounds.chime.readyState >= 2) {
                    this.heavenlySounds.chime.currentTime = 0;
                    this.heavenlySounds.chime.play().catch(() => {});
                }
            });
        });

        // Play sound on button clicks
        const buttons = document.querySelectorAll('.heavenly-btn');
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                if (this.heavenlySounds.bell.readyState >= 2) {
                    this.heavenlySounds.bell.currentTime = 0;
                    this.heavenlySounds.bell.play().catch(() => {});
                }
            });
        });
    }

    setupScrollEffects() {
        let lastScrollTop = 0;
        const navbar = document.querySelector('.navbar');

        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Navbar scroll effect
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                navbar.style.transform = 'translateY(-100%)';
            } else {
                navbar.style.transform = 'translateY(0)';
            }
            
            // Parallax effect for hero section
            const heroSection = document.querySelector('.hero-section');
            if (heroSection) {
                const speed = 0.5;
                heroSection.style.transform = `translateY(${scrollTop * speed}px)`;
            }
            
            // Fade in elements on scroll
            this.fadeInElementsOnScroll();
            
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        });
    }

    fadeInElementsOnScroll() {
        const elements = document.querySelectorAll('.heavenly-card, .heavenly-section > div');
        elements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const elementBottom = elementTop + element.offsetHeight;
            
            if (elementTop < window.innerHeight && elementBottom > 0) {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }
        });
    }

    initializeParallax() {
        // Add parallax to multiple sections
        const parallaxElements = document.querySelectorAll('.hero-section, .heavenly-section');
        parallaxElements.forEach(element => {
            this.setupParallaxForElement(element);
        });
    }

    setupParallaxForElement(element) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            const yPos = rate;
            element.style.transform = `translateY(${yPos}px)`;
        });
    }

    addHeavenlyCursor() {
        // Create custom cursor
        const cursor = document.createElement('div');
        cursor.className = 'heavenly-cursor';
        cursor.style.cssText = `
            position: fixed;
            width: 20px;
            height: 20px;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.8) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            transition: all 0.1s ease;
            transform: translate(-50%, -50%);
        `;
        document.body.appendChild(cursor);

        // Update cursor position
        document.addEventListener('mousemove', (e) => {
            cursor.style.left = e.clientX + 'px';
            cursor.style.top = e.clientY + 'px';
        });

        // Hide cursor when leaving window
        document.addEventListener('mouseleave', () => {
            cursor.style.opacity = '0';
        });

        document.addEventListener('mouseenter', () => {
            cursor.style.opacity = '1';
        });
    }

    setupLoadingAnimations() {
        // Add loading states to images
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            img.addEventListener('load', () => {
                img.style.animation = 'fadeIn 0.5s ease-in';
            });
        });
    }

    // Advanced functionality methods
    addFloatingScripture() {
        const scriptures = [
            "The Lord is my shepherd; I shall not want. - Psalm 23:1",
            "For God so loved the world that He gave His only begotten Son. - John 3:16",
            "I can do all things through Christ who strengthens me. - Philippians 4:13",
            "Be still, and know that I am God. - Psalm 46:10",
            "The Lord will fight for you; you need only to be still. - Exodus 14:14"
        ];

        const scripture = scriptures[Math.floor(Math.random() * scriptures.length)];
        this.displayFloatingScripture(scripture);
    }

    displayFloatingScripture(text) {
        const scriptureElement = document.createElement('div');
        scriptureElement.className = 'floating-scripture';
        scriptureElement.innerHTML = `
            <div class="scripture-content">
                <div class="scripture-text">"${text}"</div>
                <div class="scripture-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </div>
            </div>
        `;
        
        scriptureElement.style.cssText = `
            position: fixed;
            top: 20%;
            right: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 248, 255, 0.95));
            color: #1a1a2e;
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(255, 215, 0, 0.2);
            z-index: 1000;
            max-width: 300px;
            font-family: 'Playfair Display', serif;
            border: 2px solid rgba(255, 215, 0, 0.3);
            animation: floatScripture 15s ease-in-out infinite;
        `;
        
        document.body.appendChild(scriptureElement);
        
        // Auto-remove after 20 seconds
        setTimeout(() => {
            if (scriptureElement.parentNode) {
                scriptureElement.remove();
            }
        }, 20000);
    }

    addDivineIntervention() {
        // Random divine interventions
        const interventions = [
            () => this.addFloatingScripture(),
            () => this.playHeavenlyChime(),
            () => this.showDivineLight(),
            () => this.addAngelPresence()
        ];

        const intervention = interventions[Math.floor(Math.random() * interventions.length)];
        intervention();
    }

    playHeavenlyChime() {
        if (this.heavenlySounds && this.heavenlySounds.whisper.readyState >= 2) {
            this.heavenlySounds.whisper.currentTime = 0;
            this.heavenlySounds.whisper.play().catch(() => {});
        }
    }

    showDivineLight() {
        const divineLight = document.createElement('div');
        divineLight.className = 'divine-light-intervention';
        divineLight.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.3) 0%, transparent 70%);
            pointer-events: none;
            z-index: 999;
            animation: divineLightPulse 3s ease-in-out;
        `;
        
        document.body.appendChild(divineLight);
        
        setTimeout(() => {
            divineLight.remove();
        }, 3000);
    }

    addAngelPresence() {
        const angelSymbols = ['🕊️', '✨', '👼', '💫', '🌟'];
        const symbol = angelSymbols[Math.floor(Math.random() * angelSymbols.length)];
        
        const angelPresence = document.createElement('div');
        angelPresence.className = 'angel-presence';
        angelPresence.textContent = symbol;
        angelPresence.style.cssText = `
            position: fixed;
            top: ${Math.random() * 80 + 10}%;
            left: ${Math.random() * 90 + 5}%;
            font-size: ${Math.random() * 2 + 1}rem;
            opacity: 0;
            animation: angelAppear 4s ease-in-out;
            pointer-events: none;
            z-index: 998;
            filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.5));
        `;
        
        document.body.appendChild(angelPresence);
        
        setTimeout(() => {
            angelPresence.remove();
        }, 4000);
    }

    // Initialize periodic divine interventions
    startDivineInterventions() {
        setInterval(() => {
            if (Math.random() > 0.8) { // 20% chance
                this.addDivineIntervention();
            }
        }, 30000); // Every 30 seconds
    }

    // Performance optimization
    optimizePerformance() {
        // Lazy loading for images
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    // Accessibility features
    setupAccessibility() {
        // Add ARIA labels
        const interactiveElements = document.querySelectorAll('button, a');
        interactiveElements.forEach(element => {
            if (!element.getAttribute('aria-label')) {
                element.setAttribute('aria-label', element.textContent || 'Interactive element');
            }
        });

        // Add keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });

        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-navigation');
        });
    }

    // Error handling
    setupErrorHandling() {
        window.addEventListener('error', (e) => {
            console.error('Heavenly system error:', e);
            // Try to recover gracefully
            this.recoverFromError(e);
        });

        window.addEventListener('unhandledrejection', (e) => {
            console.error('Unhandled promise rejection:', e);
            e.preventDefault();
        });
    }

    recoverFromError(error) {
        // Graceful recovery from errors
        console.log('Attempting to recover from error:', error);
        // Add recovery logic here
    }
}

// CSS for additional animations
const heavenlyCSS = `
@keyframes floatScripture {
    0%, 100% { 
        transform: translateY(0px) scale(1); 
        opacity: 0.9; 
    }
    25% { 
        transform: translateY(-15px) scale(1.02); 
        opacity: 1; 
    }
    50% { 
        transform: translateY(-25px) scale(1.03); 
        opacity: 1; 
    }
    75% { 
        transform: translateY(-15px) scale(1.01); 
        opacity: 0.95; 
    }
}

@keyframes divineLightPulse {
    0%, 100% { opacity: 0; }
    50% { opacity: 0.6; }
}

@keyframes angelAppear {
    0%, 100% { opacity: 0; transform: scale(0) rotate(0deg); }
    50% { opacity: 0.8; transform: scale(1) rotate(180deg); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.floating-scripture {
    font-family: 'Playfair Display', serif;
}

.scripture-content {
    position: relative;
    z-index: 1;
}

.scripture-text {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    line-height: 1.5;
    color: #1a1a2e;
    text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5);
}

.scripture-close {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(220, 53, 69, 0.1);
    border: none;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    color: #dc3545;
}

.scripture-close:hover {
    background: rgba(220, 53, 69, 0.2);
    transform: scale(1.1);
}

.keyboard-navigation *:focus {
    outline: 2px solid var(--heavenly-gold);
    outline-offset: 2px;
}
`;

// Add CSS to head
const style = document.createElement('style');
style.textContent = heavenlyCSS;
document.head.appendChild(style);

// Initialize heavenly system when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.heavenlySystem = new HeavenlySystem();
    
    // Start divine interventions after 10 seconds
    setTimeout(() => {
        window.heavenlySystem.startDivineInterventions();
    }, 10000);
    
    // Optimize performance
    setTimeout(() => {
        window.heavenlySystem.optimizePerformance();
    }, 2000);
    
    // Setup accessibility
    setTimeout(() => {
        window.heavenlySystem.setupAccessibility();
    }, 3000);
    
    // Setup error handling
    setTimeout(() => {
        window.heavenlySystem.setupErrorHandling();
    }, 1000);
});

// Export for global access
window.HeavenlySystem = HeavenlySystem;
