<div class="top-header">
    <div class="left-section">
        <img src="../img/pfp.jpg" alt="Profile Picture" class="profile-pic">
        <div class="logoDental d-none d-md-block">CHARMING SMILE<br>DENTAL CLINIC</div>
    </div>

    <div class="user-info">
        <div class='current-datetime me-3 d-none d-md-block'>
            <span id="datetime"></span>
        </div>
        <button class="btn text-white d-md-none" type="button" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </div>


</div>
<?php

if (!empty($_SESSION['modal'])):
    $modal = $_SESSION['modal'];
    $modalType = $modal['type'] ?? 'info';
    $modalTitle = $modal['title'] ?? 'Notice';
    $modalMessage = $modal['message'] ?? '';
    unset($_SESSION['modal']);
    ?>
    <!-- Modal HTML -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-<?php echo $modalType; ?>">
                <div class="modal-header bg-<?php echo $modalType; ?> text-white">
                    <h5 class="modal-title" id="alertModalLabel"><?php echo $modalTitle; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo $modalMessage; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-<?php echo $modalType; ?>" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto show script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
            alertModal.show();
        });
    </script>
<?php endif; ?>