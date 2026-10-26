@extends('layout.app')
@section('title', 'Company Rules')

@section('content')
<style>
    .company-rules-card .table thead th {
        white-space: nowrap;
        font-weight: 600;
        color: #1f2937;
    }

    .company-rules-card .table tbody td {
        vertical-align: middle;
    }

    .company-rules-card .rule-action {
        color: #ff9f43;
        font-size: 20px;
    }

    .company-rules-card .rule-action:hover {
        color: #f6871f;
    }

    .dataTables_filter,
    .dataTables_length,
    .dataTables_info {
        margin-bottom: 10px;
    }

    @media (max-width: 767px) {
        .page-header .page-btn {
            margin-top: 10px;
        }
    }
</style>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Manage Rules</h4>
            <h6>View and maintain company rule settings</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('company-rules.create-rules') }}" class="btn btn-added btn-sm">
                <i class="fa fa-edit me-1"></i> Update Rules
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card company-rules-card">
            <div class="card-body">
                <div class="table-container">
                    <table class="table datanew" id="leaveTypes-Table">
                        <thead>
                            <tr>
                                <th>Working Hours</th>
                                <th>Saturday Off Pattern</th>
                                <th>Yearly Holidays</th>
                                <th>Tax</th>
                                <th>Taxable Salary Amount</th>
                                <th>Lunch Break</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="rules-Table-Body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('js')

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var authToken = localStorage.getItem("authToken");

        fetch('/api/company-rules/rules', {
                method: 'GET',
                headers: {
                    "Authorization": "Bearer " + authToken,
                    "Content-Type": "application/json"
                }
            })
            .then(response => response.json())
            .then(responseData => {
                if (responseData.status === 'success') {
                    const rules = responseData.rules || responseData.data; // supports either key
                    let tableRows = '';

                    const ordinalMap = {
                        '1': '1st Saturday',
                        '2': '2nd Saturday',
                        '3': '3rd Saturday',
                        '4': '4th Saturday',
                        '5': '5th Saturday',
                    };

                    if (!rules || rules.length === 0) {
                        tableRows = `
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No company rules found.</td>
                            </tr>
                        `;
                    } else {
                    rules.forEach((rule, index) => {
                        // Convert "1,2" to "1st Saturday and 2nd Saturday"
                        const saturdayOffPattern = String(rule.saturday_off_pattern || '');
                        const saturdayOffList = saturdayOffPattern
                            .split(',')
                            .map(num => ordinalMap[num.trim()])
                            .filter(Boolean)
                            .join(' and ');

                        tableRows += `
                    <tr data-id="${rule.id}">
                        <td>${rule.working_hours_per_day} hours</td>
                        <td>${saturdayOffList || 'None'}</td>
                        <td>${rule.yearly_holidays ?? 0}</td>
                        <td>${rule.tax ?? 0}</td>
                        <td>${rule.salary_above_tax ?? 0}</td>
                        <td>${rule.lunch_break}</td>
                        <td><a href="/creates-rules" class="rule-action" title="Edit">
                                <i class="mdi mdi-pencil"></i>
                            </a>
                            <!-- <a href="#" class="text-danger fs-5" title="Delete" data-id="${rule.id}" onclick="deleteRule(event)">
                                <i class="mdi mdi-delete"></i>
                            </a> -->
                        </td>
                    </tr>
                `;
                    });
                    }

                    $('#rules-Table-Body').html(tableRows);

                    if ($.fn.DataTable.isDataTable('#rules-Table')) {
                        $('#rules-Table').DataTable().clear().destroy();
                    }

                    $('#rules-Table').DataTable({
                        language: {
                            search: "",
                            searchPlaceholder: "Search"
                        }
                    });

                } else {
                    console.error('Failed to fetch rules:', responseData.message);
                }
            })
            .catch(error => {
                console.error('Error fetching rules:', error);
            });
    });

    // Deletion
    function deleteRule(event) {
        event.preventDefault();
        const ruleId = event.target.closest('a').getAttribute('data-id');

        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'hr-btnbg',
                cancelButton: 'hr-btnbg',
            }
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`/api/rules/${ruleId}`, {
                        method: 'DELETE',
                        headers: {
                            "Authorization": "Bearer " + authToken,
                            "Content-Type": "application/json"
                        }
                    })

                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.querySelector(`tr[data-id="${ruleId}"]`)?.remove();
                            Swal.fire('Deleted!', 'The rule has been deleted.', 'success');
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to delete.', 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error!', err.message || 'Something went wrong.', 'error');
                    });
            }
        });
    }
</script>
@endpush

