    @extends('layout.app')

    @section('title', 'Vendor View')

    @section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Vendor View</h4>
            </div>
            <div class="page-btn d-flex gap-2">

                    <!-- Edit -->
                    <a href="{{ route('vendor.edit', $id) }}" class="btn btn-added">
                        <i class="fa fa-edit me-1"></i> Edit
                    </a>
                    <!-- Back -->
                    <a href="{{ route('vendor.list') }}" class="btn" style="background: #1b2850; color: #fff;">
                        Back
                    </a>

                </div>
        </div>
        <div class="">
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-4 col-sm-4">
                        <div class="card">
                            <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                                <img src="" alt="Profile" class="img-fluid rounded-circle border" style="width: 150px; height: 150px; object-fit: cover;">
                                <!-- profile-img.jpg -->
                                <h5 class="mt-3"><span class="profile-name"></span></h5>
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
                                            <div class="col-lg-3 col-md-4 label">PAN Number</div>
                                            <div class="col-lg-9 col-md-8"><span class="profile-pan_number"></span></div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-lg-3 col-md-4 label">GST Number</div>
                                            <div class="col-lg-9 col-md-8"><span class="profile-gst_number"></span></div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-lg-3 col-md-4 label">Address</div>
                                            <div class="col-lg-9 col-md-8"><span class="profile-address"></span></div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-lg-3 col-md-4 label">Coutry</div>
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
            let userId = '{{ $id }}';

            $.ajax({
                url: `/api/vendor/${userId}`,
                method: 'GET',
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                success: function(res) {
                    $('.profile-name').text(res.name ?? 'N/A');
                    $('.profile-email').text(res.email ?? 'N/A');
                    $('.profile-phone').text(res.phone ?? 'N/A');
                    $('.profile-role').text(res.role ?? 'N/A');
                    $('.profile-address').text(res.address && res.address.trim() !== '' ? res.address : 'N/A');
                    $('.profile-country').text(res.country && res.country.trim() !== '' ? res.country : 'N/A');
                    $('.profile-city').text(res.city && res.city.trim() !== '' ? res.city : 'N/A');
                    $('.profile-pan_number').text(res.pan_number && res.pan_number.trim() !== '' ? res.pan_number : 'N/A');
                    $('.profile-gst_number').text(res.gst_number && res.gst_number.trim() !== '' ? res.gst_number : 'N/A');

                    if (res.profile_image) {
                        $('img[alt="Profile"]').attr('src', `{{ env('ImagePath').'/storage/' }}${res.profile_image}`);
                    } else {
                        $('img[alt="Profile"]').attr('src', '{{ env('ImagePath').'/admin/assets/img/customer/customer5.jpg' }}');
                    }
                },
                error: function(err) {
                    Swal.fire("Error!", "Vendor not found!", "error");
                    window.location.href = "{{ route('vendor.list') }}";
                }
            });
        });
    </script>
    @endpush