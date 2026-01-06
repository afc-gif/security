<?php $__env->startSection('title', 'Products - Admin - ARTSCI'); ?>

<?php $__env->startSection('extra-css'); ?>
<style>
    .solution-sync-card {
        background: white;
        border: 1px solid #E0E6EF;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.04);
    }
    .solution-category-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 10px;
    }
    .solution-chip {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        background: #F0F4FF;
        color: #0366d6;
        font-weight: 600;
        font-size: 12px;
    }
    .solution-item-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 0;
        border-top: 1px solid #EEF1F7;
    }
    .solution-item-row:first-child {
        border-top: none;
    }
    .solution-item-name {
        font-weight: 700;
        color: #111827;
    }
    .solution-item-desc {
        color: #4B5563;
        font-size: 14px;
        margin: 6px 0;
    }
    .solution-specs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .solution-specs span {
        background: #F3F4F6;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 12px;
        color: #374151;
    }
    .solution-price {
        min-width: 140px;
        text-align: right;
        font-weight: 700;
        color: #0F766E;
    }
    .solution-meta {
        color: #6B7280;
        font-size: 13px;
    }
</style>
<?php $__env->stopSection(); ?>

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

        <?php
            $flattened = [];
            $rowId = 1;
            foreach ($solutionProducts as $category) {
                foreach ($category['items'] as $item) {
                    $flattened[] = [
                        'row_id' => $rowId++,
                        'category' => $category['title'] ?? 'Uncategorized',
                        'solution_id' => $category['id'] ?? null,
                        'item' => $item,
                    ];
                }
            }
        ?>

        <div class="solution-category-header">
            <div>
                <h2 style="margin: 0 0 6px 0;">Solutions Catalog (DB)</h2>
                <p class="solution-meta">Source: database solutions & items (manage under Admin → Solutions). Each item has ID + barcode for POS.</p>
            </div>
            <span class="solution-chip"><?php echo e(count($solutionProducts ?? [])); ?> categories</span>
        </div>

        <?php if(count($flattened) > 0): ?>
            <div class="products-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>ID</th>
                            <th>Barcode</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $flattened; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($item = $row['item']); ?>
                            <tr>
                                <td>
                                    <?php if(!empty($item['image'])): ?>
                                        <img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #E5E7EB;">
                                    <?php else: ?>
                                        <span class="solution-meta">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td>#<?php echo e($row['row_id']); ?></td>
                                <td>
                                    <?php if(!empty($item['barcode'])): ?>
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <code style="font-size: 12px; background: #f3f4f6; padding: 4px 8px; border-radius: 4px;"><?php echo e($item['barcode']); ?></code>
                                            <?php if(!empty($item['id']) && !empty($item['solution_id'])): ?>
                                                <a href="<?php echo e(route('barcode.download', ['solutionItem' => $item['id']])); ?>" class="btn btn-sm" style="background: #0366d6; color: white; padding: 4px 8px; text-decoration: none; border-radius: 4px; font-size: 12px;" title="Download barcode image">
                                                    <i class="fas fa-download"></i> PNG
                                                </a>
                                                <a href="<?php echo e(route('barcode.print', ['solutionItem' => $item['id']])); ?>" class="btn btn-sm" style="background: #28a745; color: white; padding: 4px 8px; text-decoration: none; border-radius: 4px; font-size: 12px;" title="Print barcode label" target="_blank">
                                                    <i class="fas fa-print"></i> Print
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="solution-meta">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo e($item['name']); ?></strong></td>
                                <td><?php echo e($row['category']); ?></td>
                                <td><?php echo e($item['price'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php ($stock = $item['stock'] ?? 0); ?>
                                    <span class="stock-badge <?php echo e($stock > 0 ? 'in-stock' : 'out-of-stock'); ?>">
                                        <?php echo e($stock > 0 ? $stock : 'Sold Out'); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if(!empty($item['id']) && !empty($item['solution_id'])): ?>
                                        <a href="<?php echo e(route('admin.solutions.items.edit', [$row['solution_id'], $item['id']])); ?>" class="btn btn-sm btn-edit" style="margin-right:6px;">Edit</a>
                                        <form action="<?php echo e(route('admin.solutions.items.destroy', [$row['solution_id'], $item['id']])); ?>" method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="solution-meta">View only</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No products found in <code>public/solutions.html</code>. Add cards there to display here.</p>
            </div>
        <?php endif; ?>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/products/index.blade.php ENDPATH**/ ?>