<?php
session_start();
include '../config/dbConnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Handle form submission
if (isset($_POST['submit'])) {
    $subject_id = intval($_POST['subject_id']);
    foreach ($_POST['marks'] as $student_id => $mark) {
        $mark = intval($mark);
        // Insert or update marks
        $stmt = $conn->prepare("
            INSERT INTO marks (student_id, subject_id, marks) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE marks = VALUES(marks)
        ");
        $stmt->bind_param("iii", $student_id, $subject_id, $mark);
        $stmt->execute();
    }
    $message = "Marks updated successfully!";
}

// Fetch subjects assigned to teacher
$teacher_id = $_SESSION['user_id'];
$subjects = $conn->query("SELECT * FROM subjects ORDER BY subject_id ASC");

// If a subject is selected, fetch enrolled students
$selected_subject_id = $_GET['subject_id'] ?? null;
$students = [];
if ($selected_subject_id) {
    $stmt = $conn->prepare("
        SELECT s.id, s.name, m.marks 
        FROM students s
        JOIN enrollments e ON s.id = e.student_id
        LEFT JOIN marks m ON s.id = m.student_id AND m.subject_id = e.subject_id
        WHERE e.subject_id = ?
    ");
    $stmt->bind_param("i", $selected_subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    // Calculate ranks, grades, min, max, avg
    $graded = array_filter($students, function($s) { return !empty($s['marks']); });
    if ($graded) {
        usort($graded, function($a, $b) {
            return $b['marks'] - $a['marks'];
        });

        $ranks = [];
        $rank = 1;
        $prev_marks = null;
        $i = 0;
        foreach ($graded as $stu) {
            if ($prev_marks !== null && $stu['marks'] < $prev_marks) {
                $rank = $i + 1;
            }
            $ranks[$stu['id']] = $rank;
            $prev_marks = $stu['marks'];
            $i++;
        }

        $marks_array = array_column($graded, 'marks');
        $min_marks = min($marks_array);
        $max_marks = max($marks_array);
        $avg_marks = round(array_sum($marks_array) / count($marks_array), 2);
    } else {
        $min_marks = $max_marks = $avg_marks = '-';
    }
}

function get_grade($mark) {
    if ($mark >= 75) return 'A';
    elseif ($mark >= 60) return 'B';
    elseif ($mark >= 40) return 'S';
    else return 'F';
}

function get_grade_color($grade) {
    switch ($grade) {
        case 'A': return '#008000'; 
        case 'B': return '#0000FF'; 
        case 'S': return '#FFA500'; 
        case 'F': return '#FF0000'; 
        default: return '#FF0000';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Marks - School Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            min-height: 100vh;
            color: #333;
        }



        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .content-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 2px solid #0000FF;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            background: #0000FF;
            color: white;
            padding: 30px 30px 20px;
            font-weight: 600;
        }

        .main-title {
            font-size: 28px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .main-subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            margin: 0;
        }

        .card-body {
            padding: 30px;
        }

        /* Success message */
        .success-message {
            background: #e8f5e8;
            color: #008000;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            border: 1px solid #008000;
            font-weight: 500;
            text-align: center;
        }

        /* Subject selection form */
        .subject-selection {
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-select {
            width: 100%;
            max-width: 400px;
            padding: 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            outline: none;
            border-color: #0000FF;
            box-shadow: 0 0 0 3px rgba(0, 0, 255, 0.1);
        }

        /* Data table */
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            margin-bottom: 30px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .data-table th {
            background: white;
            color: #333;
            padding: 16px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .data-table tr:hover {
            background: #f8f9ff;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        /* Mark input */
        .marks-input {
            width: 80px;
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .marks-input:focus {
            outline: none;
            border-color: #0000FF;
            box-shadow: 0 0 0 2px rgba(0, 0, 255, 0.1);
        }

        /* Buttons */
        .button-group {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #008000;
            color: white;
        }

        .btn-primary:hover {
            background: #0000FF;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 255, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #0000FF;
            border: 2px solid #0000FF;
        }

        .btn-secondary:hover {
            background: #0000FF;
            color: white;
            transform: translateY(-2px);
        }

        /* No data message */
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-size: 16px;
        }

        .no-data-icon {
            font-size: 48px;
            color: #0000FF;
            margin-bottom: 16px;
        }

        /* Statistics cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 2px solid #f0f0f0;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #0000FF;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .data-table th,
            .data-table td {
                padding: 12px 16px;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .content-card {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if(isset($message)): ?>
            <div class="success-message">
                ✅ <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="content-card">
            <div class="card-header">
                <h1 class="main-title">📝 Enter Marks</h1>
                <p class="main-subtitle">Manage student assessment scores</p>
                <h2 class="section-title">📚 Subject Selection</h2>
            </div>
            <div class="card-body">
                <form method="GET" class="subject-selection">
                    <div class="form-group">
                        <label class="form-label" for="subject_id">Select Subject</label>
                        <select name="subject_id" id="subject_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Choose Subject --</option>
                            <?php while ($row = $subjects->fetch_assoc()): ?>
                                <option value="<?php echo $row['subject_id']; ?>" <?php if($selected_subject_id == $row['subject_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['subject_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($selected_subject_id && count($students) > 0): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($students); ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count(array_filter($students, function($s) { return !empty($s['marks']); })); ?></div>
                    <div class="stat-label">Graded</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count(array_filter($students, function($s) { return empty($s['marks']); })); ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $min_marks; ?></div>
                    <div class="stat-label">Min Marks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $max_marks; ?></div>
                    <div class="stat-label">Max Marks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $avg_marks; ?></div>
                    <div class="stat-label">Avg Marks</div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    🎯 Student Marks Entry
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="subject_id" value="<?php echo $selected_subject_id; ?>">
                        
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Marks</th>
                                        <th>Grade</th>
                                        <th>Enter Marks (0-100)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $s): ?>
                                    <?php 
                                        $grade = !empty($s['marks']) ? get_grade($s['marks']) : null;
                                        $color = $grade ? get_grade_color($grade) : null;
                                    ?>
                                    <tr>
                                        <td><?php echo isset($ranks[$s['id']]) ? $ranks[$s['id']] : '-'; ?></td>
                                        <td><strong><?php echo $s['id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                                        <td><?php echo !empty($s['marks']) ? $s['marks'] . '/100' : 'Not Graded'; ?></td>
                                        <td>
                                            <?php if ($grade): ?>
                                                <span style="background: <?php echo $color; ?>; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                                    <?php echo $grade; ?>
                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   name="marks[<?php echo $s['id']; ?>]" 
                                                   class="marks-input"
                                                   value="<?php echo $s['marks'] ?? ''; ?>" 
                                                   min="0" 
                                                   max="100" 
                                                   placeholder="0-100"
                                                   required>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="button-group">
                            <button type="submit" name="submit" class="btn btn-primary">
                                💾 Save All Marks
                            </button>
                            <a href="../teacher_management/teachers_profile.php" class="btn btn-secondary">
                                ← Back to Profile
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ($selected_subject_id): ?>
            <div class="content-card">
                <div class="card-body">
                    <div class="no-data">
                        <div class="no-data-icon">📚</div>
                        <h3>No Students Enrolled</h3>
                        <p>There are no students enrolled in this subject yet.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>