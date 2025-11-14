<?php 
include 'header.php'; 
include 'db.php';

// Import PHPMailer classes
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
// require 'vendor/autoload.php';
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

// Fetch volunteer details
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

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_name = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Anonymous';
    $sender_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $help_type = isset($_POST['help_type']) ? trim($_POST['help_type']) : '';
    
    // Validate required fields
    if (empty($subject) || empty($message)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Create PHPMailer instance
        // $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_ENCRYPTION;
            $mail->Port       = SMTP_PORT;
            
            // Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($volunteer['email'], $volunteer['fullname']);
            $mail->addReplyTo($sender_email, $sender_name);
            
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
                        <p>Hello ' . htmlspecialchars($volunteer['fullname']) . ',</p>
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
            $mail->AltBody = "Hello " . $volunteer['fullname'] . ",\n\n";
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
            $success_message = "Your message has been sent successfully to " . htmlspecialchars($volunteer['fullname']) . "! They will receive your email and can reply directly to you.";
            
        } catch (Exception $e) {
            $error_message = "Failed to send message. Error: {$mail->ErrorInfo}";
        }
    }
}

$conn->close();
?>

<style>
    .contact-container {
        max-width: 700px;
        margin: 2rem auto;
        padding: 0 20px;
    }
    .volunteer-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 8px;
        margin-bottom: 30px;
        text-align: center;
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
    .form-container {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #333;
    }
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        font-family: inherit;
    }
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
    }
    .form-group textarea {
        resize: vertical;
        min-height: 150px;
    }
    .back-button {
        display: inline-block;
        margin-bottom: 20px;
        color: #667eea;
        text-decoration: none;
        font-weight: bold;
    }
    .back-button:hover {
        text-decoration: underline;
    }
    .char-count {
        text-align: right;
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }
    .info-box {
        background: #e7f3ff;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        border-left: 4px solid #2196F3;
    }
    .info-box p {
        margin: 0;
        color: #0d47a1;
    }
</style>

<section id="contact-volunteer" style="padding: 2rem 0;">
    <div class="contact-container">
        <a href="volunteer-detail.php?id=<?php echo $application_id; ?>" class="back-button">← Back to Volunteer Profile</a>
        
        <h1 style="text-align: center; margin-bottom: 30px;">Contact Volunteer</h1>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success" style="padding: 1rem; margin-bottom: 1rem; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
                <?php echo htmlspecialchars($success_message); ?>
                <p style="margin-top: 10px; margin-bottom: 0;">
                    <a href="volunteer-detail.php?id=<?php echo $application_id; ?>">View Profile</a> | 
                    <a href="find-volunteers.php">Find More Volunteers</a>
                </p>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error" style="padding: 1rem; margin-bottom: 1rem; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
                <?php echo htmlspecialchars($error_message); ?>
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
            <p><strong>ℹ️ Note:</strong> Your message will be sent to <strong><?php echo htmlspecialchars($volunteer['email']); ?></strong>. They will be able to reply directly to your email address.</p>
        </div>
        
        <!-- Contact Form -->
        <div class="form-container">
            <form action="contact-volunteer.php?id=<?php echo $application_id; ?>" method="post" id="contactForm">
                <div class="form-group">
                    <label for="sender-name">Your Name</label>
                    <input type="text" id="sender-name" name="sender-name" 
                           value="<?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : ''; ?>" 
                           readonly style="background-color: #f5f5f5;">
                </div>
                
                <div class="form-group">
                    <label for="sender-email">Your Email</label>
                    <input type="email" id="sender-email" name="sender-email" 
                           value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" 
                           readonly style="background-color: #f5f5f5;">
                    <small style="color: #666;">The volunteer will reply to this email address</small>
                </div>
                
                <div class="form-group">
                    <label for="help_type">Type of Help Needed</label>
                    <select id="help_type" name="help_type">
                        <option value="">Select help type (optional)</option>
                        <option value="Shelter Help">Shelter Help</option>
                        <option value="Animal Care">Animal Care</option>
                        <option value="Health">Animal Health</option>
                        <option value="Transportation">Transportation</option>
                        <option value="Fundraising & Donations">Fundraising & Donations</option>
                        <option value="Emergency Rescue">Emergency Rescue</option>
                        <option value="Fostering">Fostering</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject <span style="color: red;">*</span></label>
                    <input type="text" id="subject" name="subject" 
                           placeholder="e.g., Need help with animal rescue" 
                           maxlength="100" required>
                    <div class="char-count">
                        <span id="subject-count">0</span>/100 characters
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="message">Your Message <span style="color: red;">*</span></label>
                    <textarea id="message" name="message" 
                              placeholder="Describe what kind of help you need, provide details about the situation, location, timing, etc." 
                              maxlength="1000" required></textarea>
                    <div class="char-count">
                        <span id="message-count">0</span>/1000 characters
                    </div>
                </div>
                
                <button type="submit" class="btn btn-accent" style="width: 100%;">
                    📧 Send Message to Volunteer
                </button>
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