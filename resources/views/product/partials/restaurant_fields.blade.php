{{-- Product detail tabs (below core name/SKU/stock/price fields) --}}
<style>
    .product-form-tabs {
        margin-top: 8px;
        width: 100%;
    }
    .product-form-tabs .nav-tabs {
        border-bottom: 2px solid #e8edf3;
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        gap: 2px;
        scrollbar-width: thin;
    }
    .product-form-tabs .nav-tabs .nav-link {
        border: 0;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        padding: 10px 14px;
        border-radius: 8px 8px 0 0;
        background: transparent;
    }
    .product-form-tabs .nav-tabs .nav-link:hover {
        color: #ff9f43;
        background: #fff7ed;
    }
    .product-form-tabs .nav-tabs .nav-link.active {
        color: #ff9f43;
        border-bottom-color: #ff9f43;
        background: #fff;
    }
    .product-form-tabs .tab-content {
        padding: 16px 4px 8px;
        min-height: 180px;
    }
    .restaurant-repeat-table .form-control {
        min-height: 36px;
    }
    .restaurant-repeat-table td {
        vertical-align: middle;
    }
    .btn-row-add {
        background: #ff9f43;
        color: #fff;
        border: 0;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 600;
    }
    .btn-row-remove {
        background: #ef4444;
        color: #fff;
        border: 0;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 12px;
    }
    @media (max-width: 768px) {
        .product-form-tabs .nav-tabs .nav-link {
            padding: 8px 10px;
            font-size: 12px;
        }
    }
</style>

<div class="col-12 product-form-tabs">
    <ul class="nav nav-tabs" id="productDetailTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-basic-btn" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" role="tab">Basic</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-type-btn" data-bs-toggle="tab" data-bs-target="#tab-type" type="button" role="tab">Type &amp; Food</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-pricing-btn" data-bs-toggle="tab" data-bs-target="#tab-pricing" type="button" role="tab">Pricing &amp; Tax</button>
        </li>
        <li class="nav-item restaurant-variant-tab" role="presentation">
            <button class="nav-link" id="tab-variants-btn" data-bs-toggle="tab" data-bs-target="#tab-variants" type="button" role="tab">Variants &amp; Add-ons</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-inventory-btn" data-bs-toggle="tab" data-bs-target="#tab-inventory" type="button" role="tab">Inventory</button>
        </li>
        <li class="nav-item restaurant-recipe-tab" role="presentation" style="display:none;">
            <button class="nav-link" id="tab-recipe-btn" data-bs-toggle="tab" data-bs-target="#tab-recipe" type="button" role="tab">Recipe</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-media-btn" data-bs-toggle="tab" data-bs-target="#tab-media" type="button" role="tab">Image &amp; Description</button>
        </li>
    </ul>

    <div class="tab-content" id="productDetailTabsContent">
        {{-- 1. Basic --}}
        <div class="tab-pane fade show active" id="tab-basic" role="tabpanel">
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="select" name="status" id="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <span class="error_status text-danger"></span>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Stock Status</label>
                        <select class="select" name="availablility" id="availablility" disabled>
                            <option value="in_stock">In Stock</option>
                            <option value="out_stock">Out Of Stock</option>
                        </select>
                        <span class="error_availablility text-danger"></span>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Barcode</label>
                        <input type="text" name="barcode" id="barcode" class="form-control">
                        <span class="error_barcode text-danger"></span>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Display Order</label>
                        <input type="number" class="form-control" name="display_order" id="display_order" value="0" min="0">
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Available for POS</label>
                        <select class="select" name="is_pos_available" id="is_pos_available">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Type & Food --}}
        <div class="tab-pane fade" id="tab-type" role="tabpanel">
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Product Type</label>
                        <select class="select" name="product_kind" id="product_kind">
                            <option value="general">General / Inventory</option>
                            <option value="prepared_food">Prepared Food</option>
                            <option value="raw_material">Raw Material</option>
                            <option value="packaged">Packaged Product</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6 restaurant-food-fields">
                    <div class="form-group">
                        <label>Food Type</label>
                        <select class="select" name="food_type" id="food_type">
                            <option value="na">Not Applicable</option>
                            <option value="veg">Veg</option>
                            <option value="non_veg">Non-Veg</option>
                            <option value="egg">Egg</option>
                            <option value="vegan">Vegan</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6 restaurant-food-fields">
                    <div class="form-group">
                        <label>Kitchen Station</label>
                        <input type="text" class="form-control" name="kitchen_station" id="kitchen_station" placeholder="e.g. Grill, Bar">
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6 restaurant-food-fields">
                    <div class="form-group">
                        <label>Prep Time (minutes)</label>
                        <input type="number" class="form-control" name="prep_time_minutes" id="prep_time_minutes" min="0" step="1">
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Pricing & Tax --}}
        <div class="tab-pane fade" id="tab-pricing" role="tabpanel">
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Cost Price</label>
                        <input type="number" class="form-control" name="cost_price" id="cost_price" min="0" step="0.01" placeholder="Purchase / avg cost">
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Tax (optional)</label>
                        <select class="select" name="tax_id" id="tax_id">
                            <option value="">Use order-level GST</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Tax Pricing</label>
                        <select class="select" name="tax_inclusive" id="tax_inclusive">
                            <option value="0">Tax Exclusive</option>
                            <option value="1">Tax Inclusive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Variants & Add-ons --}}
        <div class="tab-pane fade" id="tab-variants" role="tabpanel">
            <div class="restaurant-variant-fields">
                <p class="text-muted small mb-2">Half / Full / Size variants and extras — do not create separate products.</p>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered restaurant-repeat-table" id="variantsTable">
                        <thead>
                            <tr>
                                <th>Variant Name</th>
                                <th>SKU (optional)</th>
                                <th>Price</th>
                                <th style="width:70px;"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <button type="button" class="btn-row-add" id="addVariantRow">+ Add Variant</button>
                </div>
                <div class="table-responsive mb-2">
                    <table class="table table-bordered restaurant-repeat-table" id="addonsTable">
                        <thead>
                            <tr>
                                <th>Add-on Name</th>
                                <th>Price</th>
                                <th style="width:70px;"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <button type="button" class="btn-row-add" id="addAddonRow">+ Add Add-on</button>
                </div>
            </div>
        </div>

        {{-- 5. Inventory --}}
        <div class="tab-pane fade" id="tab-inventory" role="tabpanel">
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Track Inventory</label>
                        <select class="select" name="track_inventory" id="track_inventory">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Minimum Stock / Reorder Level</label>
                        <input type="number" class="form-control" name="min_stock" id="min_stock" min="0" step="0.01">
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Recipe Required</label>
                        <select class="select" name="recipe_required" id="recipe_required">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- 6. Recipe --}}
        <div class="tab-pane fade" id="tab-recipe" role="tabpanel">
            <div class="restaurant-recipe-fields">
                <div class="row mb-2">
                    <div class="col-lg-3 col-sm-6">
                        <div class="form-group">
                            <label>Yield Qty (per recipe)</label>
                            <input type="number" class="form-control" name="recipe_yield_qty" id="recipe_yield_qty" value="1" min="0.0001" step="0.0001">
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6">
                        <div class="form-group">
                            <label>Recipe Notes</label>
                            <input type="text" class="form-control" name="recipe_notes" id="recipe_notes">
                        </div>
                    </div>
                </div>
                <div class="table-responsive mb-2">
                    <table class="table table-bordered restaurant-repeat-table" id="recipeItemsTable">
                        <thead>
                            <tr>
                                <th>Ingredient Product ID</th>
                                <th>Quantity</th>
                                <th>Qty Type ID (optional)</th>
                                <th style="width:70px;"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <button type="button" class="btn-row-add" id="addRecipeRow">+ Add Ingredient</button>
                    <p class="text-muted small mt-2 mb-0">Use raw material / packaged product IDs. Ingredients deduct on confirmed sale when Recipe Required is Yes.</p>
                </div>
            </div>
        </div>

        {{-- 7. Media --}}
        <div class="tab-pane fade" id="tab-media" role="tabpanel">
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" id="description" rows="4"></textarea>
                        <span class="error_description text-danger"></span>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <div class="form-group">
                        <label>Product Image</label>
                        <div class="image-upload">
                            <input type="file" name="images[]" id="images" multiple>
                            <div class="image-uploads">
                                <img src="{{ env('ImagePath') . '/admin/assets/img/icons/upload.svg' }}" alt="img">
                                <h4>Drag and drop a file to upload</h4>
                            </div>
                        </div>
                        <div class="image-preview existing-images" style="margin-top: 10px;"></div>
                        <span class="error_images text-danger"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
