<?php $__env->startSection('content'); ?>
<div class="container mx-auto py-8 px-4">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Users Management</h2>
            <p class="text-gray-600">Manage approved users and their roles</p>
        </div>
        <?php if($pendingCount > 0): ?>
            <a href="<?php echo e(route('admin.users.pending')); ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded font-semibold transition flex items-center gap-2">
                ⚠️ <?php echo e($pendingCount); ?> Pending Approval
            </a>
        <?php endif; ?>
    </div>

    <?php if(session('success')): ?>
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($approvedUsers->count() > 0): ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Role</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Joined</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__currentLoopData = $approvedUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-gray-900 font-medium"><?php echo e($user->name); ?></span>
                                <?php if($user->id === auth()->id()): ?>
                                    <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">You</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?php echo e($user->email); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($user->isAdmin()): ?>
                                    <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">👑 Admin</span>
                                <?php elseif($user->isPOS()): ?>
                                    <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">🛒 POS</span>
                                <?php else: ?>
                                    <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold">None</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded text-xs font-semibold">✓ Approved</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo e($user->created_at->format('M d, Y')); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if($user->id !== auth()->id()): ?>
                                    <form action="<?php echo e(route('admin.users.delete', $user)); ?>" method="POST" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold transition" onclick="return confirm('Delete user <?php echo e($user->name); ?>?');">
                                            Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <?php echo e($approvedUsers->links()); ?>

        </div>
    <?php else: ?>
        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
            <p class="text-blue-700 font-semibold">No approved users yet</p>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/users/index.blade.php ENDPATH**/ ?>