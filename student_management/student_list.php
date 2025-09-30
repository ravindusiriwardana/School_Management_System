<?php
session_start();
include __DIR__ . '/../config/dbConnection.php';
include __DIR__ . '/../navbar.php';

// Ensure only teachers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Fetch all students
$result = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students List - LMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            padding: 40px 30px;
            animation: fadeInUp 0.6s ease-out;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header-title {
            font-size: 28px;
            font-weight: 600;
            color: #1e293b;
        }
        .add-student-btn {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #16a34a;
            color: white;
            box-shadow: 0 4px 15px rgba(22, 163, 74, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .add-student-btn i { margin-right: 8px; font-size: 16px; }
        .add-student-btn:hover {
            background: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(22, 163, 74, 0.4);
        }
        .add-student-btn::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }
        .add-student-btn:hover::before { left: 100%; }
        .table-container { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 16px 20px;
            text-align: left;
            font-size: 16px;
            color: #1e293b;
        }
        th {
            background: #2563eb;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tr { border-bottom: 1px solid #e2e8f0; transition: all 0.3s ease; }
        tr:hover { background: #e6f0fa; }
        .action-links a {
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        .action-links .edit { color: #2563eb; }
        .action-links .edit:hover { color: #1e40af; }
        .action-links .enroll { color: #16a34a; }
        .action-links .enroll:hover { color: #15803d; }
        .action-links .delete { color: #dc2626; }
        .action-links .delete:hover { color: #b91c1c; }

        @media (max-width: 768px) {
            .container { margin: 10px; padding: 30px 20px; }
            .header { flex-direction: column; gap: 20px; text-align: center; }
            .header-title { font-size: 24px; }
            th, td { font-size: 14px; padding: 12px 15px; }
        }
        @media (max-width: 480px) {
            th, td { font-size: 12px; padding: 10px 12px; }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 class="header-title">Students List</h2>
            <a href="add_student.php" class="add-student-btn"><i class="fas fa-user-plus"></i> Add Student</a>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Age</th>
                        
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['age']); ?></td>
                                
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="action-links">
                                    <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="edit">Edit</a>
                                    <a href="subj_enrollment.php?student_id=<?php echo $row['id']; ?>" class="enroll">Enroll</a>
                                    <a href="delete_student.php?id=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">No students found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>