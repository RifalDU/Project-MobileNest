/**
 * Product Filter - Handle filtering by brand and price
 */

/**
 * Get selected filters
 */
function getSelectedFilters() {
    const filters = {
        brands: [],
        prices: []
    };

    // Get selected brands
    const brandCheckboxes = document.querySelectorAll('input[type="checkbox"][id^="merek_"]');
    brandCheckboxes.forEach(checkbox => {
        if (checkbox.checked) {
            filters.brands.push(checkbox.value);
        }
    });

    // Get selected prices
    const priceCheckboxes = document.querySelectorAll('input[type="checkbox"][id^="harga_"]');
    priceCheckboxes.forEach(checkbox => {
        if (checkbox.checked) {
            filters.prices.push(checkbox.value);
        }
    });

    return filters;
}

/**
 * Apply filter to products
 */
function applyFilter() {
    const filters = getSelectedFilters();
    console.log('Applying filters:', filters);

    // Get all product cards
    const productCards = document.querySelectorAll('.col-md-6.col-lg-4');
    let visibleCount = 0;

    productCards.forEach(card => {
        const merekElement = card.querySelector('.text-muted.small');
        const priceElement = card.querySelector('.text-primary');

        if (!merekElement || !priceElement) {
            return;
        }

        const merek = merekElement.textContent.trim();
        const priceText = priceElement.textContent.trim();
        
        // Extract price from text "Rp 1.234.567"
        const priceMatch = priceText.match(/([\d.]+)/);
        const price = priceMatch ? parseInt(priceMatch[1].replace(/\./g, '')) : 0;

        // Check brand filter
        let brandMatch = filters.brands.length === 0; // Show all if no filter selected
        if (filters.brands.length > 0) {
            brandMatch = filters.brands.includes(merek);
        }

        // Check price filter
        let priceMatch = filters.prices.length === 0; // Show all if no filter selected
        if (filters.prices.length > 0) {
            priceMatch = filters.prices.some(priceRange => {
                switch (priceRange) {
                    case '1': return price >= 1000000 && price <= 3000000;
                    case '2': return price > 3000000 && price <= 7000000;
                    case '3': return price > 7000000 && price <= 15000000;
                    case '4': return price > 15000000;
                    default: return true;
                }
            });
        }

        // Show or hide card
        if (brandMatch && priceMatch) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // Update product count
    const countElement = document.querySelector('.text-muted.mb-0');
    if (countElement) {
        countElement.innerHTML = `Menampilkan <strong>${visibleCount}</strong> produk`;
    }

    // Show notification
    showFilterNotification('success', `Filter applied - Showing ${visibleCount} products`);
}

/**
 * Reset filter
 */
function resetFilter() {
    console.log('Resetting filters');

    // Uncheck all checkboxes
    const allCheckboxes = document.querySelectorAll('input[type="checkbox"]');
    allCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });

    // Show all products
    const productCards = document.querySelectorAll('.col-md-6.col-lg-4');
    productCards.forEach(card => {
        card.style.display = 'block';
    });

    // Get total count from database
    const totalCount = document.querySelector('.text-muted.mb-0');
    if (totalCount) {
        const matches = totalCount.textContent.match(/\d+/);
        const count = matches ? matches[0] : productCards.length;
        totalCount.innerHTML = `Menampilkan <strong>${count}</strong> produk`;
    }

    showFilterNotification('info', 'Filter reset - Showing all products');
}

/**
 * Show filter notification
 */
function showFilterNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
    const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';

    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
    alert.style.zIndex = '9999';
    alert.innerHTML = `
        <i class="bi bi-${icon}"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(alert);

    // Auto-remove after 3 seconds
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

/**
 * Initialize filter event listeners
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Filter JS initialized');

    // Find filter buttons
    const filterButtons = document.querySelectorAll('button');
    let applyBtn = null;
    let resetBtn = null;

    filterButtons.forEach(btn => {
        if (btn.textContent.includes('terapkan') || btn.textContent.includes('Terapkan')) {
            applyBtn = btn;
            btn.addEventListener('click', applyFilter);
        }
        if (btn.textContent.includes('Reset') || btn.textContent.includes('reset')) {
            resetBtn = btn;
            btn.addEventListener('click', resetFilter);
        }
    });

    console.log('Apply button:', applyBtn ? 'Found' : 'Not found');
    console.log('Reset button:', resetBtn ? 'Found' : 'Not found');
});
