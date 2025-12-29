<?php ($active = $active ?? ''); ?>

<aside class="admin-sidebar">
    <div class="admin-brand">
        <img src="<?php echo e(asset('images/logo.png')); ?>" alt="ARTSCI logo">
        <div class="brand-text">
            <span class="brand-name">ARTSCI</span>
            <span class="brand-tagline">Admin Console</span>
        </div>
    </div>
    <nav class="admin-nav">
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-item <?php echo e($active === 'dashboard' ? 'active' : ''); ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="<?php echo e(route('admin.products.index')); ?>" class="nav-item <?php echo e($active === 'products' ? 'active' : ''); ?>">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="<?php echo e(route('admin.orders.index')); ?>" class="nav-item <?php echo e($active === 'orders' ? 'active' : ''); ?>">
            <i class="fas fa-shopping-bag"></i> Orders
        </a>
        <a href="<?php echo e(route('admin.users.index')); ?>" class="nav-item <?php echo e($active === 'users' ? 'active' : ''); ?>">
            <i class="fas fa-users"></i> Users
        </a>
    </nav>
</aside>
<div class="admin-sidebar-backdrop"></div>
<?php /**PATH /home/codecps/Desktop/security/laravel-app/resources/views/admin/partials/sidebar.blade.php ENDPATH**/ ?>