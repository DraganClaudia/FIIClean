<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'CaS - Cleaning Web Simulator'; ?></title>
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    
    <!-- Meta tags for SEO and responsive design -->
    <meta name="description" content="Sistem Web pentru managementul activităților de spălătorie - covoare, autoturisme, îmbrăcăminte">
    <meta name="keywords" content="spălătorie, curățenie, management, web simulator">
    <meta name="author" content="FII Clean Team">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="public/favicon.ico">
    
    <!-- Google Fonts for better typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    
    <!-- CSRF Token pentru securitate -->
    <?php if (isset($csrf_token)): ?>
    <meta name="csrf-token" content="<?php echo $csrf_token; ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Navigation Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><a href="?controller=public&action=home">FII-Clean</a></h1>
                    <span class="tagline">Clean up.</span>
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="?controller=public&action=home">Acasă</a></li>
                        <li><a href="?controller=public&action=scholarFII">ScholarFII</a></li>
                        <li><a href="?controller=public&action=contact">Contact</a></li>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="welcome">Bun venit, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Utilizator') ?></strong></li>
                            
                            <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
                                <li><a href="?controller=admin&action=dashboard">Admin</a></li>
                            <?php else: ?>
                                <li><a href="?controller=client&action=dashboard">Dashboard</a></li>
                            <?php endif; ?>
                            
                            <li><a href="?controller=auth&action=logout" class="logout-btn">Logout</a></li>
                        <?php else: ?>
                            <li><a href="?controller=auth&action=login">Login</a></li>
                            <li><a href="?controller=auth&action=register">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <!-- Mobile menu toggle -->
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>
    
    <!-- Main Content Area -->
    <main class="main-content">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo htmlspecialchars($_SESSION['success_message']); 
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php 
                echo htmlspecialchars($_SESSION['error_message']); 
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
