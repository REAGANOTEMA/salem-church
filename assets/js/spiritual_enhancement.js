// Spiritual Enhancement System for Salem Dominion Ministries
// Makes the website more spiritual, divine, and real

class SpiritualEnhancement {
    constructor() {
        this.init();
    }
    
    init() {
        this.addSpiritualElements();
        this.setupDivineInteractions();
        this.addBibleVerses();
        this.setupPrayerReminders();
        this.addHeavenlyEffects();
        this.setupSpiritualGuidance();
    }
    
    addSpiritualElements() {
        // Add floating scripture verses (reduced frequency)
        this.addFloatingScriptures();
        
        // Add divine light effects
        this.addDivineLight();
        
        // Add angelic presence indicators (reduced)
        this.addAngelicPresence();
        
        // Add prayer counters
        this.addPrayerCounters();
    }
    
    addFloatingScriptures() {
        // Enhanced, more meaningful verses with better timing
        const verses = [
            { 
                text: "The Lord is my shepherd; I shall not want.", 
                reference: "Psalm 23:1",
                category: "comfort",
                duration: 25000
            },
            { 
                text: "For God so loved the world that He gave His only begotten Son.", 
                reference: "John 3:16",
                category: "love",
                duration: 30000
            },
            { 
                text: "I can do all things through Christ who strengthens me.", 
                reference: "Philippians 4:13",
                category: "strength",
                duration: 28000
            },
            { 
                text: "The Lord your God is with you wherever you go.", 
                reference: "Joshua 1:9",
                category: "presence",
                duration: 32000
            },
            { 
                text: "Be still, and know that I am God.", 
                reference: "Psalm 46:10",
                category: "peace",
                duration: 35000
            },
            { 
                text: "The Lord will fight for you; you need only to be still.", 
                reference: "Exodus 14:14",
                category: "faith",
                duration: 29000
            },
            { 
                text: "Trust in the Lord with all your heart.", 
                reference: "Proverbs 3:5",
                category: "trust",
                duration: 31000
            },
            { 
                text: "The Lord is near to all who call upon Him.", 
                reference: "Psalm 145:18",
                category: "prayer",
                duration: 27000
            },
            { 
                text: "God is our refuge and strength.", 
                reference: "Psalm 46:1",
                category: "protection",
                duration: 33000
            },
            { 
                text: "The Lord bless you and keep you.", 
                reference: "Numbers 6:24",
                category: "blessing",
                duration: 26000
            },
            { 
                text: "My grace is sufficient for you, for My strength is made perfect in weakness.", 
                reference: "2 Corinthians 12:9",
                category: "grace",
                duration: 34000
            },
            { 
                text: "The steadfast love of the Lord never ceases.", 
                reference: "Lamentations 3:22",
                category: "mercy",
                duration: 30000
            }
        ];
        
        // Start with one verse after 10 seconds, then every 2 minutes (120000ms)
        setTimeout(() => {
            this.createFloatingScripture(verses[0], 0);
        }, 10000);
        
        // Continue adding verses every 2 minutes
        setInterval(() => {
            const randomVerse = verses[Math.floor(Math.random() * verses.length)];
            this.createFloatingScripture(randomVerse, Math.floor(Math.random() * 1000));
        }, 120000); // 2 minutes instead of 4 minutes
        
        // Additional random appearances (every 3 minutes)
        setInterval(() => {
            if (Math.random() > 0.5) { // 50% chance
                const randomVerse = verses[Math.floor(Math.random() * verses.length)];
                this.createFloatingScripture(randomVerse, Math.floor(Math.random() * 1000));
            }
        }, 180000); // 3 minutes instead of 6
    }
    
    createFloatingScripture(verse, index) {
        const element = document.createElement('div');
        element.className = 'floating-scripture';
        
        // Enhanced design with better graphics
        element.innerHTML = `
            <div class="scripture-content">
                <div class="scripture-header">
                    <div class="scripture-icon">
                        ${this.getScriptureIcon(verse.category)}
                    </div>
                    <div class="scripture-category">
                        ${verse.category.charAt(0).toUpperCase() + verse.category.slice(1)}
                    </div>
                </div>
                <div class="scripture-text">"${verse.text}"</div>
                <div class="scripture-reference">- ${verse.reference}</div>
                <div class="scripture-actions">
                    <button class="scripture-amen" onclick="this.parentElement.parentElement.parentElement.classList.add('amen-blessed')">
                        <i class="fas fa-heart"></i> Amen
                    </button>
                    <button class="scripture-share" onclick="this.parentElement.parentElement.parentElement.classList.add('shared')">
                        <i class="fas fa-share"></i> Share
                    </button>
                    <button class="scripture-close" onclick="this.parentElement.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        // Enhanced positioning with better logic
        const positions = [
            { top: '15%', left: '5%' },
            { top: '15%', right: '5%' },
            { top: '50%', left: '5%', transform: 'translateY(-50%)' },
            { top: '50%', right: '5%', transform: 'translateY(-50%)' },
            { top: '85%', left: '5%', transform: 'translateY(-100%)' },
            { top: '85%', right: '5%', transform: 'translateY(-100%)' }
        ];
        
        const position = positions[index % positions.length];
        element.style.cssText = `
            position: fixed;
            top: ${position.top};
            ${position.left ? `left: ${position.left};` : `right: ${position.right};`}
            ${position.transform ? `transform: ${position.transform};` : ''}
            z-index: 1000;
            animation: floatScripture 15s ease-in-out infinite;
            pointer-events: auto;
            max-width: 320px;
        `;
        
        document.body.appendChild(element);
        
        // Auto-remove after custom duration
        setTimeout(() => {
            if (element.parentNode) {
                element.style.animation = 'fadeOut 1s ease-out forwards';
                setTimeout(() => {
                    if (element.parentNode) {
                        element.remove();
                    }
                }, 1000);
            }
        }, verse.duration || 25000);
        
        // Add enhanced animation keyframes if not already added
        if (!document.querySelector('#scripture-keyframes')) {
            const style = document.createElement('style');
            style.id = 'scripture-keyframes';
            style.textContent = `
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
                
                @keyframes fadeOut {
                    from { opacity: 1; transform: scale(1); }
                    to { opacity: 0; transform: scale(0.9); }
                }
                
                .floating-scripture {
                    background: linear-gradient(135deg, 
                        rgba(255, 255, 255, 0.95) 0%, 
                        rgba(248, 248, 255, 0.95) 50%, 
                        rgba(255, 215, 0, 0.1) 100%);
                    color: #1a1a2e;
                    padding: 1.5rem;
                    border-radius: 20px;
                    box-shadow: 
                        0 20px 40px rgba(255, 215, 0, 0.15),
                        0 0 0 1px rgba(255, 215, 0, 0.2),
                        inset 0 0 20px rgba(255, 215, 0, 0.05);
                    max-width: 320px;
                    font-family: 'Playfair Display', serif;
                    backdrop-filter: blur(10px);
                    border: 2px solid rgba(255, 215, 0, 0.3);
                    position: relative;
                    overflow: hidden;
                }
                
                .floating-scripture::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: linear-gradient(90deg, 
                        transparent 0%, 
                        #FFD700 20%, 
                        #FFA500 50%, 
                        #FFD700 80%, 
                        transparent 100%);
                    animation: shimmer 3s ease-in-out infinite;
                }
                
                .floating-scripture::after {
                    content: '';
                    position: absolute;
                    top: -50%;
                    left: -50%;
                    width: 200%;
                    height: 200%;
                    background: radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, transparent 70%);
                    animation: divineGlow 4s ease-in-out infinite;
                    pointer-events: none;
                }
                
                @keyframes shimmer {
                    0%, 100% { transform: translateX(-100%); }
                    50% { transform: translateX(100%); }
                }
                
                @keyframes divineGlow {
                    0%, 100% { opacity: 0.3; transform: scale(0.8) rotate(0deg); }
                    50% { opacity: 0.6; transform: scale(1.2) rotate(180deg); }
                }
                
                .scripture-content {
                    position: relative;
                    z-index: 1;
                }
                
                .scripture-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 1rem;
                    padding-bottom: 0.5rem;
                    border-bottom: 1px solid rgba(255, 215, 0, 0.2);
                }
                
                .scripture-icon {
                    font-size: 1.5rem;
                    color: #FFD700;
                    text-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
                }
                
                .scripture-category {
                    font-size: 0.8rem;
                    font-weight: 600;
                    color: #9370DB;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                
                .scripture-text {
                    font-size: 1.1rem;
                    font-weight: 600;
                    margin-bottom: 0.8rem;
                    line-height: 1.5;
                    color: #1a1a2e;
                    text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5);
                }
                
                .scripture-reference {
                    font-size: 0.9rem;
                    font-style: italic;
                    opacity: 0.8;
                    color: #4169E1;
                    margin-bottom: 1rem;
                }
                
                .scripture-actions {
                    display: flex;
                    gap: 0.5rem;
                    justify-content: center;
                }
                
                .scripture-actions button {
                    background: linear-gradient(135deg, #FFD700, #FFA500);
                    color: #1a1a2e;
                    border: none;
                    padding: 0.5rem 1rem;
                    border-radius: 20px;
                    font-size: 0.8rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 0.3rem;
                }
                
                .scripture-actions button:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
                }
                
                .scripture-close {
                    background: rgba(220, 53, 69, 0.1) !important;
                    color: #dc3545 !important;
                }
                
                .scripture-close:hover {
                    background: rgba(220, 53, 69, 0.2) !important;
                }
                
                .floating-scripture.amen-blessed {
                    border-color: #28a745;
                    box-shadow: 
                        0 20px 40px rgba(40, 167, 69, 0.2),
                        0 0 0 1px rgba(40, 167, 69, 0.3);
                }
                
                .floating-scripture.shared {
                    border-color: #17a2b8;
                    box-shadow: 
                        0 20px 40px rgba(23, 162, 184, 0.2),
                        0 0 0 1px rgba(23, 162, 184, 0.3);
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    getScriptureIcon(category) {
        const icons = {
            'comfort': '🕊️',
            'love': '❤️',
            'strength': '💪',
            'presence': '✨',
            'peace': '🕊️',
            'faith': '🙏',
            'trust': '🤝',
            'prayer': '🙏',
            'protection': '🛡️',
            'blessing': '🌟',
            'grace': '🌈',
            'mercy': '💕'
        };
        return icons[category] || '🕊️';
    }
    
    addAngelicPresence() {
        // Much less frequent angelic presence
        const angelicSymbols = ['🕊️', '✨', '👼', '💫', '🌟'];
        
        setInterval(() => {
            if (Math.random() > 0.9) { // Only 10% chance
                const symbol = angelicSymbols[Math.floor(Math.random() * angelicSymbols.length)];
                this.createAngelicPresence(symbol);
            }
        }, 180000); // 3 minutes instead of 15 seconds
    }
    
    createAngelicPresence(symbol) {
        const presence = document.createElement('div');
        presence.className = 'angelic-presence';
        presence.textContent = symbol;
        presence.style.cssText = `
            position: fixed;
            top: ${Math.random() * 80 + 10}%;
            left: ${Math.random() * 90 + 5}%;
            font-size: ${Math.random() * 1.5 + 1}rem;
            opacity: 0;
            animation: angelicAppear 4s ease-in-out;
            pointer-events: none;
            z-index: 999;
            filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.3));
        `;
        
        document.body.appendChild(presence);
        
        setTimeout(() => {
            presence.remove();
        }, 4000);
        
        // Add animation keyframes
        if (!document.querySelector('#angelic-keyframes')) {
            const style = document.createElement('style');
            style.id = 'angelic-keyframes';
            style.textContent = `
                @keyframes angelicAppear {
                    0% { opacity: 0; transform: scale(0) rotate(0deg); }
                    25% { opacity: 0.3; transform: scale(0.5) rotate(90deg); }
                    50% { opacity: 0.6; transform: scale(1) rotate(180deg); }
                    75% { opacity: 0.3; transform: scale(0.5) rotate(270deg); }
                    100% { opacity: 0; transform: scale(0) rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    setupSpiritualGuidance() {
        // Much less frequent guidance messages (every 2 minutes)
        const guidanceMessages = [
            "God is with you always, guiding your steps.",
            "Trust in His perfect timing for your life.",
            "You are deeply loved and cherished by God.",
            "His grace is sufficient for every challenge.",
            "Walk by faith, not by sight.",
            "His mercies are new every morning.",
            "You are fearfully and wonderfully made.",
            "God has a beautiful plan for your life.",
            "His love endures forever.",
            "You are His beloved child."
        ];
        
        // Show guidance messages every 2 minutes
        setInterval(() => {
            if (Math.random() > 0.5) { // 50% chance
                const message = guidanceMessages[Math.floor(Math.random() * guidanceMessages.length)];
                this.showSpiritualGuidance(message);
            }
        }, 120000); // 2 minutes instead of 5 minutes
    }
    
    showSpiritualGuidance(message) {
        const guidance = document.createElement('div');
        guidance.className = 'spiritual-guidance';
        guidance.innerHTML = `
            <div class="guidance-content">
                <div class="guidance-header">
                    <div class="guidance-icon">
                        <i class="fas fa-dove"></i>
                    </div>
                    <div class="guidance-title">
                        Divine Guidance
                    </div>
                </div>
                <div class="guidance-text">
                    <p>${message}</p>
                </div>
                <div class="guidance-actions">
                    <button class="guidance-accept" onclick="this.parentElement.parentElement.parentElement.classList.add('guidance-accepted')">
                        <i class="fas fa-heart"></i> I Receive This
                    </button>
                    <button class="guidance-close" onclick="this.parentElement.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        guidance.style.cssText = `
            position: fixed;
            top: 150px;
            right: 20px;
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.95) 0%, 
                rgba(248, 248, 255, 0.95) 50%, 
                rgba(255, 215, 0, 0.1) 100%);
            color: #1a1a2e;
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 215, 0, 0.2);
            z-index: 1000;
            max-width: 350px;
            border: 2px solid rgba(255, 215, 0, 0.3);
            animation: guidanceSlide 0.5s ease-out;
            backdrop-filter: blur(10px);
        `;
        
        document.body.appendChild(guidance);
        
        // Auto-remove after longer time
        setTimeout(() => {
            if (guidance.parentNode) {
                guidance.style.animation = 'fadeOut 1s ease-out forwards';
                setTimeout(() => {
                    if (guidance.parentNode) {
                        guidance.remove();
                    }
                }, 1000);
            }
        }, 15000); // 15 seconds instead of 8
        
        // Add animation keyframes
        if (!document.querySelector('#guidance-keyframes')) {
            const style = document.createElement('style');
            style.id = 'guidance-keyframes';
            style.textContent = `
                @keyframes guidanceSlide {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                
                .spiritual-guidance {
                    font-family: 'Playfair Display', serif;
                }
                
                .guidance-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 1rem;
                    padding-bottom: 0.5rem;
                    border-bottom: 1px solid rgba(255, 215, 0, 0.2);
                }
                
                .guidance-icon {
                    font-size: 1.8rem;
                    color: #FFD700;
                    text-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
                }
                
                .guidance-title {
                    font-size: 1rem;
                    font-weight: 700;
                    color: #9370DB;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                
                .guidance-text p {
                    margin-bottom: 0;
                    font-style: italic;
                    text-align: center;
                    line-height: 1.6;
                    font-size: 1.1rem;
                    color: #1a1a2e;
                }
                
                .guidance-actions {
                    display: flex;
                    gap: 0.5rem;
                    justify-content: center;
                    margin-top: 1rem;
                }
                
                .guidance-actions button {
                    background: linear-gradient(135deg, #FFD700, #FFA500);
                    color: #1a1a2e;
                    border: none;
                    padding: 0.5rem 1rem;
                    border-radius: 20px;
                    font-size: 0.8rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 0.3rem;
                }
                
                .guidance-actions button:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
                }
                
                .guidance-close {
                    background: rgba(220, 53, 69, 0.1) !important;
                    color: #dc3545 !important;
                }
                
                .guidance-close:hover {
                    background: rgba(220, 53, 69, 0.2) !important;
                }
                
                .spiritual-guidance.guidance-accepted {
                    border-color: #28a745;
                    box-shadow: 
                        0 20px 40px rgba(40, 167, 69, 0.2),
                        0 0 0 1px rgba(40, 167, 69, 0.3);
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    addDivineLight() {
        // Add divine light rays to important elements
        const importantElements = document.querySelectorAll('.hero-section, .church-logo, .section-title');
        
        importantElements.forEach(element => {
            const lightRay = document.createElement('div');
            lightRay.className = 'divine-light-ray';
            lightRay.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(45deg, 
                    transparent 30%, 
                    rgba(255, 215, 0, 0.1) 50%, 
                    transparent 70%);
                pointer-events: none;
                animation: divineShimmer 4s ease-in-out infinite;
            `;
            
            element.style.position = 'relative';
            element.appendChild(lightRay);
        });
        
        // Add animation keyframes
        if (!document.querySelector('#divine-light-keyframes')) {
            const style = document.createElement('style');
            style.id = 'divine-light-keyframes';
            style.textContent = `
                @keyframes divineShimmer {
                    0%, 100% { opacity: 0.3; transform: translateX(-100%); }
                    50% { opacity: 0.6; transform: translateX(100%); }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    addPrayerCounters() {
        // Add prayer counters to show community engagement
        const prayerCounters = [
            { id: 'prayers-today', label: 'Prayers Today', count: Math.floor(Math.random() * 50) + 100 },
            { id: 'blessings-received', label: 'Blessings Received', count: Math.floor(Math.random() * 30) + 75 },
            { id: 'souls-touched', label: 'Souls Touched', count: Math.floor(Math.random() * 20) + 200 }
        ];
        
        prayerCounters.forEach(counter => {
            this.createPrayerCounter(counter);
        });
    }
    
    createPrayerCounter(counter) {
        const element = document.createElement('div');
        element.className = 'prayer-counter';
        element.innerHTML = `
            <div class="counter-icon">
                <i class="fas fa-praying-hands"></i>
            </div>
            <div class="counter-content">
                <div class="counter-number">${counter.count}</div>
                <div class="counter-label">${counter.label}</div>
            </div>
        `;
        
        element.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.9), rgba(255, 165, 0, 0.9));
            color: #1a1a2e;
            padding: 1rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(255, 215, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 998;
            animation: counterPulse 3s ease-in-out infinite;
        `;
        
        document.body.appendChild(element);
        
        // Add animation keyframes
        if (!document.querySelector('#counter-keyframes')) {
            const style = document.createElement('style');
            style.id = 'counter-keyframes';
            style.textContent = `
                @keyframes counterPulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.05); }
                }
                
                .prayer-counter {
                    font-family: 'Montserrat', sans-serif;
                }
                
                .counter-icon {
                    font-size: 1.5rem;
                }
                
                .counter-number {
                    font-size: 1.2rem;
                    font-weight: 700;
                }
                
                .counter-label {
                    font-size: 0.8rem;
                    font-weight: 600;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    setupDivineInteractions() {
        // Add divine hover effects to buttons and links
        const interactiveElements = document.querySelectorAll('.btn, .nav-link, .card');
        
        interactiveElements.forEach(element => {
            element.addEventListener('mouseenter', () => {
                this.addDivineGlow(element);
            });
            
            element.addEventListener('mouseleave', () => {
                this.removeDivineGlow(element);
            });
        });
    }
    
    addDivineGlow(element) {
        element.style.boxShadow = '0 0 20px rgba(255, 215, 0, 0.5)';
        element.style.transition = 'all 0.3s ease';
    }
    
    removeDivineGlow(element) {
        element.style.boxShadow = '';
    }
    
    addBibleVerses() {
        // Add daily verse to footer
        const dailyVerses = [
            "Today's verse: The Lord is my light and my salvation. - Psalm 27:1",
            "Today's verse: The Lord will guide you continually. - Isaiah 58:11",
            "Today's verse: Trust in the Lord forever. - Psalm 125:1",
            "Today's verse: The Lord is merciful and gracious. - Psalm 145:8",
            "Today's verse: God is love. - 1 John 4:8"
        ];
        
        const footer = document.querySelector('.footer-bottom');
        if (footer) {
            const verseElement = document.createElement('div');
            verseElement.className = 'daily-verse';
            verseElement.textContent = dailyVerses[new Date().getDate() % dailyVerses.length];
            verseElement.style.cssText = `
                text-align: center;
                color: #FFD700;
                font-style: italic;
                margin: 1rem 0;
                font-family: 'Playfair Display', serif;
            `;
            
            footer.appendChild(verseElement);
        }
    }
    
    setupPrayerReminders() {
        // Set up prayer reminders at specific times
        const prayerTimes = [6, 12, 18, 21]; // 6AM, 12PM, 6PM, 9PM
        
        const currentHour = new Date().getHours();
        const nextPrayerTime = prayerTimes.find(time => time > currentHour) || prayerTimes[0];
        
        if (nextPrayerTime) {
            this.schedulePrayerReminder(nextPrayerTime);
        }
    }
    
    schedulePrayerReminder(hour) {
        const now = new Date();
        const reminderTime = new Date();
        reminderTime.setHours(hour, 0, 0, 0);
        
        if (reminderTime <= now) {
            reminderTime.setDate(reminderTime.getDate() + 1);
        }
        
        const timeUntilReminder = reminderTime - now;
        
        setTimeout(() => {
            this.showPrayerReminder(hour);
            // Schedule next day's reminder
            this.schedulePrayerReminder(hour);
        }, timeUntilReminder);
    }
    
    showPrayerReminder(hour) {
        const reminder = document.createElement('div');
        reminder.className = 'prayer-reminder';
        reminder.innerHTML = `
            <div class="reminder-content">
                <div class="reminder-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="reminder-text">
                    <h4>Prayer Time</h4>
                    <p>It's time for prayer. Join us in communing with God.</p>
                </div>
                <button class="reminder-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        reminder.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #1a1a2e;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.4);
            z-index: 1001;
            max-width: 300px;
            animation: reminderSlide 0.5s ease-out;
        `;
        
        document.body.appendChild(reminder);
        
        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (reminder.parentNode) {
                reminder.remove();
            }
        }, 10000);
        
        // Add animation keyframes
        if (!document.querySelector('#reminder-keyframes')) {
            const style = document.createElement('style');
            style.id = 'reminder-keyframes';
            style.textContent = `
                @keyframes reminderSlide {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                
                .prayer-reminder {
                    font-family: 'Montserrat', sans-serif;
                }
                
                .reminder-content {
                    position: relative;
                }
                
                .reminder-icon {
                    font-size: 2rem;
                    margin-bottom: 1rem;
                    text-align: center;
                }
                
                .reminder-text h4 {
                    margin-bottom: 0.5rem;
                    font-weight: 700;
                }
                
                .reminder-text p {
                    margin-bottom: 0;
                    font-size: 0.9rem;
                }
                
                .reminder-close {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    background: rgba(26, 26, 46, 0.2);
                    border: none;
                    width: 25px;
                    height: 25px;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.3s ease;
                }
                
                .reminder-close:hover {
                    background: rgba(26, 26, 46, 0.4);
                    transform: scale(1.1);
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    addHeavenlyEffects() {
        // Add heavenly particle effects
        this.createHeavenlyParticles();
        
        // Add divine sound effects on scroll
        this.setupDivineSounds();
        
        // Add angelic animations
        this.addAngelicAnimations();
    }
    
    createHeavenlyParticles() {
        const canvas = document.createElement('canvas');
        canvas.id = 'heavenly-particles';
        canvas.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        `;
        
        document.body.appendChild(canvas);
        
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        
        const particles = [];
        const particleCount = 50;
        
        // Create particles
        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                size: Math.random() * 3 + 1,
                speedX: (Math.random() - 0.5) * 0.5,
                speedY: (Math.random() - 0.5) * 0.5,
                opacity: Math.random() * 0.5 + 0.2
            });
        }
        
        // Animation loop
        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            particles.forEach(particle => {
                particle.x += particle.speedX;
                particle.y += particle.speedY;
                
                // Wrap around screen
                if (particle.x < 0) particle.x = canvas.width;
                if (particle.x > canvas.width) particle.x = 0;
                if (particle.y < 0) particle.y = canvas.height;
                if (particle.y > canvas.height) particle.y = 0;
                
                // Draw particle
                ctx.beginPath();
                ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(255, 215, 0, ${particle.opacity})`;
                ctx.fill();
            });
            
            requestAnimationFrame(animate);
        }
        
        animate();
        
        // Handle window resize
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    }
    
    setupDivineSounds() {
        // Add subtle sound effects to divine interactions
        let lastSoundTime = 0;
        
        document.addEventListener('click', (e) => {
            const now = Date.now();
            if (now - lastSoundTime > 5000 && Math.random() > 0.9) {
                this.playDivineSound();
                lastSoundTime = now;
            }
        });
    }
    
    playDivineSound() {
        if (window.heavenlyGuidance) {
            window.heavenlyGuidance.playGentleChime();
        }
    }
    
    addAngelicAnimations() {
        // Add subtle animations to create angelic presence
        const angelicElements = document.querySelectorAll('.floating-angel, .hero-angel');
        
        angelicElements.forEach(element => {
            element.style.animation = 'angelicFloat 6s ease-in-out infinite';
        });
        
        // Add animation keyframes
        if (!document.querySelector('#angelic-animation-keyframes')) {
            const style = document.createElement('style');
            style.id = 'angelic-animation-keyframes';
            style.textContent = `
                @keyframes angelicFloat {
                    0%, 100% { 
                        transform: translateY(0px) rotate(0deg) scale(1); 
                        opacity: 0.3; 
                    }
                    25% { 
                        transform: translateY(-20px) rotate(5deg) scale(1.05); 
                        opacity: 0.5; 
                    }
                    50% { 
                        transform: translateY(-30px) rotate(0deg) scale(1.1); 
                        opacity: 0.7; 
                    }
                    75% { 
                        transform: translateY(-20px) rotate(-5deg) scale(1.05); 
                        opacity: 0.5; 
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    setupSpiritualGuidance() {
        // Add spiritual guidance messages
        const guidanceMessages = [
            "God is with you always.",
            "Trust in His divine plan.",
            "You are loved beyond measure.",
            "His grace is sufficient.",
            "Walk by faith, not by sight.",
            "His mercies are new every morning.",
            "You are fearfully and wonderfully made.",
            "God has a purpose for your life.",
            "His love endures forever.",
            "You are His beloved child."
        ];
        
        // Show guidance messages periodically
        setInterval(() => {
            const message = guidanceMessages[Math.floor(Math.random() * guidanceMessages.length)];
            this.showSpiritualGuidance(message);
        }, 45000);
    }
    
    showSpiritualGuidance(message) {
        const guidance = document.createElement('div');
        guidance.className = 'spiritual-guidance';
        guidance.innerHTML = `
            <div class="guidance-content">
                <div class="guidance-icon">
                    <i class="fas fa-dove"></i>
                </div>
                <div class="guidance-text">
                    <p>${message}</p>
                </div>
                <button class="guidance-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        guidance.style.cssText = `
            position: fixed;
            top: 150px;
            right: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 248, 255, 0.95));
            color: #1a1a2e;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            max-width: 300px;
            border: 2px solid rgba(255, 215, 0, 0.3);
            animation: guidanceSlide 0.5s ease-out;
        `;
        
        document.body.appendChild(guidance);
        
        // Auto-remove after 8 seconds
        setTimeout(() => {
            if (guidance.parentNode) {
                guidance.remove();
            }
        }, 8000);
        
        // Add animation keyframes
        if (!document.querySelector('#guidance-keyframes')) {
            const style = document.createElement('style');
            style.id = 'guidance-keyframes';
            style.textContent = `
                @keyframes guidanceSlide {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                
                .spiritual-guidance {
                    font-family: 'Playfair Display', serif;
                }
                
                .guidance-icon {
                    font-size: 2rem;
                    color: #FFD700;
                    margin-bottom: 1rem;
                    text-align: center;
                }
                
                .guidance-text p {
                    margin-bottom: 0;
                    font-style: italic;
                    text-align: center;
                    line-height: 1.4;
                }
                
                .guidance-close {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    background: rgba(255, 215, 0, 0.2);
                    border: none;
                    width: 25px;
                    height: 25px;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.3s ease;
                }
                
                .guidance-close:hover {
                    background: rgba(255, 215, 0, 0.4);
                    transform: scale(1.1);
                }
            `;
            document.head.appendChild(style);
        }
    }
}

// Initialize spiritual enhancement when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.spiritualEnhancement = new SpiritualEnhancement();
    console.log('Spiritual Enhancement initialized - Making the website more divine and real');
});

// Export for external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SpiritualEnhancement;
}
