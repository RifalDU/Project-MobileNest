/**
 * MobileNest API Handler
 * Wrapper untuk semua API calls dengan error handling
 */

const API_BASE_URL = './api';

class APIHandler {
    /**
     * Make API request
     */
    static async request(endpoint, options = {}) {
        const method = options.method || 'GET';
        const headers = options.headers || { 'Content-Type': 'application/json' };
        const body = options.body ? JSON.stringify(options.body) : null;

        try {
            const response = await fetch(`${API_BASE_URL}/${endpoint}`, {
                method,
                headers,
                body,
                credentials: 'include' // Include cookies for session
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || `HTTP ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    /**
     * Products API
     */
    static Products = {
        getAll: async (params = {}) => {
            const queryString = new URLSearchParams({
                action: 'getAll',
                search: params.search || '',
                brand: params.brand || '',
                minPrice: params.minPrice || 0,
                maxPrice: params.maxPrice || 999999999,
                sort: params.sort || 'terbaru',
                page: params.page || 1,
                limit: params.limit || 12
            }).toString();
            
            return APIHandler.request(`products.php?${queryString}`);
        },

        getById: async (id) => {
            return APIHandler.request(`products.php?action=getById&id=${id}`);
        },

        search: async (query) => {
            return APIHandler.request(`products.php?action=search&q=${encodeURIComponent(query)}`);
        },

        getByBrand: async (brand) => {
            return APIHandler.request(`products.php?action=getByBrand&brand=${encodeURIComponent(brand)}`);
        },

        getByPrice: async (min, max) => {
            return APIHandler.request(`products.php?action=getByPrice&min=${min}&max=${max}`);
        },

        create: async (productData) => {
            const formData = new FormData();
            formData.append('action', 'create');
            Object.keys(productData).forEach(key => {
                formData.append(key, productData[key]);
            });

            return APIHandler.request('products.php', {
                method: 'POST',
                headers: {}, // Let browser set Content-Type for FormData
                body: formData
            });
        },

        update: async (id, productData) => {
            const data = { action: 'update', id_produk: id, ...productData };
            const formData = new FormData();
            Object.keys(data).forEach(key => {
                formData.append(key, data[key]);
            });

            return APIHandler.request('products.php', {
                method: 'POST',
                headers: {},
                body: formData
            });
        },

        delete: async (id) => {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id_produk', id);

            return APIHandler.request('products.php', {
                method: 'POST',
                headers: {},
                body: formData
            });
        }
    };

    /**
     * Cart API
     */
    static Cart = {
        get: async () => {
            return APIHandler.request('cart.php?action=get');
        },

        getCount: async () => {
            return APIHandler.request('cart.php?action=count');
        },

        add: async (productId, quantity = 1) => {
            return APIHandler.request('cart.php?action=add', {
                method: 'POST',
                body: {
                    id_produk: productId,
                    jumlah: quantity
                }
            });
        },

        update: async (productId, quantity) => {
            return APIHandler.request('cart.php?action=update', {
                method: 'POST',
                body: {
                    id_produk: productId,
                    jumlah: quantity
                }
            });
        },

        remove: async (productId) => {
            return APIHandler.request('cart.php?action=remove', {
                method: 'POST',
                body: {
                    id_produk: productId
                }
            });
        },

        clear: async () => {
            return APIHandler.request('cart.php?action=clear', {
                method: 'POST'
            });
        }
    };

    /**
     * Transactions API
     */
    static Transactions = {
        getAll: async (page = 1, limit = 10) => {
            return APIHandler.request(`transactions.php?action=getUserTransactions&page=${page}&limit=${limit}`);
        },

        getById: async (id) => {
            return APIHandler.request(`transactions.php?action=getById&id=${id}`);
        },

        create: async (transactionData) => {
            return APIHandler.request('transactions.php?action=create', {
                method: 'POST',
                body: transactionData
            });
        },

        updateStatus: async (id, status, noResi = null) => {
            return APIHandler.request('transactions.php?action=updateStatus', {
                method: 'POST',
                body: {
                    id_transaksi: id,
                    status_pesanan: status,
                    no_resi: noResi
                }
            });
        }
    };

    /**
     * Reviews API
     */
    static Reviews = {
        getByProduct: async (productId, page = 1, limit = 10) => {
            return APIHandler.request(
                `reviews.php?action=getByProduct&product_id=${productId}&page=${page}&limit=${limit}`
            );
        },

        getByUser: async (page = 1, limit = 10) => {
            return APIHandler.request(`reviews.php?action=getByUser&page=${page}&limit=${limit}`);
        },

        getStats: async (productId) => {
            return APIHandler.request(`reviews.php?action=getStats&product_id=${productId}`);
        },

        create: async (productId, rating, komentar) => {
            return APIHandler.request('reviews.php?action=create', {
                method: 'POST',
                body: {
                    id_produk: productId,
                    rating: rating,
                    komentar: komentar
                }
            });
        },

        update: async (reviewId, rating, komentar) => {
            return APIHandler.request('reviews.php?action=update', {
                method: 'POST',
                body: {
                    id_ulasan: reviewId,
                    rating: rating,
                    komentar: komentar
                }
            });
        },

        delete: async (reviewId) => {
            return APIHandler.request('reviews.php?action=delete', {
                method: 'POST',
                body: {
                    id_ulasan: reviewId
                }
            });
        }
    };
}

/**
 * Helper functions untuk UI updates
 */
const UIHelper = {
    /**
     * Show success message
     */
    showSuccess: (message, duration = 3000) => {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x';
        alert.style.zIndex = '9999';
        alert.style.marginTop = '20px';
        alert.innerHTML = `
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <i class="bi bi-check-circle"></i> ${message}
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, duration);
    },

    /**
     * Show error message
     */
    showError: (message, duration = 5000) => {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x';
        alert.style.zIndex = '9999';
        alert.style.marginTop = '20px';
        alert.innerHTML = `
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <i class="bi bi-exclamation-circle"></i> ${message}
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, duration);
    },

    /**
     * Format currency to Rupiah
     */
    formatRupiah: (amount) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    },

    /**
     * Show loading spinner
     */
    showLoading: (show = true) => {
        let spinner = document.getElementById('loading-spinner');
        if (!spinner) {
            spinner = document.createElement('div');
            spinner.id = 'loading-spinner';
            spinner.className = 'position-fixed top-50 start-50 translate-middle';
            spinner.style.zIndex = '9999';
            spinner.innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            `;
            document.body.appendChild(spinner);
        }
        spinner.style.display = show ? 'block' : 'none';
    }
};

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { APIHandler, UIHelper };
}
