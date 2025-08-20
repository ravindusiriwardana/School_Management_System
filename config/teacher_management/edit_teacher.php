<?php
session_start();
include '../dbConnection.php';

// Make sure only teachers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch teacher data
$stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "Teacher not found.";
    exit();
}

// Handle form submission
if (isset($_POST['update'])) {
    $name = trim($_POST['name']);
    $age = $_POST['age'];
    $subject = trim($_POST['subject']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    // Server-side validation
    $errors = [];
    
    if (strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters long.";
    }
    
    if ($age < 21 || $age > 100) {
        $errors[] = "Please enter a valid age (21-100).";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    // Check if email already exists (excluding current user)
    $emailCheck = $conn->prepare("SELECT id FROM teachers WHERE email = ? AND id != ?");
    $emailCheck->bind_param("si", $email, $id);
    $emailCheck->execute();
    if ($emailCheck->get_result()->num_rows > 0) {
        $errors[] = "This email is already registered.";
    }
    
    if (empty($errors)) {
        // Handle profile picture upload
        $profile_picture = $row['profile_picture'] ?? ''; // Keep existing pic by default
        
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_picture']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                if ($_FILES['profile_picture']['size'] <= 5242880) { // 5MB limit
                    $new_filename = 'teacher_' . $id . '_' . time() . '.' . $ext;
                    $upload_path = '../uploads/' . $new_filename;
                    
                    // Create directory if it doesn't exist
                    if (!file_exists('../uploads/')) {
                        mkdir('../uploads/', 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                        // Delete old profile pic if exists
                        if ($row['profile_picture'] && file_exists('../uploads/' . $row['profile_picture'])) {
                            unlink('../uploads/' . $row['profile_picture']);
                        }
                        $profile_picture = $new_filename;
                    }
                } else {
                    $errors[] = "Profile picture must be less than 5MB.";
                }
            } else {
                $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
        }
        
        if (empty($errors)) {
            // Check if additional columns exist in the database
            $checkColumns = $conn->query("SHOW COLUMNS FROM teachers");
            $columns = [];
            while($col = $checkColumns->fetch_assoc()) {
                $columns[] = $col['Field'];
            }
            
            // Build dynamic update query based on existing columns
            $updateFields = ['name=?', 'age=?', 'subject=?', 'email=?'];
            $updateTypes = 'siss';
            $updateValues = [$name, $age, $subject, $email];
            
            if (in_array('profile_picture', $columns)) {
                $updateFields[] = 'profile_picture=?';
                $updateTypes .= 's';
                $updateValues[] = $profile_picture;
            }
            
            if (in_array('phone', $columns)) {
                $updateFields[] = 'phone=?';
                $updateTypes .= 's';
                $updateValues[] = $phone;
            }
            
            if (in_array('address', $columns)) {
                $updateFields[] = 'address=?';
                $updateTypes .= 's';
                $updateValues[] = $address;
            }
            
            if (in_array('bio', $columns)) {
                $updateFields[] = 'bio=?';
                $updateTypes .= 's';
                $updateValues[] = $bio;
            }
            
            $updateValues[] = $id;
            $updateTypes .= 'i';
            
            $updateQuery = "UPDATE teachers SET " . implode(', ', $updateFields) . " WHERE id=?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param($updateTypes, ...$updateValues);
            
            if ($updateStmt->execute()) {
                $_SESSION['user_name'] = $name; // Update session name
                $success_message = "Profile updated successfully!";
                
                // Refresh data
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
            } else {
                $error_message = "Error updating profile. Please try again.";
            }
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Teacher Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .edit-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            max-width: 700px;
            width: 100%;
            position: relative;
            animation: fadeInUp 0.6s ease-out;
        }

        .edit-header {
            background: #2563eb;
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
        }

        .edit-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            position: relative;
            z-index: 1;
            border: 4px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-avatar:hover { 
            transform: scale(1.05); 
            border-color: rgba(255, 255, 255, 0.6); 
        }
        
        .profile-avatar img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            border-radius: 50%; 
        }
        
        .profile-avatar i { 
            font-size: 36px; 
            color: white; 
        }

        .avatar-upload {
            position: absolute;
            bottom: 0; 
            right: 0;
            width: 32px; 
            height: 32px;
            background: #16a34a;
            border-radius: 50%;
            display: flex; 
            align-items: center; 
            justify-content: center;
            cursor: pointer;
            border: 3px solid white;
            transition: all 0.3s ease;
        }
        
        .avatar-upload:hover { 
            background: #15803d; 
            transform: scale(1.1); 
        }
        
        .avatar-upload i { 
            font-size: 14px; 
            color: white; 
        }
        
        .avatar-input { 
            display: none; 
        }

        .edit-title { 
            color: white; 
            font-size: 28px; 
            font-weight: 600; 
            margin-bottom: 8px; 
            position: relative; 
            z-index: 1; 
        }
        
        .edit-subtitle { 
            color: rgba(255, 255, 255, 0.8); 
            font-size: 16px; 
            position: relative; 
            z-index: 1; 
        }

        .edit-body { 
            padding: 40px 30px; 
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            animation: slideDown 0.3s ease;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .alert i {
            margin-right: 10px;
            font-size: 18px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .form-group {
            position: relative;
            animation: slideInLeft 0.5s ease-out both;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.15s; }
        .form-group:nth-child(3) { animation-delay: 0.2s; }
        .form-group:nth-child(4) { animation-delay: 0.25s; }
        .form-group:nth-child(5) { animation-delay: 0.3s; }
        .form-group:nth-child(6) { animation-delay: 0.35s; }

        .form-label {
            display: flex;
            align-items: center;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-label i {
            margin-right: 8px;
            width: 16px;
            color: #2563eb;
        }

        .form-label .required {
            color: #dc2626;
            margin-left: 4px;
        }

        .form-input {
            width: 100%;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 18px;
            font-size: 16px;
            color: #1e293b;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .form-input:focus {
            outline: none;
            border-color: #2563eb;
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.15);
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        textarea.form-input {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }

        .char-counter {
            text-align: right;
            font-size: 13px;
            color: #6b7280;
            margin-top: 5px;
        }

        .help-text {
            font-size: 13px;
            color: #6b7280;
            margin-top: 5px;
            display: flex;
            align-items: center;
        }

        .help-text i {
            margin-right: 5px;
            font-size: 12px;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 20px;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .btn i {
            margin-right: 8px;
            font-size: 16px;
        }

        .btn-primary {
            background: #16a34a;
            color: white;
            box-shadow: 0 4px 15px rgba(22, 163, 74, 0.3);
        }

        .btn-primary:hover {
            background: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(22, 163, 74, 0.4);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
            box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.4);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        @media (max-width: 480px) {
            .edit-container { margin: 10px; }
            .edit-header { padding: 30px 20px 20px; }
            .edit-body { padding: 30px 20px; }
            .action-buttons { grid-template-columns: 1fr; }
            .edit-title { font-size: 24px; }
            .form-grid { grid-template-columns: 1fr; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <div class="edit-header">
            <div class="profile-avatar" onclick="document.getElementById('profilePicInput').click();">
                <?php if (!empty($row['profile_picture'])): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile Picture" id="profilePreview">
                <?php else: ?>
                    <i class="fas fa-chalkboard-teacher"></i>
                <?php endif; ?>
                <div class="avatar-upload">
                    <i class="fas fa-camera"></i>
                </div>
            </div>
            <h1 class="edit-title">Edit Profile</h1>
            <p class="edit-subtitle">Update Your Information</p>
        </div>

        <div class="edit-body">
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                <input type="file" id="profilePicInput" name="profile_picture" class="avatar-input" accept="image/*" onchange="previewImage(event)">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="name">
                            <i class="fas fa-user"></i> Full Name <span class="required">*</span>
                        </label>
                        <input type="text" id="name" name="name" class="form-input" 
                               value="<?php echo htmlspecialchars($row['name']); ?>" 
                               required minlength="2" maxlength="100">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">
                            <i class="fas fa-envelope"></i> Email Address <span class="required">*</span>
                        </label>
                        <input type="email" id="email" name="email" class="form-input" 
                               value="<?php echo htmlspecialchars($row['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="age">
                            <i class="fas fa-calendar-alt"></i> Age <span class="required">*</span>
                        </label>
                        <input type="number" id="age" name="age" class="form-input" 
                               value="<?php echo $row['age']; ?>" required min="21" max="100">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="subject">
                            <i class="fas fa-book-open"></i> Teaching Subject <span class="required">*</span>
                        </label>
                        <input type="text" id="subject" name="subject" class="form-input" 
                               value="<?php echo htmlspecialchars($row['subject']); ?>" 
                               required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">
                            <i class="fas fa-phone"></i> Phone Number
                        </label>
                        <input type="tel" id="phone" name="phone" class="form-input" 
                               value="<?php echo htmlspecialchars($row['phone'] ?? ''); ?>" 
                               pattern="[0-9]{10,15}" placeholder="10-15 digits">
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i> Optional: Enter 10-15 digits
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="address">
                            <i class="fas fa-map-marker-alt"></i> Address
                        </label>
                        <input type="text" id="address" name="address" class="form-input" 
                               value="<?php echo htmlspecialchars($row['address'] ?? ''); ?>" 
                               maxlength="255">
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i> Optional: Your current address
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="bio">
                            <i class="fas fa-align-left"></i> Professional Bio
                        </label>
                        <textarea id="bio" name="bio" class="form-input" maxlength="500" 
                                  onkeyup="updateCharCount()"><?php echo htmlspecialchars($row['bio'] ?? ''); ?></textarea>
                        <div class="char-counter">
                            <span id="charCount">0</span>/500 characters
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="submit" name="update" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="../teacher_management/teachers_profile.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Back to Profile
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialize character counter
        document.addEventListener('DOMContentLoaded', function() {
            updateCharCount();
        });

        function updateCharCount() {
            const bio = document.getElementById('bio');
            const charCount = document.getElementById('charCount');
            charCount.textContent = bio.value.length;
            
            // Change color when approaching limit
            if (bio.value.length > 450) {
                charCount.style.color = '#dc2626';
            } else {
                charCount.style.color = '#6b7280';
            }
        }

        function previewImage(event) {
            const file = event.target.files[0];
            const avatarDiv = document.querySelector('.profile-avatar');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarDiv.innerHTML = `
                        <img src="${e.target.result}" alt="Profile Picture" id="profilePreview">
                        <div class="avatar-upload">
                            <i class="fas fa-camera"></i>
                        </div>
                    `;
                }
                reader.readAsDataURL(file);
            }
        }

        function validateForm() {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const age = parseInt(document.getElementById('age').value);
            const phone = document.getElementById('phone').value.trim();
            
            // Name validation
            if (name.length < 2) {
                showMessage('Name must be at least 2 characters long.', 'error');
                return false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showMessage('Please enter a valid email address.', 'error');
                return false;
            }
            
            // Age validation
            if (age < 21 || age > 100) {
                showMessage('Please enter a valid age between 21 and 100.', 'error');
                return false;
            }
            
            // Phone validation (if provided)
            if (phone && !/^\d{10,15}$/.test(phone)) {
                showMessage('Phone number must be 10-15 digits.', 'error');
                return false;
            }
            
            // File size validation
            const fileInput = document.getElementById('profilePicInput');
            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size;
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (fileSize > maxSize) {
                    showMessage('Profile picture must be less than 5MB.', 'error');
                    return false;
                }
                
                // Check file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(fileInput.files[0].type)) {
                    showMessage('Only JPG, JPEG, PNG & GIF files are allowed.', 'error');
                    return false;
                }
            }
            
            return true;
        }

        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 600;
                z-index: 1000;
                animation: slideIn 0.3s ease-out;
                background: ${type === 'success' ? '#16a34a' : '#dc2626'};
                display: flex;
                align-items: center;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            `;
            messageDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}" style="margin-right: 10px;"></i>${message}`;
            document.body.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => messageDiv.remove(), 300);
            }, 3000);
        }

        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Add hover effect for form inputs
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>