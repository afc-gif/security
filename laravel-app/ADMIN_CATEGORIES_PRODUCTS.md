# 🏢 Admin: Manage Solutions & Products

## System Overview

Your ARTSCI website displays **solutions/categories** (like SURVEILLANCE, SOLAR POWER, ACCESS CONTROL) with **products/items** under each category (specific camera models, inverters, etc.) with **prices**.

All of this is now manageable by the admin panel!

## 🎯 How It Works

### **Categories** = Solution Types
These are the 6 main solutions shown on your homepage:
- SURVEILLANCE (CCTV Systems)
- SOLAR POWER (Inverters & Batteries)
- ACCESS CONTROL (Gates, Biometric)
- PERIMETER SECURITY (Electric Fencing)
- SMART AUTOMATION (Lighting, Climate)
- FULL INTEGRATION (Complete Systems)

### **Menu Items** = Specific Products
These are products/items under each category:
- Camera models with prices (under SURVEILLANCE)
- Inverter models with prices (under SOLAR POWER)
- Gate systems with prices (under ACCESS CONTROL)
- etc.

## 📋 Step 1: Create Categories

### Access Categories Admin
1. Login as admin
2. Click **"Categories"** in the left sidebar
3. Click **"+ Create"** button

### Add a Category
Fill in these fields:

| Field | Required | Example |
|-------|----------|---------|
| **Name** | ✓ Yes | "SURVEILLANCE" |
| **Description** | No | "HD/4K CCTV systems with AI detection..." |
| **Image** | No | Category icon/image |
| **Sort Order** | No | 1 (controls display order) |
| **Active** | ✓ Yes | Check this box to show on website |

### Example Category Creation
```
Name: SURVEILLANCE
Description: HD/4K CCTV systems with AI detection, cloud archival, and 24/7 remote monitoring
Sort Order: 1
Active: ✓ (checked)
```

After clicking "Create", the category appears on your homepage!

## 🛍️ Step 2: Add Products Under Categories

### Access Menu Items Admin
1. Click **"Menu Items"** in the left sidebar
2. Click **"+ Create"** button

### Add a Menu Item (Product)
Fill in these fields:

| Field | Required | Example |
|-------|----------|---------|
| **Category** | ✓ Yes | Select "SURVEILLANCE" |
| **Name** | ✓ Yes | "4K IP Security Camera" |
| **Description** | No | "Advanced AI detection..." |
| **Price** | No | 299.99 |
| **Image** | No | Product photo |
| **Sort Order** | No | 1 |
| **Available** | ✓ Yes | Check to show on website |

### Example Product Creation
```
Category: SURVEILLANCE
Name: Hikvision 4K IP Camera DS-2CD2143G0-I
Description: 4MP network turret camera with 2.8mm lens, 
IR range up to 30m, H.264/H.265 codec support
Price: 249.99
Image: [Select hikvision-camera.jpg]
Sort Order: 1
Available: ✓ (checked)
```

## 🔄 Update Website in Real-Time

As soon as you create/edit categories and items:
1. They appear on the homepage (index.html)
2. Website automatically fetches latest data from API
3. No manual updates needed!

### API Endpoints Used
- `GET /api/categories` - Gets all active categories
- `GET /api/categories/{id}/items` - Gets products in a category

## ✏️ Editing & Deleting

### Edit a Category or Menu Item
1. Go to Categories or Menu Items list
2. Click **"Edit"** button
3. Make changes
4. Click **"Update"**

### Delete
1. Click **"Delete"** button
2. Confirm deletion
3. Item removed from website instantly

## 📊 Database Structure

### Categories Table
```
id, name, slug, description, image, sort_order, active, created_at, updated_at
```

### Menu Items Table
```
id, category_id, name, description, image, price, sort_order, available, created_at, updated_at
```

## 🎨 Display on Website

### Homepage Layout
```
[Your Categories - Grid of 6 solutions]
  ├─ SURVEILLANCE category
  │   └─ Menu Items (Products) under this category
  ├─ SOLAR POWER category
  │   └─ Menu Items (Products) under this category
  ├─ ACCESS CONTROL category
  │   └─ Menu Items (Products) under this category
  └─ ... and more
```

## 🚀 Quick Workflow

### To add a new solution offering:
1. **Admin** → **Categories** → **+ Create**
2. Add category name and description
3. Check "Active"
4. Click Create
5. **Admin** → **Menu Items** → **+ Create**
6. Select the category you just created
7. Add product name, price, description, image
8. Check "Available"
9. Click Create
10. **Done!** Product now appears on homepage

## 💡 Pro Tips

1. **Use Clear Naming** - "4K Security Camera" not just "Camera"
2. **Organize by Sort Order** - Lower numbers appear first (1, 2, 3...)
3. **Quality Images** - Professional product/category images convert better
4. **Detailed Descriptions** - Help customers understand the offering
5. **Consistent Pricing** - Keep prices competitive and updated
6. **Check Active/Available** - Items must have these boxes checked to show

## 🔗 Important URLs

| Page | URL | Who Sees |
|------|-----|----------|
| Admin Categories | `/admin/categories` | Admin only |
| Admin Menu Items | `/admin/menu-items` | Admin only |
| Homepage | `/` | Everyone |
| API Categories | `/api/categories` | For website to fetch |

## ⚠️ Important Notes

- **Sort Order determines display order** (1 appears first, 2 second, etc.)
- **Active/Available checkboxes** must be checked to show on website
- **Images are optional** but recommended for better appearance
- **Prices are optional** for categories, required for menu items
- **Categories must be created first** before adding menu items to them

---

**Your website is now dynamic!** No more static HTML editing - manage everything from the admin panel. 🎉
