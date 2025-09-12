<?php
session_start();
include '../dbConnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Fetch all subjects
$subjects = $conn->query("SELECT * FROM subjects ORDER BY subject_id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Subjects - LMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            position: relative;
        }

        .header {
            background: #2563eb;
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .header-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            position: relative;
            z-index: 1;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }

        .header-icon i {
            font-size: 36px;
            color: white;
        }

        .header-title {
            color: white;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .header-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
            position: relative;
            z-index: 1;
        }

        .content {
            padding: 40px 30px;
        }

        .table-container {
            background: #f8fafc;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #2563eb;
            color: white;
            padding: 20px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        th:first-child {
            border-radius: 0;
        }

        th:last-child {
            border-radius: 0;
        }

        th i {
            margin-right: 8px;
            font-size: 16px;
        }

        td {
            padding: 20px 15px;
            border-bottom: 1px solid #e2e8f0;
            background: white;
            transition: all 0.3s ease;
            font-size: 15px;
            color: #1e293b;
        }

        tr:hover td {
            background: #f1f5f9;
            transform: translateY(-1px);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .subject-id {
            font-weight: 600;
            color: #2563eb;
        }

        .subject-name {
            font-weight: 500;
            color: #374151;
        }

        .enroll-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: #16a34a;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(22, 163, 74, 0.3);
            position: relative;
            overflow: hidden;
        }

        .enroll-btn i {
            margin-right: 8px;
            font-size: 14px;
        }

        .enroll-btn:hover {
            background: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(22, 163, 74, 0.4);
        }

        .enroll-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .enroll-btn:hover::before {
            left: 100%;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 25px;
            background: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
            margin-bottom: 30px;
        }

        .back-btn i {
            margin-right: 8px;
            font-size: 16px;
        }

        .back-btn:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .empty-state i {
            font-size: 48px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #374151;
        }

        .empty-state p {
            font-size: 16px;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .header {
                padding: 30px 20px 20px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .header-title {
                font-size: 24px;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 15px 10px;
            }
            
            .enroll-btn {
                padding: 8px 15px;
                font-size: 12px;
            }
        }

        /* Loading animation */
        .container {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        tr {
            animation: slideInLeft 0.5s ease-out;
            animation-fill-mode: both;
        }

        tr:nth-child(1) { animation-delay: 0.1s; }
        tr:nth-child(2) { animation-delay: 0.2s; }
        tr:nth-child(3) { animation-delay: 0.3s; }
        tr:nth-child(4) { animation-delay: 0.4s; }
        tr:nth-child(5) { animation-delay: 0.5s; }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h1 class="header-title">Available Subjects</h1>
            <p class="header-subtitle">Choose subjects to enroll in</p>
        </div>
        
        <div class="content">
            <a href="../student_management/student_profile.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Profile
            </a>
            
            <?php if ($subjects->num_rows > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i>Subject ID</th>
                                <th><i class="fas fa-book"></i>Subject Name</th>
                                <th><i class="fas fa-cog"></i>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $subjects->fetch_assoc()): ?>
                            <tr>
                                <td class="subject-id"><?php echo $row['subject_id']; ?></td>
                                <td class="subject-name"><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                <td>
                                    <a href="../subject_management/enroll_subject.php?subject_id=<?php echo $row['subject_id']; ?>" class="enroll-btn">
                                        <i class="fas fa-user-plus"></i>
                                        Enroll Now
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h3>No Subjects Available</h3>
                    <p>There are currently no subjects available for enrollment.<br>Please check back later or contact your administrator.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>