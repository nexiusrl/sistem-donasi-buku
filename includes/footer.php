    </main>

    <!-- Footer Premium -->
    <footer>
        <div class="container">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-5">
                    <h5 class="text-white mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-book-half"></i> BukuBerbagi
                    </h5>
                    <p class="small">
                        Platform pendonasian buku untuk membantu menyebarkan jendela dunia ke seluruh penjuru pelosok yang membutuhkan. Bersama kita mencerdaskan bangsa.
                    </p>
                </div>
                <div class="col-lg-3">
                    <h6 class="text-white mb-3">Tautan Cepat</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><a href="<?= $base_path ?>index.php">Beranda</a></li>
                        <?php if (isset($_SESSION["user_id"])): ?>
                            <li><a href="<?= $_SESSION["role"] === "admin"
                              ? $base_path . "views/admin/dashboard.php"
                              : $base_path .
                                "views/pendonasi/dashboard.php" ?>">Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="<?= $base_path ?>login.php">Masuk</a></li>
                            <li><a href="<?= $base_path ?>register.php">Daftar Pendonasi</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="text-white mb-3">Hubungi Kami</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><i class="bi bi-envelope me-2"></i> info@bukuberbagi.org</li>
                        <li><i class="bi bi-telephone me-2"></i> +62 812-3456-7890</li>
                        <li><i class="bi bi-geo-alt me-2"></i> Makassar, Sulawesi Selatan</li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small">
                <p class="mb-0">&copy; <?= date(
                  "Y",
                ) ?> BukuBerbagi. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Custom JS -->
    <script src="<?= $base_path ?>assets/js/main.js"></script>
</body>
</html>
