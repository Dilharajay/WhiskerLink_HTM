document.addEventListener('DOMContentLoaded', () => {
    // Back to Top Button
    const backToTopButton = document.getElementById('back-to-top');
    
    if (backToTopButton) {
        // Show/hide button based on scroll position
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('visible');
            } else {
                backToTopButton.classList.remove('visible');
            }
        });
        
        // Scroll to top on click
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Newsletter Form
    const newsletterForm = document.getElementById('newsletter-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const emailInput = newsletterForm.querySelector('input[type="email"]');
            const submitButton = newsletterForm.querySelector('button');
            const email = emailInput.value;
            
            // Show loading state
            const originalButtonText = submitButton.textContent;
            submitButton.textContent = 'Subscribing...';
            submitButton.disabled = true;
            
            // Simulate API call (replace with actual API call)
            setTimeout(() => {
                // Show success message
                submitButton.textContent = '✓ Subscribed!';
                submitButton.style.background = '#28a745';
                emailInput.value = '';
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    submitButton.textContent = originalButtonText;
                    submitButton.style.background = '';
                    submitButton.disabled = false;
                }, 3000);
                
                // You can add actual newsletter subscription logic here
                console.log('Newsletter subscription for:', email);
            }, 1000);
        });
    }
    
    // Footer link animations
    const footerLinks = document.querySelectorAll('.footer-links a');
    footerLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.paddingLeft = '10px';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.paddingLeft = '0';
        });
    });
    
    // Social links hover effects
    const socialLinks = document.querySelectorAll('.social-links a');
    socialLinks.forEach((link, index) => {
        link.style.animationDelay = `${index * 0.1}s`;
        
        link.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Add ripple effect
            const ripple = document.createElement('span');
            ripple.style.cssText = `
                position: absolute;
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.5);
                transform: scale(0);
                animation: ripple 0.6s ease-out;
            `;
            
            link.style.position = 'relative';
            link.style.overflow = 'hidden';
            link.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
    
    // Add ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(2);
                opacity: 0;
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
    
    // Footer columns fade in on scroll
    const footerColumns = document.querySelectorAll('.footer-column');
    const observerOptions = {
        threshold: 0.2,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    footerColumns.forEach((column, index) => {
        column.style.opacity = '0';
        column.style.animationDelay = `${index * 0.1}s`;
        observer.observe(column);
    });
    
    // Current year update (if needed dynamically)
    const yearElements = document.querySelectorAll('.current-year');
    yearElements.forEach(el => {
        el.textContent = new Date().getFullYear();
    });
    
    // Add hover effect to newsletter button
    const newsletterButton = newsletterForm?.querySelector('button');
    if (newsletterButton) {
        newsletterButton.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.05)';
        });
        
        newsletterButton.addEventListener('mouseleave', function() {
            if (!this.disabled) {
                this.style.transform = 'translateY(0) scale(1)';
            }
        });
    }
});

// Smooth scroll for all footer links
document.querySelectorAll('.footer-links a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});