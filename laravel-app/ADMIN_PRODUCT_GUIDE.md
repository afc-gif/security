# 🛍️ Admin Product Management Guide

## How the System Works

Your ARTSCI website has a complete product management system that allows you to:
- ✅ Add products with images, prices, and descriptions
- ✅ Organize products by category
- ✅ Manage inventory (stock levels)
- ✅ Edit or delete products anytime
- ✅ Display products beautifully on the public shop page

## 📍 Where Products Appear

### Public Shop Page
**URL:** `http://localhost:8001/shop`
- Shows all products in a beautiful grid layout
- Displays product images, names, categories, prices, and stock status
- Customers can view products and add them to cart
- Products are displayed with your uploaded images

### Individual Product Pages
**URL:** `http://localhost:8001/products/{product-id}`
- Detailed view of each product
- Full description, larger image, complete details

## 🎯 Step-by-Step: How to Add a Product

### Step 1: Access Admin Products
1. You should already be logged in as admin
2. Click **"Products"** in the left sidebar
3. You'll see a list of all products (currently empty)
4. Click the blue **"+ Add Product"** button

### Step 2: Fill in Product Information

The form has these fields:

| Field | Required | Notes | Example |
|-------|----------|-------|---------|
| **Product Name** | ✓ Yes | What customers see | "4K Security Camera" |
| **Category** | No | Product type | "Surveillance", "Power", "Access Control" |
| **Price** | ✓ Yes | In dollars | 299.99 |
| **Stock** | ✓ Yes | Quantity available | 15 |
| **Description** | No | Details about product | "Advanced HD system with AI detection..." |
| **Product Image** | No | Product photo | Select your .jpg, .png file (max 2MB) |

### Step 3: Example - Adding a Security Camera

```
Product Name: 4K HD Security Camera System
Category: Surveillance  
Price: 299.99
Stock: 15
Description: Advanced HD security camera system with AI detection, 
cloud archival, and 24/7 remote monitoring. Features night vision, 
motion alerts, and mobile app access.
Image: [Select camera-photo.jpg]
```

### Step 4: Click "Add Product"
- The product is instantly created
- Image is automatically uploaded to `/storage/app/public/products/`
- Product now appears on the shop page

### Step 5: Verify on Website
1. Go to **http://localhost:8001/shop**
2. Your product should appear in the grid
3. Click the product to view details
4. "Add to Cart" button appears (for logged-in customers)

## 📸 Image Requirements

- **Format:** JPG, PNG, WebP
- **Max Size:** 2MB
- **Recommended Size:** 500x500px or larger
- **Best Practice:** Square images work best
- **Storage Location:** Images automatically saved to `/storage/app/public/products/`

## 🔄 Editing Products

### To Edit a Product:
1. Go to **Admin → Products**
2. Find the product in the list
3. Click the **"Edit"** button
4. Make changes to any field
5. Click **"Update Product"**
6. Changes appear immediately on the website

### To Delete a Product:
1. Go to **Admin → Products**
2. Find the product
3. Click **"Delete"** (confirm the popup)
4. Product removed from website instantly

## 📊 Product Dashboard Stats

The admin dashboard shows:
- **Total Products:** Count of all products in system
- **Total Orders:** Number of customer orders placed
- **Total Revenue:** Sum of completed order amounts
- **Total Users:** Number of registered customers
- **Recent Orders:** Latest 5 orders from customers

## 🛒 How Customers Use Your Products

### Public Flow:
1. Customer visits `http://localhost:8001/shop`
2. Sees all your products with images and prices
3. Clicks "Add to Cart" (must be logged in)
4. Views cart at `/cart`
5. Proceeds to checkout
6. Order recorded in admin dashboard

### Admin Notification:
- Orders automatically appear in **Admin → Orders**
- You can track order status: Pending → Processing → Completed
- View customer details and order items

## 💡 Tips for Success

1. **Use Clear Names** - Be specific ("4K Security Camera System" not just "Camera")
2. **Add Descriptions** - Help customers understand features
3. **Organize by Category** - Makes browsing easier ("Surveillance", "Power", "Access Control")
4. **Keep Stock Updated** - Remove products when out of stock (set stock to 0)
5. **Use Good Images** - Clear, well-lit product photos convert better
6. **Competitive Pricing** - Monitor your prices against market rates

## 🔐 Security Notes

- Only admins can add/edit/delete products (protected by admin middleware)
- All product data is stored in PostgreSQL database
- Images are stored securely in `/storage/app/public/products/`
- Customers cannot modify products

## 📱 Mobile Responsive

The product display is fully responsive:
- Desktop: 3+ columns grid
- Tablet: 2 columns
- Mobile: 1 column (stacked)

All customers see your products beautifully on any device.

## 🚀 Categories (Optional)

The system supports categories for organizing products:
- Add categories in **Admin → Categories**
- Assign products to categories
- Customers can filter by category (if implemented on shop page)

---

**Questions?** Check the website logs at:
- Server logs: `/storage/logs/laravel.log`
- Browse all products: http://localhost:8001/shop
- Admin panel: http://localhost:8001/admin/products
