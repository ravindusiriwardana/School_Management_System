<?php
session_start();
include '../dbConnection.php';
include '../navbar.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile - LMS</title>
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

        .profile-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            position: relative;
            animation: fadeInUp 0.6s ease-out;
        }

        .profile-header {
            background: #2563eb;
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
        }

        .profile-header::before {
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

        .profile-avatar:hover { transform: scale(1.05); border-color: rgba(255, 255, 255, 0.6); }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        .profile-avatar i { font-size: 36px; color: white; }

        .avatar-upload {
            position: absolute;
            bottom: 0; right: 0;
            width: 32px; height: 32px;
            background: #16a34a;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            border: 3px solid white;
            transition: all 0.3s ease;
        }
        .avatar-upload:hover { background: #15803d; transform: scale(1.1); }
        .avatar-upload i { font-size: 14px; color: white; }
        .avatar-input { display: none; }

        .profile-title { color: white; font-size: 28px; font-weight: 600; margin-bottom: 8px; position: relative; z-index: 1; }
        .profile-subtitle { color: rgba(255, 255, 255, 0.8); font-size: 16px; position: relative; z-index: 1; }

        .profile-body { padding: 40px 30px; }
        .info-grid { display: grid; gap: 25px; }

        .info-item { position: relative; animation: slideInLeft 0.5s ease-out both; }
        .info-item:nth-child(1) { animation-delay: 0.1s; }
        .info-item:nth-child(2) { animation-delay: 0.2s; }
        .info-item:nth-child(3) { animation-delay: 0.3s; }
        .info-item:nth-child(4) { animation-delay: 0.4s; }

        .info-label { display: flex; align-items: center; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-label i { margin-right: 8px; width: 16px; color: #2563eb; }

        .info-value {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 18px;
            font-size: 16px;
            color: #1e293b;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .info-value::before {
            content: '';
            position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.1), transparent);
            transition: left 0.6s ease;
        }
        .info-value:hover { border-color: #2563eb; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(37, 99, 235, 0.15); }
        .info-value:hover::before { left: 100%; }

        .action-buttons { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 40px; }
        .btn {
            display: flex; align-items: center; justify-content: center;
            padding: 14px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600; font-size: 14px;
            text-transform: uppercase; letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative; overflow: hidden;
        }
        .btn i { margin-right: 8px; font-size: 16px; }
        .btn-primary { background: #16a34a; color: white; box-shadow: 0 4px 15px rgba(22, 163, 74, 0.3); }
        .btn-primary:hover { background: #15803d; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(22, 163, 74, 0.4); }
        .btn-secondary { background: #dc2626; color: white; box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3); }
        .btn-secondary:hover { background: #b91c1c; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4); }
        .btn-info { background: #2563eb; color: white; box-shadow: 0 4px 15px rgba(37,99,235,0.3); }
        .btn-info:hover { background: #1e40af; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(37,99,235,0.4); }

        .btn::before {
            content: '';
            position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }
        .btn:hover::before { left: 100%; }

        @media (max-width: 480px) {
            .profile-container { margin: 10px; }
            .profile-header { padding: 30px 20px 20px; }
            .profile-body { padding: 30px 20px; }
            .action-buttons { grid-template-columns: 1fr; }
            .profile-title { font-size: 24px; }
        }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px);} to { opacity: 1; transform: translateY(0);} }
        @keyframes slideInLeft { from { opacity: 0; transform: translateX(-20px);} to { opacity: 1; transform: translateX(0);} }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar" onclick="document.getElementById('avatarInput').click();">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="../Uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                <?php else: ?>
                    <i class="fas fa-chalkboard-teacher"></i>
                <?php endif; ?>
                <div class="avatar-upload"><i class="fas fa-camera"></i></div>
            </div>
            <input type="file" id="avatarInput" class="avatar-input" accept="image/*" onchange="uploadAvatar(this)">
            <h1 class="profile-title">Teacher Profile</h1>
            <p class="profile-subtitle">Learning Management System</p>
        </div>
        
        <div class="profile-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-user"></i> Full Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-envelope"></i> Email Address</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-calendar-alt"></i> Age</div>
                    <div class="info-value"><?php echo $user['age']; ?> years old</div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-book-open"></i> Teaching Subject</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['subject']); ?></div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="../teacher_management/edit_teacher.php" class="btn btn-info">
                    <i class="fas fa-user-edit"></i> Edit Profile
                </a>

                <a href="../subject_management/subject_list.php" class="btn btn-primary">
                    <i class="fas fa-list-alt"></i> Manage Subjects
                </a>

                <a href="../student_management/student_list.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>Student List
                </a>
                
                <a href="../logout.php" class="btn btn-secondary">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <script>
        function uploadAvatar(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('profile_picture', input.files[0]);
                const avatar = document.querySelector('.profile-avatar');
                const originalContent = avatar.innerHTML;
                avatar.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size: 36px; color: white;"></i>';
                
                fetch('../upload_profile_picture.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        avatar.innerHTML = `<img src="../Uploads/${data.filename}" alt="Profile Picture"><div class="avatar-upload"><i class="fas fa-camera"></i></div>`;
                        showMessage('Profile picture updated successfully!', 'success');
                    } else {
                        avatar.innerHTML = originalContent;
                        showMessage(data.message || 'Error uploading image', 'error');
                    }
                })
                .catch(error => {
                    avatar.innerHTML = originalContent;
                    showMessage('Error uploading image', 'error');
                    console.error('Error:', error);
                });
            }
        }
        
        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.style.cssText = `
                position: fixed; top: 20px; right: 20px;
                padding: 15px 20px; border-radius: 8px; color: white;
                font-weight: 600; z-index: 1000;
                animation: slideIn 0.3s ease-out;
                background: ${type === 'success' ? '#16a34a' : '#dc2626'};
            `;
            messageDiv.textContent = message;
            document.body.appendChild(messageDiv);
            setTimeout(() => {
                messageDiv.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => messageDiv.remove(), 300);
            }, 3000);
        }
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
            @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>