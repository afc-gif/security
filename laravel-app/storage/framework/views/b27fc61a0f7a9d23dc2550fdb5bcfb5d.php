<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'ARTSCI - POS System'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <?php echo $__env->yieldContent('extra-css'); ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="/" class="nav-logo" aria-label="ARTSCI home">
                <img src="<?php echo e(asset('images/logo.png')); ?>" alt="ARTSCI logo" class="nav-logo-img">
                <div class="brand-text">
                    <span class="brand-name">ARTSCI</span>
                    <span class="brand-tagline">Security POS Suite</span>
                </div>
            </a>
            <button class="nav-toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="nav-menu">
                <li><a href="/">Shop</a></li>
                <?php if(auth()->check()): ?>
                    <li><a href="<?php echo e(route('cart')); ?>"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                    <li><a href="<?php echo e(route('orders.index')); ?>">My Orders</a></li>
                    <?php if(auth()->user()->isAdmin()): ?>
                        <li><a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-link">Admin</a></li>
                    <?php endif; ?>
                    <li class="dropdown">
                        <a href="#"><?php echo e(auth()->user()->name); ?></a>
                        <div class="dropdown-menu">
                            <form action="<?php echo e(route('logout')); ?>" method="POST" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo e(route('login')); ?>" class="cta-nav">Login</a></li>
                    <li><a href="<?php echo e(route('register')); ?>" class="cta-nav">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if(session()->has('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

            <button onclick="this.parentElement.style.display='none';" class="close-btn">&times;</button>
        </div>
    <?php endif; ?>

    <?php if(session()->has('error')): ?>
        <div class="alert alert-error">
            <?php echo e(session('error')); ?>

            <button onclick="this.parentElement.style.display='none';" class="close-btn">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="main-content">
        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2024 ARTSCI. All rights reserved.</p>
            <div class="footer-links">
                <a href="/">Home</a>
                <a href="/">Shop</a>
                <a href="#">Contact</a>
            </div>
        </div>
    </footer>

    <script src="<?php echo e(asset('js/app.js')); ?>"></script>
    <?php echo $__env->yieldContent('extra-js'); ?>
</body>
</html>
<?php /**PATH /home/codecps/security/laravel-app/resources/views/layout.blade.php ENDPATH**/ ?>