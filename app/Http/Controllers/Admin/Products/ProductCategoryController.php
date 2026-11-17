<?php

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Products\StoreCategoryRequest;
use App\Models\Products\ProductCategory;
use App\Services\Products\ProductCategoryService;
use App\Support\GridQuery;

class ProductCategoryController extends Controller
{
    public function __construct(private readonly ProductCategoryService $categories) {}

    public function index()
    {
        return view('admin.categories.index', [
            'categories' => $this->categories->paginate(),
            'filters' => [
                'names' => GridQuery::distinct(ProductCategory::query(), 'name'),
            ],
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->categories->create($request->validated());

        return back()->with('success', 'تم إضافة التصنيف.');
    }

    public function update(StoreCategoryRequest $request, ProductCategory $category)
    {
        $this->categories->update($category, $request->validated());

        return back()->with('success', 'تم تحديث التصنيف.');
    }

    public function destroy(ProductCategory $category)
    {
        $this->categories->delete($category);

        return back()->with('success', 'تم حذف التصنيف.');
    }
}
