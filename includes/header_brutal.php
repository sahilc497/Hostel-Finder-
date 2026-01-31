<?php
// Determine root path for assets
$path_prefix = (basename(dirname($_SERVER['PHP_SELF'])) == 'hf') ? '' : '../';
?>
<nav class="navbar navbar-expand-lg brutal-nav sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $path_prefix; ?>index.php">STUDENTNEST</a>
        
        <button class="navbar-toggler border-white" type="button" data-bs-toggle="collapse" data-bs-target="#brutalNav">
            <span class="fa fa-bars text-white"></span>
        </button>

        <div class="collapse navbar-collapse" id="brutalNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="<?php echo $path_prefix; ?>index.php">HOME</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    $dash = $path_prefix . 'user/dashboard.php';
                    if ($_SESSION['role'] == 'owner') $dash = $path_prefix . 'owner/dashboard.php';
                    if ($_SESSION['role'] == 'admin') $dash = $path_prefix . 'admin/dashboard.php';
                    ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $dash; ?>">DASHBOARD</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn btn-brutal btn-sm" href="<?php echo $path_prefix; ?>auth/logout.php">LOGOUT</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $path_prefix; ?>auth/admin_login.php"><i class="fa fa-lock-open me-1"></i>ADMIN</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $path_prefix; ?>auth/login.php">LOGIN</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn btn-brutal btn-sm" href="<?php echo $path_prefix; ?>auth/register.php">SIGNUP</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
