$(document).ready(function () {
    $('#registerForm').on('submit', function (e) {
        e.preventDefault();

        const btn = $('#submitBtn');
        const alert = $('#alertMessage');
        const originalText = btn.text(); // Capture strictly inner text to restore later

        // Store original content if it was an icon+text, or just text. 
        // For now, restoring text is sufficient as per original code.
        // Validate Terms and Conditions
        if (!$('#termsAgree').is(':checked')) {
            alert.addClass('alert-danger').removeClass('d-none').text('You must agree to the Terms and Conditions to proceed.');
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Setting up...');
        alert.addClass('d-none').removeClass('alert-danger alert-success');

        $.ajax({
            url: 'api/api_register.php',
            method: 'POST',
            data: $(this).serialize() + '&action=register_demo',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert.addClass('alert-success').removeClass('d-none').html('<strong>Success!</strong> Redirecting to dashboard...');
                    setTimeout(() => {
                        window.location.href = 'index.php'; 
                    }, 1500);
                } else {
                    alert.addClass('alert-danger').removeClass('d-none').text(response.error || response.message);
                    btn.prop('disabled', false).text("Start My Demo");
                }
            },
            error: function () {
                alert.addClass('alert-danger').removeClass('d-none').text('An error occurred. Please try again.');
                btn.prop('disabled', false).text("Start My Demo");
            }
        });
    });
});
