/**
 * EmailJS Configuration
 * Centralized email settings for WhiskerLink platform
 * 
 * @package WhiskerLink
 * @version 1.0
 */

// EmailJS Configuration
const EmailJSConfig = {
    // Your EmailJS credentials
    publicKey: 'yP_HfFyjDVtRWmq6d',
    serviceId: 'service_cuwcftc',
    
    // Template IDs for different email types
    templates: {
        contactReporter: 'template_nn1rjgt',
        // Add more templates as needed
        // adoptionInquiry: 'template_xxxxx',
        // rescueAlert: 'template_yyyyy',
    },
    
    // Initialize EmailJS
    init: function() {
        if (typeof emailjs !== 'undefined') {
            emailjs.init(this.publicKey);
            console.log('✅ EmailJS initialized successfully');
            return true;
        } else {
            console.error('❌ EmailJS library not loaded');
            return false;
        }
    },
    
    // Send email with error handling
    sendEmail: function(templateId, formElement, successCallback, errorCallback) {
        emailjs.sendForm(this.serviceId, templateId, formElement)
            .then(function(response) {
                console.log('✅ Email sent successfully', response.status, response.text);
                if (successCallback) successCallback(response);
            })
            .catch(function(error) {
                console.error('❌ Email send failed', error);
                if (errorCallback) errorCallback(error);
            });
    },
    
    // Validate configuration
    validate: function() {
        const errors = [];
        
        if (!this.publicKey || this.publicKey === 'YOUR_PUBLIC_KEY') {
            errors.push('Public Key not configured');
        }
        
        if (!this.serviceId || this.serviceId === 'YOUR_SERVICE_ID') {
            errors.push('Service ID not configured');
        }
        
        if (Object.keys(this.templates).length === 0) {
            errors.push('No email templates configured');
        }
        
        if (errors.length > 0) {
            console.error('❌ EmailJS Configuration Errors:', errors);
            return false;
        }
        
        console.log('✅ EmailJS configuration is valid');
        return true;
    },
    
    // Get human-readable error message
    getErrorMessage: function(error) {
        if (!error) return 'Unknown error occurred';
        
        const errorText = error.text || error.message || '';
        
        if (errorText.includes('insufficient authentication scopes')) {
            return 'Email service authentication error. Please contact the administrator.';
        } else if (errorText.includes('Invalid API key')) {
            return 'Email service configuration error. Please contact support.';
        } else if (errorText.includes('Template not found')) {
            return 'Email template error. Please contact support.';
        } else if (errorText.includes('Rate limit')) {
            return 'Too many emails sent. Please try again later.';
        } else if (errorText.includes('Network')) {
            return 'Network error. Please check your internet connection.';
        } else if (errorText) {
            return 'Error: ' + errorText;
        } else {
            return 'Failed to send email. Please try again.';
        }
    }
};

// Auto-initialize when script loads
document.addEventListener('DOMContentLoaded', function() {
    if (EmailJSConfig.validate()) {
        EmailJSConfig.init();
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EmailJSConfig;
}