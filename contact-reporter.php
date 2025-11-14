<?php 
include 'header.php'; 
include 'db.php';

// Import PHPMailer classes
// use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader or include PHPMailer files manually
// require 'vendor/autoload.php'; 
// If you're not using Composer, use these instead:
// require 'PHPMailer/src/Exception.php';
// require 'PHPMailer/src/PHPMailer.php';
// require 'PHPMailer/src/SMTP.php';

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
$stmt = $conn->prepare("SELECT rr.*, u.fullname as reporter_name, u.email as reporter_email 
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

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_name = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Anonymous';
    $sender_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Validate required fields
    if (empty($subject) || empty($message)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Create PHPMailer instance
        // $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';  // Set your SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your-email@gmail.com';  // SMTP username
            $mail->Password   = 'your-app-password';     // SMTP password (use App Password for Gmail)
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Optional: Disable SSL verification for local testing (NOT recommended for production)
            // $mail->SMTPOptions = array(
            //     'ssl' => array(
            //         'verify_peer' => false,
            //         'verify_peer_name' => false,
            //         'allow_self_signed' => true
            //     )
            // );
            
            // Recipients
            $mail->setFrom('your-email@gmail.com', 'WhiskerLink Platform');
            $mail->addAddress($report['reporter_email'], $report['reporter_name']);
            $mail->addReplyTo($sender_email, $sender_name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'WhiskerLink: ' . $subject;
            
            // HTML email body
            $mail->Body = '
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                    .report-details { background: white; padding: 15px; margin: 20px 0; border-left: 4px solid #ff6b6b; border-radius: 4px; }
                    .report-details h3 { margin-top: 0; color: #333; }
                    .detail-row { padding: 8px 0; border-bottom: 1px solid #eee; }
                    .detail-label { font-weight: bold; color: #555; display: inline-block; width: 120px; }
                    .message-box { background: white; padding: 20px; margin: 20px 0; border-radius: 4px; border: 1px solid #ddd; }
                    .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
                    .button { display: inline-block; padding: 12px 24px; background: #ff6b6b; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🐾 WhiskerLink Message</h1>
                        <p>Someone is interested in your rescue report!</p>
                    </div>
                    
                    <div class="content">
                        <p>Hello ' . htmlspecialchars($report['reporter_name']) . ',</p>
                        <p>You have received a message regarding your rescue report.</p>
                        
                        <div class="report-details">
                            <h3>Report Details</h3>
                            <div class="detail-row">
                                <span class="detail-label">Animal:</span>
                                <span>' . htmlspecialchars($report['animal_species']) . '</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Location:</span>
                                <span>' . htmlspecialchars($report['location_found']) . '</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Report Type:</span>
                                <span>' . htmlspecialchars($report['report_type']) . '</span>
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
                            <a href="http://' . $_SERVER['HTTP_HOST'] . '/report-detail.php?id=' . $report_id . '" class="button">View Full Report</a>
                        </p>
                        
                        <p><strong>How to respond:</strong> Simply reply to this email to contact ' . htmlspecialchars($sender_name) . ' directly.</p>
                    </div>
                    
                    <div class="footer">
                        <p>This message was sent through WhiskerLink Animal Rescue Platform</p>
                        <p>&copy; ' . date('Y') . ' WhiskerLink. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
            ';
            
            // Plain text alternative
            $mail->AltBody = "You have received a message regarding your rescue report.\n\n";
            $mail->AltBody .= "Report Details:\n";
            $mail->AltBody .= "Animal: " . $report['animal_species'] . "\n";
            $mail->AltBody .= "Location: " . $report['location_found'] . "\n";
            $mail->AltBody .= "Report Type: " . $report['report_type'] . "\n\n";
            $mail->AltBody .= "Message from: " . $sender_name . " (" . $sender_email . ")\n\n";
            $mail->AltBody .= "Subject: " . $subject . "\n\n";
            $mail->AltBody .= "Message:\n" . $message . "\n\n";
            $mail->AltBody .= "To view the full report, visit: http://" . $_SERVER['HTTP_HOST'] . "/report-detail.php?id=" . $report_id;
            
            // Send email
            $mail->send();
            $success_message = "Your message has been sent successfully to the reporter! They will receive your email and can reply directly to you.";
            
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
    .report-summary {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
        border-left: 4px solid #ff6b6b;
    }
    .report-summary h3 {
        margin-top: 0;
        color: #333;
    }
    .report-summary-grid {
        display: grid;
        grid-template-columns: 100px auto;
        gap: 10px;
        margin-top: 15px;
    }
    .report-summary-label {
        font-weight: bold;
        color: #555;
    }
    .report-summary-value {
        color: #333;
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
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        font-family: inherit;
    }
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #ff6b6b;
    }
    .form-group textarea {
        resize: vertical;
        min-height: 150px;
    }
    .back-button {
        display: inline-block;
        margin-bottom: 20px;
        color: #ff6b6b;
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

<section id="contact-reporter" style="padding: 2rem 0;">
    <div class="contact-container">
        <a href="report-detail.php?id=<?php echo $report_id; ?>" class="back-button">← Back to Report</a>
        
        <h2 style="text-align: center; margin-bottom: 10px;">Contact Reporter</h2>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">Send a message to the person who reported this case</p>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success" style="padding: 1rem; margin-bottom: 1rem; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
                <?php echo htmlspecialchars($success_message); ?>
                <p style="margin-top: 10px; margin-bottom: 0;">
                    <a href="report-detail.php?id=<?php echo $report_id; ?>">View Report</a> | 
                    <a href="rescue-reports.php">Browse More Reports</a>
                </p>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error" style="padding: 1rem; margin-bottom: 1rem; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Report Summary -->
        <div class="report-summary">
            <h3>About This Report</h3>
            <div class="report-summary-grid">
                <span class="report-summary-label">Animal:</span>
                <span class="report-summary-value"><?php echo htmlspecialchars($report['animal_species']); ?></span>
                
                <span class="report-summary-label">Location:</span>
                <span class="report-summary-value"><?php echo htmlspecialchars($report['location_found']); ?></span>
                
                <span class="report-summary-label">Type:</span>
                <span class="report-summary-value"><?php echo htmlspecialchars($report['report_type']); ?></span>
                
                <span class="report-summary-label">Reporter:</span>
                <span class="report-summary-value"><?php echo htmlspecialchars($report['reporter_name']); ?></span>
            </div>
        </div>
        
        <div class="info-box">
            <p><strong>ℹ️ Note:</strong> Your message will be sent to <strong><?php echo htmlspecialchars($report['reporter_email']); ?></strong>. They will be able to reply directly to your email address.</p>
        </div>
        
        <!-- Contact Form -->
        <div class="form-container">
            <form action="contact-reporter.php?report_id=<?php echo $report_id; ?>" method="post" id="contactForm">
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
                    <small style="color: #666;">The reporter will reply to this email address</small>
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject <span style="color: red;">*</span></label>
                    <input type="text" id="subject" name="subject" 
                           placeholder="e.g., Interested in helping with this case" 
                           maxlength="100" required>
                    <div class="char-count">
                        <span id="subject-count">0</span>/100 characters
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="message">Your Message <span style="color: red;">*</span></label>
                    <textarea id="message" name="message" 
                              placeholder="Write your message here... Include any questions or offers to help." 
                              maxlength="1000" required></textarea>
                    <div class="char-count">
                        <span id="message-count">0</span>/1000 characters
                    </div>
                </div>
                
                <button type="submit" class="btn btn-accent" style="width: 100%;">
                    📧 Send Message
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