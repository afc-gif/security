# 🔐 ARTSCI System Credentials

## **Quick Login Reference**

### **Admin Account**
```
📧 Email:    admin@example.com
🔑 Password: admin123
👑 Role:     Admin (Full Access)
Status:      Approved ✓
```

**Access:** [http://localhost:8000/login](http://localhost:8000/login)

---

### **POS Account (Test/Demo)**
```
📧 Email:    pos@example.com
🔑 Password: pos123
🛒 Role:     POS (Sales Only)
Status:      Approved ✓
```

**Access:** [http://localhost:8000/login](http://localhost:8000/login)

---

## **For New POS Staff**

### **Registration Process**
1. Go to: [http://localhost:8000/register](http://localhost:8000/register)
2. Create account with your own email & password
3. Wait for admin approval
4. Admin approves at: `/admin/users/pending`
5. Login with your credentials

### **What New Staff Can Do After Approval**
- ✅ Login to POS system
- ✅ Scan products with barcode scanner
- ✅ Complete sales transactions
- ❌ Cannot access admin dashboard
- ❌ Cannot manage products or users

---

## **Admin Approval Workflow**

### **Step 1: Admin Reviews Pending Users**
- URL: [http://localhost:8000/admin/users/pending](http://localhost:8000/admin/users/pending)
- Admin sees list of users awaiting approval

### **Step 2: Admin Assigns Role**
- Click **"✓ Approve as POS"** → User gets POS access
- Click **"👑 Approve as Admin"** → User gets Admin access (confirm dialog)
- Click **"✕ Reject"** → Removes the account (confirm dialog)

### **Step 3: User Can Now Login**
Once approved, users can login with their registered email & password

---

## **System URLs**

| Page | URL | Who Can Access |
|------|-----|----------------|
| Login | `/login` | Everyone (public) |
| Register | `/register` | Everyone (public) |
| Admin Dashboard | `/admin/dashboard` | Admin only |
| Users Management | `/admin/users` | Admin only |
| Pending Approvals | `/admin/users/pending` | Admin only |
| POS System | `/pos` | Admin & POS staff |
| Products | `/admin/products` | Admin only |
| Solutions | `/admin/solutions` | Admin only |

---

## **Default Database Values**

When you run `php artisan db:seed`, these accounts are created:

| Email | Password | Role | Status |
|-------|----------|------|--------|
| admin@example.com | admin123 | admin | approved |
| pos@example.com | pos123 | pos | approved |
| john@example.com | password123 | user | approved |

---

## **Security Notes**

⚠️ **For Development Only**
- These are default credentials for testing
- Change passwords before production deployment
- Use strong passwords in production
- Store credentials securely (environment variables)

⚠️ **Access Control**
- Pending users cannot login
- POS users cannot access admin
- Admin users have full system access
- Only admins can approve new users

---

## **Password Requirements**

- Minimum 8 characters
- Case-sensitive
- Must match in registration (password confirmation)
- No special character requirements

---

## **What Each Role Can Do**

### **Admin Role** 👑
- ✅ Login to admin dashboard
- ✅ Manage products & inventory
- ✅ Generate & download barcodes
- ✅ Create solutions/bundles
- ✅ Manage users (approve/reject/delete)
- ✅ View orders & sales history
- ✅ Access POS system
- ✅ View statistics & analytics

### **POS Role** 🛒
- ✅ Login to POS system
- ✅ Scan product barcodes
- ✅ Add products to cart
- ✅ Adjust quantities
- ✅ Complete sales transactions
- ✅ Select payment methods
- ❌ Cannot manage products
- ❌ Cannot access admin
- ❌ Cannot approve users
- ❌ Cannot view reports

---

## **Troubleshooting Login Issues**

### **"Account pending approval" Error**
- Your account hasn't been approved yet
- Ask admin to visit `/admin/users/pending`
- Admin will click "Approve as POS" or "Approve as Admin"

### **"Invalid credentials" Error**
- Check email spelling (case-insensitive)
- Check password (case-sensitive)
- Verify account exists in system

### **"No role assigned" Error**
- Account is approved but role not set
- Contact admin to assign a role
- Admin goes to `/admin/users` and reassigns role

### **Forgot Password?**
- Contact system administrator
- Admin can create new account or reset password in database

---

## **First-Time Setup**

1. **Database Migration**
   ```bash
   php artisan migrate
   ```

2. **Seed Default Data**
   ```bash
   php artisan db:seed
   ```

3. **Login with Default Credentials**
   - Email: `admin@example.com`
   - Password: `admin123`

4. **Create New POS Staff**
   - Staff register at `/register`
   - Admin approves at `/admin/users/pending`
   - Staff login and use POS

---

## **For Production Deployment**

1. **Change Default Passwords**
   - Update `admin@example.com` password
   - Remove `pos@example.com` test account
   - Use environment variables for credentials

2. **Enable HTTPS**
   - All login pages should use HTTPS
   - Update URLs in configuration

3. **Session Security**
   - Configure session timeout
   - Enable CSRF protection (enabled by default)
   - Use secure cookies

4. **Audit Logging**
   - Monitor login attempts
   - Log all user approvals
   - Track role assignments

---

**Last Updated:** January 6, 2026  
**Version:** 1.0.0
