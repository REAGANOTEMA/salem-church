<?php
// Reusable Header Component for Salem Dominion Ministries
// Include this file in any page to display the header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Salem Dominion Ministries'; ?></title>
    
    <!-- Force cache refresh with timestamp -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $pageDescription ?? 'Salem Dominion Ministries - Divine Worship Experience with Apostle Faty Musasizi'; ?>">
    <meta name="keywords" content="<?php echo $pageKeywords ?? 'church, divine worship, Apostle Faty Musasizi, Iganga, Uganda, spiritual'; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="public/logo-icon.jpeg">
    <link rel="icon" type="image/png" sizes="16x16" href="public/logo-icon.jpeg">
    <link rel="icon" type="image/jpeg" href="public/logo-icon.jpeg">
    <link rel="apple-touch-icon" sizes="180x180" href="public/logo-icon.jpeg">
    <link rel="manifest" href="public/site.webmanifest">
    <meta name="theme-color" content="#0f172a">
    <meta name="msapplication-TileColor" content="#0f172a">
    <meta name="msapplication-config" content="public/browserconfig.xml">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css?v=<?php echo time(); ?>" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css?v=<?php echo time(); ?>" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css?v=<?php echo time(); ?>" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Great+Vibes&display=swap" rel="stylesheet">
    
    <!-- Custom CSS Variables -->
    <style>
        :root {
            /* Primary Palette - Ultra Premium */
            --midnight-blue: #0f172a;
            --ocean-blue: #0ea5e9;
            --sky-blue: #38bdf8;
            --ice-blue: #7dd3fc;
            --snow-white: #ffffff;
            --pearl-white: #f8fafc;
            
            /* Divine Accents */
            --heavenly-gold: #fbbf24;
            --divine-light: #fef3c7;
            
            /* Supporting Colors */
            --gray-light: #e9ecef;
            --gray-medium: #6c757d;
            --gray-dark: #495057;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-church"></i>
                <span class="brand-text">Salem Dominion Ministries</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>" href="about.php">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'news' ? 'active' : ''; ?>" href="news.php">
                            <i class="fas fa-newspaper me-1"></i>News
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'sermons' ? 'active' : ''; ?>" href="sermons.php">
                            <i class="fas fa-bible me-1"></i>Sermons
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'events' ? 'active' : ''; ?>" href="events.php">
                            <i class="fas fa-calendar me-1"></i>Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'gallery' ? 'active' : ''; ?>" href="gallery.php">
                            <i class="fas fa-images me-1"></i>Gallery
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>" href="contact.php">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<style>
/* Navigation Styles */
.navbar {
    background: var(--snow-white) !important;
    box-shadow: 0 2px 20px rgba(0,0,0,0.1);
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 1000;
    transition: all 0.3s ease;
}

.navbar.scrolled {
    padding: 0.5rem 0;
    box-shadow: 0 4px 30px rgba(0,0,0,0.15);
}

.navbar-brand {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--midnight-blue) !important;
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.navbar-brand:hover {
    color: var(--ocean-blue) !important;
    transform: translateY(-2px);
}

.navbar-brand i {
    margin-right: 12px;
    font-size: 1.5rem;
    color: var(--heavenly-gold);
}

.brand-text {
    background: linear-gradient(135deg, var(--midnight-blue), var(--ocean-blue));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.navbar-nav .nav-link {
    color: var(--midnight-blue) !important;
    font-weight: 500;
    font-family: 'Montserrat', sans-serif;
    margin: 0 8px;
    padding: 8px 16px !important;
    border-radius: 25px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.navbar-nav .nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(14, 165, 233, 0.1), transparent);
    transition: left 0.5s ease;
}

.navbar-nav .nav-link:hover::before {
    left: 100%;
}

.navbar-nav .nav-link:hover,
.navbar-nav .nav-link.active {
    color: var(--snow-white) !important;
    background: linear-gradient(135deg, var(--ocean-blue), var(--sky-blue));
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(14, 165, 233, 0.3);
}

.navbar-nav .nav-link i {
    font-size: 14px;
    opacity: 0.8;
}

.navbar-nav .nav-link:hover i,
.navbar-nav .nav-link.active i {
    opacity: 1;
}

.navbar-toggler {
    border: 2px solid var(--ocean-blue);
    padding: 6px 12px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.navbar-toggler:hover {
    background: var(--ocean-blue);
    border-color: var(--ocean-blue);
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23fbbf24' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .navbar {
        padding: 0.75rem 0;
    }
    
    .navbar-brand {
        font-size: 1.4rem;
    }
    
    .navbar-brand i {
        font-size: 1.2rem;
        margin-right: 8px;
    }
    
    .navbar-nav {
        background: var(--snow-white);
        padding: 20px;
        border-radius: 15px;
        margin-top: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .navbar-nav .nav-link {
        margin: 5px 0;
        padding: 12px 20px !important;
        border-radius: 10px;
        text-align: center;
    }
    
    .navbar-nav .nav-link i {
        margin-right: 8px;
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .navbar-brand {
        font-size: 1.2rem;
    }
    
    .brand-text {
        display: none;
    }
    
    .navbar-brand i {
        margin-right: 0;
        font-size: 1.4rem;
    }
}
</style>

<script>
// Navbar scroll effect
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
});
</script>
