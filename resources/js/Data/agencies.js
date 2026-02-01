// resources/js/Data/agencies.js

// --- 1. DATA WILAYAH INDONESIA (38 PROVINSI) ---
const regionalData = {
    '11': ['Aceh', [
        'Kab. Aceh Barat', 'Kab. Aceh Barat Daya', 'Kab. Aceh Besar', 'Kab. Aceh Jaya', 'Kab. Aceh Selatan', 'Kab. Aceh Singkil', 'Kab. Aceh Tamiang', 'Kab. Aceh Tengah', 'Kab. Aceh Tenggara', 'Kab. Aceh Timur', 'Kab. Aceh Utara', 'Kab. Bener Meriah', 'Kab. Bireuen', 'Kab. Gayo Lues', 'Kab. Nagan Raya', 'Kab. Pidie', 'Kab. Pidie Jaya', 'Kab. Simeulue', 'Kota Banda Aceh', 'Kota Langsa', 'Kota Lhokseumawe', 'Kota Sabang', 'Kota Subulussalam'
    ]],
    '12': ['Sumatera Utara', [
        'Kab. Asahan', 'Kab. Batu Bara', 'Kab. Dairi', 'Kab. Deli Serdang', 'Kab. Humbang Hasundutan', 'Kab. Karo', 'Kab. Labuhanbatu', 'Kab. Labuhanbatu Selatan', 'Kab. Labuhanbatu Utara', 'Kab. Langkat', 'Kab. Mandailing Natal', 'Kab. Nias', 'Kab. Nias Barat', 'Kab. Nias Selatan', 'Kab. Nias Utara', 'Kab. Padang Lawas', 'Kab. Padang Lawas Utara', 'Kab. Pakpak Bharat', 'Kab. Samosir', 'Kab. Serdang Bedagai', 'Kab. Simalungun', 'Kab. Tapanuli Selatan', 'Kab. Tapanuli Tengah', 'Kab. Tapanuli Utara', 'Kab. Toba', 'Kota Binjai', 'Kota Gunungsitoli', 'Kota Medan', 'Kota Padangsidimpuan', 'Kota Pematangsiantar', 'Kota Sibolga', 'Kota Tanjungbalai', 'Kota Tebing Tinggi'
    ]],
    '13': ['Sumatera Barat', [
        'Kab. Agam', 'Kab. Dharmasraya', 'Kab. Kepulauan Mentawai', 'Kab. Lima Puluh Kota', 'Kab. Padang Pariaman', 'Kab. Pasaman', 'Kab. Pasaman Barat', 'Kab. Pesisir Selatan', 'Kab. Sijunjung', 'Kab. Solok', 'Kab. Solok Selatan', 'Kab. Tanah Datar', 'Kota Bukittinggi', 'Kota Padang', 'Kota Padang Panjang', 'Kota Pariaman', 'Kota Payakumbuh', 'Kota Sawahlunto', 'Kota Solok'
    ]],
    '14': ['Riau', [
        'Kab. Bengkalis', 'Kab. Indragiri Hilir', 'Kab. Indragiri Hulu', 'Kab. Kampar', 'Kab. Kepulauan Meranti', 'Kab. Kuantan Singingi', 'Kab. Pelalawan', 'Kab. Rokan Hilir', 'Kab. Rokan Hulu', 'Kab. Siak', 'Kota Dumai', 'Kota Pekanbaru'
    ]],
    '15': ['Jambi', [
        'Kab. Batanghari', 'Kab. Bungo', 'Kab. Kerinci', 'Kab. Merangin', 'Kab. Muaro Jambi', 'Kab. Sarolangun', 'Kab. Tanjung Jabung Barat', 'Kab. Tanjung Jabung Timur', 'Kab. Tebo', 'Kota Jambi', 'Kota Sungai Penuh'
    ]],
    '16': ['Sumatera Selatan', [
        'Kab. Banyuasin', 'Kab. Empat Lawang', 'Kab. Lahat', 'Kab. Muara Enim', 'Kab. Musi Banyuasin', 'Kab. Musi Rawas', 'Kab. Musi Rawas Utara', 'Kab. Ogan Ilir', 'Kab. Ogan Komering Ilir', 'Kab. Ogan Komering Ulu', 'Kab. Ogan Komering Ulu Selatan', 'Kab. Ogan Komering Ulu Timur', 'Kab. Penukal Abab Lematang Ilir', 'Kota Lubuklinggau', 'Kota Pagar Alam', 'Kota Palembang', 'Kota Prabumulih'
    ]],
    '17': ['Bengkulu', [
        'Kab. Bengkulu Selatan', 'Kab. Bengkulu Tengah', 'Kab. Bengkulu Utara', 'Kab. Kaur', 'Kab. Kepahiang', 'Kab. Lebong', 'Kab. Mukomuko', 'Kab. Rejang Lebong', 'Kab. Seluma', 'Kota Bengkulu'
    ]],
    '18': ['Lampung', [
        'Kab. Lampung Barat', 'Kab. Lampung Selatan', 'Kab. Lampung Tengah', 'Kab. Lampung Timur', 'Kab. Lampung Utara', 'Kab. Mesuji', 'Kab. Pesawaran', 'Kab. Pesisir Barat', 'Kab. Pringsewu', 'Kab. Tanggamus', 'Kab. Tulang Bawang', 'Kab. Tulang Bawang Barat', 'Kab. Way Kanan', 'Kota Bandar Lampung', 'Kota Metro'
    ]],
    '19': ['Kepulauan Bangka Belitung', [
        'Kab. Bangka', 'Kab. Bangka Barat', 'Kab. Bangka Selatan', 'Kab. Bangka Tengah', 'Kab. Belitung', 'Kab. Belitung Timur', 'Kota Pangkalpinang'
    ]],
    '21': ['Kepulauan Riau', [
        'Kab. Bintan', 'Kab. Karimun', 'Kab. Kepulauan Anambas', 'Kab. Lingga', 'Kab. Natuna', 'Kota Batam', 'Kota Tanjungpinang'
    ]],
    '31': ['DKI Jakarta', [
        'Kab. Adm. Kepulauan Seribu', 'Kota Adm. Jakarta Barat', 'Kota Adm. Jakarta Pusat', 'Kota Adm. Jakarta Selatan', 'Kota Adm. Jakarta Timur', 'Kota Adm. Jakarta Utara'
    ]],
    '32': ['Jawa Barat', [
        'Kab. Bandung', 'Kab. Bandung Barat', 'Kab. Bekasi', 'Kab. Bogor', 'Kab. Ciamis', 'Kab. Cianjur', 'Kab. Cirebon', 'Kab. Garut', 'Kab. Indramayu', 'Kab. Karawang', 'Kab. Kuningan', 'Kab. Majalengka', 'Kab. Pangandaran', 'Kab. Purwakarta', 'Kab. Subang', 'Kab. Sukabumi', 'Kab. Sumedang', 'Kab. Tasikmalaya', 'Kota Bandung', 'Kota Banjar', 'Kota Bekasi', 'Kota Bogor', 'Kota Cimahi', 'Kota Cirebon', 'Kota Depok', 'Kota Sukabumi', 'Kota Tasikmalaya'
    ]],
    '33': ['Jawa Tengah', [
        'Kab. Banjarnegara', 'Kab. Banyumas', 'Kab. Batang', 'Kab. Blora', 'Kab. Boyolali', 'Kab. Brebes', 'Kab. Cilacap', 'Kab. Demak', 'Kab. Grobogan', 'Kab. Jepara', 'Kab. Karanganyar', 'Kab. Kebumen', 'Kab. Kendal', 'Kab. Klaten', 'Kab. Kudus', 'Kab. Magelang', 'Kab. Pati', 'Kab. Pekalongan', 'Kab. Pemalang', 'Kab. Purbalingga', 'Kab. Purworejo', 'Kab. Rembang', 'Kab. Semarang', 'Kab. Sragen', 'Kab. Sukoharjo', 'Kab. Tegal', 'Kab. Temanggung', 'Kab. Wonogiri', 'Kab. Wonosobo', 'Kota Magelang', 'Kota Pekalongan', 'Kota Salatiga', 'Kota Semarang', 'Kota Surakarta', 'Kota Tegal'
    ]],
    '34': ['DI Yogyakarta', [
        'Kab. Bantul', 'Kab. Gunungkidul', 'Kab. Kulon Progo', 'Kab. Sleman', 'Kota Yogyakarta'
    ]],
    '35': ['Jawa Timur', [
        'Kab. Bangkalan', 'Kab. Banyuwangi', 'Kab. Blitar', 'Kab. Bojonegoro', 'Kab. Bondowoso', 'Kab. Gresik', 'Kab. Jember', 'Kab. Jombang', 'Kab. Kediri', 'Kab. Lamongan', 'Kab. Lumajang', 'Kab. Madiun', 'Kab. Magetan', 'Kab. Malang', 'Kab. Mojokerto', 'Kab. Nganjuk', 'Kab. Ngawi', 'Kab. Pacitan', 'Kab. Pamekasan', 'Kab. Pasuruan', 'Kab. Ponorogo', 'Kab. Probolinggo', 'Kab. Sampang', 'Kab. Sidoarjo', 'Kab. Situbondo', 'Kab. Sumenep', 'Kab. Trenggalek', 'Kab. Tuban', 'Kab. Tulungagung', 'Kota Batu', 'Kota Blitar', 'Kota Kediri', 'Kota Madiun', 'Kota Malang', 'Kota Mojokerto', 'Kota Pasuruan', 'Kota Probolinggo', 'Kota Surabaya'
    ]],
    '36': ['Banten', [
        'Kab. Lebak', 'Kab. Pandeglang', 'Kab. Serang', 'Kab. Tangerang', 'Kota Cilegon', 'Kota Serang', 'Kota Tangerang', 'Kota Tangerang Selatan'
    ]],
    '51': ['Bali', [
        'Kab. Badung', 'Kab. Bangli', 'Kab. Buleleng', 'Kab. Gianyar', 'Kab. Jembrana', 'Kab. Karangasem', 'Kab. Klungkung', 'Kab. Tabanan', 'Kota Denpasar'
    ]],
    '52': ['Nusa Tenggara Barat', [
        'Kab. Bima', 'Kab. Dompu', 'Kab. Lombok Barat', 'Kab. Lombok Tengah', 'Kab. Lombok Timur', 'Kab. Lombok Utara', 'Kab. Sumbawa', 'Kab. Sumbawa Barat', 'Kota Bima', 'Kota Mataram'
    ]],
    '53': ['Nusa Tenggara Timur', [
        'Kab. Alor', 'Kab. Belu', 'Kab. Ende', 'Kab. Flores Timur', 'Kab. Kupang', 'Kab. Lembata', 'Kab. Malaka', 'Kab. Manggarai', 'Kab. Manggarai Barat', 'Kab. Manggarai Timur', 'Kab. Nagekeo', 'Kab. Ngada', 'Kab. Rote Ndao', 'Kab. Sabu Raijua', 'Kab. Sikka', 'Kab. Sumba Barat', 'Kab. Sumba Barat Daya', 'Kab. Sumba Tengah', 'Kab. Sumba Timur', 'Kab. Timor Tengah Selatan', 'Kab. Timor Tengah Utara', 'Kota Kupang'
    ]],
    '61': ['Kalimantan Barat', [
        'Kab. Bengkayang', 'Kab. Kapuas Hulu', 'Kab. Kayong Utara', 'Kab. Ketapang', 'Kab. Kubu Raya', 'Kab. Landak', 'Kab. Melawi', 'Kab. Mempawah', 'Kab. Sambas', 'Kab. Sanggau', 'Kab. Sekadau', 'Kab. Sintang', 'Kota Pontianak', 'Kota Singkawang'
    ]],
    '62': ['Kalimantan Tengah', [
        'Kab. Barito Selatan', 'Kab. Barito Timur', 'Kab. Barito Utara', 'Kab. Gunung Mas', 'Kab. Kapuas', 'Kab. Katingan', 'Kab. Kotawaringin Barat', 'Kab. Kotawaringin Timur', 'Kab. Lamandau', 'Kab. Murung Raya', 'Kab. Pulang Pisau', 'Kab. Seruyan', 'Kab. Sukamara', 'Kota Palangka Raya'
    ]],
    '63': ['Kalimantan Selatan', [
        'Kab. Balangan', 'Kab. Banjar', 'Kab. Barito Kuala', 'Kab. Hulu Sungai Selatan', 'Kab. Hulu Sungai Tengah', 'Kab. Hulu Sungai Utara', 'Kab. Kotabaru', 'Kab. Tabalong', 'Kab. Tanah Bumbu', 'Kab. Tanah Laut', 'Kab. Tapin', 'Kota Banjarbaru', 'Kota Banjarmasin'
    ]],
    '64': ['Kalimantan Timur', [
        'Kab. Berau', 'Kab. Kutai Barat', 'Kab. Kutai Kartanegara', 'Kab. Kutai Timur', 'Kab. Mahakam Ulu', 'Kab. Paser', 'Kab. Penajam Paser Utara', 'Kota Balikpapan', 'Kota Bontang', 'Kota Samarinda'
    ]],
    '65': ['Kalimantan Utara', [
        'Kab. Bulungan', 'Kab. Malinau', 'Kab. Nunukan', 'Kab. Tana Tidung', 'Kota Tarakan'
    ]],
    '71': ['Sulawesi Utara', [
        'Kab. Bolaang Mongondow', 'Kab. Bolaang Mongondow Selatan', 'Kab. Bolaang Mongondow Timur', 'Kab. Bolaang Mongondow Utara', 'Kab. Kepulauan Sangihe', 'Kab. Kepulauan Siau Tagulandang Biaro', 'Kab. Kepulauan Talaud', 'Kab. Minahasa', 'Kab. Minahasa Selatan', 'Kab. Minahasa Tenggara', 'Kab. Minahasa Utara', 'Kota Bitung', 'Kota Kotamobagu', 'Kota Manado', 'Kota Tomohon'
    ]],
    '72': ['Sulawesi Tengah', [
        'Kab. Banggai', 'Kab. Banggai Kepulauan', 'Kab. Banggai Laut', 'Kab. Buol', 'Kab. Donggala', 'Kab. Morowali', 'Kab. Morowali Utara', 'Kab. Parigi Moutong', 'Kab. Poso', 'Kab. Sigi', 'Kab. Tojo Una-Una', 'Kab. Tolitoli', 'Kota Palu'
    ]],
    '73': ['Sulawesi Selatan', [
        'Kab. Bantaeng', 'Kab. Barru', 'Kab. Bone', 'Kab. Bulukumba', 'Kab. Enrekang', 'Kab. Gowa', 'Kab. Jeneponto', 'Kab. Kepulauan Selayar', 'Kab. Luwu', 'Kab. Luwu Timur', 'Kab. Luwu Utara', 'Kab. Maros', 'Kab. Pangkajene dan Kepulauan', 'Kab. Pinrang', 'Kab. Sidenreng Rappang', 'Kab. Sinjai', 'Kab. Soppeng', 'Kab. Takalar', 'Kab. Tana Toraja', 'Kab. Toraja Utara', 'Kab. Wajo', 'Kota Makassar', 'Kota Palopo', 'Kota Parepare'
    ]],
    '74': ['Sulawesi Tenggara', [
        'Kab. Bombana', 'Kab. Buton', 'Kab. Buton Selatan', 'Kab. Buton Tengah', 'Kab. Buton Utara', 'Kab. Kolaka', 'Kab. Kolaka Timur', 'Kab. Kolaka Utara', 'Kab. Konawe', 'Kab. Konawe Kepulauan', 'Kab. Konawe Selatan', 'Kab. Konawe Utara', 'Kab. Muna', 'Kab. Muna Barat', 'Kab. Wakatobi', 'Kota Baubau', 'Kota Kendari'
    ]],
    '75': ['Gorontalo', [
        'Kab. Boalemo', 'Kab. Bone Bolango', 'Kab. Gorontalo', 'Kab. Gorontalo Utara', 'Kab. Pohuwato', 'Kota Gorontalo'
    ]],
    '76': ['Sulawesi Barat', [
        'Kab. Majene', 'Kab. Mamasa', 'Kab. Mamuju', 'Kab. Mamuju Tengah', 'Kab. Pasangkayu', 'Kab. Polewali Mandar'
    ]],
    '81': ['Maluku', [
        'Kab. Buru', 'Kab. Buru Selatan', 'Kab. Kepulauan Aru', 'Kab. Kepulauan Tanimbar', 'Kab. Maluku Barat Daya', 'Kab. Maluku Tengah', 'Kab. Maluku Tenggara', 'Kab. Seram Bagian Barat', 'Kab. Seram Bagian Timur', 'Kota Ambon', 'Kota Tual'
    ]],
    '82': ['Maluku Utara', [
        'Kab. Halmahera Barat', 'Kab. Halmahera Selatan', 'Kab. Halmahera Tengah', 'Kab. Halmahera Timur', 'Kab. Halmahera Utara', 'Kab. Kepulauan Sula', 'Kab. Pulau Morotai', 'Kab. Pulau Taliabu', 'Kota Ternate', 'Kota Tidore Kepulauan'
    ]],
    '91': ['Papua Barat', [
        'Kab. Fakfak', 'Kab. Kaimana', 'Kab. Manokwari', 'Kab. Manokwari Selatan', 'Kab. Pegunungan Arfak', 'Kab. Teluk Bintuni', 'Kab. Teluk Wondama'
    ]],
    '92': ['Papua Tengah', [
        'Kab. Deiyai', 'Kab. Dogiyai', 'Kab. Intan Jaya', 'Kab. Mimika', 'Kab. Nabire', 'Kab. Paniai', 'Kab. Puncak', 'Kab. Puncak Jaya'
    ]],
    '93': ['Papua Pegunungan', [
        'Kab. Jayawijaya', 'Kab. Lanny Jaya', 'Kab. Mamberamo Tengah', 'Kab. Nduga', 'Kab. Pegunungan Bintang', 'Kab. Tolikara', 'Kab. Yahukimo', 'Kab. Yalimo'
    ]],
    '94': ['Papua', [
        'Kab. Biak Numfor', 'Kab. Jayapura', 'Kab. Keerom', 'Kab. Kepulauan Yapen', 'Kab. Mamberamo Raya', 'Kab. Sarmi', 'Kab. Supiori', 'Kab. Waropen', 'Kota Jayapura'
    ]],
    '95': ['Papua Selatan', [
        'Kab. Asmat', 'Kab. Boven Digoel', 'Kab. Mappi', 'Kab. Merauke'
    ]],
    '96': ['Papua Barat Daya', [
        'Kab. Maybrat', 'Kab. Raja Ampat', 'Kab. Sorong', 'Kab. Sorong Selatan', 'Kab. Tambrauw', 'Kota Sorong'
    ]]
};

// --- 2. INSTANSI PUSAT (Urutan A-Z) ---
const centralAgencies = [
    "Arsip Nasional Republik Indonesia",
    "Badan Gizi Nasional",
    "Badan Informasi Geospasial",
    "Badan Intelijen Negara",
    "Badan Kepegawaian Negara",
    "Badan Kependudukan dan Keluarga Berencana Nasional (BKKBN)",
    "Badan Meteorologi, Klimatologi, dan Geofisika (BMKG)",
    "Badan Narkotika Nasional (BNN)",
    "Badan Nasional Penanggulangan Bencana (BNPB)",
    "Badan Nasional Penanggulangan Terorisme (BNPT)",
    "Badan Nasional Pencarian dan Pertolongan (BASARNAS)",
    "Badan Pangan Nasional",
    "Badan Pengawas Keuangan dan Pembangunan (BPKP)",
    "Badan Pengawas Obat dan Makanan (BPOM)",
    "Badan Pengawas Tenaga Nuklir (BAPETEN)",
    "Badan Pemeriksa Keuangan (BPK)",
    "Badan Pusat Statistik (BPS)",
    "Badan Riset dan Inovasi Nasional (BRIN)",
    "Badan Siber dan Sandi Negara (BSSN)",
    "Badan Standardisasi Nasional (BSN)",
    "Kejaksaan Agung",
    "Kementerian Agama",
    "Kementerian Agraria dan Tata Ruang / Badan Pertanahan Nasional (ATR/BPN)",
    "Kementerian Badan Usaha Milik Negara (BUMN)",
    "Kementerian Dalam Negeri",
    "Kementerian Desa dan Pembangunan Daerah Tertinggal",
    "Kementerian Ekonomi Kreatif / Badan Ekonomi Kreatif",
    "Kementerian Energi dan Sumber Daya Mineral (ESDM)",
    "Kementerian Hak Asasi Manusia (HAM)",
    "Kementerian Hukum",
    "Kementerian Imigrasi dan Pemasyarakatan",
    "Kementerian Investasi dan Hilirisasi / BKPM",
    "Kementerian Kebudayaan",
    "Kementerian Kehutanan",
    "Kementerian Kelautan dan Perikanan",
    "Kementerian Kependudukan dan Pembangunan Keluarga",
    "Kementerian Kesehatan",
    "Kementerian Ketenagakerjaan",
    "Kementerian Keuangan",
    "Kementerian Komunikasi dan Digital",
    "Kementerian Koperasi",
    "Kementerian Lingkungan Hidup / Badan Pengendalian Lingkungan Hidup",
    "Kementerian Luar Negeri",
    "Kementerian Pariwisata",
    "Kementerian Pekerjaan Umum (PU)",
    "Kementerian Pelindungan Pekerja Migran Indonesia (BP2MI)",
    "Kementerian Pemuda dan Olahraga",
    "Kementerian Pemberdayaan Perempuan dan Perlindungan Anak",
    "Kementerian Pendayagunaan Aparatur Negara dan Reformasi Birokrasi (PANRB)",
    "Kementerian Pendidikan Dasar dan Menengah",
    "Kementerian Pendidikan Tinggi, Sains, dan Teknologi",
    "Kementerian Perdagangan",
    "Kementerian Perencanaan Pembangunan Nasional / Bappenas",
    "Kementerian Perhubungan",
    "Kementerian Perindustrian",
    "Kementerian Pertahanan",
    "Kementerian Pertanian",
    "Kementerian Perumahan dan Kawasan Permukiman",
    "Kementerian Sosial",
    "Kementerian Sekretariat Negara",
    "Kementerian Transmigrasi",
    "Kementerian Usaha Mikro, Kecil, dan Menengah (UMKM)",
    "Kementerian Koordinator Bidang Hukum, HAM, Imigrasi, dan Pemasyarakatan",
    "Kementerian Koordinator Bidang Infrastruktur dan Pembangunan Kewilayahan",
    "Kementerian Koordinator Bidang Pangan",
    "Kementerian Koordinator Bidang Pembangunan Manusia dan Kebudayaan",
    "Kementerian Koordinator Bidang Pemberdayaan Masyarakat",
    "Kementerian Koordinator Bidang Perekonomian",
    "Kementerian Koordinator Bidang Politik dan Keamanan",
    "Kepolisian Negara Republik Indonesia (POLRI)",
    "Komisi Pemberantasan Korupsi (KPK)",
    "Komisi Yudisial (KY)",
    "Lembaga Administrasi Negara (LAN)",
    "Lembaga Kebijakan Pengadaan Barang/Jasa Pemerintah (LKPP)",
    "Lembaga Ketahanan Nasional (Lemhannas)",
    "Mahkamah Agung",
    "Mahkamah Konstitusi",
    "Otorita Ibu Kota Nusantara (IKN)",
    "Sekretariat Kabinet",
    "Tentara Nasional Indonesia (TNI)"
];

// --- EXPORT HELPERS ---

// 1. Export List Provinsi (Untuk Dropdown)
export const provinces = Object.keys(regionalData).map(code => ({
    code: code,
    name: regionalData[code][0]
})).sort((a, b) => a.name.localeCompare(b.name));

// 2. Export Gabungan Instansi (Disusun Berkelompok)
const generateAgencies = () => {
    // A. INSTANSI PUSAT (Urutan A-Z)
    // *Sudah diurutkan manual di array centralAgencies di atas*
    const central = [...centralAgencies].sort(); 

    // B. PEMERINTAH PROVINSI (Urutan A-Z)
    const provincial = provinces.map(p => `Pemerintah Provinsi ${p.name}`).sort();

    // C. PEMERINTAH KAB/KOTA (Urutan A-Z)
    let districts = [];
    Object.keys(regionalData).forEach(code => {
        const dList = regionalData[code][1];
        if (dList) {
            districts.push(...dList.map(d => `Pemerintah ${d}`));
        }
    });
    districts.sort(); // Urutkan A-Z semua kab/kota

    // GABUNGKAN SESUAI URUTAN: PUSAT -> PROVINSI -> KAB/KOTA
    return [...central, ...provincial, ...districts];
};

export const agencies = generateAgencies();