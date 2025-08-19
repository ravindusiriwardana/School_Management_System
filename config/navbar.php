<?php
if (!isset($_SESSION)) {
    session_start();
}
?>

<style>
    .navbar {
        background: #2563eb;
        color: white;
        padding: 12px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .navbar .logo {
        font-size: 20px;
        font-weight: bold;
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
        display: inline;
    }

    .navbar ul li a {
        text-decoration: none;
        color: white;
        font-weight: 500;
        transition: color 0.3s;
    }

    .navbar ul li a:hover {
        color: #facc15;
    }

    body {
        padding-top: 60px; /* prevent content hiding behind navbar */
    }
</style>

<div class="navbar">
    <div class="logo">LMS</div>
    <ul>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
            <li><a href="../student/profile.php">Profile</a></li>
            <li><a href="../subject_management/available_subjects.php">Subjects</a></li>
            <li><a href="../subject_management/enrolled_subjects.php">My Subjects</a></li>
            <li><a href="../logout.php">Logout</a></li>
        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher'): ?>
            <li><a href="../teacher/profile.php">Profile</a></li>
            <li><a href="../subject_management/subject_list.php">Manage Subjects</a></li>
            <li><a href="../subject_management/add_marks.php">Add Marks</a></li>
            <li><a href="../logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="../login.php">Login</a></li>
        <?php endif; ?>
    </ul>
</div>