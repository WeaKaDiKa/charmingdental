$(document).ready(function () {
    InsertRecord(); // Call only once on page load
});

function InsertRecord() {
    $('#btn-add').off('click').on('click', function (event) {
        event.preventDefault(); // Prevent form default submission

        const Name = $('#name').val();
        const Date = $('#date').val();
        const Treatment = $('#treatment').val();
        const Price = $('#price').val();
        const Status = $('#status').val();

        $.ajax({
            url: '../includes/insert.php',
            method: 'POST',
            data: {
                PName: Name,
                PDate: Date,
                PTreatment: Treatment,
                PPrice: Price,
                PStatus: Status,
            },
            success: function (data) {
                console.log(data); // Debug response

                // Reset the form
                $('#paymentForm')[0].reset();

                // Close the modal
                $('#addPaymentModal').removeClass('active');
            },
            error: function () {
                console.error('Error inserting data.');
            },
        });
    });
}