<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat - <?php echo e($attempt->id); ?></title>
    <style>
        @page { margin: 0; size: A5 landscape; }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #fff;
            color: #333;
        }

        .cert-border-outer {
            position: fixed; top: 10px; left: 10px; right: 10px; bottom: 10px;
            border: 10px solid #0f172a; padding: 15px; z-index: -2;
        }

        .cert-border-inner {
            position: fixed; top: 22px; left: 22px; right: 22px; bottom: 22px;
            border: 4px double #d4af37; background-color: #fffcf5; z-index: -1;
        }

        .watermark {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 300px; opacity: 0.03; z-index: -1;
        }

        .master-container {
            display: table; width: 100%; height: 100%; position: relative; z-index: 10;
        }

        .content-wrapper {
            display: table-cell; vertical-align: middle; text-align: center; padding: 30px 50px;
        }

        .logo-img { height: 55px; width: auto; margin-bottom: 8px; display: block; margin: 0 auto; }
        .org-name { font-size: 10px; text-transform: uppercase; letter-spacing: 4px; color: #64748b; margin-bottom: 12px; font-weight: bold; }
        
        .cert-title {
            font-size: 22px; color: #d4af37; text-transform: uppercase; letter-spacing: 2px;
            font-weight: bold; margin-bottom: 12px; border-bottom: 2px solid #d4af37;
            display: inline-block; padding-bottom: 4px;
        }

        .presented-to { font-size: 11px; color: #475569; margin-bottom: 4px; }
        .candidate-name { font-size: 26px; color: #0f172a; font-weight: bold; text-transform: uppercase; margin: 4px 0 12px 0; letter-spacing: 1px; }
        .description { font-size: 10px; color: #334155; line-height: 1.5; margin-bottom: 12px; padding: 0 40px; }

        .stats-table { margin: 0 auto 12px auto; width: 75%; border-collapse: collapse; }
        .stats-table th { background-color: #0f172a; color: #d4af37; font-size: 9px; padding: 6px; border: 1px solid #0f172a; }
        .stats-table td { background-color: #fff; border: 1px solid #cbd5e1; padding: 6px; font-weight: bold; font-size: 11px; }

        .footer-table { width: 100%; margin-top: 15px; }
        .footer-left { text-align: left; width: 33%; font-size: 8px; color: #94a3b8; vertical-align: bottom; }
        .footer-center { text-align: center; width: 33%; vertical-align: bottom; }
        .footer-right { text-align: right; width: 33%; vertical-align: bottom; padding-right: 10px;}

        .qr-placeholder { width: 45px; height: 45px; border: 1px dashed #cbd5e1; margin: 0 auto; font-size: 7px; color: #94a3b8; line-height: 45px; background-color: #fff; }
        .signature-line { border-bottom: 1.5px solid #0f172a; width: 120px; margin-left: auto; margin-bottom: 3px; }
        .cert-id { font-family: monospace; background: #f1f5f9; padding: 2px 5px; font-size: 8px; color: #475569; }
    </style>
</head>
<body>

    <?php
        // ENGINE KONVERSI GAMBAR KE BASE64 AGAR PDF TIDAK HANG!
        $logoBase64 = '';
        $logoPath = public_path('images/logo.png');
        if(file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }
    ?>

    <div class="cert-border-outer">
        <div class="cert-border-inner">
            <?php if($logoBase64): ?>
                <img src="<?php echo e($logoBase64); ?>" class="watermark">
            <?php endif; ?>
        </div>
    </div>

    <div class="master-container">
        <div class="content-wrapper">
            
            <?php if($logoBase64): ?>
                <img src="<?php echo e($logoBase64); ?>" class="logo-img">
            <?php endif; ?>
            <div class="org-name">CPNS NUSANTARA LEARNING CENTER</div>

            <div class="cert-title">
                <?php echo e($isPassed ? 'SERTIFIKAT KOMPETENSI' : 'SERTIFIKAT PARTISIPASI'); ?>

            </div>

            <div class="presented-to">Diberikan kepada:</div>
            <div class="candidate-name"><?php echo e($attempt->user->name ?? 'Peserta'); ?></div>
            
            <div class="description">
                Atas partisipasi dan pencapaiannya dalam simulasi <strong><?php echo e($attempt->tryout->title ?? 'Tryout CAT'); ?></strong>.<br>
                Dilaksanakan pada tanggal <?php echo e($attempt->created_at ? $attempt->created_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y')); ?> dengan rincian nilai resmi:
            </div>

            <table class="stats-table">
                <thead>
                    <tr>
                        <th>TWK</th>
                        <th>TIU</th>
                        <th>TKP</th>
                        <th style="background-color: #d4af37; color: #0f172a;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo e($attempt->twk_score ?? 0); ?></td>
                        <td><?php echo e($attempt->tiu_score ?? 0); ?></td>
                        <td><?php echo e($attempt->tkp_score ?? 0); ?></td>
                        <td style="background-color: #fffbeb;"><?php echo e($attempt->total_score ?? 0); ?></td>
                    </tr>
                </tbody>
            </table>

            <table class="footer-table">
                <tr>
                    <td class="footer-left">
                        <span class="cert-id">ID: CERT-<?php echo e($attempt->id ?? '00'); ?>-<?php echo e(date('Y')); ?></span><br><br>
                        Dokumen digital sah.<br>Terautentikasi sistem.
                    </td>
                    <td class="footer-center">
                        <div class="qr-placeholder">QR CODE</div>
                    </td>
                    <td class="footer-right">
                        <div style="font-size: 9px; margin-bottom: 35px;">
                            Palembang, <?php echo e(now()->translatedFormat('d F Y')); ?><br>
                            <strong>Direktur Akademik</strong>
                        </div>
                        <div class="signature-line"></div>
                        <div style="font-weight: bold; font-size: 10px;">Admin Pusat</div>
                    </td>
                </tr>
            </table>

        </div>
    </div>

</body>
</html><?php /**PATH /home/tyasvara/ToV2/resources/views/pdf/certificate.blade.php ENDPATH**/ ?>