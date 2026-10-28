@extends('layout.app')

@section('title', 'Profile')

@section('content')
    <style>
        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 15px !important
            }

            .profile-set {
                margin-bottom: 0 !important;
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Profile</h4>
                <h6>User Profile</h6>
            </div>
        </div>
        <form id="updateProfileForm" enctype="multipart/form-data">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="profile-set">
                        <div class="profile-head"></div>
                        <div class="profile-top">
                            <div class="profile-content">
                                <div class="profile-contentimg">
                                    <img src="{{ !empty($user->profile_image) ? tenant_image_url($user->profile_image) : image_path('admin/assets/img/customer/customer5.jpg') }}" alt="img"
                                        id="blah"
                                        onerror="this.onerror=null; this.src='{{ image_path('admin/assets/img/customer/customer5.jpg') }}';">

                                    <div class="profileupload">
                                        <input type="file" name="profile_image" id="imgInp">
                                        <a href="javascript:void(0);">
                                            <img src="{{ image_path('admin/assets/img/icons/edit-set.svg') }}"
                                                alt="img">
                                        </a>
                                    </div>
                                </div>
                                <div class="profile-contentname">
                                    <h2>{{ $user->name }}</h2>
                                    <h4>Update Your Photo and Personal Details.</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" placeholder="William" value="{{ $user->name }}">
                            </div>
                        </div>

                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" name="email" placeholder="william@example.com"
                                    value="{{ $user->email }}">
                            </div>
                        </div>

                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" placeholder="+1452 876 5432"
                                    value="{{ $user->phone }}">
                            </div>
                        </div>

                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Password</label>
                                <div class="pass-group">
                                    <input type="password" name="password" class="pass-input" id="password"
                                        placeholder="********">
                                    <span class="fas toggle-password fa-eye-slash"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-submit me-2">Submit</button>
                            <!-- <button type="reset" class="btn btn-cancel">Cancel</button> -->
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            $("#updateProfileForm").submit(function(e) {
                e.preventDefault();

                var formData = new FormData(this);


                $.ajax({
                    url: "/api/updateProfile",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    // success: function (response) {
                    //     if (response.status) {
                    //         window.location.reload();
                    //     } else {
                    //         alert("Failed to update profile!");
                    //     }
                    // },
                    // error: function (xhr) {
                    //     console.log(xhr.responseJSON);
                    //     alert("Error updating profile!");
                    // }
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: "success",
                                title: "Profile Updated!",
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Update Failed",
                                text: "Failed to update profile.",
                            });
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseJSON);
                        let errorMessage = "Error updating profile!";

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join("\n");
                        }

                        Swal.fire({
                            icon: "error",
                            title: "Validation Error",
                            text: errorMessage,
                        });
                    }
                });
            });
        });
    </script>
@endpush
