<?php
$isEdit = $product !== null;
$action = $isEdit ? url('/penjual/produk/' . $product['id']) : url('/penjual/produk');
$titleValue = old('title', $product['title'] ?? '');
$categoryValue = (int) old('category_id', $product['category_id'] ?? 0);
$typeValue = old('type', $product['type'] ?? '');
$sizeValue = old('size', $product['size'] ?? '');
$priceValue = old('price', $product['price'] ?? '');
$conditionValue = old('condition', $product['item_condition'] ?? '');
$brandValue = old('brand', $product['brand'] ?? '');
$colorValue = old('color', $product['color'] ?? '');
$descriptionValue = old('description', $product['description'] ?? '');
$defectValue = old('defect', $product['defect'] ?? '');
?>
<section class="dashboard-page product-form-page">
    <header class="dashboard-page__head">
        <div>
            <span class="eyebrow"><?= $isEdit ? 'Perbarui etalase' : 'Produk baru' ?></span>
            <h1><?= $isEdit ? 'Ubah <em>produk.</em>' : 'Tambah <em>produk.</em>' ?></h1>
            <p class="dashboard-page__sub">Tulis apa adanya. Pembeli yang tepat akan menghargai kejujuranmu.</p>
        </div>
        <a href="<?= url('/penjual/produk') ?>" class="text-link"><?= icon('arrow-left') ?> Kembali ke produk</a>
    </header>

    <form action="<?= $action ?>" method="post" enctype="multipart/form-data" class="product-form-layout" data-product-form>
        <?= csrf_field() ?>
        <div class="product-form-main">
            <section class="form-section panel-card">
                <div class="form-section__head">
                    <span class="form-step">01</span>
                    <div><h2>Identitas barang</h2><p>Nama yang jelas membantu pembeli menemukan produkmu.</p></div>
                </div>
                <div class="field">
                    <label for="product-title">Nama produk</label>
                    <input id="product-title" type="text" name="title" value="<?= $titleValue ?>" placeholder="Contoh: Kemeja Linen Putih Oversize" minlength="5" maxlength="100" required>
                    <small class="field-help">5–100 karakter. Sebutkan jenis dan warna utama.</small>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="product-category">Kategori</label>
                        <select id="product-category" name="category_id" required data-category-select>
                            <option value="">Pilih kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" data-type="<?= e($category['type']) ?>" <?= $categoryValue === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="product-type">Jenis barang</label>
                        <select id="product-type" name="type" required data-type-select>
                            <option value="">Pilih jenis</option>
                            <?php foreach (product_types() as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= $typeValue === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="field-help">Otomatis mengikuti kategori yang dipilih.</small>
                    </div>
                </div>
                <div class="field-row field-row--three">
                    <div class="field">
                        <label for="product-size">Ukuran</label>
                        <select id="product-size" name="size" required>
                            <option value="">Pilih ukuran</option>
                            <?php foreach (sizes() as $size): ?>
                                <option value="<?= e($size) ?>" <?= $sizeValue === $size ? 'selected' : '' ?>><?= e($size) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="product-condition">Kondisi</label>
                        <select id="product-condition" name="condition" required>
                            <option value="">Pilih kondisi</option>
                            <?php foreach (conditions() as $condition): ?>
                                <option value="<?= e($condition) ?>" <?= $conditionValue === $condition ? 'selected' : '' ?>><?= e($condition) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="product-price">Harga</label>
                        <div class="currency-input">
                            <span>Rp</span>
                            <input id="product-price" type="number" name="price" value="<?= $priceValue ?>" min="10000" max="100000000" step="1000" placeholder="150000" required>
                        </div>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="product-brand">Merek <span class="optional">(opsional)</span></label>
                        <input id="product-brand" type="text" name="brand" value="<?= $brandValue ?>" maxlength="60" placeholder="Uniqlo, Levi's, …">
                    </div>
                    <div class="field">
                        <label for="product-color">Warna <span class="optional">(opsional)</span></label>
                        <input id="product-color" type="text" name="color" value="<?= $colorValue ?>" maxlength="40" placeholder="Putih, indigo, camel, …">
                    </div>
                </div>
            </section>

            <section class="form-section panel-card">
                <div class="form-section__head">
                    <span class="form-step">02</span>
                    <div><h2>Ceritakan kondisinya</h2><p>Detail kecil yang jujur membuat pembeli lebih percaya.</p></div>
                </div>
                <div class="field">
                    <label for="product-description">Deskripsi</label>
                    <textarea id="product-description" name="description" rows="6" minlength="30" maxlength="2000" placeholder="Bahan, potongan, kapan terakhir dipakai, dan hal yang membuat barang ini layak diberi cerita baru…" required><?= $descriptionValue ?></textarea>
                    <small class="field-help">30–2.000 karakter. Hindari klaim berlebihan.</small>
                </div>
                <div class="field">
                    <label for="product-defect">Catatan kondisi / cacat</label>
                    <textarea id="product-defect" name="defect" rows="4" minlength="3" maxlength="300" placeholder="Contoh: Ada lecet halus di bagian tumit kanan. Tidak ada lubang atau noda." required><?= $defectValue ?></textarea>
                    <small class="field-help">Wajib diisi. Jika tidak ada cacat, tulis “Tidak ada cacat”.</small>
                </div>
            </section>

            <section class="form-section panel-card">
                <div class="form-section__head">
                    <span class="form-step">03</span>
                    <div><h2>Foto barang</h2><p>1–5 foto. Foto pertama akan menjadi sampul etalase.</p></div>
                </div>

                <?php if ($isEdit && $images): ?>
                    <div class="existing-images">
                        <span class="filter-label">Foto saat ini</span>
                        <div class="existing-images__grid">
                            <?php foreach ($images as $image): ?>
                                <label class="existing-image">
                                    <img src="<?= e(product_image($image['image_path'])) ?>" alt="Foto produk">
                                    <?php if ($image['is_primary']): ?><span class="existing-image__primary">Sampul</span><?php endif; ?>
                                    <span class="existing-image__remove">
                                        <input type="checkbox" name="remove_images[]" value="<?= (int) $image['id'] ?>">
                                        <span>Hapus</span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <small class="field-help">Centang foto yang ingin dihapus. Minimal satu foto harus tetap ada.</small>
                    </div>
                <?php endif; ?>

                <label class="upload-zone" for="product-images" data-upload-zone>
                    <span class="upload-zone__icon"><?= icon('plus') ?></span>
                    <span class="upload-zone__title">Pilih foto atau seret ke sini</span>
                    <span class="upload-zone__hint">JPG, PNG, atau WEBP · maksimal 4 MB per foto · maksimal 5 foto</span>
                    <input id="product-images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple data-image-input <?= $isEdit ? '' : 'required' ?>>
                </label>
                <div class="image-preview-grid" data-image-preview></div>
            </section>
        </div>

        <aside class="product-form-aside">
            <section class="panel-card form-submit-card">
                <span class="eyebrow"><?= $isEdit ? 'Simpan perubahan' : 'Terbitkan produk' ?></span>
                <h2><?= $isEdit ? 'Perbarui etalase.' : 'Siap menemukan pemilik baru?' ?></h2>
                <p>Produk akan langsung tayang dan dapat ditemukan pembeli. Kamu dapat mengeditnya kapan saja selama belum terjual.</p>
                <button type="submit" class="button button--dark button--full"><?= $isEdit ? 'Simpan Perubahan' : 'Terbitkan Produk' ?> <?= icon('arrow-right') ?></button>
                <a href="<?= url('/penjual/produk') ?>" class="text-link text-link--center">Batal</a>
                <div class="form-submit-card__note"><?= icon('shield') ?> Dengan menerbitkan, kamu menyatakan foto dan kondisi barang sesuai kenyataan.</div>
            </section>

            <section class="panel-card checklist-card">
                <span class="eyebrow">Sebelum terbit</span>
                <h2>Checklist foto.</h2>
                <ul class="check-list">
                    <li><?= icon('check') ?> Cahaya alami, tanpa filter warna berlebihan</li>
                    <li><?= icon('check') ?> Latar bersih dan tidak mengalihkan perhatian</li>
                    <li><?= icon('check') ?> Tampak depan, belakang, dan detail bahan</li>
                    <li><?= icon('check') ?> Label ukuran dan merek bila ada</li>
                    <li><?= icon('check') ?> Cacat kecil difoto dari dekat</li>
                </ul>
            </section>

            <section class="panel-card panel-card--accent product-form-tip">
                <span class="eyebrow">Catatan</span>
                <p>Barang yang sudah terjual tidak dapat diedit atau dihapus karena terhubung dengan riwayat pesanan. Pastikan detailnya sudah benar sebelum transaksi.</p>
            </section>
        </aside>
    </form>
</section>
