/**
 * Checkout Functions
 * Order processing dan transaction creation
 */

/**
 * Create transaction from cart
 */
async function processCheckout() {
    try {
        // Validate form
        const alamatPengiriman = document.getElementById('alamat_pengiriman').value.trim();
        const metodePembayaran = document.getElementById('metode_pembayaran').value;
        const catatanUser = document.getElementById('catatan_user').value.trim();
        
        if (!alamatPengiriman) {
            UIHelper.showError('⚠️ Alamat pengiriman harus diisi');
            return;
        }
        
        if (!metodePembayaran) {
            UIHelper.showError('⚠️ Metode pembayaran harus dipilih');
            return;
        }
        
        UIHelper.showLoading(true);
        
        // Create transaction
        const transactionData = {
            alamat_pengiriman: alamatPengiriman,
            metode_pembayaran: metodePembayaran,
            catatan_user: catatanUser
        };
        
        const response = await APIHandler.Transactions.create(transactionData);
        
        UIHelper.showLoading(false);
        UIHelper.showSuccess('✅ Pesanan berhasil dibuat!');
        
        // Store transaction ID for next step
        window.lastTransactionId = response.data.transaction_id;
        window.lastTransactionCode = response.data.kode_transaksi;
        
        // Redirect to payment page
        setTimeout(() => {
            window.location.href = `./proses-pembayaran.php?id=${response.data.transaction_id}`;
        }, 1500);
        
    } catch (error) {
        UIHelper.showLoading(false);
        UIHelper.showError('❌ Gagal membuat pesanan: ' + error.message);
    }
}

/**
 * Display checkout summary
 */
async function loadCheckoutSummary() {
    try {
        const summaryContainer = document.getElementById('checkout-summary');
        if (!summaryContainer) return;
        
        const response = await APIHandler.Cart.get();
        const items = response.data.items;
        const summary = response.data.summary;
        
        if (items.length === 0) {
            window.location.href = './keranjang.php';
            return;
        }
        
        // Display items in summary
        let itemsHtml = '';
        items.forEach(item => {
            itemsHtml += `
                <div class="row mb-2">
                    <div class="col-6">${item.nama} (x${item.jumlah})</div>
                    <div class="col-6 text-end">${UIHelper.formatRupiah(item.subtotal)}</div>
                </div>
            `;
        });
        
        summaryContainer.innerHTML = `
            <h5 class="mb-3">📦 Ringkasan Pesanan</h5>
            ${itemsHtml}
            <hr>
            <div class="row mb-3">
                <div class="col-6 fw-bold">Total:</div>
                <div class="col-6 text-end fw-bold">${UIHelper.formatRupiah(summary.total_price)}</div>
            </div>
        `;
    } catch (error) {
        UIHelper.showError('❌ Gagal memuat ringkasan pesanan');
    }
}

/**
 * Display transaction details
 */
async function loadTransactionDetails(transactionId) {
    try {
        UIHelper.showLoading(true);
        
        const response = await APIHandler.Transactions.getById(transactionId);
        const transaction = response.data;
        
        UIHelper.showLoading(false);
        
        const detailsContainer = document.getElementById('transaction-details');
        if (!detailsContainer) return;
        
        // Display transaction info
        let itemsHtml = '';
        transaction.items.forEach(item => {
            itemsHtml += `
                <tr>
                    <td>${item.nama_produk}</td>
                    <td class="text-center">${item.jumlah}</td>
                    <td class="text-end">${UIHelper.formatRupiah(item.harga_satuan)}</td>
                    <td class="text-end">${UIHelper.formatRupiah(item.subtotal)}</td>
                </tr>
            `;
        });
        
        detailsContainer.innerHTML = `
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Nomor Pesanan: ${transaction.kode_transaksi}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Status:</strong> 
                                <span class="badge bg-info">${transaction.status}</span>
                            </p>
                            <p><strong>Tanggal:</strong> ${new Date(transaction.tanggal_transaksi).toLocaleDateString('id-ID')}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Metode Pembayaran:</strong> ${transaction.metode_pembayaran}</p>
                            <p><strong>Alamat Pengiriman:</strong> ${transaction.alamat_pengiriman}</p>
                        </div>
                    </div>
                    
                    <h6 class="mb-3">Detail Produk:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHtml}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold">${UIHelper.formatRupiah(transaction.total)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    ${transaction.no_resi ? `
                        <p class="mt-3">
                            <strong>No. Resi:</strong> ${transaction.no_resi}
                        </p>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        UIHelper.showError('❌ Gagal memuat detail transaksi');
    }
}

/**
 * Load user transactions history
 */
async function loadTransactionsHistory(page = 1) {
    try {
        const historyContainer = document.getElementById('transactions-history');
        if (!historyContainer) return;
        
        UIHelper.showLoading(true);
        
        const response = await APIHandler.Transactions.getAll(page, 10);
        const transactions = response.data.transactions;
        const pagination = response.data.pagination;
        
        UIHelper.showLoading(false);
        
        if (transactions.length === 0) {
            historyContainer.innerHTML = `
                <div class="alert alert-info text-center">
                    Belum ada riwayat transaksi
                </div>
            `;
            return;
        }
        
        // Display transactions
        let html = '';
        transactions.forEach(txn => {
            const statusBadge = getStatusBadge(txn.status);
            html += `
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-1">${txn.kode_transaksi}</h6>
                                <small class="text-muted">${new Date(txn.tanggal_transaksi).toLocaleDateString('id-ID')}</small>
                            </div>
                            <div class="col-md-3">
                                ${statusBadge}
                            </div>
                            <div class="col-md-3 text-end">
                                <strong>${UIHelper.formatRupiah(txn.total)}</strong><br>
                                <a href="#" onclick="loadTransactionDetails(${txn.id}); return false;" 
                                   class="btn btn-sm btn-primary mt-2">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        historyContainer.innerHTML = html;
        
    } catch (error) {
        UIHelper.showError('❌ Gagal memuat riwayat transaksi');
    }
}

/**
 * Get status badge HTML
 */
function getStatusBadge(status) {
    const badges = {
        'Menunggu Pembayaran': '<span class="badge bg-warning">Menunggu Pembayaran</span>',
        'Pembayaran Dikonfirmasi': '<span class="badge bg-info">Pembayaran Dikonfirmasi</span>',
        'Diproses': '<span class="badge bg-primary">Diproses</span>',
        'Dikirim': '<span class="badge bg-secondary">Dikirim</span>',
        'Selesai': '<span class="badge bg-success">Selesai</span>',
        'Dibatalkan': '<span class="badge bg-danger">Dibatalkan</span>'
    };
    return badges[status] || `<span class="badge bg-secondary">${status}</span>`;
}

/**
 * Initialize checkout page
 */
document.addEventListener('DOMContentLoaded', function() {
    // Load checkout summary if on checkout page
    if (document.getElementById('checkout-summary')) {
        loadCheckoutSummary();
    }
    
    // Load transactions history if on payment/history page
    if (document.getElementById('transactions-history')) {
        loadTransactionsHistory();
    }
    
    // Handle checkout form submission
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            processCheckout();
        });
    }
});
