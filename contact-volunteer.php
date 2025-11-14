<?php 
include 'header.php'; 
include 'db.php';

require_once 'email-config.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Get volunteer ID from URL
$application_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($application_id <= 0) {
    header('Location: find-volunteers.php');
    exit;
}

// Fetch volunteer details - ONLY APPROVED volunteers
$stmt = $conn->prepare("SELECT va.*, u.fullname, u.email, u.phone, u.address 
                        FROM Volunteer_Application va 
                        INNER JOIN users u ON va.user_id = u.user_id 
                        WHERE va.application_id = ? AND va.status = 'Approved'");
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: find-volunteers.php');
    exit;
}

$volunteer = $result->fetch_assoc();
$stmt->close();

// Get current user's email from users table
$current_user_id = $_SESSION['user_id'];
$user_stmt = $conn->prepare("SELECT email, fullname FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $current_user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$current_user = $user_result->fetch_assoc();
$user_stmt->close();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sender info from current logged-in user (from users table)
    $sender_name = $current_user['fullname'];
    $sender_email = $current_user['email'];
    
    // Receiver info (volunteer's application email)
    $receiver_email = $volunteer['email']; // From Volunteer_Application table
    $receiver_name = $volunteer['fullname'];
    
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $help_type = isset($_POST['help_type']) ? trim($_POST['help_type']) : '';
    
    // Validate required fields
    if (empty($subject) || empty($message)) {
        $error_message = "Please fill in all required fields.";
    } else {
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_ENCRYPTION;
            $mail->Port       = SMTP_PORT;
            
            // Recipients - Send TO volunteer's application email
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($receiver_email, $receiver_name); // Volunteer's email
            $mail->addReplyTo($sender_email, $sender_name); // Current user's email
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'WhiskerLink: ' . $subject;
            
            // Get interest tags
            $interests = array_filter(array_map('trim', explode(',', $volunteer['interested'])));
            $interest_tags = '';
            foreach($interests as $interest) {
                $interest_tags .= '<span style="display: inline-block; padding: 5px 12px; background: #e7f3ff; color: #0066cc; border-radius: 12px; font-size: 12px; margin-right: 8px;">' . htmlspecialchars($interest) . '</span>';
            }
            
            // HTML email body
            $mail->Body = '
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                    .volunteer-info { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #667eea; }
                    .volunteer-info h3 { margin-top: 0; color: #333; }
                    .info-row { padding: 10px 0; border-bottom: 1px solid #eee; }
                    .info-label { font-weight: bold; color: #555; }
                    .message-box { background: white; padding: 25px; margin: 20px 0; border-radius: 8px; border: 1px solid #ddd; }
                    .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
                    .button { display: inline-block; padding: 12px 30px; background: #ff6b6b; color: white; text-decoration: none; border-radius: 5px; margin: 15px 0; }
                    .help-type-badge { display: inline-block; padding: 8px 16px; background: #ffc107; color: #000; border-radius: 20px; font-size: 14px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🤝 New Volunteer Opportunity!</h1>
                        <p>Someone needs your help!</p>
                    </div>
                    
                    <div class="content">
                        <p>Hello ' . htmlspecialchars($receiver_name) . ',</p>
                        <p>Great news! Someone from the WhiskerLink community is interested in connecting with you for volunteer work.</p>
                        
                        ' . (!empty($help_type) ? '<div class="help-type-badge">Type of Help Needed: ' . htmlspecialchars($help_type) . '</div>' : '') . '
                        
                        <div class="volunteer-info">
                            <h3>Your Volunteer Profile</h3>
                            <div class="info-row">
                                <span class="info-label">Interest Areas:</span><br>
                                <div style="margin-top: 10px;">' . $interest_tags . '</div>
                            </div>
                        </div>
                        
                        <h3>Message from: ' . htmlspecialchars($sender_name) . '</h3>
                        <p><strong>Email:</strong> ' . htmlspecialchars($sender_email) . '</p>
                        <p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>
                        
                        <div class="message-box">
                            <h4>Message:</h4>
                            <p>' . nl2br(htmlspecialchars($message)) . '</p>
                        </div>
                        
                        <p style="text-align: center;">
                            <a href="mailto:' . htmlspecialchars($sender_email) . '" class="button">Reply to ' . htmlspecialchars($sender_name) . '</a>
                        </p>
                        
                        <p><strong>How to respond:</strong> Simply reply to this email or click the button above to contact ' . htmlspecialchars($sender_name) . ' directly.</p>
                    </div>
                    
                    <div class="footer">
                        <p>This message was sent through WhiskerLink Volunteer Connect</p>
                        <p>&copy; ' . date('Y') . ' WhiskerLink. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
            ';
            
            // Plain text alternative
            $mail->AltBody = "Hello " . $receiver_name . ",\n\n";
            $mail->AltBody .= "Someone is interested in connecting with you for volunteer work!\n\n";
            if (!empty($help_type)) {
                $mail->AltBody .= "Type of Help Needed: " . $help_type . "\n\n";
            }
            $mail->AltBody .= "Message from: " . $sender_name . " (" . $sender_email . ")\n\n";
            $mail->AltBody .= "Subject: " . $subject . "\n\n";
            $mail->AltBody .= "Message:\n" . $message . "\n\n";
            $mail->AltBody .= "Reply to this email to contact them directly.";
            
            // Send email
            $mail->send();
            $success_message = "Your message has been sent successfully to " . htmlspecialchars($receiver_name) . "! They will receive your email and can reply directly to you.";
            
        } catch (Exception $e) {
            $error_message = "Failed to send message. Error: {$mail->ErrorInfo}";
        }
    }
}

$conn->close();
?>

<style>
    .contact-page {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 60px 0;
        min-height: calc(100vh - 140px);
    }
    
    .contact-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .page-header h1 {
        font-size: 42px;
        color: #333;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }
    
    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .back-button:hover {
        color: #764ba2;
        transform: translateX(-5px);
    }
    
    .volunteer-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 20px;
        margin-bottom: 30px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
    }
    
    .volunteer-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: white;
        color: #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: bold;
        margin: 0 auto 15px;
        border: 3px solid white;
    }
    
    .volunteer-summary h2 {
        margin: 0 0 10px 0;
        font-size: 28px;
    }
    
    .interest-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        margin-top: 15px;
    }
    
    .interest-badge {
        padding: 5px 12px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        font-size: 12px;
    }
    
    .form-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    
    .form-section {
        margin-bottom: 35px;
        padding: 25px;
        background: #f8f9fa;
        border-radius: 12px;
        border-left: 4px solid #667eea;
    }
    
    .form-section h3 {
        margin: 0 0 20px 0;
        color: #333;
        font-size: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 15px;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 14px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 15px;
        font-family: inherit;
        transition: all 0.3s;
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
        min-height: 150px;
        line-height: 1.6;
    }
    
    .form-group small {
        display: block;
        margin-top: 6px;
        color: #888;
        font-size: 13px;
    }
    
    .readonly-field {
        background: #f5f5f5;
        cursor: not-allowed;
    }
    
    .required {
        color: #ff6b6b;
        margin-left: 3px;
    }
    
    .char-count {
        text-align: right;
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }
    
    .info-box {
        background: #e7f3ff;
        padding: 16px;
        border-radius: 10px;
        margin-bottom: 20px;
        border-left: 4px solid #2196F3;
        font-size: 14px;
        color: #0d47a1;
    }
    
    .submit-section {
        margin-top: 35px;
        text-align: center;
    }
    
    .submit-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 16px 50px;
        font-size: 18px;
        font-weight: 600;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
    }
    
    .submit-btn:active {
        transform: translateY(-1px);
    }
    
    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 15px;
        animation: slideDown 0.3s ease;
    }
    
    .alert-success {
        background: #d4edda;
        border: 2px solid #c3e6cb;
        color: #155724;
    }
    
    .alert-error {
        background: #f8d7da;
        border: 2px solid #f5c6cb;
        color: #721c24;
    }
    
    .alert a {
        color: inherit;
        font-weight: bold;
        text-decoration: underline;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 32px;
        }
        
        .form-card {
            padding: 25px;
        }
        
        .form-section {
            padding: 20px;
        }
    }
</style>

<section class="contact-page">
    <div class="contact-container">
        <a href="volunteer-detail.php?id=<?php echo $application_id; ?>" class="back-button">
            ← Back to Volunteer Profile
        </a>
        
        <div class="page-header">
            <h1><span>📧</span> Contact Volunteer</h1>
            <p style="color: #666;">Send a message to connect with this volunteer</p>
        </div>
        
        <div class="form-card">
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <span style="font-size: 24px;">✓</span>
                    <div>
                        <div><?php echo htmlspecialchars($success_message); ?></div>
                        <div style="margin-top: 10px;">
                            <a href="volunteer-detail.php?id=<?php echo $application_id; ?>">View Profile</a> | 
                            <a href="find-volunteers.php">Find More Volunteers</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <span style="font-size: 24px;">✗</span>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Volunteer Summary -->
            <div class="volunteer-summary">
                <div class="volunteer-avatar">
                    <?php echo strtoupper(substr($volunteer['fullname'], 0, 1)); ?>
                </div>
                <h2><?php echo htmlspecialchars($volunteer['fullname']); ?></h2>
                <p style="margin: 5px 0;">✓ Verified Volunteer</p>
                <div class="interest-badges">
                    <?php 
                    $interests = array_filter(array_map('trim', explode(',', $volunteer['interested'])));
                    foreach($interests as $interest): 
                    ?>
                        <span class="interest-badge"><?php echo htmlspecialchars($interest); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="info-box">
                <p><strong>ℹ️ Note:</strong> Your message will be sent to <strong><?php echo htmlspecialchars($volunteer['email']); ?></strong>. They will be able to reply directly to your email address <strong><?php echo htmlspecialchars($current_user['email']); ?></strong>.</p>
            </div>
            
            <!-- Contact Form -->
            <form action="contact-volunteer.php?id=<?php echo $application_id; ?>" method="post" id="contactForm">
                
                <!-- Sender Information Section -->
                <div class="form-section">
                    <h3>👤 Your Information</h3>
                    
                    <div class="form-group">
                        <label for="sender-name">Your Name</label>
                        <input type="text" id="sender-name" name="sender-name" 
                               value="<?php echo htmlspecialchars($current_user['fullname']); ?>" 
                               readonly class="readonly-field">
                    </div>
                    
                    <div class="form-group">
                        <label for="sender-email">Your Email</label>
                        <input type="email" id="sender-email" name="sender-email" 
                               value="<?php echo htmlspecialchars($current_user['email']); ?>" 
                               readonly class="readonly-field">
                        <small>The volunteer will reply to this email address</small>
                    </div>
                </div>
                
                <!-- Message Details Section -->
                <div class="form-section">
                    <h3>💬 Message Details</h3>
                    
                    <div class="form-group">
                        <label for="help_type">Type of Help Needed</label>
                        <select id="help_type" name="help_type">
                            <option value="">Select help type (optional)</option>
                            <option value="Shelter Help">🏠 Shelter Help</option>
                            <option value="Animal Care">🐾 Animal Care</option>
                            <option value="Health">💊 Animal Health</option>
                            <option value="Transportation">🚗 Transportation</option>
                            <option value="Fundraising & Donations">💰 Fundraising & Donations</option>
                            <option value="Emergency Rescue">🚨 Emergency Rescue</option>
                            <option value="Fostering">🏡 Fostering</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject <span class="required">*</span></label>
                        <input type="text" id="subject" name="subject" 
                               placeholder="e.g., Need help with animal rescue" 
                               maxlength="100" required>
                        <div class="char-count">
                            <span id="subject-count">0</span>/100 characters
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Message <span class="required">*</span></label>
                        <textarea id="message" name="message" 
                                  placeholder="Describe what kind of help you need, provide details about the situation, location, timing, etc." 
                                  maxlength="1000" required></textarea>
                        <div class="char-count">
                            <span id="message-count">0</span>/1000 characters
                        </div>
                    </div>
                </div>
                
                <!-- Submit Section -->
                <div class="submit-section">
                    <button type="submit" class="submit-btn">
                        📧 Send Message to Volunteer
                    </button>
                    <p style="margin-top: 15px; color: #888; font-size: 14px;">
                        Your message will be delivered to the volunteer's email
                    </p>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    // Character counter for subject
    const subjectInput = document.getElementById('subject');
    const subjectCount = document.getElementById('subject-count');
    
    subjectInput.addEventListener('input', function() {
        subjectCount.textContent = this.value.length;
    });
    
    // Character counter for message
    const messageInput = document.getElementById('message');
    const messageCount = document.getElementById('message-count');
    
    messageInput.addEventListener('input', function() {
        messageCount.textContent = this.value.length;
    });
</script>

<?php include 'footer.php'; ?>