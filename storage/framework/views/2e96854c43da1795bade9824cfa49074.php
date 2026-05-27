<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Ranking Affiliate</title>
    <style>
        /* Pengaturan Dasar */
        @page { margin: 2cm; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: #0F172A; 
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* Header Laporan */
        .header { 
            text-align: center; 
            margin-bottom: 40px; 
            border-bottom: 3px solid #0F172A;
            padding-bottom: 20px;
        }
        .header h1 { 
            text-transform: uppercase; 
            font-weight: 900; 
            margin: 0; 
            font-size: 24px;
            letter-spacing: 2px;
        }
        .header p { 
            font-size: 10px; 
            font-weight: bold; 
            text-transform: uppercase; 
            color: #64748B;
            margin-top: 5px;
            letter-spacing: 1px;
        }

        /* Ringkasan Data */
        .summary {
            margin-bottom: 30px;
            width: 100%;
        }
        .summary td {
            padding: 10px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            text-align: center;
        }
        .summary-label {
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            color: #64748B;
            display: block;
        }
        .summary-value {
            font-size: 16px;
            font-weight: 900;
            color: #0F172A;
        }

        /* Tabel Data */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }
        th { 
            background-color: #0F172A; 
            color: #FFFFFF; 
            text-transform: uppercase; 
            font-size: 10px; 
            font-weight: 900; 
            padding: 12px 10px;
            text-align: left;
            letter-spacing: 1px;
        }
        td { 
            padding: 12px 10px; 
            border-bottom: 1px solid #E2E8F0; 
            font-size: 11px;
            font-weight: bold;
        }

        /* Penanda Ranking Top 3 */
        .rank-top { background-color: #F1F5F9; }
        .rank-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 900;
        }

        /* Footer PDF */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 8px;
            font-weight: bold;
            color: #94A3B8;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Ranking Affiliate</h1>
        <p>Nusantara CPNS Academy • Periode: <?php echo e($period); ?></p>
    </div>

    <table class="summary">
        <tr>
            <td>
                <span class="summary-label">Total Referal Periode Ini</span>
                <span class="summary-value"><?php echo e($total_referrals); ?> User</span>
            </td>
            <td>
                <span class="summary-label">Total Komisi Sistem</span>
                <span class="summary-value">Rp <?php echo e(number_format($total_commission, 0, ',', '.')); ?></span>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 50px; text-align: center;">Rank</th>
                <th>Nama Partner</th>
                <th>Kode Voucher</th>
                <th style="text-align: center;">Referal</th>
                <th style="text-align: right;">Total Komisi</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="<?php echo e($index < 3 ? 'rank-top' : ''); ?>">
                <td style="text-align: center;">
                    <span class="rank-badge"><?php echo e($index + 1); ?></span>
                </td>
                <td><?php echo e(strtoupper($row->referrer->name)); ?></td>
                <td><strong><?php echo e($row->referrer->affiliate_code); ?></strong></td>
                <td style="text-align: center;"><?php echo e($row->total_ref); ?></td>
                <td style="text-align: right;">Rp <?php echo e(number_format($row->total_comm, 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: <?php echo e($date); ?> • Dokumen Resmi Nusantara Academy
    </div>

</body>
</html><?php /**PATH /home/tyasvara/ToV2/resources/views/pdf/affiliate-leaderboard.blade.php ENDPATH**/ ?>