<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Models\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Factory|View|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $products = Product::query()
            ->search($request->search)
            ->latest()
            ->paginate(10);

        return $request->wantsJson()
            ? response()->json($products)
            : view('products.index', ['products' => $products]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|Factory
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return RedirectResponse
     */
    public function store(ProductStoreRequest $request)
    {
        $productData = $request->validated();

        // if ($request->hasFile('image')) {
        //     $productData['image'] = $request->file('image')->store('products', 'public');
        // }

        if ($request->hasFile('image')) {
    $image = $request->file('image');

    $imageName = time() . '.' . $image->getClientOriginalExtension();

    $image->move(public_path('products'), $imageName);

    $productData['image'] = 'products/' . $imageName;
}

        Product::create($productData);

        return redirect()->route('products.index')
            ->with('success', __('product.success_creating'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Factory|View|\Illuminate\View\View
     */
    public function edit(Product $product)
    {
        return view('products.edit')->with('product', $product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return RedirectResponse
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $productData = $request->validated();

        // if ($request->hasFile('image')) {
        //     if ($product->image) {
        //         Storage::disk('public')->delete($product->image);
        //     }
        //     $productData['image'] = $request->file('image')->store('products', 'public');
        // }
        if ($request->hasFile('image')) {

    if ($product->image && file_exists(public_path($product->image))) {
        unlink(public_path($product->image));
    }

    $image = $request->file('image');

    $imageName = time() . '.' . $image->getClientOriginalExtension();

    $image->move(public_path('products'), $imageName);

    $productData['image'] = 'products/' . $imageName;
}

        $product->update($productData);

        return redirect()->route('products.index')
            ->with('success', __('product.success_updating'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        // if ($product->image) {
        //     Storage::disk('public')->delete($product->image);
        // }
        if ($product->image && file_exists(public_path($product->image))) {
    unlink(public_path($product->image));
}
        $product->delete();

        return response()->json(['success' => true]);
    }

    public function showImportForm()
{
    return view('products.import');
}


public function importCsv(Request $request)
{
    $request->validate([
        'csv_file' => 'required|mimes:csv,txt',
        'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp',
    ]);

    /*
    |--------------------------------------------------------------------------
    | UPLOAD IMAGES
    |--------------------------------------------------------------------------
    */

    $uploadedImages = [];

    if ($request->hasFile('images')) {

        foreach ($request->file('images') as $image) {

            $imageName = $image->getClientOriginalName();

            $image->move(public_path('products'), $imageName);

            $uploadedImages[$imageName] = 'products/' . $imageName;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | READ CSV
    |--------------------------------------------------------------------------
    */

    $file = $request->file('csv_file');

    $csvData = file($file->getRealPath());

    $rows = array_map('str_getcsv', $csvData);

    $header = array_map('trim', $rows[0]);

    unset($rows[0]);

    /*
    |--------------------------------------------------------------------------
    | IMPORT PRODUCTS
    |--------------------------------------------------------------------------
    */

    foreach ($rows as $row) {

        if (count($header) != count($row)) {
            continue;
        }

        $row = array_combine($header, $row);

        if (
            empty($row['name']) ||
            empty($row['barcode'])
        ) {
            continue;
        }

        $imagePath = null;

        if (
            !empty($row['image']) &&
            isset($uploadedImages[$row['image']])
        ) {
            $imagePath = $uploadedImages[$row['image']];
        }

        Product::updateOrCreate(

            [
                'barcode' => $row['barcode']
            ],

            [
                'name' => $row['name'] ?? null,

                'description' => $row['description'] ?? null,

                'image' => $imagePath,

                'price' => $row['price'] ?? 0,

                'purchase_price' => $row['purchase_price'] ?? 0,

                'quantity' => $row['quantity'] ?? 0,

                'status' => $row['status'] ?? 1,
            ]
        );
    }

    return redirect()
        ->route('products.index')
        ->with('success', 'Products imported successfully.');
}




public function downloadTemplate()
{
    $fileName = 'products-template.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename={$fileName}",
    ];

    $columns = [
        'name',
        'description',
        'image',
        'barcode',
        'price',
        'purchase_price',
        'quantity',
        'status',
    ];

    $sampleData = [
        [
            'HP Laptop',
            'Core i7 Laptop',
            'hp.jpg',
            '111222',
            '1200',
            '900',
            '5',
            '1',
        ],
        [
            'Dell Mouse',
            'Wireless Mouse',
            'mouse.jpg',
            '333444',
            '25',
            '10',
            '50',
            '1',
        ]
    ];

    $callback = function () use ($columns, $sampleData) {

        $file = fopen('php://output', 'w');

        // HEADER
        fputcsv($file, $columns);

        // SAMPLE DATA
        foreach ($sampleData as $row) {
            fputcsv($file, $row);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}
