<?php
// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Navbar Styles */
        .navbar {
            background: #2563eb;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar .logo {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }

        .navbar ul li {
            position: relative;
        }

        .navbar ul li a {
            text-decoration: none;
            color: white;
            font-size: 15px;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 6px;
            transition: background 0.3s;
        }

        .navbar ul li a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Right Side (Logout/User) */
        .navbar .right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .navbar .user {
            font-size: 15px;
            font-weight: 500;
        }

        .logout-btn {
            background: #ef4444;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .navbar ul {
                flex-direction: column;
                width: 100%;
                gap: 10px;
                margin-top: 10px;
            }

            .navbar ul li a {
                display: block;
                width: 100%;
                padding: 10px;
            }

            .navbar .right {
                margin-top: 10px;
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo"><i class="fas fa-graduation-cap"></i> LMS</div>
        <ul>
            <li><a href="http://localhost:8888/school_management_system/teacher_management/teachers_profile.php">Profile</a></li>
            <li><a href="http://localhost:8888/school_management_system/student_management/student_list.php">Students</a></li>
            <li><a href="http://localhost:8888/school_management_system/subject_management/subject_list.php">Courses</a></li>
            <li><a href="http://localhost:8888/school_management_system/marks_management/marks.php">Add Marks</a></li>
            
        </ul>
        
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>
</body>
</html>