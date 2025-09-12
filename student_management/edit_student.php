<?php
session_start();
include '../config/dbConnection.php';

// Ensure only students can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "Student not found.";
    exit();
}

// Handle form submission
if (isset($_POST['update'])) {
    $name = trim($_POST['name'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $grade = trim($_POST['grade'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    $errors = [];

    if (strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters long.";
    }

    if ($age < 16 || $age > 25) {
        $errors[] = "Please enter a valid age (16-25).";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Check if email exists excluding current user
    $emailCheck = $conn->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
    $emailCheck->bind_param("si", $email, $id);
    $emailCheck->execute();
    if ($emailCheck->get_result()->num_rows > 0) {
        $errors[] = "This email is already registered.";
    }

    // Handle profile picture
    $profile_picture = $row['profile_picture'] ?? '';
    if (!empty($_FILES['profile_picture']['name']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif ($_FILES['profile_picture']['size'] > 5 * 1024 * 1024) {
            $errors[] = "Profile picture must be less than 5MB.";
        } else {
            $new_filename = 'student_' . $id . '_' . time() . '.' . $ext;
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                if ($profile_picture && file_exists($upload_dir . $profile_picture)) {
                    unlink($upload_dir . $profile_picture);
                }
                $profile_picture = $new_filename;
            } else {
                $errors[] = "Failed to upload profile picture.";
            }
        }
    }

    if (empty($errors)) {
        // Build update query
        $fields = "name=?, age=?, grade=?, email=?, profile_picture=?, phone=?, address=?, bio=?";
        $stmt = $conn->prepare("UPDATE students SET $fields WHERE id=?");
        $stmt->bind_param(
            "sissssssi",
            $name,
            $age,
            $grade,
            $email,
            $profile_picture,
            $phone,
            $address,
            $bio,
            $id
        );

        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $success_message = "Profile updated successfully!";
            // Refresh data
            $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
        } else {
            $error_message = "Error updating profile. Please try again.";
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
    <title>Edit Profile - Student Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Styles same as before, omitted here for brevity */
    </style>
</head>
<body>
<div class="edit-container">
    <div class="edit-header">
        <div class="profile-avatar" onclick="document.getElementById('profilePicInput').click();">
            <?php if (!empty($row['profile_picture'])): ?>
                <img src="../uploads/<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile Picture" id="profilePreview">
            <?php else: ?>
                <i class="fas fa-user-graduate"></i>
            <?php endif; ?>
            <div class="avatar-upload"><i class="fas fa-camera"></i></div>
        </div>
        <h1 class="edit-title">Edit Profile</h1>
        <p class="edit-subtitle">Update Your Information</p>
    </div>
    <div class="edit-body">
        <?php if ($success_message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <input type="file" id="profilePicInput" name="profile_picture" class="avatar-input" accept="image/*" onchange="previewImage(event)">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="name"><i class="fas fa-user"></i> Full Name *</label>
                    <input type="text" id="name" name="name" class="form-input" value="<?php echo htmlspecialchars($row['name']); ?>" required minlength="2" maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email"><i class="fas fa-envelope"></i> Email *</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="age"><i class="fas fa-calendar-alt"></i> Age *</label>
                    <input type="number" id="age" name="age" class="form-input" value="<?php echo $row['age']; ?>" required min="16" max="25">
                </div>
                <div class="form-group">
                    <label class="form-label" for="grade"><i class="fas fa-graduation-cap"></i> Grade/Class *</label>
                    <input type="text" id="grade" name="grade" class="form-input" value="<?php echo htmlspecialchars($row['grade']); ?>" required maxlength="50">
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone"><i class="fas fa-phone"></i> Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($row['phone'] ?? ''); ?>" pattern="[0-9]{10,15}" placeholder="10-15 digits">
                </div>
                <div class="form-group">
                    <label class="form-label" for="address"><i class="fas fa-map-marker-alt"></i> Address</label>
                    <input type="text" id="address" name="address" class="form-input" value="<?php echo htmlspecialchars($row['address'] ?? ''); ?>" maxlength="255">
                </div>
                <div class="form-group full-width">
                    <label class="form-label" for="bio"><i class="fas fa-align-left"></i> About Me</label>
                    <textarea id="bio" name="bio" class="form-input" maxlength="500"><?php echo htmlspecialchars($row['bio'] ?? ''); ?></textarea>
                    <div class="char-counter"><span id="charCount">0</span>/500</div>
                </div>
            </div>
            <div class="action-buttons">
                <button type="submit" name="update" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <a href="student_profile.php" class="btn btn-secondary"><i class="fas fa-times"></i> Back</a>
            </div>
        </form>
    </div>
</div>
<script>
    function updateCharCount() {
        const bio = document.getElementById('bio');
        const charCount = document.getElementById('charCount');
        charCount.textContent = bio.value.length;
        charCount.style.color = bio.value.length > 450 ? '#dc2626' : '#6b7280';
    }
    document.addEventListener('DOMContentLoaded', updateCharCount);
    document.getElementById('bio').addEventListener('keyup', updateCharCount);

    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('.profile-avatar').innerHTML = `
                    <img src="${e.target.result}" alt="Profile Picture" id="profilePreview">
                    <div class="avatar-upload"><i class="fas fa-camera"></i></div>
                `;
            }
            reader.readAsDataURL(file);
        }
    }

    function validateForm() {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const age = parseInt(document.getElementById('age').value);
        const grade = document.getElementById('grade').value.trim();
        const phone = document.getElementById('phone').value.trim();

        if (name.length < 2) { alert('Name must be at least 2 characters'); return false; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { alert('Invalid email'); return false; }
        if (age < 16 || age > 25) { alert('Age must be 16-25'); return false; }
        if (grade.length < 1) { alert('Enter grade'); return false; }
        if (phone && !/^\d{10,15}$/.test(phone)) { alert('Phone must be 10-15 digits'); return false; }
        return true;
    }
</script>
</body>
</html>