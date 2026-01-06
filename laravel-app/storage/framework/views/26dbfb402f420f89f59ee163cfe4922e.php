<?php $__env->startSection('title', 'Solutions/Categories - Admin - ARTSCI'); ?>

<?php $__env->startSection('extra-css'); ?>
<style>
    .solution-card {
        background: white;
        border: 1px solid #E0E6EF;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }

    .solution-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        border-color: #03A9F4;
    }

    .solution-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 12px;
    }

    .solution-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .solution-title h3 {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .solution-icon {
        font-size: 28px;
        line-height: 1;
    }

    .solution-description {
        color: #6B7280;
        font-size: 14px;
        margin: 8px 0 0 0;
    }

    .solution-meta {
        display: flex;
        gap: 24px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #E5E7EB;
        font-size: 13px;
        color: #6B7280;
    }

    .status-badge {
        font-weight: 600;
    }

    .status-active {
        color: #10B981;
    }

    .status-inactive {
        color: #EF4444;
    }

    .solution-actions {
        display: flex;
        gap: 8px;
    }

    .btn-icon {
        padding: 8px 12px;
        font-size: 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 600;
    }

    .btn-view {
        background: #E0F2FE;
        color: #0369A1;
    }

    .btn-view:hover {
        background: #0369A1;
        color: white;
    }

    .btn-edit {
        background: #DBEAFE;
        color: #1D4ED8;
    }

    .btn-edit:hover {
        background: #1D4ED8;
        color: white;
    }

    .btn-delete {
        background: #FEE2E2;
        color: #991B1B;
    }

    .btn-delete:hover {
        background: #991B1B;
        color: white;
    }

    .empty-state {
        background: #FFFBEB;
        border: 1px solid #FCD34D;
        border-radius: 8px;
        padding: 32px;
        text-align: center;
        color: #92400E;
    }

    .empty-state h2 {
        margin-top: 0;
        margin-bottom: 12px;
        font-size: 18px;
        font-weight: 600;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-container">
    <?php echo $__env->make('admin.partials.sidebar', ['active' => 'solutions'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <main class="admin-main">
        <div class="admin-header">
            <div class="admin-header-left">
                <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Categories Management</h1>
            </div>
            <a href="<?php echo e(route('admin.solutions.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Category
            </a>
        </div>

        <?php if(session('success')): ?>
            <div style="background: #D1FAE5; border: 1px solid #6EE7B7; border-radius: 8px; color: #065F46; padding: 12px 16px; margin-bottom: 24px;">
                <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div style="max-width: 100%;">
            <?php $__empty_1 = true; $__currentLoopData = $solutions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $solution): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="solution-card">
                    <div class="solution-header">
                        <div style="flex: 1;">
                            <div class="solution-title">
                                <span class="solution-icon"><?php echo e($solution->icon); ?></span>
                                <h3><?php echo e($solution->name); ?></h3>
                            </div>
                            <?php if($solution->description): ?>
                                <p class="solution-description"><?php echo e($solution->description); ?></p>
                            <?php endif; ?>
                            <div class="solution-meta">
                                <span><strong>Products:</strong> <?php echo e($solution->items->count()); ?></span>
                                <span><strong>Status:</strong> <span class="status-badge <?php echo e($solution->active ? 'status-active' : 'status-inactive'); ?>"><?php echo e($solution->active ? 'Active' : 'Inactive'); ?></span></span>
                                <span><strong>Order:</strong> <?php echo e($solution->sort_order ?? '—'); ?></span>
                            </div>
                        </div>
                        <div class="solution-actions">
                            <a href="<?php echo e(route('admin.solutions.show', $solution)); ?>" class="btn-icon btn-view" title="View products in this category">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="<?php echo e(route('admin.solutions.edit', $solution)); ?>" class="btn-icon btn-edit" title="Edit category">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="<?php echo e(route('admin.solutions.destroy', $solution)); ?>" method="POST" style="display:inline;" onsubmit="return confirm('Delete this category? This action cannot be undone.');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn-icon btn-delete" title="Delete category">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state">
                    <h2><i class="fas fa-inbox"></i> No Categories Found</h2>
                    <p>Get started by creating your first product category.</p>
                    <a href="<?php echo e(route('admin.solutions.create')); ?>" class="btn btn-primary" style="display: inline-block; margin-top: 12px;">
                        <i class="fas fa-plus"></i> Create Category
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/solutions/index.blade.php ENDPATH**/ ?>