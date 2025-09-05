<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "My Leaves";

if (!isLoggedIn()) { redirect("/hrms/auth/login.php"); }
if (!in_array($_SESSION['role_id'], [4])) { redirect("/hrms/unauthorized.php"); }

$user_id = $_SESSION['user_id'];
$employee_res = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
if (!$employee_res['success'] || empty($employee_res['data'])) { redirect('/hrms/unauthorized.php'); }
$employee_id = $employee_res['data'][0]['id'];

require_once '../components/layout/header.php';
?>
<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="h3 mb-0"><i class="fas fa-plane-departure me-2"></i>My Leaves</h2>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header"><h6 class="m-0">Apply for Leave</h6></div>
                    <div class="card-body">
                        <form id="applyLeaveForm" class="row g-3">
                            <input type="hidden" name="action" value="apply_leave">
                            <div class="col-12">
                                <label class="form-label">Leave Type</label>
                                <select class="form-select" name="leave_type" required>
                                    <option value="">-- Select --</option>
                                    <option value="Casual">Casual</option>
                                    <option value="Sick">Sick</option>
                                    <option value="Paid">Paid</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Reason</label>
                                <textarea class="form-control" name="reason" rows="2" placeholder="Optional"></textarea>
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0">My Leave History</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive mobile-table-scroll">
                            <table class="table table-hover table-sm align-middle nowrap" id="leavesTable" style="width:100%">
                                <thead><tr>
                                    <th>Type</th><th>From</th><th>To</th><th>Status</th><th>Applied</th>
                                </tr></thead>
                                <tbody></tbody>
                            </table>
                            <div id="noLeavesMessage" class="text-center py-4" style="display:none;">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No leaves found</h5>
                                <p class="text-muted">You haven't applied for any leaves yet.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../components/layout/footer.php'; ?>
<script>
    const EMPLOYEE_ID = <?= json_encode($employee_id) ?>;
    
    // Debug mode - add ?debug=1 to URL to see API response
    const urlParams = new URLSearchParams(window.location.search);
    const debugMode = urlParams.get('debug') === '1';
    
    $(function(){
        const table = $('#leavesTable').DataTable({
                autoWidth:false,
                responsive:true,
                ajax:{ url: '/hrms/api/api_leaves.php?action=get_my_leaves&employee_id='+EMPLOYEE_ID, dataSrc:'data', error: function(xhr, status, error) {
                        if(debugMode) {
                            console.error('API Error:', error);
                            console.log('Response:', xhr.responseText);
                            alert('API Error: ' + error + '\nResponse: ' + xhr.responseText);
                        }
                    } },
                columns:[
                    { data:'leave_type' },
                    { data:'start_date', render:d=> new Date(d).toLocaleDateString() },
                    { data:'end_date', render:d=> new Date(d).toLocaleDateString() },
                    { data:'status', render:s=> `<span class="badge text-bg-${s==='approved'?'success':(s==='rejected'?'danger':'warning')}">${s.charAt(0).toUpperCase()+s.slice(1)}</span>` },
                    { data:'applied_at', render:d=> new Date(d).toLocaleString() }
                ],
                initComplete: function() {
                    if(this.api().data().length === 0) {
                        $('#noLeavesMessage').show();
                    }
                }
            });

        $('#applyLeaveForm').on('submit', function(e){
            e.preventDefault();
            const form = new FormData(this);
            form.append('employee_id', EMPLOYEE_ID);
            fetch('/hrms/api/api_leaves.php', { method:'POST', body: form })
                .then(r=>r.json())
                .then(res=>{
                    if(debugMode) {
                        console.log('API Response:', res);
                        alert('API Response: ' + JSON.stringify(res));
                    }
                    showToast(res.message, res.success?'success':'error');
                    if(res.success){ table.ajax.reload(); this.reset(); }
                })
                .catch(()=>showToast('Request failed','error'));
        });
    });
</script>


