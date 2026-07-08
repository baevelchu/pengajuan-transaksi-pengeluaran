<?php $__env->startSection('title', 'Riwayat Pengajuan'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="mb-1 fw-bold"><i class="bi bi-clock-history text-primary me-1"></i> Riwayat Pengajuan Saya</h5>
        <p class="text-muted small mb-0">Pantau status dan riwayat seluruh pengajuan transaksi Anda.</p>
    </div>
    <a href="<?php echo e(route('pengajuan.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Buat Pengajuan</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No. Pengajuan</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th class="text-end">Nilai</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $pengajuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($p->nomor_pengajuan); ?></td>
                            <td><?php echo e($p->tanggal_pengajuan->format('d-m-Y')); ?></td>
                            <td><?php echo e($p->kategori); ?></td>
                            <td class="text-end">Rp <?php echo e(number_format($p->nilai, 0, ',', '.')); ?></td>
                            <td><span class="badge bg-<?php echo e($p->statusBadgeClass()); ?> badge-status"><?php echo e($p->status); ?></span></td>
                            <td>
                                <a href="<?php echo e(route('pengajuan.show', $p)); ?>" class="btn btn-sm btn-outline-secondary">Detail</a>
                                <?php if($p->status === 'Draft'): ?>
                                    <a href="<?php echo e(route('pengajuan.edit', $p)); ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="<?php echo e(route('pengajuan.submit', $p)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Ajukan pengajuan ini sekarang?')">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-primary">Ajukan</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pengajuan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($pengajuans->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\arlia\Downloads\Compressed\pengajuan-transaksi-pengeluaran\resources\views/pengajuan/index.blade.php ENDPATH**/ ?>