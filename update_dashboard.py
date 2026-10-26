import re

index_path = r'e:\xampp8.1\htdocs\hotel-erp\resources\views\index.blade.php'
staff_path = r'e:\xampp8.1\htdocs\hotel-erp\resources\views\staff_dashboard.blade.php'

with open(index_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

extracted = lines[916:1222]
extracted_str = ''.join(extracted)

extracted_str = extracted_str.replace("in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin'])", "app('hasPermission')(2, 'view')")
extracted_str = extracted_str.replace("in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin'])", "app('hasPermission')(3, 'view')")

with open(staff_path, 'r', encoding='utf-8') as f:
    staff_content = f.read()

if '</div>\n\n<script>' in staff_content:
    new_staff = staff_content.replace('</div>\n\n<script>', extracted_str + '\n</div>\n\n<script>')
    with open(staff_path, 'w', encoding='utf-8') as f:
        f.write(new_staff)
    print('Successfully updated staff_dashboard.blade.php')
else:
    print('Target not found in staff_dashboard.blade.php')
