<!DOCTYPE html>
<html>
<head>
    <title>Home - School Management System</title>
    <style>
        /* Reset default margins and padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body styling */
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f7fa;
            color: #333;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header styling */
        header {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        /* Hero section with hover effect */
        .hero {
            position: relative;
            background: url("src/LMS-for-teaching.jpg") center/cover no-repeat;
            color: white;
            text-align: center;
            padding: 4rem 1rem;
            margin-bottom: 2rem;
            overflow: hidden;
            transition: transform 0.5s ease;
        }

        /* Overlay for darkening effect */
        .hero::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            transition: background 0.5s ease;
        }

        /* Hover zoom & dark overlay */
        .hero:hover {
            transform: scale(1.05);
        }

        .hero:hover::before {
            background: rgba(0, 0, 0, 0.5);
        }

        /* Hero text styling */
        .hero h2,
        .hero p {
            position: relative;
            z-index: 1;
        }

        .hero h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Navigation styling */
        nav {
            width: 80%;
            max-width: 800px;
            margin: 0 auto 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1.1rem;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .nav-item:hover {
            background-color: #2980b9;
            transform: translateY(-3px);
        }

        .nav-item:active {
            transform: translateY(0);
        }

        .nav-item img {
            width: 24px;
            height: 24px;
            margin-right: 0.75rem;
            filter: brightness(0) invert(1); /* Makes icons white */
        }

        /* Main content */
        main {
            flex: 1;
        }

        /* Footer styling */
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 1rem;
            font-size: 0.9rem;
        }

        /* Responsive design */
        @media (max-width: 600px) {
            header h1 {
                font-size: 1.8rem;
            }

            .hero h2 {
                font-size: 1.5rem;
            }

            .hero p {
                font-size: 1rem;
            }

            nav {
                width: 90%;
                grid-template-columns: 1fr;
            }

            .nav-item {
                font-size: 1rem;
                padding: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>School Management System</h1>
    </header>
    <main>
        <section class="hero">
            <h2>Welcome to Our Learning Management System</h2>
            <p>Streamline education with tools for students, teachers, and administrators.</p>
        </section>
        <nav>
            <a href="login.php" class="nav-item">
                <img src="https://via.placeholder.com/24?text=Login" alt="Login icon">
                Login
            </a>
        </nav>
    </main>
    <footer>
        &copy; 2025 School Management System. All rights reserved.
    </footer>
</body>
</html>