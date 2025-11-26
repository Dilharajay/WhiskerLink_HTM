<?php 
include 'header.php'; 
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$admin_check = $conn->query("SELECT * FROM admin WHERE user_id = $user_id");

if ($admin_check->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$success_message = '';
$error_message = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($application_id > 0 && in_array($action, ['approve', 'reject'])) {
        $new_status = ($action === 'approve') ? 'Approved' : 'Rejected';
        
        $stmt = $conn->prepare("UPDATE Volunteer_Application SET status = ? WHERE application_id = ?");
        $stmt->bind_param("si", $new_status, $application_id);
        
        if ($stmt->execute()) {
            $success_message = "Application has been " . strtolower($new_status) . " successfully!";
        } else {
            $error_message = "Error updating application: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all volunteer applications
$result = $conn->query("SELECT va.*, u.fullname, u.email, u.phone, u.address 
                        FROM Volunteer_Application va 
                        INNER JOIN users u ON va.user_id = u.user_id 
                        ORDER BY va.applied_at DESC");
?>

<style>
    .admin-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 20px;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        text-align: center;
    }
    .stat-number {
        font-size: 36px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .stat-label {
        color: #666;
        font-size: 14px;
    }
    .pending { color: #ffc107; }
    .approved { color: #28a745; }
    .rejected { color: #dc3545; }
    
    .applications-table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    th {
        background: #f8f9fa;
        font-weight: bold;
        color: #333;
    }
    tr:hover {
        background: #f8f9fa;
    }
    .status-badge {
        padding: 5px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
    }
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    .status-approved {
        background: #d4edda;
        color: #155724;
    }
    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    .btn-approve {
        background: #28a745;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    .btn-approve:hover {
        background: #218838;
    }
    .btn-reject {
        background: #dc3545;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    .btn-reject:hover {
        background: #c82333;
    }
    .btn-view {
        background: #007bff;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        text-decoration: none;
        display: inline-block;
    }
    .btn-view:hover {
        background: #0056b3;
    }
    .interest-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }
    .interest-tag {
        padding: 3px 8px;
        background: #e7f3ff;
        color: #0066cc;
        border-radius: 8px;
        font-size: 11px;
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
        background-color: #fefefe;
        margin: 3% auto;
        padding: 0;
        border-radius: 8px;
        width: 90%;
        max-width: 800px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    .modal-header {
        padding: 20px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px 8px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header h2 {
        margin: 0;
        font-size: 24px;
    }
    .close {
        color: white;
        font-size: 32px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
        transition: transform 0.2s;
    }
    .close:hover {
        transform: scale(1.2);
    }
    .modal-body {
        padding: 30px;
        max-height: 500px;
        overflow-y: auto;
    }
    .detail-section {
        margin-bottom: 25px;
    }
    .detail-section h3 {
        color: #667eea;
        margin-bottom: 15px;
        font-size: 18px;
        border-bottom: 2px solid #e7f3ff;
        padding-bottom: 8px;
    }
    .detail-row {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 15px;
        margin-bottom: 12px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 6px;
    }
    .detail-label {
        font-weight: bold;
        color: #555;
    }
    .detail-value {
        color: #333;
    }
    .modal-footer {
        padding: 20px 30px;
        background: #f8f9fa;
        border-radius: 0 0 8px 8px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-top: 1px solid #dee2e6;
    }
    .modal-footer button,
    .modal-footer form {
        margin: 0;
    }
    .btn-close-modal {
        background: #6c757d;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }
    .btn-close-modal:hover {
        background: #5a6268;
    }
    .modal-footer .btn-approve,
    .modal-footer .btn-reject {
        padding: 10px 20px;
        font-size: 14px;
    }
</style>

<section id="admin-volunteers" style="padding: 2rem 0;">
    <div class="admin-container">
        <h1 style="text-align: center; margin-bottom: 10px;">Volunteer Management</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">View, approve or reject volunteer applications</p>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success" style="padding: 1rem; margin-bottom: 1rem; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error" style="padding: 1rem; margin-bottom: 1rem; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <?php
            $stats = $conn->query("SELECT 
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
                COUNT(*) as total
                FROM Volunteer_Application")->fetch_assoc();
            ?>
            <div class="stat-card">
                <div class="stat-number pending"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number approved"><?php echo $stats['approved']; ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number rejected"><?php echo $stats['rejected']; ?></div>
                <div class="stat-label">Rejected</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #667eea;"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
        </div>
        
        <!-- Applications Table -->
        <div class="applications-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Interests</th>
                        <th>Status</th>
                        <th>Applied Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($app = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $app['application_id']; ?></td>
                                <td><?php echo htmlspecialchars($app['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($app['email']); ?></td>
                                <td>
                                    <div class="interest-tags">
                                        <?php 
                                        $interests = array_filter(array_map('trim', explode(',', $app['interested'])));
                                        foreach($interests as $interest): 
                                        ?>
                                            <span class="interest-tag"><?php echo htmlspecialchars($interest); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                        <?php echo htmlspecialchars($app['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view" onclick="viewApplication(<?php echo htmlspecialchars(json_encode($app)); ?>)">
                                            👁 View Details
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                No volunteer applications found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal for viewing application details -->
<div id="applicationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 style="color: whitesmoke;">Volunteer Application Details</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer" id="modalFooter">
            <!-- Action buttons will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
function viewApplication(app) {
    const modal = document.getElementById('applicationModal');
    const modalBody = document.getElementById('modalBody');
    const modalFooter = document.getElementById('modalFooter');
    
    // Parse interests
    const interests = app.interested ? app.interested.split(',').map(i => i.trim()).filter(i => i) : [];
    
    // Build modal content
    modalBody.innerHTML = `
        <div class="detail-section">
            <h3>Personal Information</h3>
            <div class="detail-row">
                <div class="detail-label">Full Name:</div>
                <div class="detail-value">${escapeHtml(app.fullname)}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Email:</div>
                <div class="detail-value">${escapeHtml(app.email)}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Phone:</div>
                <div class="detail-value">${escapeHtml(app.phone || 'N/A')}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Address:</div>
                <div class="detail-value">${escapeHtml(app.address || 'N/A')}</div>
            </div>
        </div>
        
        <div class="detail-section">
            <h3>Application Details</h3>
            <div class="detail-row">
                <div class="detail-label">Application ID:</div>
                <div class="detail-value">#${app.application_id}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Status:</div>
                <div class="detail-value">
                    <span class="status-badge status-${app.status.toLowerCase()}">
                        ${escapeHtml(app.status)}
                    </span>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Applied Date:</div>
                <div class="detail-value">${new Date(app.applied_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Areas of Interest:</div>
                <div class="detail-value">
                    <div class="interest-tags">
                        ${interests.map(interest => `<span class="interest-tag">${escapeHtml(interest)}</span>`).join('')}
                    </div>
                </div>
            </div>
            ${app.experience ? `
            <div class="detail-row">
                <div class="detail-label">Experience:</div>
                <div class="detail-value">${escapeHtml(app.experience)}</div>
            </div>
            ` : ''}
            ${app.availability ? `
            <div class="detail-row">
                <div class="detail-label">Availability:</div>
                <div class="detail-value">${escapeHtml(app.availability)}</div>
            </div>
            ` : ''}
            ${app.message ? `
            <div class="detail-row">
                <div class="detail-label">Message:</div>
                <div class="detail-value">${escapeHtml(app.message)}</div>
            </div>
            ` : ''}
        </div>
    `;
    
    // Build footer with action buttons
    let footerHTML = '<button class="btn-close-modal" onclick="closeModal()">Close</button>';
    
    if (app.status !== 'Approved') {
        footerHTML += `
            <form method="POST" style="display: inline;">
                <input type="hidden" name="application_id" value="${app.application_id}">
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="btn-approve" onclick="return confirm('Approve this application?')">
                     Approve
                </button>
            </form>
        `;
    }
    
    if (app.status !== 'Rejected') {
        footerHTML += `
            <form method="POST" style="display: inline;">
                <input type="hidden" name="application_id" value="${app.application_id}">
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="btn-reject" onclick="return confirm('Reject this application?')">
                     Reject
                </button>
            </form>
        `;
    }
    
    modalFooter.innerHTML = footerHTML;
    
    // Show modal
    modal.style.display = 'block';
}

function closeModal() {
    document.getElementById('applicationModal').style.display = 'none';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('applicationModal');
    if (event.target === modal) {
        closeModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
</script>

<?php 
$conn->close();
include 'footer.php'; 
?>