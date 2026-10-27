<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserPermission;
use App\Services\StaffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Get the brand/admin prefix to apply to staff usernames.
     * Takes the prefix before underscore in admin username (e.g. 'apex' from 'apex_admin')
     * or the first 4 chars of company name.
     */
    public static function getAdminPrefix($admin = null): string
    {
        if (!$admin) {
            $admin = Auth::guard('api')->user() ?? Auth::user();
        }

        if ($admin && in_array($admin->role, ['staff', 'hr']) && !empty($admin->branch_id)) {
            $branchAdmin = User::find($admin->branch_id);
            if ($branchAdmin) {
                $admin = $branchAdmin;
            }
        }

        if ($admin) {
            if (!empty($admin->username)) {
                if (str_contains($admin->username, '_')) {
                    $parts = explode('_', $admin->username, 2);
                    $cand = trim($parts[0]);
                    if (!empty($cand)) {
                        return strtolower($cand);
                    }
                } else {
                    $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $admin->username);
                    if (!empty($cleaned)) {
                        return strtolower(substr($cleaned, 0, 4));
                    }
                }
            }

            if (!empty($admin->company_name)) {
                $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $admin->company_name);
                if (!empty($cleaned)) {
                    return strtolower(substr($cleaned, 0, 4));
                }
            }
        }

        try {
            $setting = Setting::first();
            if ($setting && !empty($setting->company_name)) {
                $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $setting->company_name);
                if (!empty($cleaned)) {
                    return strtolower(substr($cleaned, 0, 4));
                }
            }
        } catch (\Throwable $e) {}

        return '';
    }

    public function getAllStaff(Request $request)
    {
        $user = Auth::guard('api')->user();
        $selectedSubAdminId = $request->input('selectedSubAdminId');
        if ($selectedSubAdminId === 'null') {
            $selectedSubAdminId = null;
        }

        if (!empty($selectedSubAdminId)) {
            $userBranchId = $selectedSubAdminId;
        } elseif (in_array(strtolower($user->role ?? ''), ['staff', 'hr']) && $user->branch_id) {
            $userBranchId = $user->branch_id;
        } elseif (($user->role ?? '') === 'sub-admin') {
            $userBranchId = $user->id;
        } else {
            $userBranchId = null; // Admin with no specific branch
        }

        // Pagination & search parameters
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search');

        $query = User::select(
            'id',
            'name',
            'username',
            'email',
            'phone',
            'profile_image',
            'role',
            'gst_number',
            'pan_number'
        )
            ->whereIn('role', ['staff', 'hr'])
            ->when($userBranchId, function($q) use ($userBranchId) {
                $q->where('branch_id', $userBranchId);
            })
            ->where('isDeleted', 0)
            ->with('details:user_id,country,city,department_id,designation_id,joining_date,shift_time,working_location,salary,face_photo');

        // Apply search filter on name, username, email, phone
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('gst_number', 'LIKE', "%{$search}%")
                    ->orWhere('pan_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('details', function ($d) use ($search) {
                        $d->where('country', 'LIKE', "%{$search}%")
                            ->orWhere('city', 'LIKE', "%{$search}%");
                    });
            });
        }

        $staff = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        // Map to include details (country, city)
        $data = $staff->map(function ($customer) {
            $details = $customer->details;
            return [
                'id'                => $customer->id,
                'name'              => $customer->name,
                'username'          => $customer->username ?? '',
                'role'              => $customer->role,
                'email'             => $customer->email,
                'phone'             => $customer->phone,
                'gst_number'        => $customer->gst_number,
                'pan_number'        => $customer->pan_number,
                'profile_image'     => $customer->profile_image,
                'profile_image_url' => $customer->profile_image_url,
                'country'           => $details->country ?? '',
                'city'              => $details->city ?? '',
                'department_id'     => $details->department_id ?? null,
                'designation_id'    => $details->designation_id ?? null,
                'joining_date'      => $details->joining_date ?? null,
                'shift_time'        => $details->shift_time ?? '',
                'working_location'  => $details->working_location ?? '',
                'salary'            => $details->salary ?? null,
                'face_photo'        => $details->face_photo ?? null,
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $data,
            'pagination' => [
                'current_page' => $staff->currentPage(),
                'last_page'    => $staff->lastPage(),
                'per_page'     => $staff->perPage(),
                'total'        => $staff->total(),
                'next_page_url' => $staff->nextPageUrl(),
                'prev_page_url' => $staff->previousPageUrl(),
            ]
        ], 200);
    }

    public function getCustomerById($id)
    {
        $customer = User::find($id);

        if (! $customer) {
            return response()->json([
                'status' => false,
                'error'  => 'Customer not found',
            ], 404);
        }

        return response()->json([
            'status'   => true,
            'customer' => $customer,
        ], 200);
    }

    public function createStaff(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!empty($request->sub_admin_id)) {
            $userBranchId = $request->sub_admin_id;
        } elseif (in_array(strtolower($user->role ?? ''), ['staff', 'hr']) && $user->branch_id) {
            $userBranchId = $user->branch_id;
        } elseif (($user->role ?? '') === 'sub-admin') {
            $userBranchId = $user->id;
        } else {
            $userBranchId = $user->branch_id ?? ($user->id ?? null);
        }

        if (!$this->branchExists($userBranchId)) {
            return response()->json([
                'status' => false,
                'errors' => [
                    'sub_admin_id' => ['Selected branch is invalid. Please choose a valid hotel branch before creating staff.'],
                ],
            ], 422);
        }

        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function ($table) {
                $table->string('username', 100)->nullable()->unique()->after('name');
            });
        }

        $rules =  [
            'customer_name' => 'required|string|max:80',
            'username'      => 'required|string|max:80',
            'email'         => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->where(function ($query) use ($userBranchId) {
                    return $query->where('isDeleted', 0)->where('branch_id', $userBranchId);
                }),
            ],
            'role'          => 'required|in:staff,hr',
            'phone'         => [
                'required',
                'numeric',
                'digits:10',
                Rule::unique('users', 'phone')->where(function ($query) use ($userBranchId) {
                    return $query->where('isDeleted', 0)->where('branch_id', $userBranchId);
                }),
            ],
            'gst_number'    => 'nullable|string|max:15',
            'pan_number'    => 'nullable|string|max:15',
            'country'       => 'nullable|string|max:100',
            'department_id' => 'nullable|integer|exists:department,id',
            'designation_id' => 'nullable|integer|exists:designation,id',
            'joining_date'  => 'required|date',
            'shift_time'    => 'nullable|string|max:50',
            'working_location' => 'nullable|string|max:100',
            'salary'        => 'required|numeric|min:0',
            'permission_type' => 'required|in:0,1',
            'modules' => 'nullable|array',
            'modules.*.module_id' => 'required_with:modules|integer|exists:modules,id',
            'modules.*.view' => 'nullable|boolean',
            'modules.*.add' => 'nullable|boolean',
            'modules.*.edit' => 'nullable|boolean',
            'modules.*.delete' => 'nullable|boolean',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
            'city'          => 'nullable|string|max:100',
            'address'       => 'nullable|string|max:500',
            'avatar'        => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp,gif',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if ($value && !in_array($value->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'webp', 'gif'])) {
                        $fail('The ' . $attribute . ' must be an image file (jpeg, png, jpg, webp, gif).');
                    }
                },
            ],
            'face_photo'    => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp,gif',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if ($value && !in_array($value->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'webp', 'gif'])) {
                        $fail('The ' . $attribute . ' must be an image file (jpeg, png, jpg, webp, gif).');
                    }
                },
            ],
        ];

        $messages = [
            'password.regex' =>
            'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character (@$!%*?&).',
            'password.min' => 'Password must be at least 8 characters.',
            'email.unique' => 'Already exist with this mail',
            'phone.unique' => 'Already exist with this phone',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Generate staff username with admin prefix
        $branchAdmin = User::find($userBranchId);
        $adminPrefix = self::getAdminPrefix($branchAdmin ?: $user);
        $rawUsername = trim($request->input('username', ''));
        $lowerUsername = strtolower($rawUsername);

        if (!empty($adminPrefix)) {
            $prefixWithUnder = $adminPrefix . '_';
            if (str_starts_with($lowerUsername, $prefixWithUnder)) {
                $finalUsername = $lowerUsername;
            } elseif (str_starts_with($lowerUsername, $adminPrefix)) {
                $remainder = substr($lowerUsername, strlen($adminPrefix));
                $finalUsername = $adminPrefix . '_' . ltrim($remainder, '_');
            } else {
                $finalUsername = $prefixWithUnder . ltrim($lowerUsername, '_');
            }
        } else {
            $finalUsername = $lowerUsername;
        }

        // Validate uniqueness of username across active users
        $duplicateUsername = User::where('isDeleted', 0)
            ->whereRaw('LOWER(TRIM(username)) = ?', [$finalUsername])
            ->exists();

        if ($duplicateUsername) {
            return response()->json([
                'status' => false,
                'message' => 'The username has already been taken.',
                'errors' => [
                    'username' => ['The username has already been taken.']
                ]
            ], 422);
        }

        $plainPassword = $request->password;
        $createdStaff = null;

        DB::transaction(function () use ($request, $userBranchId, $finalUsername, &$createdStaff) {
            $customer = new User();
            $customer->name          = $request->customer_name;
            $customer->username      = $finalUsername;
            $customer->email         = $request->email;
            $customer->phone         = $request->phone;
            $customer->gst_number    = $request->gst_number;
            $customer->pan_number    = $request->pan_number;
            $customer->haspermission = (int) $request->input('permission_type', 0);
            $customer->password      = Hash::make($request->password);
            $customer->branch_id     = $userBranchId;
            $customer->role          = $request->role;
            $customer->status        = 1;

            if ($request->hasFile('avatar')) {
                $customer->profile_image = $request->file('avatar')
                    ->store('staff', 'public');
            }

            $customer->save();
            $createdStaff = $customer;

            $facePhotoPath = null;
            if ($request->hasFile('face_photo')) {
                $facePhotoPath = $request->file('face_photo')->store('faces', 'public');
            } elseif ($request->filled('captured_photo')) {
                $imageParts = explode(";base64,", $request->input('captured_photo'));
                if (count($imageParts) == 2) {
                    $imageBase64 = base64_decode($imageParts[1]);
                    $fileName = uniqid() . '.jpg';
                    $filePath = 'faces/' . $fileName;
                    \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, $imageBase64);
                    $facePhotoPath = $filePath;
                }
            }

            if ($request->filled('face_descriptor')) {
                $descriptor = json_decode($request->input('face_descriptor'), true);
                if (is_array($descriptor) || is_object($descriptor)) {
                    $customer->face_descriptor = $descriptor;
                    $customer->save();
                }
            }

            UserDetail::create([
                'user_id' => $customer->id,
                'country' => $request->country,
                'city'    => $request->city,
                'address' => $request->address,
                'department_id' => $request->filled('department_id') ? (int) $request->department_id : null,
                'designation_id' => $request->filled('designation_id') ? (int) $request->designation_id : null,
                'joining_date' => $request->joining_date,
                'shift_time' => $request->shift_time,
                'working_location' => $request->working_location,
                'salary' => $request->salary,
                'face_photo' => $facePhotoPath,
            ]);

            // ✅ Default permissions for Attendance and Leaves
            $attendance = Module::firstOrCreate(['module' => 'Attendance']);
            $leaves = Module::firstOrCreate(['module' => 'Leaves']);
            $payroll = Module::firstOrCreate(['module' => 'Payroll']);

            $defaultModules = [$attendance->id, $leaves->id];
            if ($request->role === 'hr') {
                $defaultModules[] = $payroll->id;
            }

            foreach ($defaultModules as $moduleId) {
                UserPermission::create([
                    'user_id'   => $customer->id,
                    'module_id' => $moduleId,
                    'view'      => 1,
                    'add'       => 1,
                    'edit'      => 1,
                    'delete'    => 1,
                ]);
            }

            // ✅ Custom module permissions from request (skip defaults to avoid duplicates)
            if ((int) $request->input('permission_type', 0) === 1 && is_array($request->modules)) {
                foreach ($request->modules as $moduleData) {
                    // Skip if already added as default
                    if (in_array($moduleData['module_id'], $defaultModules)) {
                        continue;
                    }

                    UserPermission::create([
                        'user_id'   => $customer->id,
                        'module_id' => $moduleData['module_id'],
                        'view'      => isset($moduleData['view']) ? 1 : 0,
                        'add'       => isset($moduleData['add']) ? 1 : 0,
                        'edit'      => isset($moduleData['edit']) ? 1 : 0,
                        'delete'    => isset($moduleData['delete']) ? 1 : 0,
                    ]);
                }
            }

            // 🔔 CREATE NOTIFICATION FOR STAFF CREATION
            Notification::create([
                'user_id'   => $customer->id,
                'type'      => 'staff',
                'title'     => 'New Staff Member Created',
                'message'   => $customer->name . ' has been added as staff successfully.',
                'link'      => '/staff-view/' . $customer->id,
                'is_read'   => 0,
                'is_sound'  => 0,
                'branch_id' => $userBranchId,
            ]);
        });

        $mailSent = false;
        if ($createdStaff) {
            $setting = Setting::where('branch_id', $createdStaff->branch_id)->first();
            $sendMailEnabled = is_null($setting?->send_mail) ? true : (bool) $setting->send_mail;

            if ($sendMailEnabled) {
                $mailSent = StaffService::sendStaffCreatedEmail($createdStaff, $plainPassword);
            } else {
                Log::info('Staff creation email skipped: send_mail is off for branch ' . $createdStaff->branch_id);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => $mailSent
                ? 'Staff created successfully and email sent.'
                : 'Staff created successfully.',
            'mail_sent' => $mailSent,
        ], 201);
    }

    public function deleteStaff(Request $request, $id)
    {
        $customer = User::find($id);

        if (! $customer) {
            return response()->json([
                'status' => false,
                'error'  => 'Staff not found',
            ], 404);
        }

        if (Order::where('user_id', $id)->exists()) {
            return response()->json([
                'status' => false,
                'error'  => 'This Staff cannot be deleted because they have placed orders.',
            ], 400);
        }

        DB::transaction(function () use ($id, $customer) {
            UserDetail::where('user_id', $id)->update(['isDeleted' => 1]);
            $customer->update([
                'isDeleted' => 1,
                'email' => $customer->email ? $customer->email . '-deleted-' . time() : null,
                'phone' => $customer->phone ? $customer->phone . '-deleted-' . time() : null,
            ]);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Staff deleted successfully!',
        ]);
    }

    public function getStaff($id)
    {
        $customer = User::with('details')->find($id);
        if (! $customer) {
            return response()->json(['status' => false, 'error' => 'Staff not found'], 404);
        }

        // Get all modules
        $modules = Module::all();

        // Fetch permissions for this user
        $permissions = UserPermission::where('user_id', $id)->get();

        return response()->json([
            'status'      => true,
            'customer'    => $customer,
            'modules'     => $modules,
            'permissions' => $permissions,
        ]);
    }

    public function updateStaff(Request $request, $id)
    {
        $customer = User::find($id);
        if (! $customer) {
            return response()->json([
                'status' => false,
                'error'  => 'Staff not found',
            ], 404);
        }

        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function ($table) {
                $table->string('username', 100)->nullable()->unique()->after('name');
            });
        }

        // Validate input data
        $messages = [
            'email.unique' => 'Already exist with this mail',
            'phone.unique' => 'Already exist with this phone',
        ];

        $validator = Validator::make($request->all(), [
            'staff_name' => 'required|string|max:80',
            'username'   => 'nullable|string|max:80',
            'email'      => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($id)->where(function ($query) use ($customer) {
                    return $query->where('isDeleted', 0)->where('branch_id', $customer->branch_id);
                }),
            ],
            'phone'      => [
                'required',
                'numeric',
                'digits:10',
                Rule::unique('users', 'phone')->ignore($id)->where(function ($query) use ($customer) {
                    return $query->where('isDeleted', 0)->where('branch_id', $customer->branch_id);
                }),
            ],
            'country'    => 'nullable|string|max:100',
            'role'       => 'nullable|in:staff,hr',
            'city'       => 'nullable|string|max:100',
            'address'    => 'nullable|string|max:500',
            'department_id' => 'nullable|integer|exists:department,id',
            'designation_id' => 'nullable|integer|exists:designation,id',
            'joining_date' => 'required|date',
            'shift_time' => 'nullable|string|max:50',
            'working_location' => 'nullable|string|max:100',
            'salary' => 'required|numeric|min:0',
            'permission_type' => 'required|in:0,1',
            'modules' => 'nullable|array',
            'modules.*.module_id' => 'required_with:modules|integer|exists:modules,id',
            'modules.*.view' => 'nullable|boolean',
            'modules.*.add' => 'nullable|boolean',
            'modules.*.edit' => 'nullable|boolean',
            'modules.*.delete' => 'nullable|boolean',
            'password'   => 'nullable|string|min:8|max:255',
            'avatar'        => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp,gif',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if ($value && !in_array($value->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'webp', 'gif'])) {
                        $fail('The ' . $attribute . ' must be an image file (jpeg, png, jpg, webp, gif).');
                    }
                },
            ],
            'face_photo'    => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp,gif',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if ($value && !in_array($value->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'webp', 'gif'])) {
                        $fail('The ' . $attribute . ' must be an image file (jpeg, png, jpg, webp, gif).');
                    }
                },
            ],
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $finalUsername = null;
        if ($request->filled('username')) {
            $branchAdmin = User::find($customer->branch_id);
            $adminPrefix = self::getAdminPrefix($branchAdmin);
            $rawUsername = trim($request->input('username', ''));
            $lowerUsername = strtolower($rawUsername);

            if (!empty($adminPrefix)) {
                $prefixWithUnder = $adminPrefix . '_';
                if (str_starts_with($lowerUsername, $prefixWithUnder)) {
                    $finalUsername = $lowerUsername;
                } elseif (str_starts_with($lowerUsername, $adminPrefix)) {
                    $remainder = substr($lowerUsername, strlen($adminPrefix));
                    $finalUsername = $adminPrefix . '_' . ltrim($remainder, '_');
                } else {
                    $finalUsername = $prefixWithUnder . ltrim($lowerUsername, '_');
                }
            } else {
                $finalUsername = $lowerUsername;
            }

            $duplicateUsername = User::where('id', '!=', $id)
                ->where('isDeleted', 0)
                ->whereRaw('LOWER(TRIM(username)) = ?', [$finalUsername])
                ->exists();

            if ($duplicateUsername) {
                return response()->json([
                    'status' => false,
                    'message' => 'The username has already been taken.',
                    'errors' => [
                        'username' => ['The username has already been taken.']
                    ]
                ], 422);
            }
        }

        DB::transaction(function () use ($request, $customer, $id, $finalUsername) {
            // Update main user fields
            $customer->name          = $request->staff_name;
            if ($finalUsername !== null) {
                $customer->username  = $finalUsername;
            }
            $customer->email         = $request->email;
            $customer->phone         = $request->phone;
            $customer->gst_number    = $request->gst_number;
            $customer->pan_number    = $request->pan_number;
            $customer->haspermission = (int) $request->input('permission_type', 0);
            if ($request->filled('role')) {
                $customer->role = $request->role;
            }

            if ($request->filled('password')) {
                $customer->password = Hash::make($request->password);
            }

            if ($request->hasFile('avatar')) {
                $path                    = $request->file('avatar')->store('staff', 'public');
                $customer->profile_image = $path;
            }

            $customer->save();

            // Update or create user details
            $customerDetail          = UserDetail::firstOrNew(['user_id' => $id]);
            $customerDetail->country = $request->country;
            $customerDetail->city    = $request->city;
            $customerDetail->address = $request->address;
            $customerDetail->department_id = $request->filled('department_id') ? (int) $request->department_id : null;
            $customerDetail->designation_id = $request->filled('designation_id') ? (int) $request->designation_id : null;
            $customerDetail->joining_date = $request->joining_date;
            $customerDetail->shift_time = $request->shift_time;
            $customerDetail->working_location = $request->working_location;
            $customerDetail->salary = $request->salary;

            if ($request->hasFile('face_photo')) {
                $customerDetail->face_photo = $request->file('face_photo')->store('faces', 'public');
            } elseif ($request->filled('captured_photo')) {
                $imageParts = explode(";base64,", $request->input('captured_photo'));
                if (count($imageParts) == 2) {
                    $imageBase64 = base64_decode($imageParts[1]);
                    $fileName = uniqid() . '.jpg';
                    $filePath = 'faces/' . $fileName;
                    \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, $imageBase64);
                    $customerDetail->face_photo = $filePath;
                }
            }

            if ($request->filled('face_descriptor')) {
                $descriptor = json_decode($request->input('face_descriptor'), true);
                if (is_array($descriptor) || is_object($descriptor)) {
                    $customer->face_descriptor = $descriptor;
                    $customer->save();
                }
            }

            $customerDetail->save();

            // ✅ Default module IDs for Leave and Attendance
            $attendance = Module::firstOrCreate(['module' => 'Attendance']);
            $leaves = Module::firstOrCreate(['module' => 'Leaves']);
            $payroll = Module::firstOrCreate(['module' => 'Payroll']);

            $defaultModules = [$attendance->id, $leaves->id];
            if ($request->input('role', $customer->role) === 'hr') {
                $defaultModules[] = $payroll->id;
            }
            // Update permissions according to selected permission type
            if ((int) $request->input('permission_type', 0) === 0) {
                // Without permission: remove all permissions EXCEPT default modules
                UserPermission::where('user_id', $customer->id)
                    ->whereNotIn('module_id', $defaultModules)
                    ->delete();

                // ✅ Always ensure default permissions exist
                foreach ($defaultModules as $moduleId) {
                    UserPermission::updateOrCreate(
                        [
                            'user_id'   => $customer->id,
                            'module_id' => $moduleId,
                        ],
                        [
                            'view'   => 1,
                            'add'    => 1,
                            'edit'   => 1,
                            'delete' => 1,
                        ]
                    );
                }
            } else {
                $moduleInputs = $request->input('modules', []);

                // ✅ Always ensure default permissions exist
                foreach ($defaultModules as $moduleId) {
                    UserPermission::updateOrCreate(
                        [
                            'user_id'   => $customer->id,
                            'module_id' => $moduleId,
                        ],
                        [
                            'view'   => 1,
                            'add'    => 1,
                            'edit'   => 1,
                            'delete' => 1,
                        ]
                    );
                }

                // Upsert submitted permissions (skip default modules to avoid override)
                foreach ($moduleInputs as $moduleData) {
                    // Skip default modules — already handled above
                    if (in_array((int) $moduleData['module_id'], $defaultModules)) {
                        continue;
                    }

                    UserPermission::updateOrCreate(
                        [
                            'user_id'   => $customer->id,
                            'module_id' => (int) $moduleData['module_id'],
                        ],
                        [
                            'view'   => !empty($moduleData['view']) ? (int) $moduleData['view'] : 0,
                            'add'    => !empty($moduleData['add']) ? (int) $moduleData['add'] : 0,
                            'edit'   => !empty($moduleData['edit']) ? (int) $moduleData['edit'] : 0,
                            'delete' => !empty($moduleData['delete']) ? (int) $moduleData['delete'] : 0,
                        ]
                    );
                }
            }
        });

        return response()->json([
            'status'        => true,
            'message'       => 'Staff updated successfully!',
            'data'          => [
                'user_id' => $customer->id,
            ],
            'staff'         => $customer,
            'staff_details' => $customer->details ?? null,
        ]);
    }

    public function getStaffProfile($id)
    {
        $user = User::with([
            'userDetail.designation:id,designation_name',
            'userDetail.department:id,department_name',
        ])->find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $userDetail = $user->userDetail;

        $permissions = UserPermission::with('module')
            ->where('user_id', $id)
            ->get()
            ->map(function ($perm) {
                return [
                    'module' => $perm->module->module ?? 'Unknown',
                    'view'   => (bool) $perm->view,
                    'add'    => (bool) $perm->add,
                    'edit'   => (bool) $perm->edit,
                    'delete' => (bool) $perm->delete,
                ];
            });

        return response()->json([
            'name'          => $user->name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'gst_number'    => $user->gst_number,
            'role'          => $user->role,
            'profile_image' => $user->profile_image,
            'address'       => $userDetail->address ?? 'N/A',
            'city'          => $userDetail->city ?? 'N/A',
            'country'       => $userDetail->country ?? 'N/A',
            'designation_id'    => $userDetail?->designation_id,
            'department_id'     => $userDetail?->department_id,
            'joining_date' => $userDetail?->joining_date 
                ? \Carbon\Carbon::parse($userDetail->joining_date)->format('Y-m-d') 
                : null,
            'shift_time'        => $userDetail?->shift_time,
            'working_location'  => $userDetail?->working_location,
            'salary'            => $userDetail?->salary,
            'face_photo'        => $userDetail?->face_photo,
            'designation_name'  => $userDetail?->designation?->designation_name,
            'department_name'   => $userDetail?->department?->department_name,
            'permissions'   => $permissions,
        ]);
    }

    public function index()
    {
        $modules = Module::select('id', 'module as module_name', 'created_at')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Modules fetched successfully',
            'data'    => $modules,
        ], 200);
    }

    private function branchExists($branchId): bool
    {
        return !empty($branchId) && DB::table('branches')->where('id', $branchId)->exists();
    }
}
