  @extends('layout.app')

  @section('title', 'Staff View')

  @section('content')
  <style>
    .profile-card .profile-name {
    text-align: center;
    font-weight: 600;
    word-break: break-word;
}

.staff-face-photo {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.profile-detail-grid .profile-detail-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #e9ecef;
    min-height: 54px;
}

.profile-detail-grid .profile-detail-label {
    width: 42%;
    font-weight: 600;
    color: #1b2850;
}

.profile-detail-grid .profile-detail-value {
    width: 58%;
    word-break: break-word;
}

@media (max-width: 767px) {
    .profile-detail-grid .profile-detail-label,
    .profile-detail-grid .profile-detail-value {
        width: 50%;
    }
}
  </style>
      <div class="content">
          <div class="page-header">
              <div class="page-title">
                  <h4>Staff View</h4>
              </div>

              <div class="page-btn d-flex gap-2">

                  <!-- Edit -->
                  @if (app('hasPermission')(8, 'edit'))
                  <a href="{{ route('staff.edit', $id) }}" class="btn btn-added">
                      <i class="fa fa-edit me-1"></i> Edit
                  </a>
                  @endif

                  <!-- Back -->
                  @if (app('hasPermission')(8, 'view'))
                  <a href="{{ route('staff.list') }}" class="btn" style="background: #1b2850; color: #fff;">
                      Back
                  </a>
                  @endif

              </div>
          </div>

          <div class="">
              <div class="card-body">
                  <div class="row">
                      <div class="col-xl-4 col-sm-4">
                          <div class="card">
                              <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                                  <img src="/admin/assets/img/customer/customer5.jpg" alt="Profile" class="img-fluid rounded-circle border"
                                      style="width: 150px; height: 150px; object-fit: cover;"
                                      onerror="if(this.src && !this.src.includes('customer5.jpg')){this.src='/admin/assets/img/customer/customer5.jpg';}else{this.onerror=null;this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'150\' height=\'150\' viewBox=\'0 0 150 150\'><rect fill=\'%23f3f4f6\' width=\'150\' height=\'150\'/><circle cx=\'75\' cy=\'55\' r=\'28\' fill=\'%23d1d5db\'/><path d=\'M35 125 C35 95, 55 90, 75 90 C95 90, 115 95, 115 125 Z\' fill=\'%23d1d5db\'/></svg>';}">
                                  <!-- profile-img.jpg -->
                                  <h5 class="mt-3"><span class="profile-name"></span></h5>
                                  <p class="text-muted mb-0 mt-1" style="font-size: 14px;"><i class="fas fa-user-tag me-1 text-warning"></i><span class="profile-username font-weight-bold"></span></p>
                                  <!-- <h4><span class="profile-role"></span></h4> -->
                              </div>
                          </div>
                      </div>
                      <div class="col-xl-8 col-sm-8">
                          <div class="card">
                              <div class="card-body pt-3">

                                  <div class="tab-content pt-2">

                                      <div class="tab-pane fade show active profile-overview" id="profile-overview">

                                          <h5 class="card-title">Profile Details</h5>

                                          <div class="row profile-detail-grid">
                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">Name</div>
                                                      <div class="profile-detail-value"><span class="profile-name"></span></div>
                                                  </div>
                                              </div>
                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">Username</div>
                                                      <div class="profile-detail-value"><span class="badge bg-light text-dark border px-2 py-1 profile-username" style="font-size: 13px;"></span></div>
                                                  </div>
                                              </div>
                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">Role</div>
                                                      <div class="profile-detail-value"><span class="profile-role"></span></div>
                                                  </div>
                                              </div>
                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">Email</div>
                                                      <div class="profile-detail-value"><span class="profile-email"></span></div>
                                                  </div>
                                              </div>
                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">Contact Number</div>
                                                      <div class="profile-detail-value"><span class="profile-phone"></span></div>
                                                  </div>
                                              </div>
                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">GST Number</div>
                                                      <div class="profile-detail-value"><span class="profile-gst_number"></span></div>
                                                  </div>
                                              </div>
                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">Address</div>
                                                      <div class="profile-detail-value"><span class="profile-address"></span></div>
                                                  </div>
                                              </div>
                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">Country</div>
                                                      <div class="profile-detail-value"><span class="profile-country"></span></div>
                                                  </div>
                                              </div>
                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">City</div>
                                                      <div class="profile-detail-value"><span class="profile-city"></span></div>
                                                  </div>
                                              </div>
                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">Department</div>
                                                      <div class="profile-detail-value"><span class="profile-department"></span></div>
                                                  </div>
                                              </div>
                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">Designation</div>
                                                      <div class="profile-detail-value"><span class="profile-designation"></span></div>
                                                  </div>
                                              </div>
                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">Joining Date</div>
                                                      <div class="profile-detail-value"><span class="profile-joining_date"></span></div>
                                                  </div>
                                              </div>

                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">Salary</div>
                                                      <div class="profile-detail-value"><span class="profile-salary"></span></div>
                                                  </div>
                                              </div>
                                              <div class="col-lg-6 col-md-6 col-12">
                                                  <div class="profile-detail-item">
                                                      <div class="profile-detail-label">Face Photo</div>
                                                      <div class="profile-detail-value">
                                                          <img src="{{ env('ImagePath') . '/admin/assets/img/customer/customer5.jpg' }}" alt="Face Photo"
                                                              class="staff-face-photo profile-face-photo" style="display:none;">
                                                          <span class="profile-face-photo-text">N/A</span>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>

                  <hr>


                  <h5 class="mt-4 fw-bold">PERMISSION :</h5>



                  <div class="table-responsive">
                      <table class="table table-bordered table-hover align-middle" id="module-permission-table">
                          <thead class="table-light">
                              <tr class="text-center">
                                  <th class="text-start">Module Name</th>
                                  <th>View</th>
                                  <th>Insert</th>
                                  <th>Edit</th>
                                  <th>Delete</th>

                              </tr>
                          </thead>
                          <tbody>
                              <!-- Filled by JavaScript -->
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
      </div>


  @endsection
  @push('js')
      <script>
          $(document).ready(function() {
              var authToken = localStorage.getItem("authToken");
              let userId = '{{ $id }}';

              $.ajax({
                  url: `/api/staff/${userId}`,
                  method: 'GET',
                  headers: {
                      "Authorization": "Bearer " + authToken,
                  },
                  success: function(res) {
                      const getValue = (value) => {
                          if (value === null || value === undefined || value === '') {
                              return 'N/A';
                          }
                          return value;
                      };

                      const formatDate = (dateValue) => {
                          if (!dateValue) {
                              return 'N/A';
                          }
                          const dateObj = new Date(dateValue);
                          if (Number.isNaN(dateObj.getTime())) {
                              return dateValue;
                          }
                          return dateObj.toLocaleDateString('en-IN', {
                              day: '2-digit',
                              month: 'short',
                              year: 'numeric'
                          });
                      };

                      $('.profile-name').text(getValue(res.name));
                      let staffUsername = res.username ? (res.username.startsWith('@') ? res.username : '@' + res.username) : 'N/A';
                      $('.profile-username').text(staffUsername);
                      $('.profile-role').text(getValue(res.role));
                      $('.profile-email').text(getValue(res.email));
                      $('.profile-phone').text(getValue(res.phone));
                      $('.profile-gst_number').text(getValue(res.gst_number));
                      $('.profile-address').text(getValue(res.address));
                      $('.profile-country').text(getValue(res.country));
                      $('.profile-city').text(getValue(res.city));
                      $('.profile-department').text(getValue(res.department_name ?? res.department_id));
                      $('.profile-designation').text(getValue(res.designation_name ?? res.designation_id));
                      $('.profile-joining_date').text(formatDate(res.joining_date));
                      $('.profile-salary').text(getValue(res.salary));

                      let rawImg = res.profile_image_url || res.profile_image || '';
                      let profileImage = window.resolveImageUrl ? window.resolveImageUrl(rawImg, '/admin/assets/img/customer/customer5.jpg') : null;
                      if (!profileImage) {
                          if (rawImg) {
                              let clean = String(rawImg).replace(/^https?:\/\/[^\/]+/, '').replace(/^\/+/, '').replace(/^storage\//, '');
                              profileImage = '/storage/' + clean;
                          } else {
                              profileImage = '/admin/assets/img/customer/customer5.jpg';
                          }
                      }
                      $('img[alt="Profile"]').attr('src', profileImage);

                      if (res.face_photo) {
                          let cleanFace = String(res.face_photo).replace(/^\/+/, '').replace(/^storage\//, '');
                          $('.profile-face-photo').attr(
                              'src',
                              `/storage/${cleanFace}`
                          ).show();
                          $('.profile-face-photo-text').hide();
                      } else {
                          $('.profile-face-photo').hide();
                          $('.profile-face-photo-text').text('N/A').show();
                      }

                      let permissionRows = '';
                      if (res.permissions && res.permissions.length > 0) {
                          res.permissions.forEach(function(perm) {
                              permissionRows += `
            <tr class="text-center">
                <td class="text-start">${perm.module}</td>
                <td>${perm.view ? '✔️' : '❌'}</td>
                <td>${perm.add ? '✔️' : '❌'}</td>
                <td>${perm.edit ? '✔️' : '❌'}</td>
                <td>${perm.delete ? '✔️' : '❌'}</td>
            </tr>
        `;
                          });
                      } else {
                          permissionRows =
                              `<tr><td colspan="5" class="text-center">No permissions assigned.</td></tr>`;
                      }

                      $('#module-permission-table tbody').html(permissionRows);

                  },
                  error: function(err) {
                      Swal.fire("Error!", "Staff not found!", "error");

                  }
              });
          });
      </script>
  @endpush
