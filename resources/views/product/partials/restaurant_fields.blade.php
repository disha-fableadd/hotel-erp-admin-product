{{-- Restaurant / POS product master sections (conditional by product_kind) --}}
<style>
    .product-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #1b2850;
        margin: 18px 0 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid #e8edf3;
        width: 100%;
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
</style>

<div class="col-12">
    <div class="product-section-title">1. Basic Information</div>
</div>

<div class="col-12">
    <div class="product-section-title">2. Product Type and Food Details</div>
</div>
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

<div class="col-12">
    <div class="product-section-title">3. Pricing and Tax</div>
</div>
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

<div class="col-12 restaurant-variant-fields">
    <div class="product-section-title">4. Variants and Add-ons
        <small class="text-muted fw-normal">(Half/Full, extras — not separate products)</small>
    </div>
    <div class="table-responsive mb-2">
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

<div class="col-12">
    <div class="product-section-title">5. Inventory and Stock</div>
</div>
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

<div class="col-12 restaurant-recipe-fields" style="display:none;">
    <div class="product-section-title">6. Recipe and Ingredients</div>
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
        <p class="text-muted small mt-1 mb-0">Use raw material / packaged product IDs from the product list. Stock is deducted on confirmed sale when Recipe Required is Yes.</p>
    </div>
</div>

<div class="col-12">
    <div class="product-section-title">7. Image and Description</div>
</div>
