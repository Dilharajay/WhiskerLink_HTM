// Counter Animation for Statistics
function animateCounters() {
    const counters = document.querySelectorAll('.count-up');
    const speed = 200; // Animation speed

    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-count'));
                const increment = target / speed;
                let count = 0;

                const updateCount = () => {
                    count += increment;
                    if (count < target) {
                        counter.textContent = Math.ceil(count);
                        requestAnimationFrame(updateCount);
                    } else {
                        counter.textContent = target;
                    }
                };

                updateCount();
                observer.unobserve(counter);
            }
        });
    }, observerOptions);

    counters.forEach(counter => observer.observe(counter));
}

// Scroll Animation for Elements
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Animate step cards
    const stepCards = document.querySelectorAll('.step-card');
    stepCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = `all 0.6s ease ${index * 0.2}s`;
        observer.observe(card);
    });

    // Animate feature items
    const featureItems = document.querySelectorAll('.feature-item');
    featureItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-30px)';
        item.style.transition = `all 0.6s ease ${index * 0.1}s`;
        observer.observe(item);
    });
}

// Smooth Scroll for Links
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
}

// Parallax Effect for Hero Section
function initParallax() {
    const hero = document.querySelector('.hero-section');
    if (!hero) return;

    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const rate = scrolled * 0.5;
        hero.style.transform = `translateY(${rate}px)`;
    });
}

// Button Hover Effects
function initButtonEffects() {
    const buttons = document.querySelectorAll('.hero-btn, .btn');
    
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function(e) {
            const x = e.offsetX;
            const y = e.offsetY;
            
            const ripple = document.createElement('span');
            ripple.style.cssText = `
                position: absolute;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: translate(-50%, -50%);
                left: ${x}px;
                top: ${y}px;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            // Animate ripple
            ripple.style.transition = 'width 0.6s, height 0.6s';
            setTimeout(() => {
                ripple.style.width = '300px';
                ripple.style.height = '300px';
            }, 0);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
}

// Add floating animation to icons
function initFloatingIcons() {
    const icons = document.querySelectorAll('.step-icon, .feature-icon');
    
    icons.forEach((icon, index) => {
        icon.style.animation = `float 3s ease-in-out ${index * 0.3}s infinite`;
    });
}

// Create floating animation keyframes
function addFloatingAnimation() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    `;
    document.head.appendChild(style);
}

// Loading Animation
function initLoadingAnimation() {
    const sections = document.querySelectorAll('section');
    sections.forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        section.style.transition = 'all 0.6s ease';
        
        setTimeout(() => {
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Easter Egg: Paw prints on click
function initPawPrints() {
    let pawTimeout;
    
    document.addEventListener('click', (e) => {
        // Don't add paw prints on button clicks
        if (e.target.closest('a, button')) return;
        
        const paw = document.createElement('div');
        paw.textContent = '🐾';
        paw.style.cssText = `
            position: fixed;
            left: ${e.clientX}px;
            top: ${e.clientY}px;
            font-size: 24px;
            pointer-events: none;
            z-index: 9999;
            animation: pawFade 2s ease-out forwards;
            transform: translate(-50%, -50%);
        `;
        document.body.appendChild(paw);
        
        setTimeout(() => paw.remove(), 2000);
        
        // Add CSS for paw animation if not exists
        if (!document.getElementById('paw-animation')) {
            const style = document.createElement('style');
            style.id = 'paw-animation';
            style.textContent = `
                @keyframes pawFade {
                    0% { opacity: 1; transform: translate(-50%, -50%) scale(0); }
                    50% { opacity: 1; transform: translate(-50%, -50%) scale(1.2); }
                    100% { opacity: 0; transform: translate(-50%, -50%) scale(1) translateY(-50px); }
                }
            `;
            document.head.appendChild(style);
        }
    });
}

// Initialize all functions when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    addFloatingAnimation();
    animateCounters();
    initScrollAnimations();
    initSmoothScroll();
    initParallax();
    initButtonEffects();
    initFloatingIcons();
    initLoadingAnimation();
    initPawPrints();
});

// Recalculate on window resize
window.addEventListener('resize', () => {
    // Add any resize-specific logic here
});

// Add smooth scroll behavior to the whole page
document.documentElement.style.scrollBehavior = 'smooth';