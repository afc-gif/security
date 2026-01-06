# 🔐 ARTSCI Admin & POS Login Guide

## **System Overview**

This is a streamlined **Admin + POS System** with:
- **Admin Dashboard** - Manage products and generate barcodes
- **POS System** - Scan and sell products
- **No shop/cart features** - Admin and POS only

---

## **Quick Start**

### **1. Access Login Page**
```
http://localhost:8000/login
```

### **2. Login Credentials**

**Admin Account:**
- **Email:** `admin@example.com`
- **Password:** Your configured password
- **Role:** Admin (full system access)

### **3. After Login - Two Options**

#### **Option A: Go to POS System**
- Click **"🛒 POS"** link in admin navbar
- Or navigate to: `http://localhost:8000/pos`
- Use barcode scanner to sell products

#### **Option B: Stay in Admin**
- Click **"Dashboard"** link in navbar
- Or navigate to: `http://localhost:8000/admin/dashboard`
- Manage products and barcodes

---

## **🔧 Admin Dashboard** (http://localhost:8000/admin/dashboard)

### **Navigation Menu**
- **Dashboard** - Overview and statistics
- **Products** - Manage inventory with barcode generation
- **Solutions** - Manage product bundles/packages
- **Users** - Manage admin user accounts
- **🛒 POS** - Quick link to POS system

### **Features Available**

#### **1. Products Management**
- View all products
- Create new products
- Edit existing products
- Delete products
- **Automatic barcode generation** on product creation
- Download barcodes as PNG
- Print barcode labels

#### **2. Solutions Management**
- Create product bundles/solutions
- Add items to solutions
- Set pricing
- Manage solution descriptions

#### **3. Users Management**
- View all admin users
- Create new admin accounts
- Delete users
- Manage user roles

#### **4. Barcode Operations**
When you create a product:
1. System automatically generates a unique barcode
2. Click download button to get PNG barcode
3. Click print button to print label with barcode
4. Barcode becomes scannable in POS immediately

### **Logout from Admin**
- Click user dropdown (top-right corner)
- Shows user name, email, and role
- Click red "🚪 Logout" button

---

## **🛒 POS System** (http://localhost:8000/pos)

### **Three Main Sections**

#### **1. Left: Product Catalog**
- Browse products by category
- Search for specific items
- View prices and availability
- Click "Add to Cart" to add products manually

#### **2. Right: Shopping Cart**
- View items in cart
- Adjust quantities (+/- buttons)
- See subtotal, tax (7.5%), and total
- Select payment method (Cash, Card, Mobile)
- Click "Complete Sale" to checkout
- Clear cart option

#### **3. Bottom-Right: Barcode Scanner**
- **Input field** - Automatically captures scanner input
- **Status display** - Real-time feedback (success/error)
- **History** - Shows last 10 scans
- **Mode toggle** - Database or Sample mode
- **Minimize button** - Collapse/expand scanner

### **Using the Barcode Scanner**

#### **Setup**
1. Connect USB barcode scanner to POS terminal
2. Go to POS page (http://localhost:8000/pos)
3. Click in scanner input field (auto-focuses)

#### **Scanning**
1. **Physical Scanner:** Hold product up to scanner
2. **Manual Entry:** Type barcode and press Enter
3. Product auto-adds to cart on success
4. View scan result in history

#### **Scanner Modes**
- **Database Mode** ✅ Recommended for production
  - Scans real barcodes from admin products
  - Pulls actual prices from database
  
- **Sample Mode** For testing
  - Uses test codes (HK-001, DVR-001, etc.)
  - Good for demos and training

Toggle mode with **"🔄 Mode"** button

### **Navigation from POS**
- **📊 Admin** - Return to admin panel
- **🚪 Logout** - Sign out

---

## **🧪 Test Barcode Codes** (Sample Mode)

Use these codes to test POS scanning:

| Code | Product | Price |
|------|---------|-------|
| HK-001 | Hikvision Camera | ₦45,000 |
| DVR-001 | CCTV DVR | ₦65,000 |
| SOL-001 | Solar Panel 400W | ₦180,000 |
| BAT-001 | Battery Bank 10kW | ₦450,000 |
| INV-001 | Inverter 5kW | ₦280,000 |
| CBL-001 | Cable Reel 100m | ₦15,000 |
| SMT-001 | Smart Thermostat | ₦35,000 |
| ACC-001 | Door Access Control | ₦95,000 |

---

## **⚙️ API Endpoints**

All API endpoints require authentication and are located at `/api/pos/`

### **Barcode Lookup**
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

### **Get All Products**
```
GET /api/pos/products
```
Returns array of all products with barcodes

### **Search Products**
```
GET /api/pos/search/{query}
```
Returns products matching query (name or barcode)

### **Complete Sale**
```
POST /api/pos/complete-sale
```
Request body:
```json
{
  "items": [
    {"id": 1, "quantity": 2}
  ],
  "total": 90000,
  "payment_method": "cash"
}
```

---

## **📱 Workflow Example**

### **Admin Workflow**
1. Login to admin: `admin@example.com`
2. Go to Products → Create new product
3. System auto-generates barcode
4. Download or print barcode
5. Go to POS when ready to sell

### **POS Workflow**
1. Login to POS (same credentials as admin)
2. Choose mode: Database (real barcodes) or Sample (test codes)
3. Scan product barcode with scanner
4. Product auto-adds to cart
5. Continue scanning products
6. Select payment method
7. Click "Complete Sale"
8. Logout when done

---

## **🆘 Troubleshooting**

### **Can't login?**
- Email: `admin@example.com`
- Check password with system admin
- Clear browser cookies
- Try incognito/private browsing mode

### **POS page not loading?**
- Make sure you're logged in first
- Check URL: `http://localhost:8000/pos`
- Refresh the page
- Check browser console for errors

### **Barcode scanner not working?**
- Verify barcode created in admin first
- Try Database mode if using Sample mode
- Switch to Sample mode to test with test codes
- Try manual entry (type barcode + Enter)
- Check scanner is connected properly

### **Products not showing?**
- Create products in admin first
- Generate barcodes for products
- Refresh POS page
- Check Database mode (not Sample)

---

## **🔐 Logout**

### **From Admin**
- Click user dropdown (top-right)
- Click red "🚪 Logout" button

### **From POS**
- Click "🚪 Logout" button in header
- Redirects to login page

---

## **🎯 System Features**

✅ Admin Dashboard - Product management  
✅ Barcode Generation - Automatic on product creation  
✅ Barcode Download - PNG format for printing  
✅ POS System - Professional checkout interface  
✅ Barcode Scanner - USB scanner integration  
✅ Product Lookup - Database or sample mode  
✅ Payment Methods - Cash, Card, Mobile options  
✅ Tax Calculation - Automatic (7.5%)  
✅ Scan History - Last 10 scans displayed  
✅ User Authentication - Admin login only  

---

## **📋 System Architecture**

```
Login (admin@example.com)
    ↓
    ├─→ Admin Dashboard
    │    ├─ Products Management
    │    ├─ Barcode Generation
    │    ├─ Solutions Management
    │    └─ Users Management
    │
    └─→ POS System
         ├─ Product Catalog
         ├─ Shopping Cart
         ├─ Barcode Scanner
         └─ Checkout
```

---

## **📞 Key Information**

- **System Type:** Admin + POS Only
- **No Shop/Cart Features:** Removed as requested
- **Authentication:** Email/Password login required
- **Barcode Format:** CODE_128 (industry standard)
- **Database:** PostgreSQL
- **Framework:** Laravel 10 + PHP 8.3
- **Version:** 1.0.0 (Admin & POS)
- **Last Updated:** January 6, 2026

