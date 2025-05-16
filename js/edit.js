$(document).ready(function () {
    get_record();   // Initialize the edit record functionality
});

function get_record() {
    $(document).on('click', '#btn-edit', function () {
        const ID = $(this).attr('data-id'); // Get the ID from the clicked button
        $.ajax({
            url: '../includes/get_data.php',
            method: 'POST',
            data: { UserData: ID },
            dataType: 'JSON',
            success: function (data) {
                // Log the retrieved data to the console
                console.log('Retrieved Data:', data);
            },
            error: function () {
                console.error('Error fetching data.');
            },
        });
    });
}
