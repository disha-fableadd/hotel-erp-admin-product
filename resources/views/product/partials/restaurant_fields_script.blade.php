<script>
(function ($) {
    function variantRow(data) {
        data = data || {};
        return `<tr>
            <td><input type="hidden" class="variant-id" value="${data.id || ''}">
                <input type="text" class="form-control variant-name" value="${data.name || ''}" placeholder="Half / Full / Large"></td>
            <td><input type="text" class="form-control variant-sku" value="${data.sku || ''}" placeholder="Optional SKU"></td>
            <td><input type="number" class="form-control variant-price" value="${data.price != null ? data.price : ''}" min="0" step="0.01"></td>
            <td><button type="button" class="btn-row-remove remove-variant-row">X</button></td>
        </tr>`;
    }

    function addonRow(data) {
        data = data || {};
        return `<tr>
            <td><input type="hidden" class="addon-id" value="${data.id || ''}">
                <input type="text" class="form-control addon-name" value="${data.name || ''}" placeholder="Extra Cheese"></td>
            <td><input type="number" class="form-control addon-price" value="${data.price != null ? data.price : ''}" min="0" step="0.01"></td>
            <td><button type="button" class="btn-row-remove remove-addon-row">X</button></td>
        </tr>`;
    }

    function recipeRow(data) {
        data = data || {};
        return `<tr>
            <td><input type="hidden" class="recipe-item-id" value="${data.id || ''}">
                <input type="number" class="form-control recipe-ingredient-id" value="${data.ingredient_product_id || ''}" min="1" placeholder="Product ID"></td>
            <td><input type="number" class="form-control recipe-qty" value="${data.quantity != null ? data.quantity : ''}" min="0" step="0.0001"></td>
            <td><input type="number" class="form-control recipe-qty-type" value="${data.quantity_type_id || ''}" min="1" placeholder="Optional"></td>
            <td><button type="button" class="btn-row-remove remove-recipe-row">X</button></td>
        </tr>`;
    }

    function toggleRestaurantSections() {
        var kind = $('#product_kind').val() || 'general';
        var recipeRequired = String($('#recipe_required').val()) === '1';
        var showFood = (kind === 'prepared_food' || kind === 'packaged' || kind === 'raw_material');
        var showVariants = (kind === 'prepared_food' || kind === 'packaged');
        var showRecipe = recipeRequired || kind === 'prepared_food';

        $('.restaurant-food-fields').toggle(showFood);
        $('.restaurant-variant-tab').toggle(showVariants);
        $('.restaurant-recipe-tab').toggle(showRecipe);

        // If active tab was hidden, jump back to Basic
        if ((!showVariants && $('#tab-variants-btn').hasClass('active')) ||
            (!showRecipe && $('#tab-recipe-btn').hasClass('active'))) {
            var basicBtn = document.getElementById('tab-basic-btn');
            if (basicBtn && typeof bootstrap !== 'undefined') {
                bootstrap.Tab.getOrCreateInstance(basicBtn).show();
            } else {
                $('#tab-basic-btn').tab('show');
            }
        }

        if (kind === 'raw_material') {
            $('#food_type').val('na');
            $('#is_pos_available').val('0');
            $('#recipe_required').val('0');
        }
    }

    window.appendRestaurantProductPayload = function (formData) {
        var variants = [];
        $('#variantsTable tbody tr').each(function () {
            var name = $(this).find('.variant-name').val();
            if (!name) return;
            variants.push({
                id: $(this).find('.variant-id').val() || null,
                name: name,
                sku: $(this).find('.variant-sku').val() || null,
                price: parseFloat($(this).find('.variant-price').val()) || 0
            });
        });

        var addons = [];
        $('#addonsTable tbody tr').each(function () {
            var name = $(this).find('.addon-name').val();
            if (!name) return;
            addons.push({
                id: $(this).find('.addon-id').val() || null,
                name: name,
                price: parseFloat($(this).find('.addon-price').val()) || 0
            });
        });

        var recipeItems = [];
        $('#recipeItemsTable tbody tr').each(function () {
            var ingredientId = parseInt($(this).find('.recipe-ingredient-id').val(), 10);
            var qty = parseFloat($(this).find('.recipe-qty').val());
            if (!ingredientId || !qty) return;
            recipeItems.push({
                id: $(this).find('.recipe-item-id').val() || null,
                ingredient_product_id: ingredientId,
                quantity: qty,
                quantity_type_id: $(this).find('.recipe-qty-type').val() || null
            });
        });

        formData.set('variants_json', JSON.stringify(variants));
        formData.set('variants', JSON.stringify(variants));
        formData.set('addons_json', JSON.stringify(addons));
        formData.set('addons', JSON.stringify(addons));
        formData.set('recipe_items_json', JSON.stringify(recipeItems));
        formData.set('recipe_items', JSON.stringify(recipeItems));
    };

    window.populateRestaurantProductFields = function (product) {
        if (!product) return;
        $('#product_kind').val(product.product_kind || 'general').trigger('change');
        $('#food_type').val(product.food_type || 'na').trigger('change');
        $('#kitchen_station').val(product.kitchen_station || '');
        $('#prep_time_minutes').val(product.prep_time_minutes || '');
        $('#display_order').val(product.display_order != null ? product.display_order : 0);
        $('#is_pos_available').val(product.is_pos_available == 0 || product.is_pos_available === false ? '0' : '1').trigger('change');
        $('#cost_price').val(product.cost_price != null ? product.cost_price : '');
        $('#tax_id').val(product.tax_id || '').trigger('change');
        $('#tax_inclusive').val(product.tax_inclusive == 1 || product.tax_inclusive === true ? '1' : '0').trigger('change');
        $('#track_inventory').val(product.track_inventory == 0 || product.track_inventory === false ? '0' : '1').trigger('change');
        $('#min_stock').val(product.min_stock != null ? product.min_stock : '');
        $('#recipe_required').val(product.recipe_required == 1 || product.recipe_required === true ? '1' : '0').trigger('change');

        var variants = product.variants || [];
        var $vt = $('#variantsTable tbody').empty();
        if (variants.length) {
            variants.forEach(function (v) { $vt.append(variantRow(v)); });
        }

        var addons = product.addons || [];
        var $at = $('#addonsTable tbody').empty();
        if (addons.length) {
            addons.forEach(function (a) { $at.append(addonRow(a)); });
        }

        var recipe = product.recipe || null;
        if (recipe) {
            $('#recipe_yield_qty').val(recipe.yield_qty || 1);
            $('#recipe_notes').val(recipe.notes || '');
            var $rt = $('#recipeItemsTable tbody').empty();
            (recipe.items || []).forEach(function (ri) { $rt.append(recipeRow(ri)); });
        }

        toggleRestaurantSections();
    };

    function loadTaxes() {
        var authToken = localStorage.getItem('authToken');
        var selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
        $.ajax({
            url: '/api/tax-rates',
            type: 'GET',
            data: { selectedSubAdminId: selectedSubAdminId },
            headers: { Authorization: 'Bearer ' + authToken },
            success: function (res) {
                var list = res.taxRates || res.data || res.taxes || [];
                if (!Array.isArray(list)) return;
                var $tax = $('#tax_id');
                var current = $tax.val();
                $tax.find('option:not(:first)').remove();
                list.forEach(function (t) {
                    if (t.isDeleted) return;
                    $tax.append('<option value="' + t.id + '">' + (t.tax_name || t.name) + ' (' + t.tax_rate + '%)</option>');
                });
                if (current) $tax.val(current).trigger('change');
            }
        });
    }

    $(document).ready(function () {
        $('#addVariantRow').on('click', function () {
            $('#variantsTable tbody').append(variantRow());
        });
        $('#addAddonRow').on('click', function () {
            $('#addonsTable tbody').append(addonRow());
        });
        $('#addRecipeRow').on('click', function () {
            $('#recipeItemsTable tbody').append(recipeRow());
        });
        $(document).on('click', '.remove-variant-row', function () { $(this).closest('tr').remove(); });
        $(document).on('click', '.remove-addon-row', function () { $(this).closest('tr').remove(); });
        $(document).on('click', '.remove-recipe-row', function () { $(this).closest('tr').remove(); });
        $(document).on('change', '#product_kind, #recipe_required', toggleRestaurantSections);
        toggleRestaurantSections();
        loadTaxes();
    });
})(jQuery);
</script>
