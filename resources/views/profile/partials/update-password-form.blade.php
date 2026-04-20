<style>
    .icon-input {
        position: relative;
    }
    .icon-input i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 10;
    }
    .icon-input .form-control {
        padding-left: 45px;
        border-radius: 12px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    .icon-input .form-control:focus {
        border-color: #0f5c4d;
        box-shadow: 0 0 0 0.2rem rgba(15, 92, 77, 0.18);
        outline: none;
    }
</style>

<form method="post" action="{{ route('password.update') }}" id="updatePasswordForm">
    @csrf
    @method('put')

    @if (session('status') === 'password-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            Password berhasil diubah!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="update_password_current_password" class="form-label fw-bold">
                <i class="fas fa-lock me-2"></i>Password Saat Ini
            </label>
            <div class="icon-input">
                <i class="fas fa-key"></i>
                <input type="password" 
                       id="update_password_current_password" 
                       name="current_password" 
                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                       autocomplete="current-password"
                       placeholder="Masukkan password saat ini"
                       required>
            </div>
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="update_password_password" class="form-label fw-bold">
                <i class="fas fa-lock me-2"></i>Password Baru
            </label>
            <div class="icon-input">
                <i class="fas fa-lock"></i>
                <input type="password" 
                       id="update_password_password" 
                       name="password" 
                       class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                       autocomplete="new-password"
                       placeholder="Masukkan password baru (minimal 8 karakter)"
                       required
                       minlength="8">
            </div>
            @error('password', 'updatePassword')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="text-muted">Password minimal 8 karakter</small>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="update_password_password_confirmation" class="form-label fw-bold">
                <i class="fas fa-lock me-2"></i>Konfirmasi Password Baru
            </label>
            <div class="icon-input">
                <i class="fas fa-lock"></i>
                <input type="password" 
                       id="update_password_password_confirmation" 
                       name="password_confirmation" 
                       class="form-control" 
                       autocomplete="new-password"
                       placeholder="Konfirmasi password baru"
                       required
                       minlength="8">
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="submit" class="btn btn-primary" id="updatePasswordBtn" style="border-radius: 12px; padding: 12px 30px;">
            <span class="btn-text">
                <i class="fas fa-save me-2"></i>Simpan Password
            </span>
            <span class="btn-loading d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Menyimpan...
            </span>
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('updatePasswordForm');
    const submitBtn = document.getElementById('updatePasswordBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('update_password_password').value;
            const confirmPassword = document.getElementById('update_password_password_confirmation').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password baru dan konfirmasi password tidak cocok!');
                return;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password minimal 8 karakter!');
                return;
            }
            
            
            submitBtn.disabled = true;
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            
            
            const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
            inputs.forEach(input => {
                if (input.tagName === 'INPUT' || input.tagName === 'TEXTAREA') {
                    input.readOnly = true;
                }
            });
        });
        
        
        const passwordInput = document.getElementById('update_password_password');
        const confirmInput = document.getElementById('update_password_password_confirmation');
        
        if (confirmInput) {
            confirmInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirm = this.value;
                
                if (confirm && password !== confirm) {
                    this.setCustomValidity('Password tidak cocok');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    }
});
</script>

