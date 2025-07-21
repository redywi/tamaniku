-- Membuat tabel kategori
CREATE TABLE `kategori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Membuat tabel produk
CREATE TABLE `produk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_kategori` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_kategori` (`id_kategori`),
  CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Membuat tabel pesanan
CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produk` int(11) NOT NULL,
  `nama_pelanggan` varchar(200) NOT NULL,
  `nomor_whatsapp` varchar(20) NOT NULL,
  `status` enum('Baru','Diproses','Selesai') NOT NULL DEFAULT 'Baru',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_produk` (`id_produk`),
  CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Membuat tabel admin
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Memasukkan data admin awal
-- Passwordnya adalah 'admin'
INSERT INTO `admin` (`username`, `password`) VALUES
('admin', '$2y$10$kSqGfz1flVB5ifq2UllAI.9Eo8e7aIIAMk9me7a/FF9cmcvIyvprW');

-- Memasukkan data kategori dummy
INSERT INTO `kategori` (`nama_kategori`) VALUES
('Tanaman Hias Daun'),
('Tanaman Hias Bunga'),
('Tanaman Sukulen'),
('Tanaman Herbal'),
('Tanaman Indoor'),
('Tanaman Outdoor'),
('Tanaman Gantung'),
('Tanaman Air');

-- Memasukkan data produk dummy
INSERT INTO `produk` (`id_kategori`, `nama_produk`, `deskripsi`, `harga`, `stok`, `gambar`) VALUES
-- Tanaman Hias Daun
(1, 'Monstera Deliciosa', 'Tanaman hias daun populer dengan daun berlubang yang unik. Cocok untuk dekorasi ruangan indoor.', 150000, 25, 'monstera.jpg'),
(1, 'Philodendron Pink Princess', 'Tanaman hias langka dengan daun berwarna pink dan hijau. Sangat diminati para kolektor.', 350000, 8, 'philodendron_pink.jpg'),
(1, 'Alocasia Polly', 'Tanaman dengan daun berbentuk perisai berwarna hijau gelap dengan urat putih yang kontras.', 120000, 15, 'alocasia_polly.jpg'),
(1, 'Calathea Ornata', 'Tanaman hias dengan motif daun yang cantik, memiliki garis-garis pink pada daunnya.', 85000, 20, 'calathea_ornata.jpg'),

-- Tanaman Hias Bunga
(2, 'Anggrek Bulan Putih', 'Anggrek dengan bunga putih elegan yang tahan lama. Cocok sebagai hadiah atau dekorasi.', 200000, 12, 'anggrek_bulan.jpg'),
(2, 'Mawar Climbing Red', 'Mawar merambat dengan bunga merah menyala. Ideal untuk taman vertikal atau pagar.', 75000, 30, 'mawar_climbing.jpg'),
(2, 'Melati Putih', 'Tanaman bunga dengan aroma harum yang khas. Cocok untuk taman rumah.', 45000, 40, 'melati_putih.jpg'),
(2, 'Bougenville Ungu', 'Tanaman hias berbunga dengan warna ungu cerah yang tahan terhadap cuaca panas.', 60000, 25, 'bougenville_ungu.jpg'),

-- Tanaman Sukulen
(3, 'Echeveria Elegans', 'Sukulen dengan bentuk roset yang sempurna, mudah perawatan dan tahan kekeringan.', 35000, 50, 'echeveria_elegans.jpg'),
(3, 'Jade Plant', 'Tanaman sukulen yang dipercaya membawa keberuntungan. Daun tebal berwarna hijau mengkilap.', 40000, 35, 'jade_plant.jpg'),
(3, 'Haworthia Zebra', 'Sukulen kecil dengan motif zebra pada daunnya. Sangat cocok untuk meja kerja.', 25000, 60, 'haworthia_zebra.jpg'),
(3, 'String of Hearts', 'Sukulen gantung dengan daun berbentuk hati. Sangat Instagram-able!', 55000, 20, 'string_of_hearts.jpg'),

-- Tanaman Herbal
(4, 'Basil Sweet Genovese', 'Kemangi Italia untuk masakan. Aroma harum dan rasa yang khas.', 15000, 80, 'basil_sweet.jpg'),
(4, 'Mint', 'Daun mint segar untuk teh atau garnish. Aroma menyegarkan.', 18000, 70, 'mint.jpg'),
(4, 'Rosemary', 'Tanaman herbal dengan aroma khas untuk masakan Mediterranean.', 22000, 45, 'rosemary.jpg'),
(4, 'Lavender', 'Tanaman aromaterapi dengan bunga ungu yang harum dan menenangkan.', 35000, 30, 'lavender.jpg'),

-- Tanaman Indoor
(5, 'Snake Plant (Sansevieria)', 'Tanaman pembersih udara yang sangat mudah perawatan. Cocok untuk pemula.', 65000, 40, 'snake_plant.jpg'),
(5, 'ZZ Plant (Zamioculcas)', 'Tanaman indoor yang tahan terhadap kondisi cahaya rendah dan jarang disiram.', 75000, 25, 'zz_plant.jpg'),
(5, 'Peace Lily', 'Tanaman indoor dengan bunga putih yang elegan. Pembersih udara alami.', 55000, 30, 'peace_lily.jpg'),
(5, 'Pothos Golden', 'Tanaman merambat yang mudah tumbuh dan cocok untuk hanging pot.', 30000, 60, 'pothos_golden.jpg'),

-- Tanaman Outdoor
(6, 'Palem Kuning', 'Palem hias untuk taman dengan daun kuning yang eksotis.', 180000, 15, 'palem_kuning.jpg'),
(6, 'Bambu Hoki', 'Tanaman bambu yang dipercaya membawa keberuntungan untuk taman.', 95000, 20, 'bambu_hoki.jpg'),
(6, 'Pucuk Merah', 'Tanaman hias outdoor dengan daun merah menyala yang cantik.', 45000, 35, 'pucuk_merah.jpg'),
(6, 'Keladi Red Star', 'Tanaman hias dengan daun berwarna merah pink yang mencolok.', 65000, 25, 'keladi_red_star.jpg'),

-- Tanaman Gantung
(7, 'Boston Fern', 'Pakis gantung yang memberikan nuansa hijau segar pada ruangan.', 70000, 20, 'boston_fern.jpg'),
(7, 'Spider Plant', 'Tanaman gantung yang mudah berkembang biak dan pembersih udara.', 40000, 45, 'spider_plant.jpg'),
(7, 'String of Pearls', 'Sukulen gantung dengan bentuk seperti untaian mutiara.', 60000, 15, 'string_of_pearls.jpg'),
(7, 'Tradescantia Purple', 'Tanaman gantung dengan daun ungu yang cantik dan mudah perawatan.', 35000, 30, 'tradescantia_purple.jpg'),

-- Tanaman Air
(8, 'Lotus Pink', 'Bunga teratai pink untuk kolam hias. Memberikan kesan zen pada taman.', 125000, 10, 'lotus_pink.jpg'),
(8, 'Water Hyacinth', 'Tanaman air mengapung dengan bunga ungu yang cantik.', 35000, 25, 'water_hyacinth.jpg'),
(8, 'Papyrus', 'Tanaman air dengan bentuk unik seperti payung, cocok untuk kolam modern.', 85000, 15, 'papyrus.jpg'),
(8, 'Cattail (Typha)', 'Tanaman air dengan bentuk seperti sosis coklat, memberikan aksen alami pada kolam.', 45000, 20, 'cattail.jpg');