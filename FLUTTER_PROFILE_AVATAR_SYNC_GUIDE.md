# Flutter Sync — Profile & Avatar Bi-Directional Guide

> **Project:** Hotel ERP (`hotel-erp-admin-product`)  
> **APIs:** `POST /api/v1/sync/upload-image` · `push` · `pull`  
> **Audience:** Flutter Desktop / POS  
> **Last Updated:** November 2026  

---

## 1. Goal

Keep **Admin / Staff profile** (`users` table) and **profile photo** in sync both ways:

| Direction | Flow |
|-----------|------|
| Desktop → Web | Upload image → push `users[]` with `profile_image` |
| Web → Desktop | Pull `users[]` (includes `role=admin`) with `profile_image` + `profile_image_url` |

Tenant storage example (tenant `24`):

```text
storage/tenants/24/app/public/profile_images/{file}.jpg
URL: https://hotel-erp.fableadtech.com/public/storage/tenants/24/app/public/profile_images/{file}.jpg
```

DB column stores **relative** path only: `profile_images/xyz.jpg`.

---

## 2. Upload avatar

```http
POST /api/v1/sync/upload-image
Authorization: Bearer {token}
X-License-Key: {CULZ4}
Content-Type: multipart/form-data
```

| Form field | Value |
|------------|--------|
| `image` | file (jpeg/png/webp/gif, max 5MB) |
| `folder` | `profile_images` |
| `license_key` | optional if header set |
| `tenant_id` | optional (`X-Tenant-Id` also accepted by middleware for mismatch checks) |

### Success

```json
{
  "status": true,
  "success": true,
  "message": "Image uploaded successfully.",
  "path": "profile_images/DshrlCNtlHHKtVsyVjHpCG4vagWLwyeZri0G410j.jpg",
  "url": "https://hotel-erp.fableadtech.com/public/storage/tenants/24/app/public/profile_images/....jpg",
  "data": {
    "path": "profile_images/DshrlCNtlHHKtVsyVjHpCG4vagWLwyeZri0G410j.jpg",
    "relative_path": "profile_images/DshrlCNtlHHKtVsyVjHpCG4vagWLwyeZri0G410j.jpg",
    "url": "https://...",
    "folder": "profile_images"
  }
}
```

**Desktop must:**

1. Save `data.path` (or `path`) into local `users.profile_image`  
2. Mark user dirty / `is_synced = 0`  
3. Include that path on the next push  

---

## 3. Push profile (`payload.users[]`)

```http
POST /api/v1/sync/push
Authorization: Bearer {token}
X-License-Key: {CULZ4}
```

```json
{
  "branch_id": 1,
  "payload": {
    "users": [
      {
        "local_id": 1,
        "remote_id": 1,
        "client_uuid": "0a8dccb7-9ab2-482a-8c4c-07c295718a60",
        "branch_id": 1,
        "name": "Fableadv Admin",
        "email": "fablead@gmail.com",
        "phone": "2035236320",
        "role": "admin",
        "status": 1,
        "profile_image": "profile_images/DshrlCNtlHHKtVsyVjHpCG4vagWLwyeZri0G410j.jpg"
      }
    ]
  }
}
```

### Backend match order (no duplicate admins)

1. `remote_id` (or `id` when `remote_id` set / `role=admin`)  
2. `client_uuid`  
3. `email`  
4. First `role=admin` row  

Then persists `name`, `email`, `phone`, `role`, `status`, **`profile_image`**.

### Mapping response

```json
{
  "synced_mappings": {
    "users": [
      {
        "local_id": 1,
        "remote_id": 1,
        "client_uuid": "0a8dccb7-9ab2-482a-8c4c-07c295718a60",
        "profile_image": "profile_images/....jpg",
        "profile_image_url": "https://.../profile_images/....jpg"
      }
    ]
  }
}
```

Apply `remote_id` + `client_uuid` locally; clear dirty flags.

---

## 4. Pull profile

```http
POST /api/v1/sync/pull
```

`users` **includes admin** (not filtered out). Each row includes:

| Field | Notes |
|-------|--------|
| `id` / `remote_id` | Server PK |
| `client_uuid` | Stable sync key |
| `name`, `email`, `phone`, `role`, `status` | Profile fields |
| `profile_image` | Relative path or null |
| `profile_image_url` | Full tenant URL for UI |
| `password` | **Never** returned |

Upsert local `users` by `remote_id` / `client_uuid` / `email`.  
For My Profile UI, load the logged-in admin (`role = admin` or matched `remote_id`), **not** `company_details`.

---

## 5. Recommended Desktop flow

```dart
Future<void> saveProfile({
  required UserRow user,
  File? newPhoto,
}) async {
  String? profilePath = user.profileImage;

  if (newPhoto != null) {
    final up = await api.uploadMultipart(
      '/api/v1/sync/upload-image',
      fields: {'folder': 'profile_images', 'license_key': licenseKey},
      fileField: 'image',
      file: newPhoto,
    );
    profilePath = up['data']?['path'] ?? up['path'];
    await usersDao.updateProfileImage(user.id, profilePath);
  }

  await usersDao.updateProfile(
    id: user.id,
    name: name,
    email: email,
    phone: phone,
    profileImage: profilePath,
    isSynced: false,
    isDirty: true,
  );

  await syncService.pushDirty(tables: ['users']);
}
```

After pull:

```dart
final admin = users.where((u) => u.role == 'admin').firstOrNull;
// Prefer profile_image_url for Image.network; keep profile_image for push
```

---

## 6. Verification checklist

| # | Check |
|---|--------|
| 1 | Upload with `folder=profile_images` → file under tenant `.../public/profile_images/` |
| 2 | HTTPS URL opens (no 403/404) |
| 3 | Push admin user → MySQL `users.profile_image` updated |
| 4 | Web Profile page shows new photo |
| 5 | Change photo on Web → pull → Desktop shows new `profile_image_url` |
| 6 | Admin is present in pull `users` array |

---

## 7. Related guides

| File | Topic |
|------|--------|
| `FLUTTER_LICENSE_TENANT_SYNC_GUIDE.md` | License / tenant DB |
| `FLUTTER_SYNC_PAYMENT_ORDER_ID_GUIDE.md` | Order ↔ payment FK |
| `FLUTTER_DASHBOARD_METRICS_SYNC_GUIDE.md` | KPI metrics API |
