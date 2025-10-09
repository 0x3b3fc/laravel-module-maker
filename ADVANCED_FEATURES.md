# 🪄 Advanced Features - The Magic Wand

Laravel Module Maker now includes powerful advanced features that make module management magical!

---

## ✨ New Magical Features

### 1. 🧭 Automatic Navigation Links

**What it does:**
Automatically adds navigation links to your layout when creating Livewire or Full-Stack modules.

**How it works:**
```bash
php artisan make:module Product --type=full
```

**Result:**
Your `resources/views/components/layouts/app.blade.php` automatically gets:
```html
<a href="/products">Products</a>
```

**Features:**
- ✅ Auto-adds links when creating modules
- ✅ Auto-removes links when deleting modules
- ✅ Prevents duplicate links
- ✅ Maintains proper formatting
- ✅ Works with existing navigation

**Example:**
```
Before: Home | Orders | Categories
After:  Home | Orders | Categories | Products ← Added automatically!
```

---

### 2. 🏥 Module Health Check

**What it does:**
Analyzes your modules and provides health scores with detailed diagnostics.

**Commands:**

```bash
# Check single module
php artisan module:health Product

# Check all modules
php artisan module:health
```

**Output:**
```
🏥 Health Check: Product

✅ Health Score: 100% - Excellent!

Checks Passed: 10/10
Module Type: Full-Stack
```

**What it checks:**
- ✅ Has controllers or Livewire components
- ✅ Has models
- ✅ Has routes
- ✅ Has views
- ✅ Has migrations
- ✅ Has tests
- ✅ Has service provider
- ✅ Service provider is registered
- ✅ Routes are registered
- ✅ Namespace is configured

**Health Scores:**
- 🟢 **90-100%**: Excellent - Module is healthy
- 🟡 **70-89%**: Needs Attention - Some issues found
- 🔴 **Below 70%**: Critical - Major issues found

---

### 3. 📊 Module Dashboard

**What it does:**
Displays a beautiful, comprehensive dashboard of all your modules with statistics.

**Command:**
```bash
php artisan module:dashboard
```

**Output:**
```
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║           🏗️  LARAVEL MODULE MAKER DASHBOARD 🏗️             ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝

📊 MODULE STATISTICS

  Total Modules:        5
  Full-Stack Modules:   3
  API Modules:          2
  Livewire Modules:     0
  Total Routes:         45
  Total Files:          85
  Total Size:           250.5 KB

📦 YOUR MODULES

+----------+------------+--------+---------+
| Module   | Type       | Routes | Health  |
+----------+------------+--------+---------+
| Category | Full-Stack | 9      | ✅ 100% |
| Order    | Full-Stack | 9      | ✅ 100% |
| Product  | API        | 5      | ✅ 100% |
| Tag      | API        | 5      | ⚠️  80% |
| UserUI   | Livewire   | 3      | ✅ 100% |
+----------+------------+--------+---------+

⚡ QUICK ACTIONS

  • Create module:    php artisan make:module {name} --type=full
  • List modules:     php artisan list:modules
  • Delete module:    php artisan delete:module {name}
  • Check health:     php artisan module:health {name}
  • View routes:      php artisan route:list
```

**Features:**
- 📊 Overall statistics
- 📦 Module list with health indicators
- ⚡ Quick action commands
- 🎨 Beautiful ASCII art header

---

### 4. 🔗 Module with Relationships

**What it does:**
Creates a module with predefined Eloquent relationships automatically.

**Command:**
```bash
php artisan make:module-with-relations OrderItem \
  --type=full \
  --belongs-to=Order \
  --belongs-to=Product \
  --has-many=Review
```

**What it generates:**

**Model with relationships:**
```php
class OrderItem extends Model
{
    // ... existing code ...
    
    /**
     * Get the Order that owns this orderitem.
     */
    public function order()
    {
        return $this->belongsTo(\Modules\Order\Models\Order::class);
    }
    
    /**
     * Get the Product that owns this orderitem.
     */
    public function product()
    {
        return $this->belongsTo(\Modules\Product\Models\Product::class);
    }
    
    /**
     * Get the Reviews for this orderitem.
     */
    public function reviews()
    {
        return $this->hasMany(\Modules\Review\Models\Review::class);
    }
}
```

**Migration with foreign keys:**
```php
Schema::create('order_item', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
    $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
    $table->string('name');
    $table->text('description')->nullable();
    $table->timestamps();
});
```

**Supported relationships:**
- `--belongs-to` - BelongsTo relationships (many-to-one)
- `--has-many` - HasMany relationships (one-to-many)

**Multiple relationships:**
```bash
# Multiple BelongsTo
--belongs-to=User --belongs-to=Category --belongs-to=Author

# Multiple HasMany
--has-many=Comment --has-many=Tag --has-many=Image
```

---

## 🎯 All Available Commands

| Command | Description | Example |
|---------|-------------|---------|
| `make:module` | Create a module | `php artisan make:module Product --type=full` |
| `make:module-with-relations` | Create module with relationships | `php artisan make:module-with-relations OrderItem --belongs-to=Order` |
| `list:modules` | List all modules | `php artisan list:modules` |
| `delete:module` | Delete a module | `php artisan delete:module Product` |
| `module:health` | Check module health | `php artisan module:health Product` |
| `module:dashboard` | Show dashboard | `php artisan module:dashboard` |

---

## 🚀 Workflow Examples

### Example 1: E-commerce with Relationships

```bash
# 1. Create Category module
php artisan make:module Category --type=full

# 2. Create Product module with Category relationship
php artisan make:module-with-relations Product \
  --type=full \
  --belongs-to=Category

# 3. Create Order module
php artisan make:module Order --type=full

# 4. Create OrderItem with relationships
php artisan make:module-with-relations OrderItem \
  --type=full \
  --belongs-to=Order \
  --belongs-to=Product

# 5. Run migrations
php artisan migrate

# 6. Check dashboard
php artisan module:dashboard
```

### Example 2: Blog Platform

```bash
# 1. Create User module
php artisan make:module User --type=full

# 2. Create Category module
php artisan make:module Category --type=full

# 3. Create Post with relationships
php artisan make:module-with-relations Post \
  --type=full \
  --belongs-to=User \
  --belongs-to=Category \
  --has-many=Comment

# 4. Create Comment with relationships
php artisan make:module-with-relations Comment \
  --type=full \
  --belongs-to=Post \
  --belongs-to=User

# 5. Check health
php artisan module:health
```

### Example 3: Development Workflow

```bash
# Morning: Check dashboard
php artisan module:dashboard

# Create new feature module
php artisan make:module Feature --type=full

# Work on the module...

# Check module health
php artisan module:health Feature

# End of day: View all modules
php artisan list:modules
```

---

## 🎨 Navigation Management

### Automatic Navigation

When you create a Livewire or Full-Stack module:

**Before:**
```html
<nav>
    <a href="/">Home</a>
    <a href="/orders">Orders</a>
</nav>
```

**After creating Product module:**
```html
<nav>
    <a href="/">Home</a>
    <a href="/orders">Orders</a>
    <a href="/products">Products</a>  ← Added automatically!
</nav>
```

### Manual Navigation Management

If you need to customize navigation:

1. **View current links:**
   ```bash
   cat resources/views/components/layouts/app.blade.php | grep "href="
   ```

2. **Edit layout:**
   ```bash
   # Edit the layout file directly
   resources/views/components/layouts/app.blade.php
   ```

3. **Regenerate layout:**
   ```bash
   # Delete and recreate
   rm resources/views/components/layouts/app.blade.php
   php artisan make:module AnyModule --type=full
   ```

---

## 💡 Pro Tips

### Tip 1: Use Dashboard Daily

```bash
# Start your day with the dashboard
php artisan module:dashboard
```

See all your modules, their health, and statistics at a glance.

### Tip 2: Health Check Before Deployment

```bash
# Check all modules before deploying
php artisan module:health

# Fix any issues found
# Then deploy with confidence
```

### Tip 3: Relationships First

```bash
# Plan your relationships
# Create parent modules first
php artisan make:module Category --type=full

# Then create child modules with relationships
php artisan make:module-with-relations Product --belongs-to=Category
```

### Tip 4: Clean Navigation

```bash
# Periodically review navigation
cat resources/views/components/layouts/app.blade.php

# Remove unused links manually if needed
# Or delete and recreate modules
```

---

## 🎯 Benefits

### For Solo Developers

- 🧭 **Navigation**: Always know what modules exist
- 🏥 **Health Check**: Catch issues early
- 📊 **Dashboard**: See everything at a glance
- 🔗 **Relationships**: Scaffold complex structures quickly

### For Teams

- 📈 **Visibility**: Everyone sees all modules
- 🎯 **Standards**: Consistent module structure
- 🔍 **Monitoring**: Health checks for quality
- 🤝 **Collaboration**: Clear module organization

### For Projects

- 🚀 **Speed**: Generate complex structures in seconds
- 🛡️ **Quality**: Health checks ensure standards
- 📊 **Insights**: Dashboard shows project overview
- 🔗 **Relationships**: Proper data modeling

---

## 🔮 Future Enhancements

Coming soon:

- 🎨 **Custom navigation styles**
- 📱 **Mobile navigation support**
- 🔐 **Permission-based navigation**
- 📊 **Module analytics**
- 🔄 **Module dependencies**
- 🌐 **Multi-language support**
- 🎭 **Module themes**
- 📦 **Module marketplace**

---

## 📝 Command Reference

### Complete Command List

```bash
# MODULE CREATION
php artisan make:module {name} --type={full|api|livewire}
php artisan make:module-with-relations {name} --belongs-to={Model} --has-many={Model}

# MODULE MANAGEMENT
php artisan list:modules
php artisan delete:module {name} [--force]
php artisan module:health [{name}]
php artisan module:dashboard

# CUSTOMIZATION
php artisan vendor:publish --tag=module-maker-config
php artisan vendor:publish --tag=module-maker-stubs

# MAINTENANCE
php artisan migrate
php artisan optimize:clear
composer dump-autoload
```

---

<div align="center">

## 🪄 Your Package is Now a Magic Wand!

**Create** • **Manage** • **Monitor** • **Delete**

All with automatic navigation, health checks, and relationship scaffolding!

</div>
