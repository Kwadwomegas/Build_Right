<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Building Permit System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: LightSteelBlue; /* HTML standard color */
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Navbar Styling */
        .navbar {
            background-color: #007bff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .nav-link {
            color: white !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: #e0e0e0 !important;
        }

        /* Hero Section Styling */
        .hero-section {
            position: relative;
            background: linear-gradient(rgba(0, 123, 255, 0.7), rgba(0, 123, 255, 0.7)), url('images/hero-bg.jpg') no-repeat center center/cover;
            height: 70vh;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background-color: #007bff; /* Fallback color if image fails to load */
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3); /* Subtle overlay for better text readability */
        }
        .hero-content {
            position: relative;
            z-index: 1;
        }
        .hero-section h1 {
            font-size: 3rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 1rem;
        }
        .hero-section p {
            font-size: 1.3rem;
            font-weight: 300;
            margin-bottom: 2rem;
        }
        .hero-section .btn {
            background-color: #28a745;
            border: none;
            padding: 12px 30px;
            font-size: 1.2rem;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .hero-section .btn:hover {
            background-color: #218838;
            transform: scale(1.05);
        }

        /* Features Section Styling */
        .features-section {
            padding: 60px 0;
        }
        .features-section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 40px;
            color: #333;
        }
        .feature-card {
            background-color: white;
            border: none;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            margin-bottom: 20px;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        .feature-card i {
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 15px;
        }
        .feature-card h4 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        .feature-card p {
            font-size: 1rem;
            color: #666;
        }

        /* Footer Styling */
        footer {
            background-color: #1a252f;
            color: white;
            padding: 20px 0;
            font-size: 0.9rem;
        }
        footer p {
            margin: 0;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Build_Right</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-content">
        <h1>Apply for Development Permits Online</h1>
        <p>Fast, Secure, and Hassle-Free Permit Processing</p>
        <a href="login.php" class="btn btn-success btn-lg">Get Started</a>
    </div>
</div>

<!-- Features Section -->
<div class="features-section container">
    <h2 class="text-center">Why Use Our System?</h2>
    <div class="row text-center">
        <div class="col-md-4">
            <div class="feature-card">
                <i class="fas fa-file-alt"></i>
                <h4>Easy Application</h4>
                <p>Submit your permit applications online with ease.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <i class="fas fa-check-circle"></i>
                <h4>Fast Approvals</h4>
                <p>Get notifications when your permit is approved.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <i class="fas fa-download"></i>
                <h4>Download Permits</h4>
                <p>Download approved permits as PDFs anytime.</p>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center">
    <p>© <?php echo date("Y"); ?> Development Permit System. Designed by KabTech Consulting. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>