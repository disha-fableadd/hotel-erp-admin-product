@extends('layout.app')

@section('title', 'Branch View')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Branch View</h4>
        </div>
        <div class="page-btn">
            @if (app('hasPermission')(8, 'view'))
            <a href="{{ route('subbranch.list') }}" class="btn btn-added">
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
                            <img src="" alt="Profile" class="img-fluid rounded-circle border" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.onerror=null;this.src='{{ image_path('admin/assets/img/customer/customer5.jpg') }}';">
                            <!-- profile-img.jpg -->
                            <h5 class="mt-3"><span class="profile-name"></span></h5>
                            <p class="text-muted mb-0"><span class="profile-username"></span></p>
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

                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Name</div>
                                        <div class="col-lg-9 col-md-8"><span class="profile-name"></span></div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Username</div>
                                        <div class="col-lg-9 col-md-8"><span class="profile-username"></span></div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Role</div>
                                        <div class="col-lg-9 col-md-8"><span class="profile-role"></span></div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Email</div>
                                        <div class="col-lg-9 col-md-8"><span class="profile-email"></span></div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Contact Number</div>
                                        <div class="col-lg-9 col-md-8"><span class="profile-phone"></span></div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Address</div>
                                        <div class="col-lg-9 col-md-8"><span class="profile-address"></span></div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Country</div>
                                        <div class="col-lg-9 col-md-8"><span class="profile-country"></span></div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">City</div>
                                        <div class="col-lg-9 col-md-8"><span class="profile-city"></span></div>
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


@endsection
@push('js')
<script>
    $(document).ready(function() {
        var authToken = localStorage.getItem("authToken");
        let urlParts = window.location.pathname.split('/');
        let userId = urlParts[urlParts.length - 1];

        $.ajax({
            url: `/api/getSubbranch/${userId}`,
            method: 'GET',
            headers: {
                "Authorization": "Bearer " + authToken,
            },
            success: function(res) {
                const user = res.data;
                const userDetail = user.user_detail ?? {};

                $('.profile-name').text(user.name ?? 'N/A');
                $('.profile-username').text(user.username ?? 'N/A');
                $('.profile-role').text(user.role ?? 'N/A');
                $('.profile-email').text(user.email ?? 'N/A');
                $('.profile-phone').text(user.phone ?? 'N/A');
                $('.profile-address').text(userDetail.address?.trim() || 'N/A');
                $('.profile-country').text(userDetail.country?.trim() || 'N/A');
                $('.profile-city').text(userDetail.city?.trim() || 'N/A');

                let storageBase = "{{ image_path('storage') }}/";
                let defaultImage = "{{ image_path('admin/assets/img/customer/customer5.jpg') }}";
                let cleanUserImg = (user.profile_image || '').replace(/^\/+/, '').replace(/^storage\//, '');
                let profileImage = cleanUserImg ? `${storageBase}${cleanUserImg}` : defaultImage;

                $('img[alt="Profile"]').attr('src', profileImage);
            },
            error: function(err) {
                Swal.fire("Error!", "Customer not found!", "error");
                window.location.href = "{{ route('subbranch.list') }}";
            }
        });
    });
</script>
@endpush