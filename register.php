<?php
require_once 'includes/session.php';
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'includes/header.php';
require_once 'config/database.php';

// Fetch Sektor for the complete registration form
$stmtSektor = $pdo->query("SELECT * FROM tblSektor ORDER BY nama_sektor ASC");
$sektors = $stmtSektor->fetchAll();
?>

<div class="container mt-5 mb-5 fade-in">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-8">
            <div class="glass-card">
                <div class="text-center mb-4">
                    <i class="fas fa-user-plus fa-3x text-secondary mb-3"></i>
                    <h2 class="fw-bold">Pendaftaran Akun</h2>
                    <p class="text-muted">Apakah data Anda sudah terdaftar di data gereja / didaftarkan oleh pengurus sektor?</p>
                </div>

                <?php if (isset($_SESSION['error_msg'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success_msg'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
                    </div>
                <?php endif; ?>

                <!-- Selection Buttons -->
                <div class="d-flex justify-content-center gap-3 mb-4" id="selection-buttons">
                    <button class="btn btn-outline-primary px-4 py-2 fw-semibold" onclick="showForm('sudah')">
                        <i class="fas fa-check-circle me-2"></i>Sudah Terdaftar
                    </button>
                    <button class="btn btn-outline-secondary px-4 py-2 fw-semibold" onclick="showForm('belum')">
                        <i class="fas fa-times-circle me-2"></i>Belum Terdaftar
                    </button>
                </div>

                <!-- Form Sudah Terdaftar -->
                <div id="form-sudah" style="display: none;">
                    <div class="alert alert-info small py-2">
                        <i class="fas fa-info-circle me-1"></i> Silakan isi data di bawah ini sesuai dengan data yang telah didaftarkan.
                    </div>
                    <form action="actions/auth.php" method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="register_linked">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Username Akun Baru</label>
                                <input type="text" class="form-control form-control-custom" name="username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Email Terdaftar</label>
                                <input type="email" class="form-control form-control-custom" name="email" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">No. HP Terdaftar</label>
                                <input type="text" class="form-control form-control-custom" name="no_hp" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Nama Lengkap</label>
                                <input type="text" class="form-control form-control-custom" name="nama_lengkap" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Tanggal Lahir</label>
                                <input type="date" class="form-control form-control-custom" name="tanggal_lahir" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-medium">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-custom" name="password" id="pw_sudah" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('pw_sudah')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-medium">Konfirmasi Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-custom" name="password_confirm" id="pw_sudah_confirm" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('pw_sudah_confirm')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 mb-3" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #059669 100%);">
                            <i class="fas fa-link me-2"></i>Ajukan & Tautkan Akun
                        </button>
                    </form>
                </div>

                <!-- Opsi Belum Terdaftar -->
                <div id="opsi-belum" style="display: none;">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="border rounded p-3 d-flex justify-content-between align-items-center" style="background:#f8fafc">
                                <div>
                                    <h6 class="mb-1 fw-bold"><i class="fas fa-file-signature text-primary me-2"></i>Daftar & Lengkapi Data</h6>
                                    <small class="text-muted">Isi data jemaat secara mandiri. Akun akan aktif setelah diverifikasi Pengurus Sektor.</small>
                                </div>
                                <button class="btn btn-sm btn-primary" onclick="showForm('lengkap')">Pilih</button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="border rounded p-3 d-flex justify-content-between align-items-center" style="background:#f8fafc">
                                <div>
                                    <h6 class="mb-1 fw-bold"><i class="fas fa-user-clock text-secondary me-2"></i>Daftar Akun Tamu</h6>
                                    <small class="text-muted">Langsung aktif untuk melihat jadwal ibadah, tanpa profil jemaat lengkap.</small>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary" onclick="showForm('tamu')">Pilih</button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="border rounded p-3" style="background:#f8fafc">
                                <h6 class="mb-1 fw-bold"><i class="fas fa-phone-alt text-success me-2"></i>Hubungi Pengurus Sektor</h6>
                                <small class="text-muted">Anda juga dapat menghubungi Pengurus Sektor di wilayah Anda untuk didaftarkan secara manual ke dalam sistem.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Data Lengkap (Pending) -->
                <div id="form-lengkap" style="display: none;">
                    <div class="d-flex align-items-center mb-3">
                        <button class="btn btn-sm btn-light me-2" onclick="showForm('belum')"><i class="fas fa-arrow-left"></i></button>
                        <h5 class="mb-0 fw-bold">Isi Data Jemaat</h5>
                    </div>
                    <form action="actions/auth.php" method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="register_new_jemaat">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Username</label>
                                <input type="text" class="form-control form-control-custom" name="username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Email</label>
                                <input type="email" class="form-control form-control-custom" name="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-medium">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-custom" name="password" id="pw_lengkap" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('pw_lengkap')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-medium">Konfirmasi Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-custom" name="password_confirm" id="pw_lengkap_confirm" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('pw_lengkap_confirm')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h6 class="fw-bold mb-3">Detail Jemaat</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Nama Lengkap</label>
                                <input type="text" class="form-control form-control-custom" name="nama_lengkap" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Sektor</label>
                                <select class="form-select form-control-custom" name="id_sektor" required>
                                    <option value="">-- Pilih Sektor --</option>
                                    <?php foreach ($sektors as $s): ?>
                                        <option value="<?= $s['id_sektor'] ?>"><?= htmlspecialchars($s['nama_sektor']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Jenis Kelamin</label>
                                <select class="form-select form-control-custom" name="jenis_kelamin" required>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Tanggal Lahir</label>
                                <input type="date" class="form-control form-control-custom" name="tanggal_lahir" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Status Pernikahan</label>
                                <select class="form-select form-control-custom" name="status_pernikahan" required>
                                    <option value="Belum Menikah">Belum Menikah</option>
                                    <option value="Sudah Menikah">Sudah Menikah</option>
                                    <option value="Cerai Hidup">Cerai Hidup</option>
                                    <option value="Cerai Mati">Cerai Mati</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">No. HP</label>
                                <input type="text" class="form-control form-control-custom" name="no_hp" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Alamat</label>
                            <textarea class="form-control form-control-custom" name="alamat" rows="2"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 mb-3" style="background: linear-gradient(135deg, var(--primary-color) 0%, #3730a3 100%);">
                            <i class="fas fa-paper-plane me-2"></i>Daftar Menunggu Verifikasi
                        </button>
                    </form>
                </div>

                <!-- Form Akun Tamu -->
                <div id="form-tamu" style="display: none;">
                    <div class="d-flex align-items-center mb-3">
                        <button class="btn btn-sm btn-light me-2" onclick="showForm('belum')"><i class="fas fa-arrow-left"></i></button>
                        <h5 class="mb-0 fw-bold">Daftar Akun Tamu</h5>
                    </div>
                    <form action="actions/auth.php" method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="register_guest">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Username</label>
                            <input type="text" class="form-control form-control-custom" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Email</label>
                            <input type="email" class="form-control form-control-custom" name="email" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-medium">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-custom" name="password" id="pw_tamu" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('pw_tamu')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-medium">Konfirmasi Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-custom" name="password_confirm" id="pw_tamu_confirm" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('pw_tamu_confirm')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100 mb-3" style="background:#475569;border:none;">
                            <i class="fas fa-user me-2"></i>Daftar Sekarang
                        </button>
                    </form>
                </div>

                <div class="text-center mt-3 border-top pt-3">
                    <span class="text-muted">Sudah punya akun?</span> <a href="login.php" class="text-decoration-none fw-bold text-secondary">Masuk di sini</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showForm(type) {
    // Hide all
    document.getElementById('form-sudah').style.display = 'none';
    document.getElementById('opsi-belum').style.display = 'none';
    document.getElementById('form-lengkap').style.display = 'none';
    document.getElementById('form-tamu').style.display = 'none';

    // Remove active styles from buttons
    const btns = document.querySelectorAll('#selection-buttons button');
    btns.forEach(b => {
        b.classList.remove('active');
    });

    if (type === 'sudah') {
        document.getElementById('form-sudah').style.display = 'block';
        btns[0].classList.add('active');
    } else if (type === 'belum') {
        document.getElementById('opsi-belum').style.display = 'block';
        btns[1].classList.add('active');
    } else if (type === 'lengkap') {
        document.getElementById('form-lengkap').style.display = 'block';
    } else if (type === 'tamu') {
        document.getElementById('form-tamu').style.display = 'block';
    }
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
