<?php 
include 'header.php'; 
include 'db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Get report ID from URL
$report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;

if ($report_id <= 0) {
    header('Location: rescue-reports.php');
    exit;
}

// Fetch report details and reporter information
$stmt = $conn->prepare("SELECT rr.*, u.fullname as reporter_name, u.email as reporter_email, u.phone as reporter_phone
                        FROM Rescue_Report rr 
                        LEFT JOIN users u ON rr.reporter_id = u.user_id 
                        WHERE rr.report_id = ?");
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: rescue-reports.php');
    exit;
}

$report = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<style>
    .contact-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 20px;
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .page-header h2 {
        color: #333;
        margin-bottom: 10px;
        font-size: 32px;
    }
    
    .page-header p {
        color: #666;
        font-size: 16px;
    }
    
    .report-summary {
        background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 30px;
        border: 2px solid #667eea30;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    
    .report-summary h3 {
        margin-top: 0;
        color: #333;
        font-size: 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .report-summary-grid {
        display: grid;
        grid-template-columns: 150px auto;
        gap: 15px;
        margin-top: 15px;
    }
    
    .report-summary-label {
        font-weight: bold;
        color: #555;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .report-summary-value {
        color: #333;
        display: flex;
        align-items: center;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .status-urgent {
        background: #ff6b6b;
        color: white;
    }
    
    .status-active {
        background: #51cf66;
        color: white;
    }
    
    .form-container {
        background: white;
        padding: 35px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
    }
    
    .form-section-title {
        font-size: 18px;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #667eea;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #333;
        font-size: 14px;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 14px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
        transition: border-color 0.3s, box-shadow 0.3s;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 180px;
        line-height: 1.6;
    }
    
    .form-group small {
        display: block;
        margin-top: 5px;
        color: #666;
        font-size: 13px;
    }
    
    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 25px;
        color: #667eea;
        text-decoration: none;
        font-weight: bold;
        padding: 10px 15px;
        border-radius: 8px;
        transition: background 0.3s;
    }
    
    .back-button:hover {
        background: #667eea15;
    }
    
    .char-count {
        text-align: right;
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }
    
    .char-count.warning {
        color: #ff6b6b;
        font-weight: bold;
    }
    
    .info-box {
        background: linear-gradient(135deg, #e7f3ff 0%, #d1ecf1 100%);
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        border-left: 4px solid #2196F3;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .info-box p {
        margin: 0;
        color: #0d47a1;
        line-height: 1.6;
    }
    
    .info-box strong {
        color: #0c5460;
    }
    
    .tip-box {
        background: #fff9e6;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #ffc107;
    }
    
    .tip-box h4 {
        margin: 0 0 10px 0;
        color: #856404;
        font-size: 14px;
    }
    
    .tip-box ul {
        margin: 0;
        padding-left: 20px;
        color: #856404;
        font-size: 13px;
    }
    
    .tip-box li {
        margin-bottom: 5px;
    }
    
    .alert {
        padding: 18px;
        margin-bottom: 20px;
        border-radius: 8px;
        display: none;
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .alert-success {
        background-color: #d4edda;
        border: 2px solid #c3e6cb;
        color: #155724;
    }
    
    .alert-error {
        background-color: #f8d7da;
        border: 2px solid #f5c6cb;
        color: #721c24;
    }
    
    .alert strong {
        font-size: 16px;
    }
    
    .btn-accent {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 16px 32px;
        font-size: 16px;
        font-weight: bold;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn-accent:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .btn-accent:active {
        transform: translateY(0);
    }
    
    .btn-accent:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .quick-templates {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .template-btn {
        padding: 12px;
        background: #f8f9fa;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 13px;
        text-align: center;
        font-weight: 500;
    }
    
    .template-btn:hover {
        background: #667eea15;
        border-color: #667eea;
        transform: translateY(-2px);
    }
    
    .contact-method-selector {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .method-option {
        flex: 1;
        padding: 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: white;
    }
    
    .method-option.active {
        border-color: #667eea;
        background: #667eea15;
    }
    
    .method-option:hover {
        border-color: #667eea;
    }
    
    .method-option .icon {
        font-size: 24px;
        margin-bottom: 5px;
    }
    
    .method-option .label {
        font-weight: bold;
        font-size: 14px;
        color: #333;
    }
    
    @media (max-width: 768px) {
        .report-summary-grid {
            grid-template-columns: 1fr;
        }
        
        .quick-templates {
            grid-template-columns: 1fr;
        }
        
        .contact-method-selector {
            flex-direction: column;
        }
    }
</style>

<section id="contact-reporter" style="padding: 2rem 0;">
    <div class="contact-container">
        <a href="report-detail.php?id=<?php echo $report_id; ?>" class="back-button">
            ← Back to Report
        </a>
        
        <div class="page-header">
            <h2>📧 Contact Reporter</h2>
            <p>Send a professional message to connect with the reporter</p>
        </div>
        
        <div id="successAlert" class="alert alert-success">
            <strong>✅ Message Sent Successfully!</strong><br>
            <span id="successMessage"></span>
            <p style="margin-top: 15px; margin-bottom: 0;">
                <a href="report-detail.php?id=<?php echo $report_id; ?>" style="color: #155724; text-decoration: underline;">View Report</a> | 
                <a href="rescue-reports.php" style="color: #155724; text-decoration: underline;">Browse More Reports</a>
            </p>
        </div>
        
        <div id="errorAlert" class="alert alert-error">
            <strong>❌ Failed to Send Message</strong><br>
            <span id="errorMessage"></span>
        </div>
        
        <!-- Report Summary -->
        <div class="report-summary">
            <h3>
                <span>🐾</span>
                Report Summary
            </h3>
            <div class="report-summary-grid">
                <span class="report-summary-label">
                    <span>🦴</span> Animal:
                </span>
                <span class="report-summary-value">
                    <strong><?php echo htmlspecialchars($report['animal_species']); ?></strong>
                </span>
                
                <span class="report-summary-label">
                    <span>📍</span> Location:
                </span>
                <span class="report-summary-value">
                    <?php echo htmlspecialchars($report['location_found']); ?>
                </span>
                
                <span class="report-summary-label">
                    <span>📋</span> Report Type:
                </span>
                <span class="report-summary-value">
                    <span class="status-badge status-<?php echo strtolower($report['report_type']) === 'urgent' ? 'urgent' : 'active'; ?>">
                        <?php echo htmlspecialchars($report['report_type']); ?>
                    </span>
                </span>
                
                <span class="report-summary-label">
                    <span>👤</span> Reporter:
                </span>
                <span class="report-summary-value">
                    <?php echo htmlspecialchars($report['reporter_name']); ?>
                </span>
                
                <span class="report-summary-label">
                    <span>📅</span> Reported:
                </span>
                <span class="report-summary-value">
                    <?php echo date('F j, Y', strtotime($report['date_reported'])); ?>
                </span>
            </div>
        </div>
        
        <div class="info-box">
            <p>
                <strong>📬 Your message will be sent to:</strong> <?php echo htmlspecialchars($report['reporter_email']); ?>
            </p>
            <p style="margin-top: 8px;">
                The reporter will receive your message via email and can reply directly to <strong><?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?></strong>
            </p>
        </div>
        
        <!-- Contact Form -->
        <div class="form-container">
            <div class="form-section-title">
                <span>✍️</span>
                Compose Your Message
            </div>
            
            <!-- Quick Message Templates -->
            <div class="tip-box">
                <h4>💡 Quick Message Templates (Click to use):</h4>
                <div class="quick-templates">
                    <button type="button" class="template-btn" data-template="help">
                        🤝 Offer Help
                    </button>
                    <button type="button" class="template-btn" data-template="adopt">
                        🏠 Adoption Inquiry
                    </button>
                    <button type="button" class="template-btn" data-template="info">
                        ❓ Request More Info
                    </button>
                    <button type="button" class="template-btn" data-template="vet">
                        🏥 Veterinary Support
                    </button>
                </div>
            </div>
            
            <form id="contactForm">
                <!-- Sender Information -->
                <div class="form-group">
                    <label for="sender-name">
                        Your Name
                    </label>
                    <input type="text" id="sender-name" name="sender_name" 
                           value="<?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : ''; ?>" 
                           readonly style="background-color: #f5f5f5;">
                </div>
                
                <div class="form-group">
                    <label for="sender-email">
                        Your Email Address
                    </label>
                    <input type="email" id="sender-email" name="sender_email" 
                           value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" 
                           readonly style="background-color: #f5f5f5;">
                    <small>✓ The reporter can reply directly to this email</small>
                </div>
                
                <!-- Contact Reason -->
                <div class="form-group">
                    <label for="contact-reason">
                        I'm contacting because: <span style="color: red;">*</span>
                    </label>
                    <select id="contact-reason" name="contact_reason" required>
                        <option value="">-- Select a reason --</option>
                        <option value="Interested in helping with rescue">🤝 Interested in helping with rescue</option>
                        <option value="Want to adopt this animal">🏠 Want to adopt this animal</option>
                        <option value="Can provide temporary foster care">🏡 Can provide temporary foster care</option>
                        <option value="Can provide veterinary assistance">🏥 Can provide veterinary assistance</option>
                        <option value="Have information about this animal">ℹ️ Have information about this animal</option>
                        <option value="Want to donate supplies">📦 Want to donate supplies</option>
                        <option value="Can provide transportation">🚗 Can provide transportation</option>
                        <option value="Other">💬 Other</option>
                    </select>
                </div>
                
                <!-- Subject -->
                <div class="form-group">
                    <label for="subject">
                        Subject <span style="color: red;">*</span>
                    </label>
                    <input type="text" id="subject" name="subject" 
                           placeholder="Brief description of your message" 
                           maxlength="100" required>
                    <div class="char-count">
                        <span id="subject-count">0</span>/100 characters
                    </div>
                </div>
                
                <!-- Message -->
                <div class="form-group">
                    <label for="message">
                        Your Message <span style="color: red;">*</span>
                    </label>
                    <textarea id="message" name="message" 
                              placeholder="Write your message here...

Tip: Include:
• How you can help
• Your availability
• Any relevant experience
• Questions you have about the animal"
                              maxlength="2000" required></textarea>
                    <div class="char-count">
                        <span id="message-count">0</span>/2000 characters
                    </div>
                </div>
                
                <!-- Contact Preference -->
                <div class="form-group">
                    <label>
                        Preferred Response Method:
                    </label>
                    <select name="contact_preference">
                        <option value="Email">📧 Email (default)</option>
                        <option value="Phone">📱 Phone call</option>
                        <option value="Either">✅ Either email or phone</option>
                    </select>
                </div>
                
                <!-- Urgency Level -->
                <div class="form-group">
                    <label>
                        Urgency Level:
                    </label>
                    <select name="urgency_level">
                        <option value="Normal">⏱️ Normal - Response within 24-48 hours</option>
                        <option value="Urgent">⚠️ Urgent - Need response today</option>
                        <option value="Emergency">🚨 Emergency - Immediate assistance needed</option>
                    </select>
                </div>
                
                <!-- Hidden fields for EmailJS template -->
                <input type="hidden" name="reporter_name" value="<?php echo htmlspecialchars($report['reporter_name']); ?>">
                <input type="hidden" name="reporter_email" value="<?php echo htmlspecialchars($report['reporter_email']); ?>">
                <input type="hidden" name="animal_species" value="<?php echo htmlspecialchars($report['animal_species']); ?>">
                <input type="hidden" name="location_found" value="<?php echo htmlspecialchars($report['location_found']); ?>">
                <input type="hidden" name="report_type" value="<?php echo htmlspecialchars($report['report_type']); ?>">
                <input type="hidden" name="report_id" value="<?php echo $report_id; ?>">
                <input type="hidden" name="report_link" value="http://<?php echo $_SERVER['HTTP_HOST']; ?>/report-detail.php?id=<?php echo $report_id; ?>">
                <input type="hidden" name="report_date" value="<?php echo date('F j, Y', strtotime($report['date_reported'])); ?>">
                
                <button type="submit" class="btn-accent" id="submitBtn" style="width: 100%; margin-top: 10px;">
                    <span id="btnText">📧 Send Message</span>
                </button>
            </form>
        </div>
    </div>
</section>

<!-- EmailJS SDK -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>

<script>
    // EmailJS Configuration
    const emailConfig = {
        publicKey: 'yP_HfFyjDVtRWmq6d',
        serviceId: 'service_cuwcftc',
        templateId: 'template_nn1rjgt'
    };
    
    // Initialize EmailJS
    emailjs.init(emailConfig.publicKey);
    
    // Message Templates
    const messageTemplates = {
        help: {
            subject: "Offering Help with Rescue Case",
            message: `Hello,

I came across your rescue report on WhiskerLink and I'm interested in helping with this case.

I would like to offer my assistance with:
• [Specify what help you can provide - e.g., rescue, transport, foster care]

My experience:
• [Mention any relevant experience with animal rescue]

Availability:
• [Mention when you're available to help]

Please let me know how I can best assist with this situation. I'm happy to discuss the details further.

Thank you for reporting this case and caring for animals in need.

Best regards`
        },
        adopt: {
            subject: "Adoption Inquiry",
            message: `Hello,

I'm writing regarding the animal you reported on WhiskerLink. I'm very interested in potentially adopting this animal.

About my situation:
• Home environment: [Describe your living situation]
• Experience with pets: [Mention your experience]
• Other pets: [Do you have other pets?]

I would love to learn more about:
• The animal's current condition
• Any medical needs
• The adoption process

I'm committed to providing a loving, permanent home. Please let me know the next steps.

Thank you for your time and for caring for this animal.

Best regards`
        },
        info: {
            subject: "Request for More Information",
            message: `Hello,

I saw your rescue report on WhiskerLink and would like to learn more about this case.

I'm interested in knowing:
• Current status of the animal
• Any immediate needs
• Medical condition
• How I might be able to help

Thank you for taking the time to report this case. I appreciate your efforts in helping animals in need.

Best regards`
        },
        vet: {
            subject: "Veterinary Assistance Available",
            message: `Hello,

I'm a [veterinarian/veterinary professional/animal care provider] and I saw your rescue report on WhiskerLink.

I can offer:
• [Specify services - medical care, consultation, supplies, etc.]

My qualifications:
• [Mention your qualifications/experience]

Please let me know if you'd like my assistance with this case. I'm happy to help in any way I can.

Best regards`
        }
    };
    
    // Character counters
    const subjectInput = document.getElementById('subject');
    const subjectCount = document.getElementById('subject-count');
    const messageInput = document.getElementById('message');
    const messageCount = document.getElementById('message-count');
    
    subjectInput.addEventListener('input', function() {
        const count = this.value.length;
        subjectCount.textContent = count;
        subjectCount.parentElement.classList.toggle('warning', count > 90);
    });
    
    messageInput.addEventListener('input', function() {
        const count = this.value.length;
        messageCount.textContent = count;
        messageCount.parentElement.classList.toggle('warning', count > 1900);
    });
    
    // Template buttons
    document.querySelectorAll('.template-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const template = this.dataset.template;
            if (messageTemplates[template]) {
                subjectInput.value = messageTemplates[template].subject;
                messageInput.value = messageTemplates[template].message;
                subjectCount.textContent = subjectInput.value.length;
                messageCount.textContent = messageInput.value.length;
                
                // Smooth scroll to message
                messageInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                messageInput.focus();
            }
        });
    });
    
    // Auto-update subject based on reason
    document.getElementById('contact-reason').addEventListener('change', function() {
        if (!subjectInput.value && this.value) {
            const reasonText = this.options[this.selectedIndex].text.replace(/[🤝🏠🏡🏥ℹ️📦🚗💬]/g, '').trim();
            subjectInput.value = reasonText;
            subjectCount.textContent = subjectInput.value.length;
        }
    });
    
    // Helper function for error messages
    function getErrorMessage(error) {
        if (!error) return 'Unknown error occurred. Please try again.';
        
        const errorText = error.text || error.message || '';
        
        if (errorText.includes('insufficient authentication scopes')) {
            return 'Email service authentication error. Please contact the administrator.';
        } else if (errorText.includes('Invalid API key')) {
            return 'Email service configuration error. Please contact support.';
        } else if (errorText.includes('Template not found')) {
            return 'Email template error. Please contact support.';
        } else if (errorText.includes('Rate limit')) {
            return 'Too many emails sent. Please wait a few minutes and try again.';
        } else if (errorText.includes('Network')) {
            return 'Network error. Please check your internet connection and try again.';
        } else if (errorText) {
            return errorText;
        } else {
            return 'Failed to send message. Please try again or contact support.';
        }
    }
    
    // Form submission
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        
        // Disable button and show loading
        submitBtn.disabled = true;
        btnText.innerHTML = '<span class="spinner"></span> Sending your message...';
        
        // Hide alerts
        successAlert.style.display = 'none';
        errorAlert.style.display = 'none';
        
        // Send email
        emailjs.sendForm(emailConfig.serviceId, emailConfig.templateId, this)
            .then(function(response) {
                console.log('✅ SUCCESS!', response.status, response.text);
                
                // Success message
                document.getElementById('successMessage').innerHTML = 
                    'Your message has been sent to <strong>' + 
                    document.querySelector('input[name="reporter_name"]').value + 
                    '</strong>. They will receive your email and can reply directly to you at <strong>' +
                    document.querySelector('input[name="sender_email"]').value + '</strong>.';
                successAlert.style.display = 'block';
                
                // Reset form
                document.getElementById('contactForm').reset();
                subjectCount.textContent = '0';
                messageCount.textContent = '0';
                
                // Scroll to success
                successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Re-enable button
                submitBtn.disabled = false;
                btnText.innerHTML = '📧 Send Message';
                
            }, function(error) {
                console.error('❌ FAILED', error);
                
                // Error message
                document.getElementById('errorMessage').textContent = getErrorMessage(error);
                errorAlert.style.display = 'block';
                
                // Re-enable button
                submitBtn.disabled = false;
                btnText.innerHTML = '📧 Send Message';
                
                // Scroll to error
                errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
    });
</script>

<?php include 'footer.php'; ?>