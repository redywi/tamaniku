document.addEventListener('DOMContentLoaded', () => {
    const path = window.location.pathname.split("/").pop();

    // Add logout confirmation for admin pages
    const logoutLinks = document.querySelectorAll('a[href="logout.php"], a.logout-link');
    logoutLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            showLogoutConfirmation(this.href);
        });
    });

    // Login form validation and enhancement
    if (path === 'login.php') {
        const loginForm = document.querySelector('form[action="login.php"]');
        if (loginForm) {
            // Real-time validation
            const usernameField = document.getElementById('username');
            const passwordField = document.getElementById('password');
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const loginContainer = document.querySelector('.login-container');
            
            // Add validation messages
            function addValidationMessage(input, message) {
                let msgDiv = input.parentNode.querySelector('.validation-message');
                if (!msgDiv) {
                    msgDiv = document.createElement('div');
                    msgDiv.className = 'validation-message';
                    input.parentNode.appendChild(msgDiv);
                }
                msgDiv.textContent = message;
                msgDiv.classList.add('show');
            }
            
            function removeValidationMessage(input) {
                const msgDiv = input.parentNode.querySelector('.validation-message');
                if (msgDiv) {
                    msgDiv.classList.remove('show');
                }
            }
            
            function validateForm() {
                const username = usernameField.value.trim();
                const password = passwordField.value;
                let isValid = true;
                
                // Validate username
                if (username.length === 0) {
                    addValidationMessage(usernameField, 'Username harus diisi');
                    isValid = false;
                } else if (username.length < 3) {
                    addValidationMessage(usernameField, 'Username minimal 3 karakter');
                    isValid = false;
                } else {
                    removeValidationMessage(usernameField);
                }
                
                // Validate password
                if (password.length === 0) {
                    addValidationMessage(passwordField, 'Password harus diisi');
                    isValid = false;
                } else if (password.length < 3) {
                    addValidationMessage(passwordField, 'Password minimal 3 karakter');
                    isValid = false;
                } else {
                    removeValidationMessage(passwordField);
                }
                
                if (isValid) {
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                } else {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.6';
                }
                
                return isValid;
            }
            
            usernameField.addEventListener('input', validateForm);
            passwordField.addEventListener('input', validateForm);
            usernameField.addEventListener('blur', validateForm);
            passwordField.addEventListener('blur', validateForm);
            validateForm(); // Initial check
            
            loginForm.addEventListener('submit', function(e) {
                const username = usernameField.value.trim();
                const password = passwordField.value;
                
                if (!validateForm()) {
                    e.preventDefault();
                    showAlert('Silakan perbaiki error pada form!', 'error');
                    return;
                }
                
                // Show loading state
                const originalText = submitBtn.textContent;
                submitBtn.innerHTML = '<span class="loading"></span> Memproses Login...';
                submitBtn.disabled = true;
                loginContainer.classList.add('loading');
                
                // Reset button after 10 seconds if form doesn't redirect
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    loginContainer.classList.remove('loading');
                }, 10000);
            });
            
            // Auto-focus username field
            usernameField.focus();
        }
    }

    // Form enhancements for admin pages
    if (path.includes('tambah.php') || path.includes('edit.php')) {
        enhanceAdminForms();
    }

    // Filter enhancements for admin pages
    if (path.includes('admin/')) {
        enhanceFilters();
    }

    // Product and category page enhancements
    if (path === 'index.php' || path === '') {
        loadProducts();
        loadCategories();
        
        // Setup front-end filters
        setupProductFilters();
    }

    if (path === 'detail_produk.php') {
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('id');
        if (productId) {
            loadProductDetail(productId);
        }
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.error, .success');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        }, 5000);
    });
});

function enhanceAdminForms() {
    // File upload preview
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                // Remove existing preview
                const existingPreview = input.parentNode.querySelector('.file-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }
                
                // Create preview
                const preview = document.createElement('div');
                preview.className = 'file-preview';
                preview.style.cssText = 'margin-top: 10px;';
                
                const img = document.createElement('img');
                img.style.cssText = 'max-width: 150px; max-height: 150px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);';
                img.src = URL.createObjectURL(file);
                
                const info = document.createElement('p');
                info.style.cssText = 'margin: 5px 0 0 0; font-size: 0.9em; color: #666;';
                info.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                
                preview.appendChild(img);
                preview.appendChild(info);
                input.parentNode.appendChild(preview);
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                const originalText = submitBtn.textContent;
                submitBtn.innerHTML = '<span class="loading"></span> ' + originalText;
                submitBtn.disabled = true;
                
                // Re-enable after 10 seconds in case of error
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 10000);
            }
        });
    });

    // Number input validation
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            const min = parseFloat(this.min) || 0;
            const max = parseFloat(this.max) || Infinity;
            
            if (value < min) {
                this.setCustomValidity(`Nilai minimal ${min}`);
            } else if (value > max) {
                this.setCustomValidity(`Nilai maksimal ${max}`);
            } else {
                this.setCustomValidity('');
            }
        });
    });

    // Auto-resize textarea
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
}

async function loadProducts(filters = {}) {
    const productGrid = document.getElementById('product-grid');
    const resultsSummary = document.getElementById('results-summary');
    if (!productGrid) return;
    
    productGrid.innerHTML = '<div style="text-align: center; padding: 40px;"><span class="loading"></span> Memuat produk...</div>';
    
    if (resultsSummary) {
        resultsSummary.innerHTML = '<span class="results-info">Memuat produk...</span>';
    }

    try {
        // Build query parameters
        const params = new URLSearchParams();
        if (filters.category) params.append('kategori', filters.category);
        if (filters.search) params.append('search', filters.search);
        if (filters.price_min) params.append('harga_min', filters.price_min);
        if (filters.price_max) params.append('harga_max', filters.price_max);
        if (filters.sort) params.append('sort', filters.sort);
        
        const url = `api/produk.php${params.toString() ? '?' + params.toString() : ''}`;
        const response = await fetch(url);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            productGrid.innerHTML = '';
            
            // Update results summary
            if (resultsSummary) {
                const totalProducts = result.data.length;
                const activeFilters = Object.values(filters).filter(f => f && f.trim() !== '').length;
                let summaryText = `Menampilkan ${totalProducts} produk`;
                if (activeFilters > 0) {
                    summaryText += ` (${activeFilters} filter aktif)`;
                }
                resultsSummary.innerHTML = `<span class="results-info">${summaryText}</span>`;
            }
            
            result.data.forEach(product => {
                const productCard = `
                    <div class="product-card">
                        <div class="product-image">
                            ${product.gambar ? 
                                `<img src="uploads/${product.gambar}" alt="${product.nama_produk}" loading="lazy">` : 
                                '<div class="no-image">No Image</div>'
                            }
                        </div>
                        <div class="product-info">
                            <h3>${product.nama_produk}</h3>
                            <p class="category">${product.nama_kategori}</p>
                            <p class="description">${product.deskripsi ? product.deskripsi.substring(0, 100) + '...' : 'Tidak ada deskripsi'}</p>
                            <p class="price">Rp ${new Intl.NumberFormat('id-ID').format(product.harga)}</p>
                            <p class="stock">Stok: ${product.stok}</p>
                            <div class="product-actions">
                                <a href="detail_produk.php?id=${product.id}" class="btn-detail">Lihat Detail</a>
                                <button class="btn-pesan" data-id="${product.id}" data-nama="${product.nama_produk}" onclick="handlePesan(event)">
                                    Pesan Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                productGrid.innerHTML += productCard;
            });
        } else {
            productGrid.innerHTML = '<div style="text-align: center; padding: 40px; color: #666;">Tidak ada produk ditemukan.</div>';
            if (resultsSummary) {
                resultsSummary.innerHTML = '<span class="results-info">Tidak ada produk ditemukan</span>';
            }
        }
    } catch (error) {
        console.error('Error loading products:', error);
        productGrid.innerHTML = '<div style="text-align: center; padding: 40px; color: #e74c3c;">Gagal memuat produk. Silakan coba lagi.</div>';
        if (resultsSummary) {
            resultsSummary.innerHTML = '<span class="results-info">Gagal memuat produk</span>';
        }
    }
}

async function loadCategories() {
    const categorySelect = document.getElementById('category-filter');
    const categoryFilters = document.getElementById('category-filters');

    try {
        const response = await fetch('api/kategori.php');
        const result = await response.json();

        if (result.success) {
            // Populate category select dropdown
            if (categorySelect) {
                let selectHTML = '<option value="">Semua Kategori</option>';
                result.data.forEach(category => {
                    selectHTML += `<option value="${category.id}">${category.nama_kategori}</option>`;
                });
                categorySelect.innerHTML = selectHTML;
            }
            
            // Legacy filter buttons (if exists)
            if (categoryFilters) {
                let filtersHTML = '<button class="active" data-id="">Semua Kategori</button>';
                result.data.forEach(category => {
                    filtersHTML += `<button data-id="${category.id}">${category.nama_kategori}</button>`;
                });
                categoryFilters.innerHTML = filtersHTML;

                // Add event listener to filter buttons
                categoryFilters.querySelectorAll('button').forEach(button => {
                    button.addEventListener('click', () => {
                        // Update active state
                        categoryFilters.querySelector('button.active').classList.remove('active');
                        button.classList.add('active');
                        
                        // Load filtered products
                        const filters = button.dataset.id ? { category: button.dataset.id } : {};
                        loadProducts(filters);
                    });
                });
            }
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

async function loadProductDetail(id) {
    const productDetailContainer = document.getElementById('product-detail');
    if (!productDetailContainer) return;
    
    productDetailContainer.innerHTML = '<div style="text-align: center; padding: 40px;"><span class="loading"></span> Memuat detail produk...</div>';

    try {
        const response = await fetch(`api/produk.php?id=${id}`);
        const result = await response.json();

        if (result.success && result.data) {
            const product = result.data;
            const detailHTML = `
                <div class="product-detail-image">
                    <img src="uploads/${product.gambar}" alt="${product.nama_produk}"
                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjBmMGYwIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIyMCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='">
                </div>
                <div class="product-detail-info">
                    <span class="category">${product.nama_kategori}</span>
                    <h2>${product.nama_produk}</h2>
                    <p class="price">Rp ${parseInt(product.harga).toLocaleString('id-ID')}</p>
                    <p class="stock">Stok: <span style="font-weight: bold; color: ${product.stok > 0 ? '#27ae60' : '#e74c3c'}">${product.stok > 0 ? product.stok : 'Habis'}</span></p>
                    <div class="description">
                        <h3>Deskripsi</h3>
                        <p>${product.deskripsi ? product.deskripsi.replace(/\n/g, '<br>') : 'Tidak ada deskripsi tersedia.'}</p>
                    </div>
                    <button id="pesan-btn" class="btn-pesan" data-id="${product.id}" data-nama="${product.nama_produk}" ${product.stok <= 0 ? 'disabled' : ''}>
                        ${product.stok > 0 ? 'Pesan via WhatsApp' : 'Stok Habis'}
                    </button>
                </div>
            `;
            productDetailContainer.innerHTML = detailHTML;

            // Add event listener for order button
            const pesanBtn = document.getElementById('pesan-btn');
            if (pesanBtn && !pesanBtn.disabled) {
                pesanBtn.addEventListener('click', handlePesan);
            }
        } else {
            productDetailContainer.innerHTML = '<div style="text-align: center; padding: 40px; color: #e74c3c;"><p>Produk tidak ditemukan.</p></div>';
        }
    } catch (error) {
        productDetailContainer.innerHTML = '<div style="text-align: center; padding: 40px; color: #e74c3c;"><p>Gagal memuat detail produk.</p></div>';
        console.error('Error loading product detail:', error);
    }
}

function handlePesan(event) {
    const button = event.target;
    const productId = button.dataset.id;
    const productName = button.dataset.nama;

    // Admin WhatsApp number (ganti dengan nomor yang sesuai)
    const nomorAdmin = '6281234567890';
    const pesan = `Halo Tamaniku, saya tertarik untuk memesan produk: ${productName} (ID: ${productId}). Mohon informasinya.`;

    // Customer data collection
    const namaPelanggan = prompt("Masukkan nama Anda:", "");
    if (!namaPelanggan || namaPelanggan.trim() === "") {
        showAlert("Nama harus diisi!", "error");
        return;
    }

    const nomorWhatsapp = prompt("Masukkan nomor WhatsApp Anda (contoh: 628xxxxxxxxxx):", "");
    if (!nomorWhatsapp || nomorWhatsapp.trim() === "") {
        showAlert("Nomor WhatsApp harus diisi!", "error");
        return;
    }

    // Validate WhatsApp number format
    const waRegex = /^(\+?62|0)8[1-9][0-9]{6,9}$/;
    if (!waRegex.test(nomorWhatsapp.replace(/\s+/g, ''))) {
        showAlert("Format nomor WhatsApp tidak valid! Contoh: 628123456789", "error");
        return;
    }

    // Show loading state
    const originalText = button.textContent;
    button.innerHTML = '<span class="loading"></span> Memproses...';
    button.disabled = true;

    // Send order data to API
    fetch('api/pesanan.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id_produk: productId,
            nama_pelanggan: namaPelanggan.trim(),
            nomor_whatsapp: nomorWhatsapp.replace(/\s+/g, '')
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert('Terima kasih! Pesanan Anda telah kami catat.', 'success');
            // Open WhatsApp
            const linkWA = `https://wa.me/${nomorAdmin}?text=${encodeURIComponent(pesan)}`;
            window.open(linkWA, '_blank');
        } else {
            showAlert('Maaf, terjadi kesalahan saat mencatat pesanan. Silakan coba lagi.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan koneksi. Silakan coba lagi.', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function enhanceFilters() {
    // Auto-submit filter forms on change
    const filterForm = document.querySelector('.filter-form');
    if (filterForm) {
        const searchInput = filterForm.querySelector('#search');
        const selectInputs = filterForm.querySelectorAll('select');
        const dateInputs = filterForm.querySelectorAll('input[type="date"]');
        
        // Debounce search input
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.length >= 3 || this.value.length === 0) {
                        filterForm.submit();
                    }
                }, 500);
            });
        }
        
        // Auto-submit on select change
        selectInputs.forEach(select => {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });
        
        // Auto-submit on date change
        dateInputs.forEach(dateInput => {
            dateInput.addEventListener('change', function() {
                filterForm.submit();
            });
        });
        
        // Price input debounce
        const priceInputs = filterForm.querySelectorAll('.price-input');
        priceInputs.forEach(input => {
            let priceTimeout;
            input.addEventListener('input', function() {
                clearTimeout(priceTimeout);
                priceTimeout = setTimeout(() => {
                    filterForm.submit();
                }, 1000);
            });
        });
    }
    
    // Enhanced table interactions
    const statusSelects = document.querySelectorAll('.status-form select');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            if (form && confirm('Yakin ingin mengubah status pesanan?')) {
                // Add loading state
                this.disabled = true;
                this.style.opacity = '0.6';
                
                // Submit form
                form.submit();
            } else {
                // Reset select if cancelled
                this.selectedIndex = 0;
            }
        });
    });
    
    // Table row highlighting
    const tableRows = document.querySelectorAll('table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#e3f2fd';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
    
    // Filter statistics
    updateFilterStats();
}

function updateFilterStats() {
    const resultsInfo = document.querySelector('.results-info');
    const filterForm = document.querySelector('.filter-form');
    
    if (resultsInfo && filterForm) {
        const formData = new FormData(filterForm);
        let activeFilters = 0;
        
        for (let [key, value] of formData.entries()) {
            if (value && value.trim() !== '') {
                activeFilters++;
            }
        }
        
        if (activeFilters > 0) {
            const filterBadge = document.createElement('span');
            filterBadge.className = 'filter-badge';
            filterBadge.textContent = `${activeFilters} filter aktif`;
            filterBadge.style.cssText = `
                background: #007bff;
                color: white;
                padding: 2px 8px;
                border-radius: 12px;
                font-size: 0.8em;
                margin-left: 10px;
            `;
            
            if (!resultsInfo.querySelector('.filter-badge')) {
                resultsInfo.appendChild(filterBadge);
            }
        }
    }
}

// Function to show custom alerts
function showAlert(message, type = 'info') {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.custom-alert');
    existingAlerts.forEach(alert => alert.remove());

    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `custom-alert ${type}`;
    alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        max-width: 350px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    `;

    // Set background color based on type
    switch(type) {
        case 'error':
            alertDiv.style.background = 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)';
            break;
        case 'success':
            alertDiv.style.background = 'linear-gradient(135deg, #27ae60 0%, #2ecc71 100%)';
            break;
        case 'warning':
            alertDiv.style.background = 'linear-gradient(135deg, #f39c12 0%, #e67e22 100%)';
            break;
        default:
            alertDiv.style.background = 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)';
    }

    alertDiv.textContent = message;

    // Add close button
    const closeBtn = document.createElement('span');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = `
        float: right;
        margin-left: 15px;
        cursor: pointer;
        font-size: 20px;
        line-height: 1;
        opacity: 0.8;
    `;
    closeBtn.onclick = () => hideAlert(alertDiv);

    alertDiv.appendChild(closeBtn);
    document.body.appendChild(alertDiv);

    // Show alert with animation
    setTimeout(() => {
        alertDiv.style.opacity = '1';
        alertDiv.style.transform = 'translateX(0)';
    }, 100);

    // Auto hide after 5 seconds
    setTimeout(() => hideAlert(alertDiv), 5000);
}

function hideAlert(alertElement) {
    alertElement.style.opacity = '0';
    alertElement.style.transform = 'translateX(100%)';
    setTimeout(() => {
        if (alertElement.parentNode) {
            alertElement.parentNode.removeChild(alertElement);
        }
    }, 300);
}

// Function to show logout confirmation
function showLogoutConfirmation(logoutUrl) {
    // Remove existing modals
    const existingModals = document.querySelectorAll('.logout-modal');
    existingModals.forEach(modal => modal.remove());

    // Create modal overlay
    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'logout-modal';
    modalOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;

    // Create modal content
    const modalContent = document.createElement('div');
    modalContent.style.cssText = `
        background-color: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        text-align: center;
        max-width: 400px;
        width: 90%;
        transform: scale(0.7);
        transition: transform 0.3s ease;
    `;

    modalContent.innerHTML = `
        <h3 style="color: #e74c3c; margin-bottom: 20px; font-size: 1.5em;">Konfirmasi Logout</h3>
        <p style="margin-bottom: 30px; color: #666; line-height: 1.6;">Apakah Anda yakin ingin keluar dari panel admin?</p>
        <div style="display: flex; gap: 15px; justify-content: center;">
            <button id="confirm-logout" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.3s ease;">Ya, Logout</button>
            <button id="cancel-logout" style="background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%); color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.3s ease;">Batal</button>
        </div>
    `;

    modalOverlay.appendChild(modalContent);
    document.body.appendChild(modalOverlay);

    // Show modal with animation
    setTimeout(() => {
        modalOverlay.style.opacity = '1';
        modalContent.style.transform = 'scale(1)';
    }, 10);

    // Add event listeners
    document.getElementById('confirm-logout').addEventListener('click', () => {
        window.location.href = logoutUrl;
    });

    document.getElementById('cancel-logout').addEventListener('click', () => {
        closeModal();
    });

    // Close modal when clicking overlay
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) {
            closeModal();
        }
    });

    // Close modal with ESC key
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            closeModal();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);

    function closeModal() {
        modalOverlay.style.opacity = '0';
        modalContent.style.transform = 'scale(0.7)';
        setTimeout(() => {
            if (modalOverlay.parentNode) {
                modalOverlay.parentNode.removeChild(modalOverlay);
            }
        }, 300);
    }
}

// Setup product filters for front-end page
function setupProductFilters() {
    const searchInput = document.getElementById('search-product');
    const categorySelect = document.getElementById('category-filter');
    const priceSelect = document.getElementById('price-range');
    const sortSelect = document.getElementById('sort-products');
    const resetBtn = document.getElementById('reset-filter');
    
    let searchTimeout;
    
    // Search input with debounce
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 500);
        });
    }
    
    // Filter selects
    [categorySelect, priceSelect, sortSelect].forEach(select => {
        if (select) {
            select.addEventListener('change', applyFilters);
        }
    });
    
    // Reset button
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (categorySelect) categorySelect.value = '';
            if (priceSelect) priceSelect.value = '';
            if (sortSelect) sortSelect.value = 'nama';
            applyFilters();
        });
    }
    
    function applyFilters() {
        const filters = {};
        
        if (searchInput && searchInput.value.trim()) {
            filters.search = searchInput.value.trim();
        }
        
        if (categorySelect && categorySelect.value) {
            filters.category = categorySelect.value;
        }
        
        if (priceSelect && priceSelect.value) {
            const priceRange = priceSelect.value.split('-');
            if (priceRange.length === 2) {
                filters.price_min = priceRange[0];
                filters.price_max = priceRange[1];
            }
        }
        
        if (sortSelect && sortSelect.value) {
            filters.sort = sortSelect.value;
        }
        
        loadProducts(filters);
    }
}