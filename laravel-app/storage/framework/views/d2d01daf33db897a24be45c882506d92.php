<?php $__env->startSection('title', 'Categories - Admin - ARTSCI'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-container">
    <?php echo $__env->make('admin.partials.sidebar', ['active' => 'categories'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <main class="admin-main">
        <div class="admin-header">
            <div class="admin-header-left">
                <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Solution Categories</h1>
            </div>
            <a href="<?php echo e(route('categories.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Category
            </a>
        </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="products-table">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Items</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>#<?php echo e($category->id); ?></td>
                        <td><strong><?php echo e($category->name); ?></strong></td>
                        <td>
                            <span class="badge <?php echo e($category->active ? 'badge-success' : 'badge-warning'); ?>">
                                <?php echo e($category->active ? 'Active' : 'Inactive'); ?>

                            </span>
                        </td>
                        <td><?php echo e($category->items()->count()); ?></td>
                        <td>
                            <a href="<?php echo e(route('categories.edit', $category)); ?>" class="btn btn-sm btn-edit">Edit</a>
                            <form action="<?php echo e(route('categories.destroy', $category)); ?>" method="POST" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px;">
                            No categories found. <a href="<?php echo e(route('categories.create')); ?>">Create one</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <?php echo e($categories->links()); ?>

    </div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/codecps/security/laravel-app/resources/views/admin/categories/index.blade.php ENDPATH**/ ?>