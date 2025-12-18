# ⚡ QUICK REFERENCE - MOBILENEST API & JAVASCRIPT

## 🎯 JAVASCRIPT QUICK START

### Include di HTML
```html
<script src="../js/api-handler.js"></script>
<script src="../js/cart.js"></script>
<script src="../js/checkout.js"></script>
```

### Add to Cart
```javascript
addToCart(productId, quantity);
```

### Show Messages
```javascript
UIHelper.showSuccess('✅ Produk ditambahkan!');
UIHelper.showError('❌ Terjadi kesalahan');
UIHelper.formatRupiah(1500000); // Rp 1.500.000
```

## 🔌 API ENDPOINTS

### Products
```
GET  /api/products.php?action=getAll&page=1&limit=12
GET  /api/products.php?action=getById&id=1
GET  /api/products.php?action=search&q=samsung
POST /api/products.php (admin)
```

### Cart
```
GET  /api/cart.php?action=get
GET  /api/cart.php?action=count
POST /api/cart.php?action=add
POST /api/cart.php?action=update
POST /api/cart.php?action=remove
POST /api/cart.php?action=clear
```

### Transactions
```
GET  /api/transactions.php?action=getUserTransactions
GET  /api/transactions.php?action=getById&id=1
POST /api/transactions.php?action=create
POST /api/transactions.php?action=updateStatus (admin)
```

### Reviews
```
GET  /api/reviews.php?action=getByProduct&product_id=1
GET  /api/reviews.php?action=getByUser
GET  /api/reviews.php?action=getStats&product_id=1
POST /api/reviews.php?action=create
POST /api/reviews.php?action=update
POST /api/reviews.php?action=delete
```

---

**Lihat QUICK_REFERENCE.md untuk dokumentasi lengkap!**