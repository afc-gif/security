# 🔐 ARTSCI POS & Admin Login Guide

## **Quick Start**

### **1. Access Login Page**
```
http://localhost:8000/login
```

### **2. Login Credentials**

**Admin Account:**
- **Email:** `admin@example.com`
- **Password:** Ask your system administrator or use your configured password
- **Role:** Admin (full system access)

### **3. After Login**

#### **Option A: Go to POS System**
- Click **"🛒 POS"** link in admin navbar
- Or navigate to: `http://localhost:8000/pos`

#### **Option B: Go to Admin Dashboard**
- Click **"Dashboard"** link in navbar
- Or navigate to: `http://localhost:8000/admin/dashboard`

---

## **🛒 POS System Features** (http://localhost:8000/pos)

### **Left Side: Product Catalog**
- Browse products by category
- Search for specific items
- Click "Add to Cart" to add products

### **Right Side: Shopping Cart**
- View items in cart
- Adjust quantities (+/- buttons)
- See subtotal, tax, and total
- Select payment method (Cash, Card, Mobile)
- Click "Complete Sale" to checkout

### **Bottom Right: Barcode Scanner**
- Scan product barcodes with a physical scanner
- Or type barcode manually and press Enter
- Auto-adds product to cart on successful scan
- Toggle between Database mode (real barcodes) and Sample mode (test SKUs)
- View scan history

### **Navigation**
- **📊 Admin** - Go to admin panel
- **🚪 Logout** - Sign out

---

## **🔧 Admin Dashboard** (http://localhost:8000/admin/dashboard)

### **Features Available**
- **Dashboard** - Overview and statistics
- **Categories** - Manage product categories
- **Menu Items** - Manage items and pricing
- **Products** (admin/products) - View all products with generated barcodes
- **Users** - Manage user accounts
- **Orders** - View and manage orders
- **Solutions** - Manage solution bundles

### **Barcode Generation**
When you add a product:
1. Go to **Admin** → **Products**
2. Add or create a product
3. System automatically generates a unique barcode
4. Download barcode as PNG or print label
5. Barcode becomes scannable in POS system

### **Navigation**
- **🛒 POS** - Go to POS system
- **User Menu** (top right dropdown) - View profile and logout

---

## **🔄 How to Use Barcode Scanner in POS**

### **Setup**
1. Connect USB barcode scanner to your POS terminal
2. Go to POS page (http://localhost:8000/pos)
3. Scanner input field auto-focuses at bottom-right

### **Scanning**
1. **Physical Scanner:** Hold product up to scanner
2. **Manual Entry:** Type barcode and press Enter
3. Product auto-adds to cart on success
4. Scan history shows last 10 scans

### **Modes**
- **Database Mode:** Scans real barcodes from admin products
- **Sample Mode:** Scans test codes (HK-001, DVR-001, etc.)
- Toggle mode with **"🔄 Mode"** button

### **Tips**
- Make sure barcode is generated in admin first
- Keep scanner focused on POS page
- Clear history with **"🗑️ Clear"** button
- Minimize scanner with **"−"** button for more space

---

## **📱 Test Barcode Codes** (Sample Mode)

Use these codes to test POS scanning:
- `HK-001` - Hikvision Camera (₦45,000)
- `DVR-001` - CCTV DVR (₦65,000)
- `SOL-001` - Solar Panel 400W (₦180,000)
- `BAT-001` - Battery Bank 10kW (₦450,000)
- `INV-001` - Inverter 5kW (₦280,000)
- `CBL-001` - Cable Reel 100m (₦15,000)
- `SMT-001` - Smart Thermostat (₦35,000)
- `ACC-001` - Door Access Control (₦95,000)

---

## **⚙️ API Endpoints**

### **POS API Routes** (all require authentication)

**Barcode Lookup:**
```
GET /api/pos/barcode/{barcode}
```
Response:
```json
{
  "id": 1,
  "name": "Hikvision Camera",
  "sku": "HK-001",
  "barcode": "HK-001",
  "price": 45000,
  "stock": 999,
  "category": "product",
  "emoji": "📦"
}
```

**Get All Products:**
```
GET /api/pos/products
```

**Search Products:**
```
GET /api/pos/search/{query}
```

**Complete Sale:**
```
POST /api/pos/complete-sale
Body: {
  "items": [{"id": 1, "quantity": 2}],
  "total": 90000,
  "payment_method": "cash"
}
```

---

## **🆘 Troubleshooting**

### **Can't login?**
- Check if email is correct: `admin@example.com`
- Check password (ask system admin)
- Clear browser cookies and try again

### **POS page not loading?**
- Make sure you're logged in
- Check URL: `http://localhost:8000/pos`
- Refresh page

### **Barcode scanner not working?**
- Verify barcode was created in admin
- Try switching between Database/Sample mode
- Check scanner is connected and working
- Try manual entry (type barcode + press Enter)

### **Products not showing in POS?**
- Create products in admin first
- Generate barcodes for products
- Refresh POS page
- Check that products have valid prices

---

## **🔐 Logout**

### **From POS:**
- Click **"🚪 Logout"** button in top-right

### **From Admin:**
- Click user dropdown (top-right)
- Click **"🚪 Logout"**

---

## **💡 Tips & Best Practices**

1. **Generate Barcodes First** - Always generate barcodes in admin before scanning
2. **Database Mode for Live** - Use Database mode for production with real barcodes
3. **Sample Mode for Testing** - Use Sample mode for demos and training
4. **Keep Scanner Focused** - Make sure POS page has focus before scanning
5. **Regular Backups** - Backup your barcode data regularly
6. **Multiple Users** - Each user can login with their own credentials

---

## **📞 Support**

For issues or questions:
- Check the admin dashboard
- Verify products and barcodes are created
- Review scan history for errors
- Check API responses in browser console

**Version:** 1.0.0  
**Last Updated:** January 6, 2026
