<?php $__env->startSection('title', 'Admin Dashboard - ARTSCI'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-container">
    <?php echo $__env->make('admin.partials.sidebar', ['active' => 'dashboard'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <main class="admin-main">
        <div class="admin-header">
            <div class="admin-header-left">
                <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Dashboard</h1>
            </div>
            <form action="<?php echo e(route('logout')); ?>" method="POST" style="display:inline;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-secondary">Logout</button>
            </form>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(3, 169, 244, 0.1); color: var(--primary-blue);">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Total Products</p>
                    <p class="stat-value"><?php echo e($totalProducts); ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(76, 175, 80, 0.1); color: #4CAF50;">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Total Orders</p>
                    <p class="stat-value"><?php echo e($totalOrders); ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(255, 193, 7, 0.1); color: var(--primary-yellow);">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Total Revenue</p>
                    <p class="stat-value">$<?php echo e(number_format($totalRevenue, 2)); ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(233, 30, 99, 0.1); color: #E91E63;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Total Users</p>
                    <p class="stat-value"><?php echo e($totalUsers); ?></p>
                </div>
            </div>
        </div>

        <div class="recent-orders">
            <h2>Recent Orders</h2>
            <?php if($recentOrders->count() > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>#<?php echo e($order->id); ?></td>
                                <td><?php echo e($order->user->name); ?></td>
                                <td>$<?php echo e(number_format($order->total_amount, 2)); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo e($order->status); ?>">
                                        <?php echo e(ucfirst($order->status)); ?>

                                    </span>
                                </td>
                                <td><?php echo e($order->created_at->format('M d, Y')); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="btn btn-sm">View</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/codecps/Desktop/security/laravel-app/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>