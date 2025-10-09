<div align="center">

# 🏗️ Laravel Module Maker

### Transform Your Laravel App into a Modular Masterpiece

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![Laravel](https://img.shields.io/badge/Laravel-12%2B-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=flat-square&logo=livewire&logoColor=white)](https://livewire.laravel.com)

**Build scalable, maintainable Laravel applications with self-contained HMVC modules.**

**One command. Complete module. Zero configuration.**

[Quick Start](#-quick-start) • [Features](#-features) • [Commands](#-all-commands) • [Examples](#-real-world-examples) • [Support](#-support)

---

</div>

## 🎯 What Is This?

Laravel Module Maker is a powerful package that generates **complete, production-ready modules** for your Laravel applications using the HMVC (Hierarchical Model-View-Controller) pattern.

### The Problem

```bash
Traditional Laravel App          Your App After Module Maker
─────────────────────           ──────────────────────────────
app/                            modules/
├── Controllers/                ├── Product/
│   ├── UserController              ├── Controllers/
│   ├── ProductController           ├── Models/
│   ├── OrderController             ├── Views/
│   └── ... (100+ files)            ├── Routes/
├── Models/                         ├── Tests/
│   └── ... (50+ files)             └── ... (self-contained)
└── ... (messy!)                ├── Order/
                                │   └── ... (self-contained)
❌ Hard to maintain             └── User/
❌ Tight coupling                   └── ... (self-contained)
❌ Difficult to scale           
                                ✅ Easy to maintain
                                ✅ Loose coupling
                                ✅ Scales effortlessly
```

### The Solution

```bash
php artisan make:module Product --type=full
```

**You get:** A complete, self-contained module with API, UI, database, tests, and more. **In 2 seconds.**

---

## ✨ Features

<div align="center">

| 🚀 Module Types | 🔧 Automation | 📦 Management | 🎨 UI/UX |
|:---:|:---:|:---:|:---:|
| Full-Stack | Auto-register providers | List modules | Tailwind CSS |
| API-only | Auto-update composer | Delete safely | Alpine.js |
| Livewire-only | Auto-add navigation | Health checks | Responsive |
| With relationships | Auto-register routes | Dashboard | Real-time validation |

</div>

### 🪄 Magical Features

- **🧭 Auto Navigation**: Adds links to your layout automatically
- **🏥 Health Monitoring**: 10-point health check system
- **📊 Dashboard**: Beautiful overview of all modules
- **🔗 Relationships**: Scaffold BelongsTo and HasMany relationships
- **⚡ Zero Config**: Everything works out of the box
- **🛡️ Safe Deletion**: Double confirmation + automatic cleanup

---

## 📋 Requirements

```bash
PHP      >= 8.2
Laravel  >= 12.0
Composer >= 2.0
Livewire >= 3.0  (optional, for Livewire modules)
```

---

## 🚀 Installation

```bash
composer require phpsamurai/laravel-module-maker
```

**Optional:** For Livewire modules:

```bash
composer require livewire/livewire
```

**That's it!** Ready to use. No configuration needed.

---

## ⚡ Quick Start

### Create Your First Module

```bash
php artisan make:module Blog --type=full
```

### Run Migrations

```bash
php artisan migrate
```

### Access Your Module

- 🌐 **Web UI**: `http://your-app.test/blogs`
- 🔌 **API**: `http://your-app.test/api/blogs`

### See the Magic

```bash
php artisan module:dashboard
```

**Done!** Your module is ready with UI, API, database, tests, and navigation. 🎉

---

## 🎯 Module Types

<table>
<tr>
<th width="25%">Type</th>
<th width="25%">Command</th>
<th width="25%">What You Get</th>
<th width="25%">Use Case</th>
</tr>

<tr>
<td align="center">
<strong>🌟 Full-Stack</strong><br/>
<em>Recommended</em>
</td>
<td>

```bash
--type=full
```

</td>
<td>

• API Controller<br/>
• Livewire Components<br/>
• Models & DB<br/>
• Both Web & API<br/>
• 18 files

</td>
<td>

Complete features needing both UI and API

</td>
</tr>

<tr>
<td align="center">
<strong>🔌 API</strong>
</td>
<td>

```bash
--type=api
```

</td>
<td>

• API Controller<br/>
• Models & DB<br/>
• Bootstrap Views<br/>
• API Resources<br/>
• 15 files

</td>
<td>

Backend services, REST APIs, microservices

</td>
</tr>

<tr>
<td align="center">
<strong>⚡ Livewire</strong>
</td>
<td>

```bash
--type=livewire
```

</td>
<td>

• Livewire Components<br/>
• Tailwind Views<br/>
• Web Routes<br/>
• No Backend<br/>
• 8 files

</td>
<td>

UI for existing APIs, frontend features

</td>
</tr>

</table>

---

## 📚 All Commands

### 🎨 Module Creation

```bash
# Interactive mode (prompts for type)
php artisan make:module Product

# Full-Stack module (API + Livewire)
php artisan make:module Product --type=full

# API module (backend only)
php artisan make:module Product --type=api

# Livewire module (UI only)
php artisan make:module Product --type=livewire

# With relationships
php artisan make:module-with-relations OrderItem \
  --type=full \
  --belongs-to=Order \
  --belongs-to=Product \
  --has-many=Review

# With options
php artisan make:module Product --force --no-tests --no-seeders
```

### 📊 Module Management

```bash
# Interactive dashboard
php artisan module:dashboard

# List all modules
php artisan list:modules

# Check module health
php artisan module:health Product

# Check all modules health
php artisan module:health

# Delete module (with confirmation)
php artisan delete:module Product

# Force delete (no confirmation)
php artisan delete:module Product --force
```

### ⚙️ Customization

```bash
# Publish configuration
php artisan vendor:publish --tag=module-maker-config

# Publish stub templates
php artisan vendor:publish --tag=module-maker-stubs
```

---

## 🏗️ What Gets Generated

### Full-Stack Module Structure

```
modules/Product/
│
├── 📁 Controllers/
│   └── ProductApiController.php       # RESTful API with search, filter, pagination
│
├── 📁 Livewire/
│   ├── Index.php                      # List with real-time search & delete
│   ├── Create.php                     # Create form with validation
│   └── Edit.php                       # Edit form with validation
│
├── 📁 Models/
│   └── Product.php                    # Eloquent model with scopes
│
├── 📁 Views/
│   └── livewire/
│       ├── index.blade.php            # Tailwind styled list view
│       ├── create.blade.php           # Tailwind styled form
│       └── edit.blade.php             # Tailwind styled form
│
├── 📁 Routes/
│   ├── web.php                        # Livewire routes (/products)
│   └── api.php                        # API routes (/api/products)
│
├── 📁 Http/
│   ├── Resources/
│   │   ├── ProductResource.php        # JSON resource transformer
│   │   └── ProductCollection.php      # JSON collection wrapper
│   ├── Middleware/                    # Ready for custom middleware
│   └── Requests/                      # Ready for form requests
│
├── 📁 Database/
│   ├── Migrations/
│   │   └── 2025_xx_xx_create_product_table.php
│   ├── Seeders/
│   │   └── ProductSeeder.php          # Sample data seeder
│   └── Factories/
│       └── ProductFactory.php         # Factory for testing
│
├── 📁 Tests/
│   ├── Feature/
│   │   └── ProductTest.php            # HTTP endpoint tests
│   └── Unit/
│       └── ProductTest.php            # Model unit tests
│
├── 📁 Providers/
│   └── ProductServiceProvider.php     # Auto-registered provider
│
└── 📁 Config/                         # Module-specific config
```

**Total: 18 files, ~35 KB, Production-ready**

---

## 🔥 Automatic Features

### What Happens Automatically

When you run `php artisan make:module Product --type=full`:

<table>
<tr>
<td width="50%">

**✅ File Generation**

- 18 files created
- All properly namespaced
- PSR-4 compliant
- Modern PHP 8.2+ syntax

**✅ Registration**

- Service provider registered
- Routes registered (web & API)
- Livewire components registered
- View namespaces registered

</td>
<td width="50%">

**✅ Configuration**

- Composer autoload updated
- `composer dump-autoload` runs
- All caches cleared
- Migrations loaded

**✅ UI Enhancement**

- Navigation link added
- Layout created (if needed)
- Views registered
- Components ready

</td>
</tr>
</table>

### What You Need to Do

```bash
php artisan migrate
```

**That's it!** Everything else is automatic. 🎉

---

## 💻 Code Examples

### Generated API Controller

```php
namespace Modules\Product\Controllers;

class ProductApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();
        
        // 🔍 Built-in search
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        
        // 🎯 Built-in filtering
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        // 📄 Built-in pagination
        $products = $query->paginate(15);
        
        return response()->json(new ProductCollection($products));
    }
    
    // ✅ store(), show(), update(), destroy() included
}
```

### Generated Livewire Component

```php
namespace Modules\Product\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    
    public $search = '';
    public $confirmingDeletion = false;
    
    // 🔍 Real-time search
    protected $queryString = ['search'];
    
    // 🗑️ Delete with confirmation
    public function delete($id)
    {
        $this->confirmingDeletion = true;
        $this->deletingId = $id;
    }
    
    public function render()
    {
        $products = Product::query()
            ->when($this->search, fn($q) => 
                $q->where('name', 'like', "%{$this->search}%")
            )
            ->latest()
            ->paginate(15);
            
        return view('products::livewire.index', [
            'products' => $products
        ]);
    }
}
```

### Generated Routes

```php
// Web Routes (Livewire UI)
Route::get('/products', Index::class)->name('products.index');
Route::get('/products/create', Create::class)->name('products.create');
Route::get('/products/{product}/edit', Edit::class)->name('products.edit');

// API Routes (JSON)
Route::prefix('products')->group(function () {
    Route::get('/', [ProductApiController::class, 'index']);
    Route::post('/', [ProductApiController::class, 'store']);
    Route::get('/{product}', [ProductApiController::class, 'show']);
    Route::put('/{product}', [ProductApiController::class, 'update']);
    Route::delete('/{product}', [ProductApiController::class, 'destroy']);
});
```

---

## 🌟 Advanced Features

### 1. 🧭 Automatic Navigation

**Navigation links are added automatically!**

```bash
php artisan make:module Product --type=full
```

**Before:**

```html
<nav>
  <a href="/">Home</a>
  <a href="/orders">Orders</a>
</nav>
```

**After:**

```html
<nav>
  <a href="/">Home</a>
  <a href="/orders">Orders</a>
  <a href="/products">Products</a>  ← Added automatically!
</nav>
```

**Features:**

- ✅ Auto-adds for Livewire/Full-Stack modules
- ✅ Auto-removes when module deleted
- ✅ No duplicates
- ✅ Maintains formatting

---

### 2. 📊 Interactive Dashboard

**See everything at a glance!**

```bash
php artisan module:dashboard
```

**Output:**

```
╔══════════════════════════════════════════════════════════════╗
║           🏗️  LARAVEL MODULE MAKER DASHBOARD 🏗️             ║
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

┌──────────┬────────────┬────────┬─────────┐
│ Module   │ Type       │ Routes │ Health  │
├──────────┼────────────┼────────┼─────────┤
│ Category │ Full-Stack │ 9      │ ✅ 100% │
│ Order    │ Full-Stack │ 9      │ ✅ 100% │
│ Product  │ Full-Stack │ 9      │ ✅ 100% │
│ Tag      │ API        │5       │ ✅ 100% │
│ UserUI   │ Livewire   │ 3      │ ✅ 100% │
└──────────┴────────────┴────────┴─────────┘

⚡ QUICK ACTIONS
  • Create:  php artisan make:module {name} --type=full
  • Health:  php artisan module:health {name}
  • Delete:  php artisan delete:module {name}
```

---

### 3. 🏥 Health Check System

**Monitor your modules' health!**

```bash
# Check single module
php artisan module:health Product

# Check all modules
php artisan module:health
```

**10-Point Health Check:**

1. ✅ Has controllers/components
2. ✅ Has models
3. ✅ Has routes
4. ✅ Has views
5. ✅ Has migrations
6. ✅ Has tests
7. ✅ Has service provider
8. ✅ Provider registered
9. ✅ Routes registered
10. ✅ Namespace configured

**Health Scores:**

- 🟢 **90-100%**: Excellent
- 🟡 **70-89%**: Needs attention
- 🔴 **Below 70%**: Critical

---

### 4. 🔗 Relationship Scaffolding

**Generate modules with relationships built-in!**

```bash
php artisan make:module-with-relations OrderItem \
  --type=full \
  --belongs-to=Order \
  --belongs-to=Product \
  --has-many=Review
```

**Automatically generates:**

**In Model:**

```php
public function order()
{
    return $this->belongsTo(\Modules\Order\Models\Order::class);
}

public function product()
{
    return $this->belongsTo(\Modules\Product\Models\Product::class);
}

public function reviews()
{
    return $this->hasMany(\Modules\Review\Models\Review::class);
}
```

**In Migration:**

```php
$table->foreignId('order_id')->constrained()->onDelete('cascade');
$table->foreignId('product_id')->constrained()->onDelete('cascade');
```

---

## 🌈 Real-World Examples

### Example 1: E-commerce Platform

```bash
# Step 1: Create product catalog
php artisan make:module Product --type=full
php artisan migrate

# Step 2: Create orders with relationships
php artisan make:module-with-relations Order \
  --type=full \
  --has-many=OrderItem

# Step 3: Create order items with relationships
php artisan make:module-with-relations OrderItem \
  --type=full \
  --belongs-to=Order \
  --belongs-to=Product

# Step 4: View dashboard
php artisan module:dashboard

# Done! You have:
# ✅ Product catalog with UI and API
# ✅ Order management with relationships
# ✅ Order items linked to orders and products
# ✅ All navigation links added automatically
```

### Example 2: Blog Platform

```bash
# Backend API
php artisan make:module Post --type=api
php artisan make:module Comment --type=api

# Frontend UIs
php artisan make:module BlogPublic --type=livewire
php artisan make:module BlogAdmin --type=livewire

# Result: Separate backend and multiple frontends!
```

### Example 3: SaaS Application

```bash
# Core modules
php artisan make:module User --type=full
php artisan make:module Subscription --type=full
php artisan make:module Payment --type=full

# Feature modules
php artisan make:module Analytics --type=api
php artisan make:module Reporting --type=api

# UI modules
php artisan make:module Dashboard --type=livewire
php artisan make:module Settings --type=livewire

# Check everything
php artisan module:dashboard
```

---

## 🔌 API Endpoints

### Generated Endpoints

| Method | Endpoint | Features |
|--------|----------|----------|
| `GET` | `/api/products` | List, search, filter, paginate, sort |
| `POST` | `/api/products` | Create with validation |
| `GET` | `/api/products/{id}` | Show single resource |
| `PUT` | `/api/products/{id}` | Update with validation |
| `DELETE` | `/api/products/{id}` | Delete resource |

### Example API Usage

```bash
# List products
curl http://your-app.test/api/products

# Search
curl "http://your-app.test/api/products?search=laptop"

# Filter
curl "http://your-app.test/api/products?is_active=1"

# Paginate
curl "http://your-app.test/api/products?page=2"

# Create
curl -X POST http://your-app.test/api/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Laptop","description":"Gaming laptop"}'

# Update
curl -X PUT http://your-app.test/api/products/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Updated Laptop"}'

# Delete
curl -X DELETE http://your-app.test/api/products/1
```

**Response Format:**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "description": "Description",
      "is_active": true,
      "created_at": "2025-10-09T12:00:00.000000Z"
    }
  ],
  "meta": {
    "total": 100,
    "per_page": 15,
    "current_page": 1
  }
}
```

---

## 🎨 Livewire Components

### Interactive UI Components

**Index Component:**

- 🔍 Real-time search
- 📄 Pagination
- 🗑️ Delete with confirmation modal
- ✅ Success/error messages
- 📱 Responsive design

**Create Component:**

- 📝 Form with validation
- ⚡ Real-time validation
- 🎯 Wire:model binding
- ✅ Success redirect

**Edit Component:**

- 📝 Pre-filled form
- ⚡ Real-time validation
- 💾 Update functionality
- ✅ Success redirect

### Example Livewire View

```blade
<div class="max-w-7xl mx-auto px-4">
    <!-- Search -->
    <input wire:model.live="search" 
           placeholder="Search products..." 
           class="w-full px-4 py-2 border rounded-lg">
    
    <!-- Results -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        @foreach($products as $product)
            <div class="bg-white p-6 rounded-lg shadow">
                <h3>{{ $product->name }}</h3>
                <p>{{ $product->description }}</p>
                
                <button wire:click="delete({{ $product->id }})"
                        class="text-red-600 hover:text-red-800">
                    Delete
                </button>
            </div>
        @endforeach
    </div>
    
    <!-- Pagination -->
    {{ $products->links() }}
</div>
```

---

## 🧪 Testing

### Generated Tests

**Feature Test:**

```php
public function test_product_can_be_created(): void
{
    $response = $this->post(route('api.products.store'), [
        'name' => 'Test Product',
        'description' => 'Test description',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('products', [
        'name' => 'Test Product'
    ]);
}
```

**Unit Test:**

```php
public function test_product_active_scope(): void
{
    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);
    
    $this->assertCount(1, Product::active()->get());
}
```

**Livewire Test:**

```php
public function test_can_create_product_via_livewire(): void
{
    Livewire::test(Create::class)
        ->set('name', 'New Product')
        ->call('save')
        ->assertRedirect(route('products.index'));
}
```

### Run Tests

```bash
# All tests
php artisan test

# Specific module
php artisan test modules/Product/Tests/

# With coverage
php artisan test --coverage
```

---

## ⚙️ Configuration

### Publish Config

```bash
php artisan vendor:publish --tag=module-maker-config
```

### Available Options

```php
return [
    // Module directory
    'path' => 'modules',
    
    // Namespace prefix
    'namespace' => 'Modules',
    
    // Default module type
    'default_type' => 'api',
    
    // Auto-register routes
    'auto_register_routes' => true,
    
    // Route types to register
    'route_registration' => 'both', // 'web', 'api', or 'both'
    
    // Generation options
    'generate_tests' => true,
    'generate_seeders' => true,
    'generate_factories' => true,
];
```

### Customize Stubs

```bash
php artisan vendor:publish --tag=module-maker-stubs
```

Stubs copied to `resources/stubs/module-maker/`

**Available variables:**

| Variable | Output | Use |
|----------|--------|-----|
| `{{module}}` | `Product` | Class names |
| `{{moduleLower}}` | `product` | Variables |
| `{{moduleSnake}}` | `product` | Tables |
| `{{modulePlural}}` | `Products` | Labels |
| `{{namespace}}` | `Modules` | Namespaces |
| `{{moduleNamespace}}` | `Modules\Product` | Full namespace |

---

## 🚨 Troubleshooting

<details>
<summary><strong>❓ Class Not Found</strong></summary>

```bash
composer dump-autoload
php artisan optimize:clear
```

Verify `bootstrap/providers.php` contains your service provider.
</details>

<details>
<summary><strong>❓ Routes Not Working</strong></summary>

```bash
php artisan route:clear
php artisan route:list --name=products
```

Check service provider is registered.
</details>

<details>
<summary><strong>❓ Livewire Component Not Found</strong></summary>

```bash
composer require livewire/livewire
php artisan optimize:clear
```

Ensure `@livewireStyles` and `@livewireScripts` in layout.
</details>

<details>
<summary><strong>❓ Views Not Found</strong></summary>

```bash
php artisan view:clear
```

Use namespace notation: `products::livewire.index`
</details>

<details>
<summary><strong>❓ Navigation Link Not Added</strong></summary>

Check `resources/views/components/layouts/app.blade.php` exists.
The package creates it automatically for first Livewire module.
</details>

---

## 📖 Best Practices

### ✅ DO

- **Use PascalCase** for module names: `ProductCatalog`, `UserManagement`
- **Plan relationships** before creating modules
- **Run health checks** before deployment
- **Use dashboard** to monitor modules
- **Test modules** after generation
- **Backup** before deleting modules
- **Use Full-Stack** for complete features

### ❌ DON'T

- **Use snake_case** for module names
- **Skip migrations** after creating modules
- **Delete without confirmation** in production
- **Ignore health warnings**
- **Create without planning structure**
- **Use --force** without backups

---

## 🎯 Use Cases

<table>
<tr>
<td width="33%">

### 🏢 Enterprise

- User Management
- Role & Permissions
- Audit Logging
- Reporting
- Admin Dashboards
- Multi-tenancy

</td>
<td width="33%">

### 🛒 E-commerce

- Product Catalog
- Shopping Cart
- Order Processing
- Payment Gateway
- Inventory
- Customer Portal

</td>
<td width="33%">

### 📱 SaaS

- Subscription Management
- Billing
- Analytics
- User Dashboards
- API Services
- Webhooks

</td>
</tr>
<tr>
<td width="33%">

### 📝 CMS

- Blog Posts
- Pages
- Media Library
- Comments
- Categories
- Tags

</td>
<td width="33%">

### 🎓 Education

- Courses
- Lessons
- Quizzes
- Student Management
- Progress Tracking
- Certificates

</td>
<td width="33%">

### 🏥 Healthcare

- Patient Records
- Appointments
- Prescriptions
- Billing
- Reports
- Notifications

</td>
</tr>
</table>

---

## 🚀 Performance

### Production Optimization

```bash
# Cache everything
php artisan route:cache
php artisan config:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### Module Performance

- ⚡ **Fast Generation**: 1-3 seconds per module
- ⚡ **Lazy Loading**: Resources loaded only when needed
- ⚡ **Efficient Autoloading**: PSR-4 compliant
- ⚡ **Zero Runtime Overhead**: Only used during generation
- ⚡ **Optimized Queries**: Includes pagination and filtering

---

## 📊 Command Reference

### Complete Command List

| Command | Description | Example |
|---------|-------------|---------|
| `make:module` | Create module | `php artisan make:module Product --type=full` |
| `make:module-with-relations` | Create with relationships | `php artisan make:module-with-relations OrderItem --belongs-to=Order` |
| `list:modules` | List all modules | `php artisan list:modules` |
| `module:dashboard` | Show dashboard | `php artisan module:dashboard` |
| `module:health` | Check health | `php artisan module:health Product` |
| `delete:module` | Delete module | `php artisan delete:module Product` |

### Command Options

| Option | Available On | Description |
|--------|--------------|-------------|
| `--type={full\|api\|livewire}` | make:module | Module type |
| `--belongs-to={Model}` | make:module-with-relations | BelongsTo relationship |
| `--has-many={Model}` | make:module-with-relations | HasMany relationship |
| `--force` | make:module, delete:module | Skip confirmations |
| `--no-tests` | make:module | Skip test generation |
| `--no-seeders` | make:module | Skip seeder generation |
| `--no-factories` | make:module | Skip factory generation |

---

## 🎓 Learning Path

### Beginner

1. **Install** the package
2. **Create** your first module: `php artisan make:module Blog --type=full`
3. **Run** migrations: `php artisan migrate`
4. **Visit** in browser: `/blogs`
5. **Explore** the generated files

### Intermediate

1. **Create** multiple modules
2. **Use** relationships: `make:module-with-relations`
3. **Customize** stubs
4. **Monitor** with dashboard
5. **Run** health checks

### Advanced

1. **Publish** configuration
2. **Customize** all stubs
3. **Create** custom module types
4. **Integrate** with CI/CD
5. **Build** complex architectures

---

## 🤝 Contributing

We welcome contributions!

1. Fork the repository
2. Create feature branch: `git checkout -b feature/amazing`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push branch: `git push origin feature/amazing`
5. Open Pull Request

### Development

```bash
git clone https://github.com/phpsamurai/laravel-module-maker.git
cd laravel-module-maker
composer install
```

---

## 📄 License

MIT License - see [LICENSE](LICENSE) file for details.

---

## 💬 Support

<div align="center">

**Need Help?**

[📧 Email](mailto:dev.sief.hesham@gmail.com) • [🐛 Issues](https://github.com/phpsamurai/laravel-module-maker/issues) • [📚 Wiki](https://github.com/phpsamurai/laravel-module-maker/wiki)

</div>

---

## 📈 Changelog

### Version 1.0.2 (October 2025)

**🎉 Initial Release**

**Core Features:**

- ✅ Three module types (Full-Stack, API, Livewire)
- ✅ Interactive CLI with type selection
- ✅ 27 customizable stub templates
- ✅ Complete HMVC structure generation

**Automation:**

- ✅ Auto-register service providers
- ✅ Auto-update composer autoload
- ✅ Auto-register routes (web & API)
- ✅ Auto-register Livewire components
- ✅ Auto-add navigation links
- ✅ Auto-create Livewire layout

**Module Management:**

- ✅ List modules with statistics
- ✅ Interactive dashboard
- ✅ Health check system (10-point)
- ✅ Safe module deletion
- ✅ Automatic cleanup

**Advanced Features:**

- ✅ Relationship scaffolding
- ✅ BelongsTo and HasMany support
- ✅ Foreign key generation
- ✅ Navigation management
- ✅ Health monitoring

**Developer Experience:**

- ✅ Beautiful CLI output
- ✅ Helpful tips and suggestions
- ✅ Comprehensive error messages
- ✅ Progress indicators
- ✅ Double confirmations for safety

---

## 🗺️ Roadmap

### Coming Soon

- 🔄 Module update command
- 📦 Module export/import
- 🔗 Module dependency graph
- 🌐 Multi-language support
- 🎨 More UI frameworks (Bootstrap, Vuetify)
- 🧩 Plugin system
- 📊 Advanced analytics
- 🔐 Permission scaffolding
- 🚀 Performance dashboard
- 📱 Mobile-first templates

---

## ⭐ Show Your Support

<div align="center">

If this package helps you build better Laravel applications:

**⭐ Star on GitHub**  **💬 Leave Feedback**

---

**Laravel Module Maker** - Your magic wand for building modular Laravel applications.

*Transform monolithic apps into modular masterpieces with a single command.*

</div>
