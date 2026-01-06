<?php $__env->startSection('content'); ?>
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-12">
            <a href="/" class="btn btn-outline-secondary">← Back to Shop</a>
        </div>
    </div>

    <div class="row">
        <!-- Product Image -->
        <div class="col-md-6 mb-4">
            <?php if($product->image): ?>
                <img src="<?php echo e($product->image); ?>" alt="<?php echo e($product->name); ?>" class="img-fluid rounded" style="max-height: 500px; object-fit: cover;">
            <?php else: ?>
                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 500px;">
                    <i class="fas fa-image" style="font-size: 100px; color: #ccc;"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Details -->
        <div class="col-md-6">
            <h1 class="mb-2"><?php echo e($product->name); ?></h1>
            
            <?php if($product->category): ?>
                <p class="text-muted mb-3">
                    <small>Category: <strong><?php echo e($product->category); ?></strong></small>
                </p>
            <?php endif; ?>

            <div class="mb-4">
                <h3 class="text-primary">$<?php echo e(number_format($product->price, 2)); ?></h3>
            </div>

            <div class="mb-4">
                <p class="lead"><?php echo e($product->description); ?></p>
            </div>

            <!-- Stock Status -->
            <div class="mb-4">
                <?php if($product->stock > 0): ?>
                    <p class="text-success">
                        <i class="fas fa-check-circle"></i> In Stock (<?php echo e($product->stock); ?> available)
                    </p>
                <?php else: ?>
                    <p class="text-danger">
                        <i class="fas fa-times-circle"></i> Out of Stock
                    </p>
                <?php endif; ?>
            </div>

            <!-- Add to Cart Form -->
            <?php if(auth()->guard()->check()): ?>
                <?php if($product->stock > 0): ?>
                    <form action="<?php echo e(route('cart.add', $product->id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="input-group mb-3" style="max-width: 200px;">
                            <label class="input-group-text">Quantity:</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?php echo e($product->stock); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg" disabled>
                        <i class="fas fa-ban"></i> Out of Stock
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-muted mb-3">
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-primary btn-lg">Login to Purchase</a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Products (Optional) -->
    <?php if($product->category): ?>
        <hr class="my-5">
        <div class="row">
            <div class="col-12">
                <h3 class="mb-4">More from <?php echo e($product->category); ?></h3>
            </div>
            <?php $__currentLoopData = \App\Models\Product::where('category', $product->category)->where('id', '!=', $product->id)->limit(4)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <?php if($related->image): ?>
                            <img src="<?php echo e($related->image); ?>" class="card-img-top" alt="<?php echo e($related->name); ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image" style="font-size: 50px; color: #ccc;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo e($related->name); ?></h5>
                            <p class="card-text text-primary">$<?php echo e(number_format($related->price, 2)); ?></p>
                        </div>
                        <div class="card-footer">
                            <a href="<?php echo e(route('products.show', $related->id)); ?>" class="btn btn-sm btn-outline-primary w-100">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>

<style>
.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/shop/show.blade.php ENDPATH**/ ?>