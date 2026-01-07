<?php $__env->startSection('content'); ?>
<div class="container mx-auto py-8 px-4">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">Products Management</h1>
            <p class="text-gray-600 mt-2">Manage products and generate barcodes for POS system</p>
        </div>
        <a href="<?php echo e(route('admin.products.create')); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
            + Create Product
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

    <?php if(count($flattened) > 0): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Image</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Barcode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__currentLoopData = $flattened; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php ($item = $row['item']); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if(!empty($item['image'])): ?>
                                    <img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>" class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">No image</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900"><?php echo e($item['name']); ?></div>
                                <div class="text-sm text-gray-600"><?php echo e(substr($item['description'] ?? '', 0, 50)); ?>...</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if(!empty($item['barcode'])): ?>
                                    <code class="bg-gray-100 px-3 py-1 rounded text-sm font-mono"><?php echo e($item['barcode']); ?></code>
                                    <?php if(!empty($item['id']) && !empty($item['solution_id'])): ?>
                                        <div class="mt-2 space-x-2">
                                            <a href="<?php echo e(route('barcode.download', ['solutionItem' => $item['id']])); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">Download</a>
                                            <a href="<?php echo e(route('barcode.print', ['solutionItem' => $item['id']])); ?>" class="text-green-600 hover:text-green-800 text-sm font-semibold" target="_blank">Print</a>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?php echo e($row['category']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold"><?php echo e($item['price'] ?? '₦0.00'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php ($stock = $item['stock'] ?? 0); ?>
                                <span class="px-3 py-1 rounded text-sm font-semibold <?php if($stock > 0): ?> bg-green-100 text-green-800 <?php else: ?> bg-red-100 text-red-800 <?php endif; ?>">
                                    <?php echo e($stock > 0 ? $stock . ' in stock' : 'Sold Out'); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <?php if(!empty($item['id']) && !empty($item['solution_id'])): ?>
                                    <a href="<?php echo e(route('admin.solutions.items.edit', [$row['solution_id'], $item['id']])); ?>" class="text-blue-600 hover:text-blue-800 font-semibold">Edit</a>
                                    <form action="<?php echo e(route('admin.solutions.items.destroy', [$row['solution_id'], $item['id']])); ?>" method="POST" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold" onclick="return confirm('Delete this product?')">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-gray-400">View only</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
            <p class="text-blue-700 font-semibold">No products yet</p>
            <p class="text-blue-600 mt-2">Create your first product to get started. Products will appear in the POS system for sales.</p>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/products/index.blade.php ENDPATH**/ ?>