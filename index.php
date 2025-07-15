<?php
session_start();
$is_logged_in = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiraChatBot - Home</title>
    <style>
        body {
            background: #e6f0ff;
            color: #222;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            width: 100%;
            background: #1565c0;
            color: #fff;
            box-shadow: 0 2px 8px rgba(21,101,192,0.08);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
        }
        .nav-container {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 0.8rem 1.5rem;
        }
        .topbar .logo-link {
            color: #fff;
            text-decoration: none;
            font-size: 1.4rem;
            font-weight: bold;
            letter-spacing: 1px;
            transition: opacity 0.15s;
        }
        .topbar .logo-link:hover {
            opacity: 0.7;
        }
        .topbar .nav-link {
            color: #fff;
            text-decoration: none;
            font-size: 1.08rem;
            background: #003c8f;
            border: none;
            cursor: pointer;
            font-family: inherit;
            padding: 0.4rem 1.2rem;
            border-radius: 4px;
            margin-left: 1rem;
            transition: background 0.15s, color 0.15s;
            font-weight: 500;
        }
        .topbar .nav-link:hover {
            background: #fff;
            color: #1565c0;
        }
        .hero {
            margin-top: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            text-align: center;
        }
        .hero-title {
            font-size: 2.8rem;
            font-weight: bold;
            color: #1565c0;
            margin-bottom: 1rem;
            letter-spacing: 1px;
        }
        .hero-desc {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 2.5rem;
            max-width: 600px;
        }
        .hero-actions {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
        }
        .hero-btn {
            background: #1565c0;
            color: #fff;
            border: none;
            padding: 0.9rem 2.2rem;
            border-radius: 6px;
            font-size: 1.15rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(21,101,192,0.08);
            transition: background 0.18s, transform 0.18s;
        }
        .hero-btn:hover {
            background: #003c8f;
            transform: translateY(-2px) scale(1.04);
        }
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2.5rem;
            margin: 3rem auto 2rem auto;
            max-width: 900px;
        }
        .feature-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(21,101,192,0.09);
            padding: 2rem 1.5rem;
            width: 260px;
            text-align: center;
            transition: box-shadow 0.18s, transform 0.18s;
        }
        .feature-card:hover {
            box-shadow: 0 6px 18px rgba(21,101,192,0.16);
            transform: translateY(-4px) scale(1.03);
        }
        .feature-icon {
            font-size: 2.2rem;
            margin-bottom: 0.7rem;
            color: #1565c0;
        }
        .feature-title {
            font-size: 1.18rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #1565c0;
        }
        .feature-desc {
            color: #444;
            font-size: 1rem;
        }
        .footer {
            background: #1565c0;
            color: #fff;
            text-align: center;
            padding: 1.2rem 0 0.5rem 0;
            margin-top: auto;
            font-size: 1.08rem;
            letter-spacing: 0.5px;
        }
        @media (max-width: 700px) {
            .nav-container { padding: 0.8rem 0.7rem; }
            .hero-title { font-size: 2rem; }
            .features { flex-direction: column; gap: 1.5rem; }
            .feature-card { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="nav-container">
            <a href="index.php" class="logo-link">MiraChatBot</a>
            <?php if ($is_logged_in): ?>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">Login</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero">
        <div class="hero-title">Meet MiraChatBot</div>
        <div class="hero-desc">
            Your all-in-one AI chatbot platform.<br>
            Create, customize, and manage multiple bots for your business, website, or personal use.<br>
            Powerful, scalable, and beautifully simple.
        </div>
        <div class="hero-actions">
            <?php if ($is_logged_in): ?>
                <a href="dashboard.php" class="hero-btn">Go to Dashboard</a>
            <?php else: ?>
                <a href="signup.php" class="hero-btn">Get Started</a>
                <a href="login.php" class="hero-btn" style="background:#fff;color:#1565c0;border:1.5px solid #1565c0;">Login</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="features">
        <div class="feature-card">
            <div class="feature-icon">🤖</div>
            <div class="feature-title">Multi-Bot Management</div>
            <div class="feature-desc">Create and manage unlimited chatbots, each with its own name, colors, logo, and knowledge base.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🎨</div>
            <div class="feature-title">Easy Customization</div>
            <div class="feature-desc">Personalize your bots with custom branding, color schemes, and unique personalities.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">⚡</div>
            <div class="feature-title">Fast & Scalable</div>
            <div class="feature-desc">Built for performance and growth. Add more bots or users anytime—no limits.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔒</div>
            <div class="feature-title">Secure & Private</div>
            <div class="feature-desc">Your data is safe. Only you can access and manage your bots and knowledge bases.</div>
        </div>
    </div>
    <div class="footer">
        A product of Medha Tech &copy; 2025
    </div>
</body>
</html>