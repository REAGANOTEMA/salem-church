// Heavenly Sounds and Guidance System
class HeavenlyGuidance {
    constructor() {
        this.audioContext = null;
        this.welcomeSound = null;
        this.angelMessages = [
            "Welcome, child of God. You are loved beyond measure.",
            "The Lord your God is with you wherever you go.",
            "Fear not, for I am with you; be not dismayed, for I am your God.",
            "The angel of the Lord encamps around those who fear Him.",
            "For He shall give His angels charge over you, to keep you in all your ways.",
            "Behold, I send an Angel before you, to keep you in the way.",
            "The Lord will command His angels concerning you.",
            "Are they not all ministering spirits sent forth to minister for us?",
            "You are fearfully and wonderfully made.",
            "God's love surrounds you like a warm embrace."
        ];
        
        this.scriptures = [
            { verse: "Psalm 91:11", text: "For He shall give His angels charge over you, to keep you in all your ways." },
            { verse: "Hebrews 1:14", text: "Are they not all ministering spirits sent forth to minister for those who will inherit salvation?" },
            { verse: "Exodus 23:20", text: "Behold, I send an Angel before you, to keep you in the way and to bring you into the place which I have prepared." },
            { verse: "Psalm 34:7", text: "The angel of the Lord encamps all around those who fear Him, and delivers them." },
            { verse: "Matthew 18:10", text: "Take heed that you do not despise one of these little ones, for I say to you that in heaven their angels always see the face of My Father." },
            { verse: "Acts 12:7", text: "Now behold, an angel of the Lord stood by him, and a light shone in the prison; and he struck Peter on the side and raised him up." },
            { verse: "Daniel 6:22", text: "My God sent His angel and shut the lions' mouths, so that they have not hurt me." },
            { verse: "Luke 1:19", text: "I am Gabriel, who stands in the presence of God." },
            { verse: "Revelation 5:11", text: "Then I looked, and I heard the voice of many angels around the throne." },
            { verse: "John 20:12", text: "And she saw two angels in white sitting, one at the head and the other at the feet, where the body of Jesus had lain." }
        ];
        
        this.angelNames = ["Gabriel", "Michael", "Raphael", "Uriel", "Chamuel", "Jophiel", "Zadkiel", "Sariel"];
        this.init();
    }
    
    init() {
        this.loadWelcomeSound();
        this.setupEventListeners();
        this.showWelcomeMessage();
    }
    
    loadWelcomeSound() {
        // Create a gentle welcome sound using Web Audio API
        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) {
            console.log('Web Audio API not supported');
        }
    }
    
    playWelcomeSound() {
        if (!this.audioContext) return;
        
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        // Create a gentle, heavenly sound
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(523.25, this.audioContext.currentTime); // C5
        oscillator.frequency.exponentialRampToValueAtTime(659.25, this.audioContext.currentTime + 0.5); // E5
        oscillator.frequency.exponentialRampToValueAtTime(783.99, this.audioContext.currentTime + 1); // G5
        
        gainNode.gain.setValueAtTime(0.1, this.audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 2);
        
        oscillator.start(this.audioContext.currentTime);
        oscillator.stop(this.audioContext.currentTime + 2);
    }
    
    playGentleChime() {
        if (!this.audioContext) return;
        
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        oscillator.type = 'triangle';
        oscillator.frequency.setValueAtTime(880, this.audioContext.currentTime); // A5
        
        gainNode.gain.setValueAtTime(0.05, this.audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.5);
        
        oscillator.start(this.audioContext.currentTime);
        oscillator.stop(this.audioContext.currentTime + 0.5);
    }
    
    setupEventListeners() {
        // Play welcome sound on page load (once per session)
        if (!sessionStorage.getItem('welcomePlayed')) {
            setTimeout(() => {
                this.playWelcomeSound();
                sessionStorage.setItem('welcomePlayed', 'true');
            }, 1000);
        }
        
        // Play gentle chime on important interactions
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-primary') || 
                e.target.classList.contains('btn-hero') ||
                e.target.closest('.nav-link')) {
                this.playGentleChime();
            }
        });
        
        // Show angel guidance on scroll
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                if (Math.random() > 0.95) { // 5% chance
                    this.showAngelGuidance();
                }
            }, 1000);
        });
    }
    
    showWelcomeMessage() {
        setTimeout(() => {
            const message = this.angelMessages[Math.floor(Math.random() * this.angelMessages.length)];
            const scripture = this.scriptures[Math.floor(Math.random() * this.scriptures.length)];
            const angelName = this.angelNames[Math.floor(Math.random() * this.angelNames.length)];
            
            this.createHeavenlyNotification(message, scripture, angelName);
        }, 2000);
    }
    
    showAngelGuidance() {
        const message = this.angelMessages[Math.floor(Math.random() * this.angelMessages.length)];
        const scripture = this.scriptures[Math.floor(Math.random() * this.scriptures.length)];
        const angelName = this.angelNames[Math.floor(Math.random() * this.angelNames.length)];
        
        this.createHeavenlyNotification(message, scripture, angelName);
    }
    
    createHeavenlyNotification(message, scripture, angelName) {
        // Remove existing notification if present
        const existing = document.querySelector('.heavenly-notification');
        if (existing) {
            existing.remove();
        }
        
        const notification = document.createElement('div');
        notification.className = 'heavenly-notification';
        notification.innerHTML = `
            <div class="heavenly-content">
                <div class="angel-avatar">
                    <i class="fas fa-dove"></i>
                </div>
                <div class="angel-message">
                    <h6>Angel ${angelName}</h6>
                    <p class="message-text">${message}</p>
                    <div class="scripture-verse">
                        <small><em>${scripture.verse}</em></small>
                        <p class="scripture-text">${scripture.text}</p>
                    </div>
                </div>
                <button class="close-angel" onclick="this.closest('.heavenly-notification').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 8 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 8000);
    }
    
    // Dashboard specific guidance
    showDashboardGuidance(userRole = 'member') {
        const guidance = {
            'member': "Welcome back, beloved child of God. Your spiritual journey continues today.",
            'admin': "Blessed servant, you are doing God's work. May wisdom guide your decisions.",
            'pastor': "Anointed one, the Lord has called you to shepherd His flock with love.",
            'teacher': "Faithful teacher, you are planting seeds of faith in young hearts.",
            'visitor': "Welcome, seeker of truth. God's love awaits you here."
        };
        
        const message = guidance[userRole] || guidance['member'];
        const scripture = this.scriptures[Math.floor(Math.random() * this.scriptures.length)];
        const angelName = this.angelNames[Math.floor(Math.random() * this.angelNames.length)];
        
        this.createHeavenlyNotification(message, scripture, angelName);
    }
    
    // Prayer guidance
    showPrayerGuidance(prayerType = 'general') {
        const prayers = {
            'general': "The Lord hears your prayers. Cast your burdens upon Him, for He cares for you.",
            'healing': "By His stripes, we are healed. Trust in the Lord's healing power.",
            'guidance': "Trust in the Lord with all your heart, and He shall direct your paths.",
            'protection': "The Lord is your refuge and fortress. In Him you shall find safety.",
            'thanksgiving': "Give thanks to the Lord, for His mercy endures forever."
        };
        
        const message = prayers[prayerType] || prayers['general'];
        const scripture = this.scriptures[Math.floor(Math.random() * this.scriptures.length)];
        const angelName = this.angelNames[Math.floor(Math.random() * this.angelNames.length)];
        
        this.createHeavenlyNotification(message, scripture, angelName);
    }
}

// Initialize the heavenly guidance system
document.addEventListener('DOMContentLoaded', () => {
    window.heavenlyGuidance = new HeavenlyGuidance();
});

// CSS for heavenly notifications
const heavenlyStyles = `
.heavenly-notification {
    position: fixed;
    top: 100px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideInFromRight 0.5s ease-out;
}

.heavenly-content {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(240, 248, 255, 0.95));
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    border: 2px solid rgba(255, 215, 0, 0.3);
    position: relative;
    overflow: hidden;
}

.heavenly-content::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255, 215, 0, 0.1), transparent);
    animation: shimmer 3s infinite;
}

.angel-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #FFD700, #FFA500);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    color: white;
    font-size: 1.5rem;
    animation: pulse 2s infinite;
}

.angel-message h6 {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 0.5rem;
    font-family: 'Playfair Display', serif;
}

.message-text {
    color: #34495e;
    font-style: italic;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.scripture-verse {
    background: rgba(255, 215, 0, 0.1);
    padding: 0.8rem;
    border-radius: 10px;
    border-left: 3px solid #FFD700;
}

.scripture-verse small {
    color: #f39c12;
    font-weight: 600;
    display: block;
    margin-bottom: 0.5rem;
}

.scripture-text {
    color: #2c3e50;
    margin: 0;
    font-size: 0.9rem;
}

.close-angel {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.8);
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #666;
}

.close-angel:hover {
    background: rgba(255, 215, 0, 0.3);
    transform: scale(1.1);
}

@keyframes slideInFromRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = heavenlyStyles;
document.head.appendChild(styleSheet);
