<?php

$index_path = 'e:\\xampp8.1\\htdocs\\hotel-erp\\resources\\views\\index.blade.php';
$staff_path = 'e:\\xampp8.1\\htdocs\\hotel-erp\\resources\\views\\staff_dashboard.blade.php';

$lines = file($index_path);
$extracted = array_slice($lines, 916, 306);
$extracted_str = implode("", $extracted);

$extracted_str = str_replace("in_array(\$role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin'])", "app('hasPermission')(2, 'view')", $extracted_str);
$extracted_str = str_replace("in_array(\$role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin'])", "app('hasPermission')(3, 'view')", $extracted_str);

$staff_content = file_get_contents($staff_path);

if (strpos($staff_content, "</div>\n\n<script>") !== false) {
    $new_staff = str_replace("</div>\n\n<script>", $extracted_str . "\n</div>\n\n<script>", $staff_content);
    file_put_contents($staff_path, $new_staff);
    echo "Successfully updated staff_dashboard.blade.php\n";
} else {
    echo "Target not found in staff_dashboard.blade.php\n";
}
