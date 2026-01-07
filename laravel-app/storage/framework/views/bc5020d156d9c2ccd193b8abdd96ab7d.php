<?php $__env->startSection('content'); ?>
<div class="container mx-auto py-8 px-4">
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Pending User Registrations</h2>
        <p class="text-gray-600">Review and approve new user registrations. Assign role (Admin or POS) to activate accounts.</p>
    </div>

    <?php if(session('success')): ?>
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if($pendingUsers->count() > 0): ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Registered</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__currentLoopData = $pendingUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-gray-900 font-medium"><?php echo e($user->name); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?php echo e($user->email); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo e($user->created_at->format('M d, Y H:i')); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <!-- Approve as POS -->
                                <form action="<?php echo e(route('admin.users.approve', ['user' => $user, 'role' => 'pos'])); ?>" method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-semibold transition">
                                        ✓ Approve as POS
                                    </button>
                                </form>

                                <!-- Approve as Admin -->
                                <form action="<?php echo e(route('admin.users.approve', ['user' => $user, 'role' => 'admin'])); ?>" method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-semibold transition" onclick="return confirm('Grant admin access to <?php echo e($user->name); ?>?');">
                                        👑 Approve as Admin
                                    </button>
                                </form>

                                <!-- Reject -->
                                <form action="<?php echo e(route('admin.users.reject', $user)); ?>" method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold transition" onclick="return confirm('Reject and delete <?php echo e($user->name); ?>?');">
                                        ✕ Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <?php echo e($pendingUsers->links()); ?>

        </div>
    <?php else: ?>
        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
            <p class="text-blue-700 font-semibold">✓ No pending registrations</p>
            <p class="text-blue-600 text-sm mt-2">All user registrations have been reviewed and processed.</p>
        </div>
    <?php endif; ?>

    <div class="mt-8 pt-6 border-t border-gray-200">
        <a href="<?php echo e(route('admin.users.index')); ?>" class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded font-semibold transition">
            ← Back to All Users
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/users/pending.blade.php ENDPATH**/ ?>