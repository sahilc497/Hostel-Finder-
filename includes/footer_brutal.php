<?php
$path_prefix = (basename(dirname($_SERVER['PHP_SELF'])) == 'hf') ? '' : '../';
?>
<footer class="brutal-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h2 class="text-white">STUDENTNEST</h2>
                <p class="text-white opacity-75">BOLDEST PLATFORM FOR PG FINDING. NO NONSENSE. JUST ROOMS.</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5>QUICK LINKS</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white text-decoration-none hover-yellow">TERMS OF SERVICE</a></li>
                    <li><a href="#" class="text-white text-decoration-none hover-yellow">PRIVACY POLICY</a></li>
                    <li><a href="#" class="text-white text-decoration-none hover-yellow">CONTACT SUPPORT</a></li>
                    <li><a href="<?php echo $path_prefix; ?>auth/admin_login.php" class="text-brutal-yellow text-decoration-none hover-white fw-bold">ADMIN LOGIN</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>LOCATION</h5>
                <p class="text-white opacity-75">DYPCET CAMPUS, KOLHAPUR<br>MAHARASHTRA, INDIA</p>
            </div>
        </div>
        <div class="brutal-footer-bottom text-center">
            <p class="mb-0">© <?php echo date('Y'); ?> STUDENTNEST | <span class="text-brutal-yellow fw-bold">MADE BY SAHIL</span> | ALL RIGHTS RESERVED.</p>
        </div>
    </div>
</footer>

<style>
    .hover-yellow:hover { color: var(--brutal-yellow) !important; }
    .text-brutal-yellow { color: #FFE500; }
</style>
