<!DOCTYPE html>
<html>
<head>
    <title>Sertifikat - {{ $attempt->id }}</title>
    <style>
        /* 1. PAGE SETUP */
        @page { margin: 0; size: A5 landscape; }
        
        html, body {
            height: 100%; /* Penting untuk vertical centering */
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #fff;
            color: #333;
        }

        /* 2. FRAME BORDER (Tetap Fixed) */
        .cert-border-outer {
            position: fixed;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 10px solid #0f172a;
            padding: 15px;
            z-index: -2;
        }

        .cert-border-inner {
            position: fixed;
            top: 22px;
            left: 22px;
            right: 22px;
            bottom: 22px;
            border: 4px double #d4af37;
            background-color: #fffcf5; 
            z-index: -1;
        }

        .watermark {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            opacity: 0.05;
            z-index: -1;
        }

        /* 3. VERTICAL & HORIZONTAL CENTERING ENGINE */
        .master-container {
            display: table;
            width: 100%;
            height: 100%; /* Mengisi seluruh halaman A5 */
            position: relative;
            z-index: 10;
        }

        .content-wrapper {
            display: table-cell;
            vertical-align: middle; /* Vertical Center */
            text-align: center;    /* Horizontal Center */
            padding: 40px 60px;
        }

        /* 4. HEADER & LOGO */
        .logo-img {
            height: 70px; /* Ukuran logo sedikit diperbesar agar lebih proporsional */
            width: auto;
            margin-bottom: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .org-name {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: #64748b;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .cert-title {
            font-size: 24px;
            color: #d4af37;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 2px solid #d4af37;
            display: inline-block;
            padding-bottom: 5px;
        }

        /* 5. BODY */
        .presented-to {
            font-size: 11px;
            color: #475569;
            margin-bottom: 5px;
        }

        .candidate-name {
            font-size: 28px;
            color: #0f172a;
            font-weight: bold;
            text-transform: uppercase;
            margin: 5px 0 15px 0;
            letter-spacing: 1px;
        }

        .description {
            font-size: 10px;
            color: #334155;
            line-height: 1.5;
            margin-bottom: 15px;
            padding: 0 50px;
        }

        /* 6. STATS TABLE */
        .stats-table {
            margin: 0 auto 15px auto;
            width: 80%; /* Dikecilkan sedikit agar lebih rapi di tengah */
            border-collapse: collapse;
        }

        .stats-table th {
            background-color: #0f172a;
            color: #d4af37;
            font-size: 9px;
            padding: 8px;
            border: 1px solid #0f172a;
        }

        .stats-table td {
            background-color: #fff;
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-weight: bold;
            font-size: 12px;
        }

        /* 7. FOOTER */
        .footer-table { 
            width: 100%; 
            margin-top: 20px; 
        }
        
        .footer-left { text-align: left; width: 33%; font-size: 8px; color: #94a3b8; vertical-align: bottom; }
        .footer-center { text-align: center; width: 33%; vertical-align: bottom; }
        .footer-right { text-align: right; width: 33%; vertical-align: bottom; padding-right: 10px;}

        .qr-code { width: 50px; border: 1px solid #cbd5e1; }
        .signature-line { border-bottom: 1.5px solid #0f172a; width: 120px; margin-left: auto; margin-bottom: 3px; }
        .cert-id { font-family: monospace; background: #f1f5f9; padding: 2px 5px; font-size: 8px; }
    </style>
</head>
<body>

    <div class="cert-border-outer">
        <div class="cert-border-inner">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" class="watermark">
            @endif
        </div>
    </div>

    <div class="master-container">
        <div class="content-wrapper">
            
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" class="logo-img">
            @endif
            <div class="org-name">CPNS NUSANTARA LEARNING CENTER</div>

            <div class="cert-title">
                {{ $isPassed ? 'SERTIFIKAT KOMPETENSI' : 'SERTIFIKAT PARTISIPASI' }}
            </div>

            <div class="presented-to">Diberikan kepada:</div>
            <div class="candidate-name">{{ $attempt->user->name }}</div>
            
            <div class="description">
                Atas partisipasi dan pencapaiannya dalam simulasi <strong>{{ $attempt->tryout->title }}</strong>.<br>
                Dilaksanakan pada tanggal {{ $attempt->created_at->translatedFormat('d F Y') }} dengan rincian nilai:
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
                        <td>{{ $attempt->twk_score }}</td>
                        <td>{{ $attempt->tiu_score }}</td>
                        <td>{{ $attempt->tkp_score }}</td>
                        <td style="background-color: #fffbeb;">{{ $attempt->total_score }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="footer-table">
                <tr>
                    <td class="footer-left">
                        <span class="cert-id">ID: CERT-{{ $attempt->id }}-{{ date('Y') }}</span><br><br>
                        Dokumen digital sah.<br>Verifikasi via QR Code.
                    </td>
                    
                    <td class="footer-center">
                         @php
                            $qrData = route('tryout.leaderboard', $attempt->tryout_id);
                            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qrData);
                            $base64Qr = ''; 
                            try {
                                $imageContent = file_get_contents($qrUrl);
                                if ($imageContent !== false) {
                                    $base64Qr = 'data:image/png;base64,' . base64_encode($imageContent);
                                }
                            } catch (\Exception $e) {}
                         @endphp
                         @if($base64Qr)
                             <img src="{{ $base64Qr }}" class="qr-code">
                         @endif
                    </td>

                    <td class="footer-right">
                        <div style="font-size: 9px; margin-bottom: 40px;">
                            Palembang, {{ now()->translatedFormat('d F Y') }}<br>
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
</html>