<?php $__env->startSection('title', 'Products - Admin - ARTSCI'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-container">
    <?php echo $__env->make('admin.partials.sidebar', ['active' => 'products'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <main class="admin-main">
        <div class="admin-header">
            <div class="admin-header-left">
                <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Products Management</h1>
            </div>
            <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>

        <?php if($products->count() > 0): ?>
            <div class="products-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>#<?php echo e($product->id); ?></td>
                                <td><strong><?php echo e($product->name); ?></strong></td>
                                <td><?php echo e($product->category ?? 'N/A'); ?></td>
                                <td>$<?php echo e(number_format($product->price, 2)); ?></td>
                                <td>
                                    <span class="stock-badge <?php echo e($product->stock > 0 ? 'in-stock' : 'out-of-stock'); ?>">
                                        <?php echo e($product->stock); ?>

                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('admin.products.edit', $product)); ?>" class="btn btn-sm btn-edit">Edit</a>
                                    <form action="<?php echo e(route('admin.products.delete', $product)); ?>" method="POST" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                <?php echo e($products->links()); ?>

            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No products found. <a href="<?php echo e(route('admin.products.create')); ?>">Create one</a></p>
            </div>
        <?php endif; ?>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/codecps/security/laravel-app/resources/views/admin/products/index.blade.php ENDPATH**/ ?>