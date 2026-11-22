<?php

class Seeder
{
    public function run(PDO $db): void
    {
        if ((int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
            return;
        }

        $db->beginTransaction();
        try {
            $now = time();
            $password = password_hash('rewear123', PASSWORD_DEFAULT);
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);

            $users = [
                ['Nadia Prameswari', 'nadia', 'admin@rewear.id', $adminPassword, 'admin', '0812 1000 2201', 'Jakarta', 'Mengelola komunitas re:wear dan menjaga kualitas kurasi.', '-180 days'],
                ['Alya Rahmawati', 'alya', 'alya@rewear.id', $password, 'penjual', '0813 2211 8890', 'Bandung', 'Penikmat gaya klasik dan penggemar warna netral. Setiap barang dicuci sebelum dikirim.', '-160 days'],
                ['Dimas Aditya', 'dimas', 'dimas@rewear.id', $password, 'penjual', '0857 8890 1122', 'Yogyakarta', 'Menjual koleksi pribadi yang jarang dipakai. Semua foto diambil apa adanya.', '-150 days'],
                ['Sinta Maharani', 'sinta', 'sinta@rewear.id', $password, 'penjual', '0819 3344 7765', 'Jakarta', 'Pecinta siluet klasik dan warna bumi. Selalu siap menjawab pertanyaan pembeli.', '-130 days'],
                ['Raka Pratama', 'raka', 'raka@rewear.id', $password, 'pembeli', '0821 5566 7788', 'Surabaya', 'Mencari pakaian berkualitas dengan cerita di baliknya.', '-90 days'],
                ['Maya Kusuma', 'maya', 'maya@rewear.id', $password, 'pembeli', '0838 1122 3344', 'Semarang', 'Suka memadupadankan gaya netral dan sepatu yang menonjol.', '-60 days'],
                ['Bima Saputra', 'bima', 'bima@rewear.id', $password, 'penjual', '0812 9988 4455', 'Malang', 'Kolektor sepatu olahraga dan gaya jalanan. Terbuka untuk negosiasi yang wajar.', '-45 days'],
            ];

            $userStmt = $db->prepare(
                'INSERT INTO users (name, username, email, password_hash, role, phone, city, address, bio, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $userIds = [];
            foreach ($users as $user) {
                $address = match ($user[1]) {
                    'raka' => 'Jl. Cendrawasih No. 18, Sukolilo, Surabaya, Jawa Timur 60111',
                    'maya' => 'Jl. Pandanaran No. 42, Semarang, Jawa Tengah 50241',
                    'alya' => 'Jl. Dago Asri No. 7, Bandung, Jawa Barat 40135',
                    default => null,
                };
                $userStmt->execute([
                    $user[0], $user[1], $user[2], $user[3], $user[4], $user[5], $user[6], $address, $user[7], 'aktif',
                    date('Y-m-d H:i:s', strtotime($user[8])),
                ]);
                $userIds[] = (int) $db->lastInsertId();
            }

            $categories = [
                ['Atasan', 'atasan', 'Kemeja, kaos, blus, dan rajutan yang siap dipakai kembali.', 'assets/images/products/flatlay-putih.jpg', 'atasan', 1, 1],
                ['Bawahan', 'bawahan', 'Denim, rok, dan celana dengan siluet yang tetap relevan.', 'assets/images/products/jeans-levis.jpg', 'bawahan', 2, 1],
                ['Outerwear', 'outerwear', 'Jaket, mantel, dan lapisan luar untuk melengkapi gaya harian.', 'assets/images/products/mantel-camel.jpg', 'outerwear', 3, 1],
                ['Dress', 'dress', 'Gaun dan jumpsuit dengan jatuh kain yang tetap indah.', 'assets/images/products/gaun-merah.jpg', 'dress', 4, 1],
                ['Sepatu', 'sepatu', 'Sneakers dan sepatu bekas terawat yang masih nyaman dipakai.', 'assets/images/products/sneaker-pastel.jpg', 'sepatu', 5, 1],
            ];
            $categoryStmt = $db->prepare(
                'INSERT INTO categories (name, slug, description, image, type, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $categoryIds = [];
            foreach ($categories as $category) {
                $categoryStmt->execute($category);
                $categoryIds[] = (int) $db->lastInsertId();
            }

            $products = [
                [1, 'Kaos Katun Oversize Putih', 1, 'atasan', 'L', 89000, 'Sangat Baik', 'Kaos katun organik dengan potongan oversize dan jatuh yang lembut. Warna putih krem, adem, dan mudah dipadukan. Sudah dicuci bersih sebelum difoto.', 'Uniqlo', 'Putih', 'Serat kain sedikit melar di area kerah, tidak terlihat saat dipakai.', 'aktif', 1, [['assets/images/products/kaos-putih.jpg', 1], ['assets/images/products/flatlay-putih.jpg', 0]]],
                [3, 'Straight Jeans Levi\'s 501', 2, 'bawahan', 'M', 245000, 'Baik', 'Straight jeans ikonik dengan bahan denim tebal yang sudah jatuh sempurna. Pas untuk gaya kasual maupun vintage. Ukuran pinggang setara 30 inci.', 'Levi\'s', 'Indigo', 'Warna sedikit memudar di bagian lutut. Semua kancing dan resleting berfungsi baik.', 'aktif', 2, [['assets/images/products/jeans-levis.jpg', 1]]],
                [4, 'Jaket Kulit Biker Hitam', 3, 'outerwear', 'M', 425000, 'Sangat Baik', 'Jaket kulit biker dengan siluet tegas dan lining satin yang masih mulus. Cocok untuk lapisan luar saat malam. Bahan terasa lembut dan tidak kaku.', 'Zara', 'Hitam', 'Tidak ada cacat berarti. Ada bekas lipatan halus di bagian lengan.', 'aktif', 3, [['assets/images/products/jaket-kulit.jpg', 1]]],
                [7, 'Sneakers Coklat Retro', 5, 'sepatu', '42', 315000, 'Baik', 'Sneakers low-top bernuansa retro dengan upper kanvas coklat dan aksen suede. Insole empuk, nyaman untuk pemakaian harian.', 'Nike', 'Coklat', 'Sol luar menguning tipis. Bagian atas masih sangat layak dan sudah dibersihkan.', 'aktif', 4, [['assets/images/products/sneaker-coklat.jpg', 1]]],
                [4, 'Gaun Satin Merah', 4, 'dress', 'S', 210000, 'Sangat Baik', 'Gaun satin merah dengan potongan A-line dan bagian pinggang terpasang rapi. Panjang di bawah lutut. Pernah dipakai sekali untuk acara keluarga.', 'Mango', 'Merah', 'Tidak ada cacat. Jahitan dan resleting dalam kondisi sempurna.', 'aktif', 5, [['assets/images/products/gaun-merah.jpg', 1]]],
                [2, 'Ponco Rajut Krim', 1, 'atasan', 'Satu Ukuran', 135000, 'Baik', 'Ponco rajut berwarna krim dengan detail rumbai di bagian bawah. Bahan hangat namun tetap ringan, pas untuk cuaca sejuk atau layering.', 'Handmade', 'Krim', 'Rumbai bagian kanan sedikit menggulung. Tidak ada lubang pada rajutan.', 'aktif', 6, [['assets/images/products/ponco-rajut.jpg', 1]]],
                [2, 'Sneakers Pastel Limited', 5, 'sepatu', '39', 480000, 'Sangat Baik', 'Sneakers warna pastel dengan panel kulit sintetis dan sol tebal. Warna lembut yang mudah dipadukan. Kondisi hampir seperti baru.', 'Nike', 'Pastel', 'Bagian dalam bersih, sol masih putih. Tanpa kotak asli.', 'aktif', 7, [['assets/images/products/sneaker-pastel.jpg', 1]]],
                [4, 'Heels Floral Biru', 5, 'sepatu', '38', 175000, 'Baik', 'Pumps hak tinggi dengan motif floral biru yang ekspresif. Tinggi hak 8 cm. Cocok untuk acara semi-formal atau koleksi yang berani.', 'Zara', 'Biru', 'Hak kiri tergores halus. Bagian dalam sudah dibersihkan.', 'aktif', 8, [['assets/images/products/heels-bunga.jpg', 1]]],
                [3, 'Mantel Wol Camel', 3, 'outerwear', 'M', 385000, 'Sangat Baik', 'Mantel wol campuran dengan warna camel hangat dan ikat pinggang senada. Potongan longgar yang elegan, panjang di bawah lutut.', 'H&M', 'Camel', 'Ada sedikit bulu kain di bagian lengan. Tidak ada noda atau robekan.', 'aktif', 9, [['assets/images/products/mantel-camel.jpg', 1]]],
                [7, 'Kaos Grafis Kucing KIIKI', 1, 'atasan', 'M', 95000, 'Baik', 'Kaos katun berwarna krem dengan grafis kucing biru yang playful. Potongan unisex dan sedikit boxy. Bahan sudah lembut karena sering dicuci.', 'KIIKI', 'Krem', 'Sablon bagian bawah sedikit memudar. Tidak ada lubang.', 'aktif', 10, [['assets/images/products/cardigan-krem.jpg', 1]]],
                [2, 'Kemeja Putih Formal', 1, 'atasan', 'L', 120000, 'Sangat Baik', 'Kemeja putih slim fit dengan bahan katun poplin. Kerah dan manset masih tegak, cocok untuk kerja atau acara resmi.', 'Uniqlo', 'Putih', 'Tidak ada cacat berarti. Kancing lengkap.', 'aktif', 11, [['assets/images/products/kemeja-linen.jpg', 1]]],
                [4, 'Rok A-line Hitam', 2, 'bawahan', 'S', 145000, 'Baik', 'Rok A-line hitam dengan pinggang tinggi dan lipatan yang jatuh rapi. Panjang di atas lutut. Bahan tebal, tidak menerawang.', 'Zara', 'Hitam', 'Ada bekas jahitan kecil di bagian pinggang dalam yang tidak terlihat.', 'aktif', 12, [['assets/images/products/rok-plisket.jpg', 1]]],
                [3, 'Boots Kulit Coklat', 5, 'sepatu', '40', 365000, 'Baik', 'Boots kulit coklat dengan tali dan sol karet tebal. Semakin nyaman seiring pemakaian. Cocok untuk gaya kasual maupun earthy.', 'Dr. Martens', 'Coklat', 'Ada lecet halus di bagian tumit dan sol. Bahan kulit tetap lentur.', 'aktif', 13, [['assets/images/products/boots-kulit.jpg', 1]]],
                [3, 'Jaket Corduroy Coklat', 3, 'outerwear', 'L', 185000, 'Baik', 'Jaket corduroy coklat dengan kerah klasik dan dua kantong depan. Hangat untuk malam hari, potongan sedikit longgar.', 'Uniqlo', 'Coklat', 'Bagian siku menunjukkan pemakaian wajar. Semua kancing utuh.', 'aktif', 14, [['assets/images/products/jaket-coklat.jpg', 1]]],
                [7, 'Sweater Rajut Merah', 1, 'atasan', 'M', 115000, 'Baik', 'Sweater rajut merah dengan potongan oversized dan tekstur lembut. Warna merah bata yang hangat, cocok untuk layering.', 'H&M', 'Merah', 'Sedikit pilling di bagian ketiak. Tidak ada lubang atau tarikan benang.', 'aktif', 15, [['assets/images/products/sweater-merah.jpg', 1]]],
                [4, 'Blazer Katun Hitam', 3, 'outerwear', 'M', 275000, 'Sangat Baik', 'Blazer hitam berpotongan relaxed dengan dua kantong dan lining penuh. Bisa dipakai formal maupun kasual.', 'Massimo Dutti', 'Hitam', 'Tidak ada cacat berarti. Sudah dry clean.', 'aktif', 16, [['assets/images/products/blazer-hitam.jpg', 1]]],
                [2, 'Cardigan Rajut Oatmeal', 1, 'atasan', 'Satu Ukuran', 155000, 'Sangat Baik', 'Cardigan rajut warna oatmeal dengan kancing kayu. Jatuh longgar dan nyaman untuk cuaca dingin.', 'Muji', 'Oatmeal', 'Tidak ada cacat. Rajutan rapi tanpa pilling.', 'terjual', 17, [['assets/images/products/rak-netral.jpg', 1]]],
                [3, 'Sneakers Putih Minimalis', 5, 'sepatu', '38', 255000, 'Sangat Baik', 'Sneakers putih dengan siluet bersih dan sol tipis. Cocok untuk gaya minimalis sehari-hari.', 'Adidas', 'Putih', 'Bagian sol sedikit kotor, sudah dibersihkan. Tanpa cacat pada upper.', 'terjual', 18, [['assets/images/products/sneaker-putih.jpg', 1]]],
                [4, 'Kemeja Putih Oversize', 1, 'atasan', 'M', 195000, 'Baik', 'Kemeja putih oversize dengan bahan katun yang ringan. Bisa dipakai sebagai outer maupun atasan utama.', 'Arket', 'Putih', 'Ada sedikit noda samar di bagian manset kanan.', 'terjual', 19, [['assets/images/products/flatlay-putih.jpg', 1]]],
            ];

            $productStmt = $db->prepare(
                'INSERT INTO products (seller_id, title, slug, category_id, type, size, price, item_condition, description, brand, color, defect, status, view_count, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $slugStmt = $db->prepare('SELECT COUNT(*) FROM products WHERE slug = ?');
            $imageStmt = $db->prepare(
                'INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)'
            );
            $productIds = [];
            foreach ($products as $p) {
                $sellerId = $userIds[$p[0] - 1];
                $created = date('Y-m-d H:i:s', $now - ($p[12] * 86400) - random_int(0, 3600));
                $baseSlug = slugify($p[1]);
                $slug = $baseSlug;
                $suffix = 2;
                $slugStmt->execute([$slug]);
                while ((int) $slugStmt->fetchColumn() > 0) {
                    $slug = $baseSlug . '-' . $suffix++;
                    $slugStmt->execute([$slug]);
                }
                $productStmt->execute([
                    $sellerId, $p[1], $slug, $categoryIds[$p[2] - 1], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[9], $p[10], $p[11],
                    random_int(38, 420), $created, $created,
                ]);
                $productId = (int) $db->lastInsertId();
                $productIds[] = $productId;
                foreach ($p[13] as $index => $image) {
                    $imageStmt->execute([$productId, $image[0], $image[1], $index]);
                }
            }

            $orderStmt = $db->prepare(
                'INSERT INTO orders (order_number, buyer_id, seller_id, total_amount, shipping_cost, status, shipping_address, notes, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $orderItemStmt = $db->prepare(
                'INSERT INTO order_items (order_id, product_id, product_title, product_image, price) VALUES (?, ?, ?, ?, ?)'
            );

            $orders = [
                [
                    'RW-260412-8K2M1', 5, 2, 175000, 20000, 'selesai',
                    'Jl. Cendrawasih No. 18, Sukolilo, Surabaya, Jawa Timur 60111',
                    'Tolong dikemas dengan kertas tisu, terima kasih.', '-18 days', '-12 days',
                    [[$productIds[16], 'Cardigan Rajut Oatmeal', 'assets/images/products/rak-netral.jpg', 155000]],
                ],
                [
                    'RW-260509-4T7N3', 5, 3, 280000, 25000, 'dikirim',
                    'Jl. Cendrawasih No. 18, Sukolilo, Surabaya, Jawa Timur 60111',
                    'Boleh tambahkan bubble wrap untuk sepatu.', '-4 days', '-1 days',
                    [[$productIds[17], 'Sneakers Putih Minimalis', 'assets/images/products/sneaker-putih.jpg', 255000]],
                ],
                [
                    'RW-260515-9Q1A6', 6, 2, 213000, 18000, 'menunggu',
                    'Jl. Pandanaran No. 42, Semarang, Jawa Tengah 50241',
                    'Apakah bisa dikirim lewat JNE?', '-5 hours', '-5 hours',
                    [[$productIds[18], 'Kemeja Putih Oversize', 'assets/images/products/flatlay-putih.jpg', 195000]],
                ],
                [
                    'RW-260506-2L8V4', 5, 4, 190000, 15000, 'dibatalkan',
                    'Jl. Cendrawasih No. 18, Sukolilo, Surabaya, Jawa Timur 60111',
                    'Ukuran ternyata tidak sesuai, mohon dibatalkan.', '-9 days', '-8 days',
                    [[$productIds[7], 'Heels Floral Biru', 'assets/images/products/heels-bunga.jpg', 175000]],
                ],
            ];
            $orderIds = [];
            foreach ($orders as $order) {
                $orderStmt->execute([
                    $order[0], $userIds[$order[1] - 1], $userIds[$order[2] - 1], $order[3], $order[4], $order[5], $order[6], $order[7],
                    date('Y-m-d H:i:s', strtotime($order[8])), date('Y-m-d H:i:s', strtotime($order[9])),
                ]);
                $orderId = (int) $db->lastInsertId();
                $orderIds[] = $orderId;
                foreach ($order[10] as $item) {
                    $orderItemStmt->execute([$orderId, $item[0], $item[1], $item[2], $item[3]]);
                }
            }

            $reviewStmt = $db->prepare(
                'INSERT INTO reviews (order_id, product_id, buyer_id, seller_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $reviewStmt->execute([
                $orderIds[0], $productIds[16], $userIds[4], $userIds[1], 5,
                'Cardigannya persis seperti foto, wanginya bersih, dan dikemas rapi. Alya juga cepat membalas chat. Terima kasih!',
                date('Y-m-d H:i:s', strtotime('-11 days')),
            ]);

            $wishlistStmt = $db->prepare('INSERT INTO wishlists (user_id, product_id, created_at) VALUES (?, ?, ?)');
            foreach ([3, 7, 9] as $productIndex) {
                $wishlistStmt->execute([$userIds[4], $productIds[$productIndex - 1], date('Y-m-d H:i:s', $now - random_int(3600, 86400 * 6))]);
            }
            foreach ([5, 12] as $productIndex) {
                $wishlistStmt->execute([$userIds[5], $productIds[$productIndex - 1], date('Y-m-d H:i:s', $now - random_int(3600, 86400 * 3))]);
            }

            $chatStmt = $db->prepare(
                'INSERT INTO chats (product_id, buyer_id, seller_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?)'
            );
            $messageStmt = $db->prepare(
                'INSERT INTO chat_messages (chat_id, sender_id, message, is_read, created_at) VALUES (?, ?, ?, ?, ?)'
            );

            $chatStmt->execute([$productIds[0], $userIds[4], $userIds[1], date('Y-m-d H:i:s', strtotime('-2 days')), date('Y-m-d H:i:s', strtotime('-1 day'))]);
            $chatOne = (int) $db->lastInsertId();
            $messageStmt->execute([$chatOne, $userIds[4], 'Halo Kak Alya, kaos putihnya masih tersedia? Apakah bahannya cukup tebal?', 1, date('Y-m-d H:i:s', strtotime('-2 days -3 hours'))]);
            $messageStmt->execute([$chatOne, $userIds[1], 'Halo Raka! Masih tersedia. Bahannya katun organik, tidak menerawang tapi tetap adem.', 1, date('Y-m-d H:i:s', strtotime('-2 days -2 hours'))]);
            $messageStmt->execute([$chatOne, $userIds[4], 'Baik, kalau untuk tinggi 175 cm kira-kira panjangnya pas di pinggul?', 1, date('Y-m-d H:i:s', strtotime('-1 day -4 hours'))]);
            $messageStmt->execute([$chatOne, $userIds[1], 'Pas di pinggul, potongannya oversize jadi masih ada ruang. Bisa aku kirim foto saat dipakai kalau perlu.', 0, date('Y-m-d H:i:s', strtotime('-1 day'))]);

            $chatStmt->execute([$productIds[1], $userIds[4], $userIds[2], date('Y-m-d H:i:s', strtotime('-1 days')), date('Y-m-d H:i:s', strtotime('-20 hours'))]);
            $chatTwo = (int) $db->lastInsertId();
            $messageStmt->execute([$chatTwo, $userIds[4], 'Halo Kak Dimas, jeans 501 ini ukuran pinggangnya 30 inci ya?', 1, date('Y-m-d H:i:s', strtotime('-1 days -2 hours'))]);
            $messageStmt->execute([$chatTwo, $userIds[2], 'Betul, setara 30 inci. Panjang inseam 76 cm. Bisa COD di sekitar Yogyakarta juga.', 0, date('Y-m-d H:i:s', strtotime('-20 hours'))]);

            $chatStmt->execute([$productIds[6], $userIds[5], $userIds[1], date('Y-m-d H:i:s', strtotime('-8 hours')), date('Y-m-d H:i:s', strtotime('-7 hours'))]);
            $chatThree = (int) $db->lastInsertId();
            $messageStmt->execute([$chatThree, $userIds[5], 'Kak, sneakers pastelnya masih ada? Bisa kirim foto sol bagian dalam?', 0, date('Y-m-d H:i:s', strtotime('-7 hours'))]);

            $reportStmt = $db->prepare(
                'INSERT INTO reports (reporter_id, reported_user_id, product_id, reason, details, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $reportStmt->execute([
                $userIds[5], $userIds[3], $productIds[7], 'Foto tidak sesuai',
                'Warna sepatu terlihat lebih terang di foto. Mohon konfirmasi apakah ada filter warna.',
                'baru', date('Y-m-d H:i:s', strtotime('-3 hours')),
            ]);
            $reportStmt->execute([
                $userIds[4], $userIds[6], null, 'Barang tidak sesuai deskripsi',
                'Pembeli melaporkan bahwa kondisi barang tidak sesuai deskripsi pada transaksi sebelumnya.',
                'diproses', date('Y-m-d H:i:s', strtotime('-2 days')),
            ]);
            $reportStmt->execute([
                $userIds[5], null, $productIds[15], 'Produk diduga tiruan',
                'Logo pada produk terlihat tidak konsisten. Mohon dilakukan peninjauan.',
                'selesai', date('Y-m-d H:i:s', strtotime('-8 days')),
            ]);

            $notificationStmt = $db->prepare(
                'INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $notificationStmt->execute([
                $userIds[1], 'pesanan', 'Pesanan baru masuk', 'Maya Kusuma memesan Kemeja Putih Oversize. Segera konfirmasi pesanan.', '/penjual/pesanan/' . $orderIds[2], 0,
                date('Y-m-d H:i:s', strtotime('-5 hours')),
            ]);
            $notificationStmt->execute([
                $userIds[4], 'pesanan', 'Pesanan sedang dikirim', 'Pesanan ' . $orders[1][0] . ' dari Dimas Aditya sedang dalam perjalanan.', '/pesanan/' . $orderIds[1], 0,
                date('Y-m-d H:i:s', strtotime('-1 days')),
            ]);
            $notificationStmt->execute([
                $userIds[4], 'ulasan', 'Ulasanmu sudah tayang', 'Terima kasih telah memberi ulasan untuk Cardigan Rajut Oatmeal.', '/produk/' . $productIds[16], 1,
                date('Y-m-d H:i:s', strtotime('-11 days')),
            ]);
            $notificationStmt->execute([
                $userIds[0], 'laporan', 'Laporan baru menunggu', 'Ada laporan produk baru yang perlu ditinjau.', '/admin', 0,
                date('Y-m-d H:i:s', strtotime('-3 hours')),
            ]);
            $notificationStmt->execute([
                $userIds[1], 'pesan', 'Pesan baru dari Maya', 'Maya menanyakan kondisi sneakers pastel.', '/chat/' . $chatThree, 0,
                date('Y-m-d H:i:s', strtotime('-7 hours')),
            ]);
            $notificationStmt->execute([
                $userIds[2], 'pesanan', 'Pesanan dalam perjalanan', 'Pembeli menandai pesanan telah diterima. Jangan lupa cek ulasan.', '/penjual/pesanan/' . $orderIds[1], 1,
                date('Y-m-d H:i:s', strtotime('-12 hours')),
            ]);

            $settingsStmt = $db->prepare('INSERT INTO settings (key, value) VALUES (?, ?)');
            $settings = [
                'site_name' => 're:wear',
                'site_tagline' => 'Pakaian punya kehidupan kedua.',
                'contact_email' => 'halo@rewear.id',
                'contact_phone' => '0812 1000 2201',
                'city' => 'Jakarta, Indonesia',
                'instagram' => '@rewear.id',
                'moderation_note' => 'Setiap produk ditinjau secara berkala untuk menjaga kualitas kurasi.',
            ];
            foreach ($settings as $key => $value) {
                $settingsStmt->execute([$key, $value]);
            }

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
