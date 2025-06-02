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

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success'];
        unset($_SESSION['success']); ?></div>
    <?php endif; ?>
</div>