<?php $__env->startSection('title', 'Antrian Finance'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Antrian Finance</h4>
    <div class="card">
        <div class="card-body py-2 px-3">
            <span class="text-muted small">Saldo Perusahaan</span><br>
            <strong class="fs-5">Rp <?php echo e(number_format($saldo->saldo, 0, ',', '.')); ?></strong>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No. Pengajuan</th>
                        <th>Pengaju</th>
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
                            <td><?php echo e($p->user->name); ?></td>
                            <td><?php echo e($p->kategori); ?></td>
                            <td class="text-end">Rp <?php echo e(number_format($p->nilai, 0, ',', '.')); ?></td>
                            <td><span class="badge bg-<?php echo e($p->statusBadgeClass()); ?>"><?php echo e($p->status); ?></span></td>
                            <td><a href="<?php echo e(route('finance.show', $p)); ?>" class="btn btn-sm btn-primary">Proses</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada pengajuan yang menunggu proses Finance.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($pengajuans->links()); ?>

    </div>
</div>

<h5 class="mb-3">Riwayat Transaksi</h5>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>No. Pengajuan</th><th>Pengaju</th><th class="text-end">Nilai</th><th>Status</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $riwayat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a href="<?php echo e(route('pengajuan.show', $r)); ?>"><?php echo e($r->nomor_pengajuan); ?></a></td>
                            <td><?php echo e($r->user->name); ?></td>
                            <td class="text-end">Rp <?php echo e(number_format($r->nilai, 0, ',', '.')); ?></td>
                            <td><span class="badge bg-<?php echo e($r->statusBadgeClass()); ?>"><?php echo e($r->status); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-muted text-center">Belum ada riwayat transaksi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\arlia\Downloads\Compressed\pengajuan-transaksi-pengeluaran\resources\views/finance/index.blade.php ENDPATH**/ ?>