<?php

use App\Http\Controllers\ProfileController;
use App\Models\Product;
use App\Models\ProductStockOpname;
use App\Models\Purchase;
use App\Models\Production;
use App\Models\RawMaterial;
use App\Models\RawMaterialStockOpname;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Support\ReportBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/language/{locale}', function (string $locale) {
    abort_unless(array_key_exists($locale, config('laravellocalization.supportedLocales', [])), 404);

    session(['locale' => $locale]);
    app()->setLocale($locale);

    return back();
})->name('language.switch');

Route::get('/dashboard', function (Request $request) {
    $today = now()->toDateString();
    $dateFrom = (string) $request->string('date_from', now()->startOfMonth()->toDateString());
    $dateTo = (string) $request->string('date_to', now()->endOfMonth()->toDateString());

    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
        $dateFrom = now()->startOfMonth()->toDateString();
    }

    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
        $dateTo = now()->endOfMonth()->toDateString();
    }

    if ($dateFrom > $dateTo) {
        [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
    }

    $salesToday = Sale::query()
        ->whereDate('sale_date', $today)
        ->sum('total_amount');
    $salesInPeriod = Sale::query()
        ->whereBetween('sale_date', [$dateFrom, $dateTo])
        ->sum('total_amount');
    $purchasesInPeriod = Purchase::query()
        ->whereBetween('purchase_date', [$dateFrom, $dateTo])
        ->sum('total_amount');
    $productionsInPeriod = Production::query()
        ->whereBetween('production_date', [$dateFrom, $dateTo])
        ->sum('production_quantity');

    $topProducts = DB::table('sale_items')
        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
        ->join('products', 'products.id', '=', 'sale_items.product_id')
        ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
        ->select(
            'products.name',
            'products.size',
            DB::raw('SUM(sale_items.quantity) as total_quantity'),
            DB::raw('SUM(sale_items.total_price) as total_amount')
        )
        ->groupBy('products.id', 'products.name', 'products.size')
        ->orderByDesc('total_quantity')
        ->limit(5)
        ->get();

    $rawMaterialUsage = DB::table('production_items')
        ->join('productions', 'productions.id', '=', 'production_items.production_id')
        ->join('raw_materials', 'raw_materials.id', '=', 'production_items.raw_material_id')
        ->whereBetween('productions.production_date', [$dateFrom, $dateTo])
        ->select(
            'raw_materials.name',
            'raw_materials.unit',
            DB::raw('SUM(production_items.quantity_used) as total_quantity')
        )
        ->groupBy('raw_materials.id', 'raw_materials.name', 'raw_materials.unit')
        ->orderByDesc('total_quantity')
        ->limit(5)
        ->get();

    return view('dashboard', [
        'periodLabel' => \Illuminate\Support\Carbon::parse($dateFrom)->format('d M Y').' - '.\Illuminate\Support\Carbon::parse($dateTo)->format('d M Y'),
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'metrics' => [
            'sales_today' => $salesToday,
            'sales_in_period' => $salesInPeriod,
            'purchases_in_period' => $purchasesInPeriod,
            'productions_in_period' => $productionsInPeriod,
            'raw_material_stock' => RawMaterial::sum('quantity'),
            'product_stock' => Product::sum('stock_quantity'),
            'low_raw_materials_count' => RawMaterial::where('quantity', '<=', 10)->count(),
            'low_products_count' => Product::where('stock_quantity', '<=', 5)->count(),
        ],
        'recentSales' => Sale::query()
            ->whereBetween('sale_date', [$dateFrom, $dateTo])
            ->latest('sale_date')
            ->latest()
            ->limit(5)
            ->get(),
        'recentPurchases' => Purchase::query()
            ->with('supplier')
            ->whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->latest('purchase_date')
            ->latest()
            ->limit(5)
            ->get(),
        'lowRawMaterials' => RawMaterial::query()
            ->orderBy('quantity')
            ->limit(6)
            ->get(),
        'lowProducts' => Product::query()
            ->orderBy('stock_quantity')
            ->limit(6)
            ->get(),
        'topProducts' => $topProducts,
        'rawMaterialUsage' => $rawMaterialUsage,
    ]);
})->middleware(['auth', 'verified', 'can:dashboard.view'])->name('dashboard');

Route::middleware('auth')->group(function () {
    $placeholderPage = function (string $title, string $description) {
        return view('admin.placeholder', [
            'title' => $title,
            'description' => $description,
        ]);
    };

    $buildProductionItems = function (Product $product, int $productionQuantity) {
        if ($product->rawMaterials->isEmpty()) {
            throw ValidationException::withMessages([
                'product_id' => 'Product ini belum memiliki detail bahan baku di master data.',
            ]);
        }

        $items = $product->rawMaterials->map(function (RawMaterial $rawMaterial) use ($productionQuantity) {
            $quantityUsed = round((float) $rawMaterial->pivot->quantity * $productionQuantity, 2);
            $stockBefore = round((float) $rawMaterial->quantity, 2);
            $stockAfter = round($stockBefore - $quantityUsed, 2);

            return [
                'raw_material_id' => $rawMaterial->id,
                'raw_material_name' => $rawMaterial->name,
                'unit' => $rawMaterial->unit,
                'quantity_used' => $quantityUsed,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ];
        });

        $insufficientItem = $items->first(fn (array $item) => $item['stock_after'] < 0);

        if ($insufficientItem) {
            throw ValidationException::withMessages([
                'production_quantity' => "Stok {$insufficientItem['raw_material_name']} tidak cukup untuk produksi ini.",
            ]);
        }

        return $items;
    };

    $buildSaleItems = function (array $details) {
        $productIds = collect($details)->pluck('product_id')->map(fn ($id) => (int) $id);
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $items = collect($details)->map(function (array $detail) use ($products) {
            $product = $products->get((int) $detail['product_id']);

            if (! $product) {
                throw ValidationException::withMessages([
                    'details' => 'Product tidak ditemukan untuk salah satu item penjualan.',
                ]);
            }

            $quantity = (int) $detail['quantity'];
            $unitPrice = (int) $product->unit_price;
            $stockAfter = (int) $product->stock_quantity - $quantity;

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $quantity * $unitPrice,
                'stock_after' => $stockAfter,
            ];
        });

        $insufficientItem = $items->first(fn (array $item) => $item['stock_after'] < 0);

        if ($insufficientItem) {
            throw ValidationException::withMessages([
                'details' => "Stok product {$insufficientItem['product_name']} tidak cukup untuk penjualan ini.",
            ]);
        }

        return $items;
    };

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/users', function (Request $request) {
        $search = trim((string) $request->string('search'));
        $sort = (string) $request->string('sort', 'name');
        $direction = $request->string('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $sortableColumns = ['name', 'email'];

        if (! in_array($sort, $sortableColumns, true)) {
            $sort = 'name';
        }

        $users = User::with('roles')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    })->middleware('can:users.view')->name('users.index');

    Route::get('/users/create', function () {
        return view('admin.users.create', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    })->middleware('can:users.view')->name('users.create');

    Route::post('/users', function (Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->syncRoles($validated['roles'] ?? []);

        return redirect()
            ->route('users.index')
            ->with('status', 'User created successfully.');
    })->middleware('can:users.view')->name('users.store');

    Route::get('/users/{user}', function (User $user) {
        $user->load('roles');

        return view('admin.users.show', [
            'user' => $user,
        ]);
    })->middleware('can:users.view')->name('users.show');

    Route::get('/users/{user}/edit', function (User $user) {
        $user->load('roles');

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
        ]);
    })->middleware('can:users.view')->name('users.edit');

    Route::patch('/users/{user}', function (Request $request, User $user) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        $user->syncRoles($validated['roles'] ?? []);

        return redirect()
            ->route('users.index')
            ->with('status', 'User updated successfully.');
    })->middleware('can:users.view')->name('users.update');

    Route::delete('/users/{user}', function (User $user) {
        if (auth()->id() === $user->id) {
            return redirect()
                ->route('users.index')
                ->with('status', 'You cannot delete the currently logged-in user.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'User deleted successfully.');
    })->middleware('can:users.view')->name('users.destroy');

    Route::delete('/users', function (Request $request) {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $ids = collect($validated['user_ids'])
            ->reject(fn ($id) => (int) $id === auth()->id())
            ->values();

        User::whereIn('id', $ids)->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'Selected users deleted successfully.');
    })->middleware('can:users.view')->name('users.bulk-destroy');

    Route::post('/notifications/read-all', function () {
        auth()->user()?->unreadNotifications->markAsRead();

        return response()->json([
            'status' => 'ok',
        ]);
    })->name('notifications.read-all');

    Route::get('/suppliers', function (Request $request) {
        $search = trim((string) $request->string('search'));
        $sort = (string) $request->string('sort', 'supplier_code');
        $direction = $request->string('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $sortableColumns = ['supplier_code', 'name', 'person_in_charge', 'phone_number', 'is_active'];

        if (! in_array($sort, $sortableColumns, true)) {
            $sort = 'supplier_code';
        }

        $suppliers = Supplier::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('supplier_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('person_in_charge', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.suppliers.index', [
            'suppliers' => $suppliers,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    })->middleware('can:suppliers.view')->name('suppliers.index');

    Route::get('/suppliers/create', function () {
        return view('admin.suppliers.create');
    })->middleware('can:suppliers.view')->name('suppliers.create');

    Route::post('/suppliers', function (Request $request) {
        $validated = $request->validate(
            [
                'supplier_code' => ['required', 'string', 'max:50', 'unique:suppliers,supplier_code'],
                'name' => ['required', 'string', 'max:255'],
                'person_in_charge' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string'],
                'phone_number' => ['required', 'string', 'max:30'],
                'is_active' => ['required', 'boolean'],
            ],
            [
                'supplier_code.unique' => 'Kode supplier ini sudah digunakan. Silakan pakai kode lain.',
            ]
        );

        Supplier::create($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('status', 'Supplier created successfully.');
    })->middleware('can:suppliers.view')->name('suppliers.store');

    Route::get('/suppliers/{supplier}', function (Supplier $supplier) {
        return view('admin.suppliers.show', [
            'supplier' => $supplier,
        ]);
    })->middleware('can:suppliers.view')->name('suppliers.show');

    Route::get('/suppliers/{supplier}/edit', function (Supplier $supplier) {
        return view('admin.suppliers.edit', [
            'supplier' => $supplier,
        ]);
    })->middleware('can:suppliers.view')->name('suppliers.edit');

    Route::patch('/suppliers/{supplier}', function (Request $request, Supplier $supplier) {
        $validated = $request->validate(
            [
                'supplier_code' => ['required', 'string', 'max:50', 'unique:suppliers,supplier_code,'.$supplier->id],
                'name' => ['required', 'string', 'max:255'],
                'person_in_charge' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string'],
                'phone_number' => ['required', 'string', 'max:30'],
                'is_active' => ['required', 'boolean'],
            ],
            [
                'supplier_code.unique' => 'Kode supplier ini sudah digunakan. Silakan pakai kode lain.',
            ]
        );

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('status', 'Supplier updated successfully.');
    })->middleware('can:suppliers.view')->name('suppliers.update');

    Route::delete('/suppliers/{supplier}', function (Supplier $supplier) {
        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('status', 'Supplier deleted successfully.');
    })->middleware('can:suppliers.view')->name('suppliers.destroy');

    Route::delete('/suppliers', function (Request $request) {
        $validated = $request->validate([
            'supplier_ids' => ['required', 'array', 'min:1'],
            'supplier_ids.*' => ['integer', 'exists:suppliers,id'],
        ]);

        Supplier::whereIn('id', $validated['supplier_ids'])->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('status', 'Selected suppliers deleted successfully.');
    })->middleware('can:suppliers.view')->name('suppliers.bulk-destroy');
    Route::get('/raw-materials', function (Request $request) {
        $search = trim((string) $request->string('search'));
        $sort = (string) $request->string('sort', 'raw_material_code');
        $direction = $request->string('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $sortableColumns = ['raw_material_code', 'name', 'quantity', 'unit'];

        if (! in_array($sort, $sortableColumns, true)) {
            $sort = 'raw_material_code';
        }

        $rawMaterials = RawMaterial::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('raw_material_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('unit', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.raw-materials.index', [
            'rawMaterials' => $rawMaterials,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    })->middleware('can:raw-materials.view')->name('raw-materials.index');

    Route::get('/raw-materials/create', function () {
        return view('admin.raw-materials.create');
    })->middleware('can:raw-materials.view')->name('raw-materials.create');

    Route::post('/raw-materials', function (Request $request) {
        $validated = $request->validate(
            [
                'raw_material_code' => ['required', 'string', 'max:50', 'unique:raw_materials,raw_material_code'],
                'name' => ['required', 'string', 'max:255'],
                'unit' => ['required', 'string', 'max:50'],
                'description' => ['nullable', 'string'],
            ],
            [
                'raw_material_code.unique' => 'Kode raw material ini sudah digunakan. Silakan pakai kode lain.',
            ]
        );

        RawMaterial::create([
            ...$validated,
            'quantity' => 0,
        ]);

        return redirect()
            ->route('raw-materials.index')
            ->with('status', 'Raw material created successfully.');
    })->middleware('can:raw-materials.view')->name('raw-materials.store');

    Route::get('/raw-materials/{rawMaterial}', function (RawMaterial $rawMaterial) {
        return view('admin.raw-materials.show', [
            'rawMaterial' => $rawMaterial,
        ]);
    })->middleware('can:raw-materials.view')->name('raw-materials.show');

    Route::get('/raw-materials/{rawMaterial}/edit', function (RawMaterial $rawMaterial) {
        return view('admin.raw-materials.edit', [
            'rawMaterial' => $rawMaterial,
        ]);
    })->middleware('can:raw-materials.view')->name('raw-materials.edit');

    Route::patch('/raw-materials/{rawMaterial}', function (Request $request, RawMaterial $rawMaterial) {
        $validated = $request->validate(
            [
                'raw_material_code' => ['required', 'string', 'max:50', 'unique:raw_materials,raw_material_code,'.$rawMaterial->id],
                'name' => ['required', 'string', 'max:255'],
                'unit' => ['required', 'string', 'max:50'],
                'description' => ['nullable', 'string'],
            ],
            [
                'raw_material_code.unique' => 'Kode raw material ini sudah digunakan. Silakan pakai kode lain.',
            ]
        );

        $rawMaterial->update($validated);

        return redirect()
            ->route('raw-materials.index')
            ->with('status', 'Raw material updated successfully.');
    })->middleware('can:raw-materials.view')->name('raw-materials.update');

    Route::delete('/raw-materials/{rawMaterial}', function (RawMaterial $rawMaterial) {
        $rawMaterial->delete();

        return redirect()
            ->route('raw-materials.index')
            ->with('status', 'Raw material deleted successfully.');
    })->middleware('can:raw-materials.view')->name('raw-materials.destroy');

    Route::delete('/raw-materials', function (Request $request) {
        $validated = $request->validate([
            'raw_material_ids' => ['required', 'array', 'min:1'],
            'raw_material_ids.*' => ['integer', 'exists:raw_materials,id'],
        ]);

        RawMaterial::whereIn('id', $validated['raw_material_ids'])->delete();

        return redirect()
            ->route('raw-materials.index')
            ->with('status', 'Selected raw materials deleted successfully.');
    })->middleware('can:raw-materials.view')->name('raw-materials.bulk-destroy');
    Route::get('/products', function (Request $request) {
        $search = trim((string) $request->string('search'));
        $sort = (string) $request->string('sort', 'name');
        $direction = $request->string('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $sortableColumns = ['name', 'size', 'unit', 'unit_price'];

        if (! in_array($sort, $sortableColumns, true)) {
            $sort = 'name';
        }

        $products = Product::with('rawMaterials')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('size', 'like', "%{$search}%")
                        ->orWhere('unit', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    })->middleware('can:products.view')->name('products.index');

    Route::get('/products/create', function () {
        return view('admin.products.create', [
            'rawMaterials' => RawMaterial::orderBy('name')->get(),
            'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'],
        ]);
    })->middleware('can:products.view')->name('products.create');

    Route::post('/products', function (Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'size' => ['required', 'string', 'in:XS,S,M,L,XL,XXL,XXXL'],
            'unit' => ['required', 'string', 'in:Pcs'],
            'unit_price' => ['required', 'integer', 'min:0'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.raw_material_id' => ['required', 'integer', 'exists:raw_materials,id', 'distinct'],
            'details.*.quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'size' => $validated['size'],
            'unit' => $validated['unit'],
            'unit_price' => $validated['unit_price'],
        ]);

        $product->rawMaterials()->sync(
            collect($validated['details'])->mapWithKeys(fn (array $detail) => [
                $detail['raw_material_id'] => ['quantity' => $detail['quantity']],
            ])->all()
        );

        return redirect()
            ->route('products.index')
            ->with('status', 'Product created successfully.');
    })->middleware('can:products.view')->name('products.store');

    Route::get('/products/{product}', function (Product $product) {
        $product->load('rawMaterials');

        return view('admin.products.show', [
            'product' => $product,
        ]);
    })->middleware('can:products.view')->name('products.show');

    Route::get('/products/{product}/edit', function (Product $product) {
        $product->load('rawMaterials');

        return view('admin.products.edit', [
            'product' => $product,
            'rawMaterials' => RawMaterial::orderBy('name')->get(),
            'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'],
        ]);
    })->middleware('can:products.view')->name('products.edit');

    Route::patch('/products/{product}', function (Request $request, Product $product) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'size' => ['required', 'string', 'in:XS,S,M,L,XL,XXL,XXXL'],
            'unit' => ['required', 'string', 'in:Pcs'],
            'unit_price' => ['required', 'integer', 'min:0'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.raw_material_id' => ['required', 'integer', 'exists:raw_materials,id', 'distinct'],
            'details.*.quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        $product->update([
            'name' => $validated['name'],
            'size' => $validated['size'],
            'unit' => $validated['unit'],
            'unit_price' => $validated['unit_price'],
        ]);

        $product->rawMaterials()->sync(
            collect($validated['details'])->mapWithKeys(fn (array $detail) => [
                $detail['raw_material_id'] => ['quantity' => $detail['quantity']],
            ])->all()
        );

        return redirect()
            ->route('products.index')
            ->with('status', 'Product updated successfully.');
    })->middleware('can:products.view')->name('products.update');

    Route::delete('/products/{product}', function (Product $product) {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'Product deleted successfully.');
    })->middleware('can:products.view')->name('products.destroy');

    Route::delete('/products', function (Request $request) {
        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        Product::whereIn('id', $validated['product_ids'])->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'Selected products deleted successfully.');
    })->middleware('can:products.view')->name('products.bulk-destroy');
    Route::get('/purchases', function (Request $request) {
        $search = trim((string) $request->string('search'));
        $sort = (string) $request->string('sort', 'purchase_date');
        $direction = $request->string('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $sortableColumns = ['purchase_date', 'total_amount'];

        if (! in_array($sort, $sortableColumns, true)) {
            $sort = 'purchase_date';
        }

        $purchases = Purchase::query()
            ->with(['supplier', 'personInCharge', 'items.rawMaterial'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('notes', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($supplierQuery) => $supplierQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('personInCharge', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.purchases.index', [
            'purchases' => $purchases,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    })->middleware('can:purchases.view')->name('purchases.index');

    Route::get('/purchases/create', function () {
        return view('admin.purchases.create', [
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(),
            'rawMaterials' => RawMaterial::query()->orderBy('name')->get(),
        ]);
    })->middleware('can:purchases.view')->name('purchases.create');

    Route::post('/purchases', function (Request $request) {
        $validated = $request->validate([
            'purchase_date' => ['required', 'date'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'person_in_charge_id' => ['required', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.raw_material_id' => ['required', 'integer', 'exists:raw_materials,id', 'distinct'],
            'details.*.quantity' => ['required', 'numeric', 'gt:0'],
            'details.*.unit_price' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $items = collect($validated['details'])->map(function (array $detail) {
                $quantity = round((float) $detail['quantity'], 2);
                $unitPrice = (int) $detail['unit_price'];

                return [
                    'raw_material_id' => (int) $detail['raw_material_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => (int) round($quantity * $unitPrice),
                ];
            });

            $purchase = Purchase::create([
                'purchase_date' => $validated['purchase_date'],
                'supplier_id' => $validated['supplier_id'],
                'person_in_charge_id' => $validated['person_in_charge_id'],
                'notes' => $validated['notes'] ?? null,
                'total_amount' => $items->sum('total_price'),
            ]);

            $purchase->items()->createMany($items->all());

            foreach ($items as $item) {
                RawMaterial::query()
                    ->whereKey($item['raw_material_id'])
                    ->increment('quantity', $item['quantity']);
            }
        });

        return redirect()
            ->route('purchases.index')
            ->with('status', 'Purchase created successfully.');
    })->middleware('can:purchases.view')->name('purchases.store');

    Route::get('/purchases/{purchase}', function (Purchase $purchase) {
        $purchase->load(['supplier', 'personInCharge', 'items.rawMaterial']);

        return view('admin.purchases.show', [
            'purchase' => $purchase,
        ]);
    })->middleware('can:purchases.view')->name('purchases.show');

    Route::get('/purchases/{purchase}/edit', function (Purchase $purchase) {
        $purchase->load('items.rawMaterial');

        return view('admin.purchases.edit', [
            'purchase' => $purchase,
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(),
            'rawMaterials' => RawMaterial::query()->orderBy('name')->get(),
        ]);
    })->middleware('can:purchases.view')->name('purchases.edit');

    Route::patch('/purchases/{purchase}', function (Request $request, Purchase $purchase) {
        $validated = $request->validate([
            'purchase_date' => ['required', 'date'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'person_in_charge_id' => ['required', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.raw_material_id' => ['required', 'integer', 'exists:raw_materials,id', 'distinct'],
            'details.*.quantity' => ['required', 'numeric', 'gt:0'],
            'details.*.unit_price' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $purchase) {
            $purchase->load('items');

            foreach ($purchase->items as $existingItem) {
                RawMaterial::query()
                    ->whereKey($existingItem->raw_material_id)
                    ->decrement('quantity', (float) $existingItem->quantity);
            }

            $purchase->items()->delete();

            $items = collect($validated['details'])->map(function (array $detail) {
                $quantity = round((float) $detail['quantity'], 2);
                $unitPrice = (int) $detail['unit_price'];

                return [
                    'raw_material_id' => (int) $detail['raw_material_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => (int) round($quantity * $unitPrice),
                ];
            });

            $purchase->update([
                'purchase_date' => $validated['purchase_date'],
                'supplier_id' => $validated['supplier_id'],
                'person_in_charge_id' => $validated['person_in_charge_id'],
                'notes' => $validated['notes'] ?? null,
                'total_amount' => $items->sum('total_price'),
            ]);

            $purchase->items()->createMany($items->all());

            foreach ($items as $item) {
                RawMaterial::query()
                    ->whereKey($item['raw_material_id'])
                    ->increment('quantity', $item['quantity']);
            }
        });

        return redirect()
            ->route('purchases.index')
            ->with('status', 'Purchase updated successfully.');
    })->middleware('can:purchases.view')->name('purchases.update');

    Route::delete('/purchases/{purchase}', function (Purchase $purchase) {
        DB::transaction(function () use ($purchase) {
            $purchase->load('items');

            foreach ($purchase->items as $item) {
                RawMaterial::query()
                    ->whereKey($item->raw_material_id)
                    ->decrement('quantity', (float) $item->quantity);
            }

            $purchase->delete();
        });

        return redirect()
            ->route('purchases.index')
            ->with('status', 'Purchase deleted successfully.');
    })->middleware('can:purchases.view')->name('purchases.destroy');
    Route::get('/productions', function (Request $request) {
        $search = trim((string) $request->string('search'));
        $sort = (string) $request->string('sort', 'production_date');
        $direction = $request->string('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $sortableColumns = ['production_date', 'production_quantity'];

        if (! in_array($sort, $sortableColumns, true)) {
            $sort = 'production_date';
        }

        $productions = Production::query()
            ->with(['product', 'items.rawMaterial'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('notes', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.productions.index', [
            'productions' => $productions,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    })->middleware('can:productions.view')->name('productions.index');

    Route::get('/productions/create', function () {
        return view('admin.productions.create', [
            'products' => Product::query()->with('rawMaterials')->orderBy('name')->get(),
        ]);
    })->middleware('can:productions.view')->name('productions.create');

    Route::post('/productions', function (Request $request) use ($buildProductionItems) {
        $validated = $request->validate([
            'production_date' => ['required', 'date'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'production_quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $product = Product::query()
            ->with('rawMaterials')
            ->findOrFail($validated['product_id']);

        $productionQuantity = (int) $validated['production_quantity'];
        $items = $buildProductionItems($product, $productionQuantity);

        DB::transaction(function () use ($validated, $product, $productionQuantity, $items) {
            $production = Production::create([
                'production_date' => $validated['production_date'],
                'product_id' => $product->id,
                'production_quantity' => $productionQuantity,
                'notes' => $validated['notes'] ?? null,
            ]);

            $production->items()->createMany($items->map(fn (array $item) => [
                'raw_material_id' => $item['raw_material_id'],
                'unit' => $item['unit'],
                'quantity_used' => $item['quantity_used'],
                'stock_before' => $item['stock_before'],
                'stock_after' => $item['stock_after'],
            ])->all());

            foreach ($items as $item) {
                RawMaterial::query()
                    ->whereKey($item['raw_material_id'])
                    ->decrement('quantity', $item['quantity_used']);
            }

            $product->increment('stock_quantity', $productionQuantity);
        });

        return redirect()
            ->route('productions.index')
            ->with('status', 'Production created successfully.');
    })->middleware('can:productions.view')->name('productions.store');

    Route::get('/productions/{production}/edit', function (Production $production) {
        return view('admin.productions.edit', [
            'production' => $production->load('items.rawMaterial', 'product'),
            'products' => Product::query()->with('rawMaterials')->orderBy('name')->get(),
        ]);
    })->middleware('can:productions.view')->name('productions.edit');

    Route::patch('/productions/{production}', function (Request $request, Production $production) use ($buildProductionItems) {
        $validated = $request->validate([
            'production_date' => ['required', 'date'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'production_quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $production, $buildProductionItems) {
            $production->load(['product', 'items.rawMaterial']);

            if ((int) $production->product->stock_quantity < (int) $production->production_quantity) {
                throw ValidationException::withMessages([
                    'production_quantity' => 'Stok product saat ini tidak cukup untuk membatalkan transaksi produksi lama.',
                ]);
            }

            $production->product->decrement('stock_quantity', (int) $production->production_quantity);

            foreach ($production->items as $existingItem) {
                RawMaterial::query()
                    ->whereKey($existingItem->raw_material_id)
                    ->increment('quantity', (float) $existingItem->quantity_used);
            }

            $newProduct = Product::query()
                ->with('rawMaterials')
                ->findOrFail($validated['product_id']);

            $productionQuantity = (int) $validated['production_quantity'];
            $items = $buildProductionItems($newProduct, $productionQuantity);

            $production->update([
                'production_date' => $validated['production_date'],
                'product_id' => $newProduct->id,
                'production_quantity' => $productionQuantity,
                'notes' => $validated['notes'] ?? null,
            ]);

            $production->items()->delete();
            $production->items()->createMany($items->map(fn (array $item) => [
                'raw_material_id' => $item['raw_material_id'],
                'unit' => $item['unit'],
                'quantity_used' => $item['quantity_used'],
                'stock_before' => $item['stock_before'],
                'stock_after' => $item['stock_after'],
            ])->all());

            foreach ($items as $item) {
                RawMaterial::query()
                    ->whereKey($item['raw_material_id'])
                    ->decrement('quantity', $item['quantity_used']);
            }

            $newProduct->increment('stock_quantity', $productionQuantity);
        });

        return redirect()
            ->route('productions.index')
            ->with('status', 'Production updated successfully.');
    })->middleware('can:productions.view')->name('productions.update');

    Route::get('/productions/{production}', function (Production $production) {
        $production->load(['product', 'items.rawMaterial']);

        return view('admin.productions.show', [
            'production' => $production,
        ]);
    })->middleware('can:productions.view')->name('productions.show');

    Route::delete('/productions/{production}', function (Production $production) {
        DB::transaction(function () use ($production) {
            $production->load(['product', 'items']);

            if ((int) $production->product->stock_quantity < (int) $production->production_quantity) {
                throw ValidationException::withMessages([
                    'production' => 'Stok product saat ini tidak cukup untuk membatalkan transaksi produksi ini.',
                ]);
            }

            foreach ($production->items as $item) {
                RawMaterial::query()
                    ->whereKey($item->raw_material_id)
                    ->increment('quantity', (float) $item->quantity_used);
            }

            $production->product->decrement('stock_quantity', (int) $production->production_quantity);
            $production->delete();
        });

        return redirect()
            ->route('productions.index')
            ->with('status', 'Production deleted successfully.');
    })->middleware('can:productions.view')->name('productions.destroy');
    Route::get('/sales', function (Request $request) {
        $search = trim((string) $request->string('search'));
        $sort = (string) $request->string('sort', 'sale_date');
        $direction = $request->string('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $sortableColumns = ['sale_date', 'invoice_number', 'total_amount'];

        if (! in_array($sort, $sortableColumns, true)) {
            $sort = 'sale_date';
        }

        $sales = Sale::query()
            ->with('items.product')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('buyer_name', 'like', "%{$search}%")
                        ->orWhere('buyer_phone', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.sales.index', [
            'sales' => $sales,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    })->middleware('can:sales.view')->name('sales.index');

    Route::get('/sales/create', function () {
        return view('admin.sales.create', [
            'products' => Product::query()->orderBy('name')->get(),
        ]);
    })->middleware('can:sales.view')->name('sales.create');

    Route::post('/sales', function (Request $request) use ($buildSaleItems) {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:50', 'unique:sales,invoice_number'],
            'sale_date' => ['required', 'date'],
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_phone' => ['nullable', 'string', 'max:30'],
            'buyer_address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'details.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $items = $buildSaleItems($validated['details']);

        DB::transaction(function () use ($validated, $items) {
            $sale = Sale::create([
                'invoice_number' => $validated['invoice_number'],
                'sale_date' => $validated['sale_date'],
                'buyer_name' => $validated['buyer_name'],
                'buyer_phone' => $validated['buyer_phone'] ?? null,
                'buyer_address' => $validated['buyer_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_amount' => $items->sum('total_price'),
            ]);

            $sale->items()->createMany($items->map(fn (array $item) => [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price'],
            ])->all());

            foreach ($items as $item) {
                Product::query()
                    ->whereKey($item['product_id'])
                    ->decrement('stock_quantity', $item['quantity']);
            }
        });

        return redirect()
            ->route('sales.index')
            ->with('status', 'Sale created successfully.');
    })->middleware('can:sales.view')->name('sales.store');

    Route::get('/sales/{sale}', function (Sale $sale) {
        return view('admin.sales.show', [
            'sale' => $sale->load('items.product'),
        ]);
    })->middleware('can:sales.view')->name('sales.show');

    Route::get('/sales/{sale}/edit', function (Sale $sale) {
        return view('admin.sales.edit', [
            'sale' => $sale->load('items.product'),
            'products' => Product::query()->orderBy('name')->get(),
        ]);
    })->middleware('can:sales.view')->name('sales.edit');

    Route::patch('/sales/{sale}', function (Request $request, Sale $sale) use ($buildSaleItems) {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:50', 'unique:sales,invoice_number,'.$sale->id],
            'sale_date' => ['required', 'date'],
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_phone' => ['nullable', 'string', 'max:30'],
            'buyer_address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'details.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated, $sale, $buildSaleItems) {
            $sale->load('items.product');

            foreach ($sale->items as $existingItem) {
                Product::query()
                    ->whereKey($existingItem->product_id)
                    ->increment('stock_quantity', (int) $existingItem->quantity);
            }

            $items = $buildSaleItems($validated['details']);

            $sale->update([
                'invoice_number' => $validated['invoice_number'],
                'sale_date' => $validated['sale_date'],
                'buyer_name' => $validated['buyer_name'],
                'buyer_phone' => $validated['buyer_phone'] ?? null,
                'buyer_address' => $validated['buyer_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_amount' => $items->sum('total_price'),
            ]);

            $sale->items()->delete();
            $sale->items()->createMany($items->map(fn (array $item) => [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price'],
            ])->all());

            foreach ($items as $item) {
                Product::query()
                    ->whereKey($item['product_id'])
                    ->decrement('stock_quantity', $item['quantity']);
            }
        });

        return redirect()
            ->route('sales.index')
            ->with('status', 'Sale updated successfully.');
    })->middleware('can:sales.view')->name('sales.update');

    Route::delete('/sales/{sale}', function (Sale $sale) {
        DB::transaction(function () use ($sale) {
            $sale->load('items');

            foreach ($sale->items as $item) {
                Product::query()
                    ->whereKey($item->product_id)
                    ->increment('stock_quantity', (int) $item->quantity);
            }

            $sale->delete();
        });

        return redirect()
            ->route('sales.index')
            ->with('status', 'Sale deleted successfully.');
    })->middleware('can:sales.view')->name('sales.destroy');
    Route::get('/stock-opname/raw-materials', function (Request $request) {
        $search = trim((string) $request->string('search'));

        $rawMaterials = RawMaterial::query()
            ->with(['stockOpnames' => fn ($query) => $query->latest()->limit(1)])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('raw_material_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('unit', 'like', "%{$search}%");
                });
            })
            ->orderBy('raw_material_code')
            ->paginate(10)
            ->withQueryString();

        $recentOpnames = RawMaterialStockOpname::query()
            ->with(['rawMaterial', 'personInCharge'])
            ->latest('opname_date')
            ->latest()
            ->limit(8)
            ->get();

        $summary = [
            'materials_count' => RawMaterial::count(),
            'total_stock' => RawMaterial::sum('quantity'),
            'adjustment_count' => RawMaterialStockOpname::count(),
            'last_opname_date' => RawMaterialStockOpname::max('opname_date'),
        ];

        return view('admin.stock-opname.raw-materials.index', [
            'rawMaterials' => $rawMaterials,
            'recentOpnames' => $recentOpnames,
            'summary' => $summary,
            'search' => $search,
        ]);
    })->name('stock-opname.raw-materials');

    Route::get('/stock-opname/raw-materials/create', function () {
        return view('admin.stock-opname.raw-materials.create', [
            'rawMaterials' => RawMaterial::query()->orderBy('raw_material_code')->get(),
        ]);
    })->name('stock-opname.raw-materials.create');

    Route::post('/stock-opname/raw-materials', function (Request $request) {
        if ($request->has('details')) {
            $validated = $request->validate([
                'opname_date' => ['required', 'date'],
                'notes' => ['nullable', 'string'],
                'details' => ['required', 'array', 'min:1'],
                'details.*.raw_material_id' => ['required', 'integer', 'exists:raw_materials,id', 'distinct'],
                'details.*.physical_quantity' => ['required', 'numeric', 'min:0'],
            ]);
        } else {
            $validated = $request->validate([
                'opname_date' => ['required', 'date'],
                'raw_material_id' => ['required', 'integer', 'exists:raw_materials,id'],
                'physical_quantity' => ['required', 'numeric', 'min:0'],
                'notes' => ['nullable', 'string'],
            ]);

            $validated['details'] = [[
                'raw_material_id' => $validated['raw_material_id'],
                'physical_quantity' => $validated['physical_quantity'],
            ]];
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated['details'] as $detail) {
                $rawMaterial = RawMaterial::query()
                    ->whereKey($detail['raw_material_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $systemQuantity = round((float) $rawMaterial->quantity, 2);
                $physicalQuantity = round((float) $detail['physical_quantity'], 2);

                RawMaterialStockOpname::create([
                    'opname_date' => $validated['opname_date'],
                    'raw_material_id' => $rawMaterial->id,
                    'person_in_charge_id' => auth()->id(),
                    'system_quantity' => $systemQuantity,
                    'physical_quantity' => $physicalQuantity,
                    'adjustment_quantity' => round($physicalQuantity - $systemQuantity, 2),
                    'notes' => $validated['notes'] ?? null,
                ]);

                $rawMaterial->update([
                    'quantity' => $physicalQuantity,
                ]);
            }
        });

        return redirect()
            ->route('stock-opname.raw-materials')
            ->with('status', 'Stok opname bahan baku berhasil disimpan.');
    })->name('stock-opname.raw-materials.store');
    Route::get('/stock-opname/products', function (Request $request) {
        $search = trim((string) $request->string('search'));

        $products = Product::query()
            ->with(['stockOpnames' => fn ($query) => $query->latest()->limit(1)])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('size', 'like', "%{$search}%")
                        ->orWhere('unit', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->orderBy('size')
            ->paginate(10)
            ->withQueryString();

        $recentOpnames = ProductStockOpname::query()
            ->with(['product', 'personInCharge'])
            ->latest('opname_date')
            ->latest()
            ->limit(8)
            ->get();

        $summary = [
            'products_count' => Product::count(),
            'total_stock' => Product::sum('stock_quantity'),
            'adjustment_count' => ProductStockOpname::count(),
            'last_opname_date' => ProductStockOpname::max('opname_date'),
        ];

        return view('admin.stock-opname.products.index', [
            'products' => $products,
            'recentOpnames' => $recentOpnames,
            'summary' => $summary,
            'search' => $search,
        ]);
    })->name('stock-opname.products');

    Route::get('/stock-opname/products/create', function () {
        return view('admin.stock-opname.products.create', [
            'products' => Product::query()->orderBy('name')->orderBy('size')->get(),
        ]);
    })->name('stock-opname.products.create');

    Route::post('/stock-opname/products', function (Request $request) {
        if ($request->has('details')) {
            $validated = $request->validate([
                'opname_date' => ['required', 'date'],
                'notes' => ['nullable', 'string'],
                'details' => ['required', 'array', 'min:1'],
                'details.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
                'details.*.physical_quantity' => ['required', 'integer', 'min:0'],
            ]);
        } else {
            $validated = $request->validate([
                'opname_date' => ['required', 'date'],
                'product_id' => ['required', 'integer', 'exists:products,id'],
                'physical_quantity' => ['required', 'integer', 'min:0'],
                'notes' => ['nullable', 'string'],
            ]);

            $validated['details'] = [[
                'product_id' => $validated['product_id'],
                'physical_quantity' => $validated['physical_quantity'],
            ]];
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated['details'] as $detail) {
                $product = Product::query()
                    ->whereKey($detail['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $systemQuantity = (int) $product->stock_quantity;
                $physicalQuantity = (int) $detail['physical_quantity'];

                ProductStockOpname::create([
                    'opname_date' => $validated['opname_date'],
                    'product_id' => $product->id,
                    'person_in_charge_id' => auth()->id(),
                    'system_quantity' => $systemQuantity,
                    'physical_quantity' => $physicalQuantity,
                    'adjustment_quantity' => $physicalQuantity - $systemQuantity,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $product->update([
                    'stock_quantity' => $physicalQuantity,
                ]);
            }
        });

        return redirect()
            ->route('stock-opname.products')
            ->with('status', 'Stok opname product berhasil disimpan.');
    })->name('stock-opname.products.store');
    Route::get('/reports', function (Request $request) {
        return view('admin.reports.index', ReportBuilder::build($request));
    })->name('reports.index');

    Route::get('/reports/view', function (Request $request) {
        return view('admin.reports.view', ReportBuilder::build($request));
    })->name('reports.view');

    Route::get('/reports/download/csv', function (Request $request) {
        $report = ReportBuilder::build($request);

        abort_unless($report['reportType'], 404);

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [$report['reportLabel']]);
            fputcsv($handle, ['Periode', ($report['dateFrom'] ?: 'awal data').' s/d '.($report['dateTo'] ?: 'akhir data')]);
            fputcsv($handle, []);

            foreach ($report['summary'] as $label => $value) {
                fputcsv($handle, [$label, $value]);
            }

            fputcsv($handle, []);
            fputcsv($handle, $report['table']['headers']);

            foreach ($report['table']['rows'] as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, ReportBuilder::filename($report, 'csv'), [
            'Content-Type' => 'text/csv',
        ]);
    })->name('reports.download.csv');

    Route::get('/reports/download/pdf', function (Request $request) {
        $report = ReportBuilder::build($request);

        abort_unless($report['reportType'], 404);

        return response(ReportBuilder::pdf($report), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.ReportBuilder::filename($report, 'pdf').'"',
        ]);
    })->name('reports.download.pdf');

    Route::get('/user-roles', function (Request $request) {
        $search = trim((string) $request->string('search'));
        $sort = (string) $request->string('sort', 'name');
        $direction = $request->string('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $sortableColumns = ['name', 'guard_name'];

        if (! in_array($sort, $sortableColumns, true)) {
            $sort = 'name';
        }

        $roles = Role::with('permissions')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.roles.index', [
            'roles' => $roles,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    })->middleware('can:roles.view')->name('roles.index');

    Route::get('/user-roles/create', function () {
        return view('admin.roles.create', [
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    })->middleware('can:roles.view')->name('roles.create');

    Route::post('/user-roles', function (Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role created successfully.');
    })->middleware('can:roles.view')->name('roles.store');

    Route::get('/user-roles/{role}', function (Role $role) {
        $role->load('permissions');

        return view('admin.roles.show', [
            'role' => $role,
        ]);
    })->middleware('can:roles.view')->name('roles.show');

    Route::get('/user-roles/{role}/edit', function (Role $role) {
        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    })->middleware('can:roles.view')->name('roles.edit');

    Route::patch('/user-roles/{role}', function (Request $request, Role $role) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role updated successfully.');
    })->middleware('can:roles.view')->name('roles.update');

    Route::delete('/user-roles/{role}', function (Role $role) {
        if ($role->name === 'super-admin') {
            return redirect()
                ->route('roles.index')
                ->with('status', 'The super-admin role cannot be deleted.');
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role deleted successfully.');
    })->middleware('can:roles.view')->name('roles.destroy');

    Route::delete('/user-roles', function (Request $request) {
        $validated = $request->validate([
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        Role::whereIn('id', collect($validated['role_ids'])->values())
            ->where('name', '!=', 'super-admin')
            ->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('status', 'Selected roles deleted successfully.');
    })->middleware('can:roles.view')->name('roles.bulk-destroy');

    Route::get('/user-permissions', function (Request $request) {
        $search = trim((string) $request->string('search'));
        $sort = (string) $request->string('sort', 'name');
        $direction = $request->string('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $sortableColumns = ['name', 'guard_name'];

        if (! in_array($sort, $sortableColumns, true)) {
            $sort = 'name';
        }

        $permissions = Permission::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('guard_name', 'like', "%{$search}%");
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.permissions.index', [
            'permissions' => $permissions,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    })->middleware('can:permissions.view')->name('permissions.index');

    Route::get('/user-permissions/create', function () {
        return view('admin.permissions.create');
    })->middleware('can:permissions.view')->name('permissions.create');

    Route::post('/user-permissions', function (Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('permissions.index')
            ->with('status', 'Permission created successfully.');
    })->middleware('can:permissions.view')->name('permissions.store');

    Route::get('/user-permissions/{permission}', function (Permission $permission) {
        return view('admin.permissions.show', [
            'permission' => $permission,
        ]);
    })->middleware('can:permissions.view')->name('permissions.show');

    Route::get('/user-permissions/{permission}/edit', function (Permission $permission) {
        return view('admin.permissions.edit', [
            'permission' => $permission,
        ]);
    })->middleware('can:permissions.view')->name('permissions.edit');

    Route::patch('/user-permissions/{permission}', function (Request $request, Permission $permission) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name,'.$permission->id],
        ]);

        $permission->update([
            'name' => $validated['name'],
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('permissions.index')
            ->with('status', 'Permission updated successfully.');
    })->middleware('can:permissions.view')->name('permissions.update');

    Route::delete('/user-permissions/{permission}', function (Permission $permission) {
        $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('permissions.index')
            ->with('status', 'Permission deleted successfully.');
    })->middleware('can:permissions.view')->name('permissions.destroy');

    Route::delete('/user-permissions', function (Request $request) {
        $validated = $request->validate([
            'permission_ids' => ['required', 'array', 'min:1'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        Permission::whereIn('id', $validated['permission_ids'])->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('permissions.index')
            ->with('status', 'Selected permissions deleted successfully.');
    })->middleware('can:permissions.view')->name('permissions.bulk-destroy');
});

require __DIR__.'/auth.php';
