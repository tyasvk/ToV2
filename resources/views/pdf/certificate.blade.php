<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Resmi - {{ $attempt->user->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;900&family=Montserrat:wght@400;500;700&family=Great+Vibes&display=swap" rel="stylesheet">
    
    <style>
        /* Konfigurasi Ukuran Halaman A4 Landscape */
        @page { 
            size: A4 landscape; 
            margin: 0; 
        }
        
        html, body {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 209mm;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Pembungkus Utama Sertifikat */
        .certificate-wrapper {
            width: 100%;
            height: 100%;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
            
            /* Tekstur latar belakang kertas piagam linen premium yang sangat halus */
            background-color: #fcfaf2;
            background-image: 
                radial-gradient(rgba(212, 175, 55, 0.03) 1px, transparent 0), 
                linear-gradient(to right, rgba(244, 240, 230, 0.4) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(244, 240, 230, 0.4) 1px, transparent 1px);
            background-size: 16px 16px, 20mm 20mm, 20mm 20mm;
            background-position: 0 0, center center, center center;
        }

        /* --- ORNAMEN BINGKAI PREMIUM MULTI-LAYER --- */
        /* Bingkai Luar Tebal Navy */
        .border-outer {
            position: absolute;
            top: 12mm; left: 12mm; right: 12mm; bottom: 12mm;
            border: 4px solid #0f172a;
            pointer-events: none;
            z-index: 5;
        }
        /* Garis Tipis Gold Tengah */
        .border-middle {
            position: absolute;
            top: 14.5mm; left: 14.5mm; right: 14.5mm; bottom: 14.5mm;
            border: 1px solid #d4af37;
            pointer-events: none;
            z-index: 5;
        }
        /* Bingkai Dalam Bermotif Sudut Klasik */
        .border-inner {
            position: absolute;
            top: 17mm; left: 17mm; right: 17mm; bottom: 17mm;
            border: 2px dashed #0f172a;
            pointer-events: none;
            z-index: 5;
        }
        /* Ornamen Aksen di Empat Sudut Bingkai */
        .corner-pattern {
            position: absolute;
            width: 25px;
            height: 25px;
            border: 3px solid #d4af37;
            z-index: 6;
        }
        .top-left  { top: 18mm; left: 18mm; border-right: none; border-bottom: none; }
        .top-right { top: 18mm; right: 18mm; border-left: none; border-bottom: none; }
        .bottom-left  { bottom: 18mm; left: 18mm; border-right: none; border-top: none; }
        .bottom-right { bottom: 18mm; right: 18mm; border-left: none; border-top: none; }

        /* --- WATERMARK BACKGROUND --- */
        .watermark {
            position: absolute;
            top: 50%; 
            left: 50%;
            transform: translate(-50%, -50%);
            width: 440px; 
            opacity: 0.05; /* Tetap terkunci lembut di 5% */
            z-index: 2;
            pointer-events: none;
            filter: grayscale(100%) contrast(110%);
        }

        /* --- ZONA AMAN KONTEN (Anti Menabrak Garis) --- */
        .safe-zone {
            position: absolute;
            top: 26mm; left: 26mm; right: 26mm; bottom: 26mm;
            z-index: 10;
            display: flex;
            flex-direction: column;
            justify-content: space-between; 
            align-items: center;
            box-sizing: border-box;
        }

        /* --- 1. BAGIAN ATAS (Header) --- */
        .header-section {
            text-align: center;
            width: 100%;
        }
        .logo-img {
            height: 95px; 
            width: auto;
            margin-bottom: 12px;
        }
        .org-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 6px;
            color: #475569;
            text-transform: uppercase;
            margin: 0;
        }

        /* --- 2. BAGIAN TENGAH (Isi Utama) --- */
        .body-section {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        .cert-label {
            font-family: 'Cinzel', serif;
            font-size: 40px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 5px;
        }
        .cert-sub-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: #d4af37;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-top: 5px;
        }
        .divider-premium {
            width: 150px;
            height: 1px;
            background: linear-gradient(to right, transparent, #d4af37, #0f172a, #d4af37, transparent);
            margin: 12px 0;
        }
        .presented-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            color: #64748b;
            margin: 0 0 6px 0;
            letter-spacing: 1px;
        }
        .candidate-name {
            font-family: 'Cinzel', serif;
            font-size: 44px;
            font-weight: 900;
            color: #0f172a;
            margin: 5px 0 12px 0;
            display: inline-block;
            letter-spacing: 2px;
            text-shadow: 0.5px 0.5px 0px rgba(212,175,55,0.4);
        }
        .name-underline {
            width: 450px;
            height: 1px;
            background: linear-gradient(to right, transparent, #cbd5e1, transparent);
            margin-top: -8px;
            margin-bottom: 12px;
        }
        .description {
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            color: #334155;
            line-height: 1.6;
            margin: 0 0 15px 0;
            max-width: 720px;
        }

        /* Desain Tabel Nilai Premium dengan Jarak Aman di Bawahnya */
        .stats-table {
            width: 460px;
            border-collapse: collapse;
            font-family: 'Montserrat', sans-serif;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
            /* PERBAIKAN: Ditambahkan margin bottom 30px agar QR Code di bawah tidak menempel */
            margin: 0 auto 30px auto; 
        }
        .stats-table th { 
            background-color: #0f172a; 
            color: #d4af37; 
            font-size: 11px; 
            font-weight: 700;
            padding: 10px; 
            border: 1px solid #0f172a; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stats-table td { 
            border: 1px solid #e2e8f0; 
            padding: 9px; 
            font-weight: 700; 
            font-size: 15px; 
            color: #0f172a;
            background-color: #ffffff; 
        }
        .total-cell {
            background-color: #fffbeb !important;
            color: #b45309 !important;
        }

        /* --- 3. BAGIAN BAWAH (Footer) --- */
        .footer-section {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            box-sizing: border-box;
        }

        .footer-item { width: 33.33%; }

        /* Sisi Kiri: Serial ID */
        .footer-left { text-align: left; padding-bottom: 5px; }
        .footer-title-small { font-family: 'Montserrat', sans-serif; font-size: 9px; font-weight: 700; color: #94a3b8; letter-spacing: 2px; margin-bottom: 25px; }
        .cert-id { font-size: 11px; font-family: monospace; font-weight: 700; background: #f1f5f9; color: #475569; padding: 6px 12px; border-radius: 4px; border: 1px solid #e2e8f0; display: inline-block; }
        
        /* Sisi Tengah: QR Code Authenticator */
        .footer-center { display: flex; justify-content: center; align-items: flex-end; }
        .qr-code-wrapper { background: #ffffff; border: 1px solid #e2e8f0; padding: 6px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); margin-bottom: 2px; }
        .qr-code-wrapper img { width: 80px; height: 80px; display: block; }

        /* Sisi Kanan: Tanda Tangan */
        .footer-right { text-align: right; font-family: 'Montserrat', sans-serif; font-size: 13px; color: #1e293b; padding-bottom: 5px; }
        .signature-line { border-bottom: 1.5px solid #0f172a; width: 190px; margin: 20px 0 6px auto; }
        .signee-title { font-size: 11px; color: #64748b; font-weight: 500; }

        @media print {
            .certificate-wrapper { background-color: #fcfaf2 !important; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="certificate-wrapper">
        
        <div class="border-outer"></div>
        <div class="border-middle"></div>
        <div class="border-inner"></div>
        
        <div class="corner-pattern top-left"></div>
        <div class="corner-pattern top-right"></div>
        <div class="corner-pattern bottom-left"></div>
        <div class="corner-pattern bottom-right"></div>

        <img src="{{ asset('images/logo.png') }}" class="watermark" onerror="this.style.display='none'">

        <div class="safe-zone">
            
            <div class="header-section">
                <img src="{{ asset('images/logo.png') }}" class="logo-img" onerror="this.style.display='none'">
                <div class="org-name">CPNS Nusantara Academy</div>
            </div>

            <div class="body-section">
                <h1 class="cert-label">Sertifikat</h1>
                <div class="cert-sub-label">{{ $isPassed ? 'Pencapaian Kompetensi' : 'Partisipasi Resmi' }}</div>
                <div class="divider-premium"></div>
                
                <p class="presented-text">Sertifikat penghargaan ini dengan bangga diberikan kepada:</p>
                <h2 class="candidate-name">{{ $attempt->user->name }}</h2>
                <div class="name-underline"></div>

                <p class="description">
                    Telah menyelesaikan evaluasi pengerjaan simulasi Computer Assisted Test (CAT) instansi pemerintah pada sistem paket materi <strong>{{ $attempt->tryout->title }}</strong> dengan pencapaian akumulasi nilai resmi:
                </p>

                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Tes Wawasan Kebangsaan (TWK)</th>
                            <th>Tes Inteligensia Umum (TIU)</th>
                            <th>Tes Karakteristik Pribadi (TKP)</th>
                            <th style="background-color: #d4af37; color: #0f172a;">Total Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $attempt->twk_score }}</td>
                            <td>{{ $attempt->tiu_score }}</td>
                            <td>{{ $attempt->tkp_score }}</td>
                            <td class="total-cell">{{ $attempt->total_score }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="footer-section">
                
                <div class="footer-item footer-left">
                    <div class="footer-title-small">VERIFIKASI SISTEM DIGITAL</div>
                    <span class="cert-id">SERI ID: {{ strtoupper(substr(md5($attempt->id), 0, 12)) }}</span>
                </div>

                <div class="footer-item footer-center">
                    <div class="qr-code-wrapper">
                        @php
                            $url = route('tryout.leaderboard', $attempt->tryout_id);
                            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($url);
                        @endphp
                        <img src="{{ $qrUrl }}" alt="QR Verification">
                    </div>
                </div>

                <div class="footer-item footer-right">
                    Jakarta, {{ now()->translatedFormat('d F Y') }}<br>
                    <strong>Direktur Akademik Pusat</strong>
                    <div class="signature-line"></div>
                    <strong>Admin Utama Pusat</strong>
                    <div class="signee-title">CPNS Nusantara Academy</div>
                </div>

            </div>

        </div> </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1200); 
        };
    </script>
</body>
</html>