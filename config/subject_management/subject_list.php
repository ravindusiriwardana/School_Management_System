<?php
session_start();
include '../dbConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle Delete (only for teachers)
if (isset($_GET['delete_id']) && $_SESSION['role'] === 'teacher') {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM subjects WHERE subject_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: subject_list.php");
    exit();
}

// Fetch all subjects
$subjects = $conn->query("SELECT * FROM subjects ORDER BY subject_id ASC");

// Fetch all students with their subjects (assuming enrollments table exists)
$students = $conn->query("
    SELECT s.id AS student_id, s.name AS student_name, sub.subject_name
    FROM students s
    LEFT JOIN enrollments e ON s.id = e.student_id
    LEFT JOIN subjects sub ON e.subject_id = sub.subject_id
    ORDER BY s.id ASC
");

// Fetch all teachers with their subjects
$teachers = $conn->query("
    SELECT t.id AS teacher_id, t.name AS teacher_name, sub.subject_name
    FROM teachers t
    LEFT JOIN subjects sub ON t.subject = sub.subject_name
    ORDER BY t.id ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Management - LMS</title>
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
            max-width: 1200px;
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
            font-size: 32px;
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

        .section {
            margin-bottom: 50px;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 12px;
            color: #2563eb;
            font-size: 28px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn i {
            margin-right: 8px;
            font-size: 14px;
        }

        .btn-add {
            background: #16a34a;
            color: white;
            box-shadow: 0 4px 15px rgba(22, 163, 74, 0.3);
        }

        .btn-add:hover {
            background: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(22, 163, 74, 0.4);
        }

        .btn-marks {
            background: #2563eb;
            color: white;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-marks:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
        }

        .btn-edit {
            background: #f59e0b;
            color: white;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
            padding: 8px 12px;
            font-size: 12px;
        }

        .btn-edit:hover {
            background: #d97706;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }

        .btn-delete {
            background: #dc2626;
            color: white;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
            padding: 8px 12px;
            font-size: 12px;
        }

        .btn-delete:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
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

        th i {
            margin-right: 8px;
            font-size: 16px;
        }

        td {
            padding: 18px 15px;
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

        .id-cell {
            font-weight: 600;
            color: #2563eb;
            font-size: 16px;
        }

        .name-cell {
            font-weight: 500;
            color: #374151;
        }

        .subject-cell {
            font-weight: 400;
            color: #6b7280;
        }

        .actions-cell {
            display: flex;
            gap: 8px;
            align-items: center;
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
                font-size: 28px;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .section-title {
                font-size: 20px;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 12px 8px;
            }

            .actions-cell {
                flex-direction: column;
                gap: 5px;
            }

            .btn {
                font-size: 12px;
                padding: 8px 12px;
            }
        }

        /* Loading animations */
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

        .section {
            animation: slideInLeft 0.5s ease-out;
            animation-fill-mode: both;
        }

        .section:nth-child(1) { animation-delay: 0.1s; }
        .section:nth-child(2) { animation-delay: 0.3s; }
        .section:nth-child(3) { animation-delay: 0.5s; }

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

        tr {
            animation: fadeInRow 0.3s ease-out;
            animation-fill-mode: both;
        }

        tr:nth-child(1) { animation-delay: 0.1s; }
        tr:nth-child(2) { animation-delay: 0.2s; }
        tr:nth-child(3) { animation-delay: 0.3s; }
        tr:nth-child(4) { animation-delay: 0.4s; }
        tr:nth-child(5) { animation-delay: 0.5s; }

        @keyframes fadeInRow {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <h1 class="header-title">Management Dashboard</h1>
            <p class="header-subtitle">Subjects, Students & Teachers</p>
        </div>
        
        <div class="content">
            <a href="../teacher_management/teachers_profile.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Profile
            </a>

            <!-- Subjects Section -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-book-open"></i>
                        Subjects
                    </h2>
                    <?php if ($_SESSION['role'] === 'teacher'): ?>
                    <div class="action-buttons">
                        <a href="add_subject.php" class="btn btn-add">
                            <i class="fas fa-plus"></i>
                            Add New Subject
                        </a>
                        <a href="../marks_management/enter_marks.php" class="btn btn-marks">
                            <i class="fas fa-chart-line"></i>
                            Enter Marks
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($subjects->num_rows > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i>ID</th>
                                    <th><i class="fas fa-book"></i>Subject Name</th>
                                    <?php if ($_SESSION['role'] === 'teacher') echo '<th><i class="fas fa-cog"></i>Actions</th>'; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $subjects->data_seek(0); // Reset pointer
                                while ($row = $subjects->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td class="id-cell"><?php echo $row['subject_id']; ?></td>
                                    <td class="name-cell"><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                    <?php if ($_SESSION['role'] === 'teacher'): ?>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="edit_subject.php?id=<?php echo $row['subject_id']; ?>" class="btn btn-edit">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </a>
                                            <a href="subject_list.php?delete_id=<?php echo $row['subject_id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this subject?');">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h3>No Subjects Available</h3>
                        <p>No subjects have been created yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Students Section -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-user-graduate"></i>
                        Students
                    </h2>
                </div>

                <?php if ($students->num_rows > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i>ID</th>
                                    <th><i class="fas fa-user"></i>Name</th>
                                    <th><i class="fas fa-book"></i>Subject</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $students->data_seek(0); // Reset pointer
                                while ($row = $students->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td class="id-cell"><?php echo $row['student_id']; ?></td>
                                    <td class="name-cell"><?php echo htmlspecialchars($row['student_name']); ?></td>
                                    <td class="subject-cell"><?php echo htmlspecialchars($row['subject_name'] ?? 'Not Enrolled'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-graduate"></i>
                        <h3>No Students Found</h3>
                        <p>No students are currently registered.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Teachers Section -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Teachers
                    </h2>
                </div>

                <?php if ($teachers->num_rows > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i>ID</th>
                                    <th><i class="fas fa-user"></i>Name</th>
                                    <th><i class="fas fa-book"></i>Subject</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $teachers->data_seek(0); // Reset pointer
                                while ($row = $teachers->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td class="id-cell"><?php echo $row['teacher_id']; ?></td>
                                    <td class="name-cell"><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                                    <td class="subject-cell"><?php echo htmlspecialchars($row['subject_name'] ?? 'No Subject Assigned'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <h3>No Teachers Found</h3>
                        <p>No teachers are currently registered.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>