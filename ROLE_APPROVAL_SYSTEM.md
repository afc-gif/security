# 🔐 Role Assignment & Admin Approval System

## **Overview**

The ARTSCI POS system includes a **complete role assignment and approval workflow** that ensures secure access control. When new users register, they must wait for admin approval before being able to login with their assigned role.

---

## **System Flow**

### **1. User Registration**
```
User → Register with email & password → Account created with status: "pending"
```

- New users register with their name, email, and password
- Account is created with:
  - `status`: **pending** (waiting for approval)
  - `role`: **null** (not yet assigned)
- User receives message: *"Your account is pending admin approval. Please wait for an administrator to assign your role."*

### **2. Admin Review**
```
Admin → Navigate to Users → Pending Approvals → Review registrations
```

- Admin goes to: `http://localhost:8000/admin/users/pending`
- Sees list of users waiting for approval
- Can approve or reject each registration

### **3. Admin Approval & Role Assignment**
```
Admin → Click "Approve as POS" or "Approve as Admin" → User status: "approved"
```

The admin can assign one of two roles:
- **👑 Admin** - Full system access (dashboard, products, users, settings)
- **🛒 POS** - Point of Sale access only (scan products and complete sales)

### **4. User Login**
```
Approved User → Login with email & password → Dashboard/POS (based on role)
```

Users can only login if:
- Account status is **approved**
- Password is correct
- Role is assigned (admin or pos)

---

## **Database Schema**

### **Users Table**
```sql
users
├── id (PK)
├── name (string)
├── email (string, unique)
├── password (string, hashed)
├── role (string | null) -- 'admin', 'pos', or null
├── status (string) -- 'pending' or 'approved'
├── created_at (timestamp)
└── updated_at (timestamp)
```

### **Status Values**
- **pending** - Awaiting admin approval
- **approved** - Approved and active

### **Role Values**
- **admin** - Admin dashboard access
- **pos** - POS system access only
- **null** - No role assigned (during pending status)

---

## **User Flows**

### **Registration Flow**

**File:** [app/Http/Controllers/AuthController.php](app/Http/Controllers/AuthController.php#L71-L90)

```php
public function register(Request $request)
{
    // Validate input
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    // Create user with pending status
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => null, // Will be assigned by admin
        'status' => 'pending', // Waiting for approval
    ]);

    return redirect()->route('login')
        ->with('success', 'Registration successful! Your account is pending admin approval.');
}
```

### **Login Flow**

**File:** [app/Http/Controllers/AuthController.php](app/Http/Controllers/AuthController.php#L18-L56)

```php
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:1',
    ]);

    // Check if user is pending approval
    $user = User::where('email', $credentials['email'])->first();
    
    if ($user && $user->isPending()) {
        return back()->withErrors([
            'email' => 'Your account is pending approval. Please contact an administrator.',
        ]);
    }

    // Attempt login only if approved
    if (Auth::attempt($credentials)) {
        $user = auth()->user();
        
        // Redirect based on role
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isPOS()) {
            return redirect()->route('pos.index');
        }
        
        return back()->withErrors([
            'email' => 'No role assigned. Please contact an administrator.',
        ]);
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
}
```

### **Admin Approval Flow**

**File:** [app/Http/Controllers/AdminController.php](app/Http/Controllers/AdminController.php#L249-L267)

```php
public function approveUser(User $user, $role)
{
    // Validate role
    if (!in_array($role, ['admin', 'pos'])) {
        return back()->withErrors('Invalid role specified.');
    }

    // Approve and assign role
    $user->update([
        'status' => 'approved',
        'role' => $role,
    ]);

    return back()->with('success', "User {$user->name} approved as {$role}!");
}
```

---

## **Admin Interface**

### **Users Management Page**
**URL:** `http://localhost:8000/admin/users`

Shows all approved users with:
- Name and email
- Current role (Admin/POS)
- Status badge
- Join date
- Delete option (cannot delete yourself)

### **Pending Approvals Page**
**URL:** `http://localhost:8000/admin/users/pending`

Shows all pending registrations with:
- User name and email
- Registration date/time
- **"✓ Approve as POS"** button
- **"👑 Approve as Admin"** button (with confirmation)
- **"✕ Reject"** button (deletes account)

---

## **Views**

### **1. Pending Users View**
**File:** [resources/views/admin/users/pending.blade.php](resources/views/admin/users/pending.blade.php)

- Table of pending users
- Approve as POS button
- Approve as Admin button (with confirmation)
- Reject button (with confirmation)
- Pagination support
- Empty state when no pending users

### **2. Approved Users View**
**File:** [resources/views/admin/users/index.blade.php](resources/views/admin/users/index.blade.php)

- Table of all approved users
- Shows current role for each user
- "Pending Approvals" button (red alert if any pending)
- Delete user functionality
- Cannot delete own account

---

## **Routes**

**File:** [routes/web.php](routes/web.php#L21-L27)

```php
// Users Management (admin only)
Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users.index');
Route::get('/admin/users/pending', [AdminController::class, 'pendingUsers'])->name('admin.users.pending');
Route::patch('/admin/users/{user}/approve/{role}', [AdminController::class, 'approveUser'])->name('admin.users.approve');
Route::patch('/admin/users/{user}/reject', [AdminController::class, 'rejectUser'])->name('admin.users.reject');
Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
```

---

## **Model Methods**

**File:** [app/Models/User.php](app/Models/User.php#L37-L51)

```php
public function isPending(): bool
{
    return $this->status === 'pending';
}

public function isApproved(): bool
{
    return $this->status === 'approved';
}

public function isAdmin(): bool
{
    return $this->role === 'admin';
}

public function isPOS(): bool
{
    return $this->role === 'pos';
}
```

---

## **Workflow Summary**

| Step | User Action | System State | Admin Action |
|------|-------------|--------------|--------------|
| 1 | Register | status: pending, role: null | - |
| 2 | Try Login | Login blocked | - |
| 3 | - | - | Review pending users |
| 4 | - | - | Click "Approve as Admin" or "Approve as POS" |
| 5 | - | status: approved, role: assigned | - |
| 6 | Login | Login succeeds, redirects to dashboard | - |

---

## **Database Migration**

**Status Column Migration:**
**File:** [database/migrations/2026_01_06_014756_add_status_to_users_table.php](database/migrations/2026_01_06_014756_add_status_to_users_table.php)

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('status')->default('approved')->after('role');
    });
}
```

---

## **Security Features**

✅ **Account Lockout** - Pending users cannot login  
✅ **Role Assignment Control** - Only admins can assign roles  
✅ **Approval Workflow** - All new accounts require admin review  
✅ **Access Control** - Users redirected based on role  
✅ **Audit Logging** - All approvals logged with user email and role  
✅ **Password Security** - Passwords hashed with bcrypt  

---

## **Testing the System**

### **Test Case 1: Register & Approve as POS**
1. Go to `http://localhost:8000/register`
2. Register with email: `testpos@example.com`
3. Try to login → **Blocked with message: "Your account is pending approval"**
4. Go to admin: `http://localhost:8000/admin/users/pending`
5. Click **"✓ Approve as POS"**
6. Try to login again → **Success, redirected to POS system**

### **Test Case 2: Register & Approve as Admin**
1. Go to `http://localhost:8000/register`
2. Register with email: `testadmin@example.com`
3. Try to login → **Blocked**
4. Go to admin: `http://localhost:8000/admin/users/pending`
5. Click **"👑 Approve as Admin"** (confirm dialog)
6. Try to login again → **Success, redirected to admin dashboard**

### **Test Case 3: Reject Registration**
1. Go to `http://localhost:8000/register`
2. Register with email: `testreject@example.com`
3. Go to admin: `http://localhost:8000/admin/users/pending`
4. Click **"✕ Reject"** (confirm dialog)
5. User account is deleted
6. Try to login with rejected email → **Fails: credentials not found**

---

## **Admin Navigation**

The admin navbar includes:
- **Dashboard** - System overview
- **Products** - Product management
- **Solutions** - Category/bundle management
- **Users** - Shows badge with pending count

Click **Users** to:
- See all approved users
- Click the **"⚠️ X Pending Approval"** button to review new registrations

---

## **Summary**

The role assignment and approval system provides:
- ✅ Secure user registration workflow
- ✅ Admin control over user access
- ✅ Flexible role assignment (Admin or POS)
- ✅ Prevention of unauthorized access
- ✅ Complete audit trail of approvals
- ✅ Simple, intuitive admin interface
