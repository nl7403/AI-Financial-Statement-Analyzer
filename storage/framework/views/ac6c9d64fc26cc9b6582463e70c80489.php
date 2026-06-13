<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Financial Health Report
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <?php
                    $health = $report['overall_health'] ?? 'unknown';
                    $healthColor = match($health) {
                        'healthy' => 'bg-green-100 text-green-800',
                        'watch' => 'bg-yellow-100 text-yellow-800',
                        'concern' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                ?>
                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold <?php echo e($healthColor); ?>">
                    Overall: <?php echo e(ucfirst($health)); ?>

                </span>
                <p class="mt-4 text-gray-700"><?php echo e($report['summary'] ?? ''); ?></p>
            </div>

            <?php if(!empty($report['ratios'])): ?>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Key Ratios</h3>
                    <div class="space-y-4">
                        <?php $__currentLoopData = $report['ratios']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ratio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $sev = $ratio['severity'] ?? 'green';
                                $badge = match($sev) {
                                    'green' => 'bg-green-100 text-green-800',
                                    'yellow' => 'bg-yellow-100 text-yellow-800',
                                    'red' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            ?>
                            <div class="border-b pb-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium"><?php echo e($ratio['name'] ?? ''); ?></span>
                                    <span class="px-2 py-1 rounded text-sm font-semibold <?php echo e($badge); ?>">
                                        <?php echo e($ratio['value'] ?? ''); ?>

                                    </span>
                                </div>
                                <p class="text-gray-600 text-sm mt-1"><?php echo e($ratio['finding'] ?? ''); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(!empty($report['anomalies'])): ?>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Warning Signs</h3>
                    <ul class="list-disc list-inside text-gray-700 space-y-1">
                        <?php $__currentLoopData = $report['anomalies']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anomaly): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($anomaly); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if(!empty($report['recommendations'])): ?>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Recommendations</h3>
                    <ul class="list-disc list-inside text-gray-700 space-y-1">
                        <?php $__currentLoopData = $report['recommendations']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($rec); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <a href="<?php echo e(route('analyses.create')); ?>" class="inline-block bg-gray-800 text-white px-4 py-2 rounded">
                Run Another Analysis
            </a>

        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\nlica\Herd\AI-Financial-Statement-Analyzer\resources\views/analyses/show.blade.php ENDPATH**/ ?>