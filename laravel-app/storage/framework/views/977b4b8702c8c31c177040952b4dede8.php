<?php $__env->startSection('title', 'Admin Dashboard - ARTSCI'); ?>

<?php $__env->startSection('content'); ?>
<div style="padding: 20px; background: #f5f5f5; min-height: 100vh;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 20px; border-radius: 8px;">
            <h1 style="margin: 0;">Dashboard</h1>
            <form action="<?php echo e(route('logout')); ?>" method="POST" style="display:inline;">
                <?php echo csrf_field(); ?>
                <button type="submit" style="padding: 10px 20px; background: #03A9F4; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Logout</button>
            </form>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div style="font-size: 14px; color: #666; margin-bottom: 10px;">Total Products</div>
                <div style="font-size: 32px; font-weight: bold; color: #03A9F4;"><?php echo e($totalProducts); ?></div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div style="font-size: 14px; color: #666; margin-bottom: 10px;">Total Orders</div>
                <div style="font-size: 32px; font-weight: bold; color: #4CAF50;"><?php echo e($totalOrders); ?></div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div style="font-size: 14px; color: #666; margin-bottom: 10px;">Total Revenue</div>
                <div style="font-size: 32px; font-weight: bold; color: #FFEB3B;">$<?php echo e(number_format($totalRevenue, 2)); ?></div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div style="font-size: 14px; color: #666; margin-bottom: 10px;">Total Users</div>
                <div style="font-size: 32px; font-weight: bold; color: #FF9800;"><?php echo e($totalUsers); ?></div>
            </div>
        </div>

        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h2 style="margin: 0 0 20px 0; font-size: 20px;">Recent Orders</h2>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #E0E6EF;">
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Order ID</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Customer</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Amount</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Status</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr style="border-bottom: 1px solid #E0E6EF;">
                                <td style="padding: 12px;">#<?php echo e($order->id); ?></td>
                                <td style="padding: 12px;"><?php echo e($order->user->name); ?></td>
                                <td style="padding: 12px;">$<?php echo e(number_format($order->total_amount, 2)); ?></td>
                                <td style="padding: 12px;"><?php echo e(ucfirst($order->status)); ?></td>
                                <td style="padding: 12px;"><?php echo e($order->created_at->format('M d, Y')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" style="padding: 20px; text-align: center; color: #999;">No recent orders</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <a href="<?php echo e(route('admin.products.index')); ?>" style="display: block; padding: 20px; background: white; border-radius: 8px; text-align: center; text-decoration: none; color: #03A9F4; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 2px solid #03A9F4;">Manage Products</a>
            <a href="<?php echo e(route('admin.orders.index')); ?>" style="display: block; padding: 20px; background: white; border-radius: 8px; text-align: center; text-decoration: none; color: #4CAF50; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 2px solid #4CAF50;">Manage Orders</a>
            <a href="<?php echo e(route('admin.users.index')); ?>" style="display: block; padding: 20px; background: white; border-radius: 8px; text-align: center; text-decoration: none; color: #FF9800; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 2px solid #FF9800;">Manage Users</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/codecps/security/laravel-app/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>