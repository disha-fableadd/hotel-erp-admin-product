<?php

$index_path = 'e:\\xampp8.1\\htdocs\\hotel-erp\\resources\\views\\index.blade.php';
$staff_path = 'e:\\xampp8.1\\htdocs\\hotel-erp\\resources\\views\\staff_dashboard.blade.php';

$lines = file($index_path);

// Find the script part from index.blade.php
// Let's just extract it exactly by lines or pattern matching
// Lines 1508 to 1604 has the options and renderChart function
// Lines 1719 to 1746 has the document.ready
$extracted_script = array_slice($lines, 1507, 98); // lines 1508-1605
$script1 = implode("", $extracted_script);

$extracted_ready = array_slice($lines, 1718, 29); // lines 1719-1747
$script2 = implode("", $extracted_ready);

// Replace in_array($role, ...) with app('hasPermission')(...)
$script2 = str_replace("in_array(\$role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin'])", "app('hasPermission')(2, 'view')", $script2);
$script2 = str_replace("in_array(\$role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin'])", "app('hasPermission')(3, 'view')", $script2);

$full_script = $script1 . "\n" . $script2 . "\n</script>\n";

$staff_content = file_get_contents($staff_path);

if (strpos($staff_content, "@endsection") !== false) {
    $new_staff = str_replace("@endsection", $full_script . "@endsection", $staff_content);
    file_put_contents($staff_path, $new_staff);
    echo "Successfully appended scripts to staff_dashboard.blade.php\n";
} else {
    echo "Target not found in staff_dashboard.blade.php\n";
}
