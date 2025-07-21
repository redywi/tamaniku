<?php include 'templates/header.php';?>

<div class="hero-section">
    <h2>Temukan Keindahan Alam di Rumah Anda</h2>
    <p>Jelajahi koleksi tanaman hias terbaik kami dengan lebih dari 40 jenis tanaman.</p>
</div>

<div class="filter-section">
    <div class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label for="search-product">Cari Tanaman:</label>
                <input type="text" id="search-product" placeholder="Nama tanaman..." class="search-input">
            </div>
            
            <div class="filter-group">
                <label for="category-filter">Kategori:</label>
                <select id="category-filter" class="kategori-select">
                    <option value="">Semua Kategori</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="price-range">Rentang Harga:</label>
                <select id="price-range" class="price-input">
                    <option value="">Semua Harga</option>
                    <option value="0-50000">Rp 0 - Rp 50.000</option>
                    <option value="50000-100000">Rp 50.000 - Rp 100.000</option>
                    <option value="100000-200000">Rp 100.000 - Rp 200.000</option>
                    <option value="200000-999999">Di atas Rp 200.000</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="sort-products">Urutkan:</label>
                <select id="sort-products" class="sort-select">
                    <option value="nama">Nama (A-Z)</option>
                    <option value="harga_asc">Harga (Termurah)</option>
                    <option value="harga_desc">Harga (Termahal)</option>
                    <option value="terbaru">Terbaru</option>
                </select>
            </div>
            
            <div class="filter-group filter-actions">
                <button id="reset-filter" class="btn-reset">Reset</button>
            </div>
        </div>
    </div>
</div>

<div class="results-summary" id="results-summary">
    <span class="results-info">Memuat produk...</span>
</div>

<div class="product-grid" id="product-grid">
    <!-- Products will be loaded here via JavaScript -->
</div>

<?php include 'templates/footer.php';?>