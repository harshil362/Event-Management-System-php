    <!-- Main Content Wrapper -->
    <div class="content-wrapper">
        <?php if(isset($content)) echo $content; ?>
    </div>
    
    <!-- Footer -->
    <footer class="admin-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> EventPro Admin Panel. All rights reserved.</p>
            <p class="version">v1.0.0</p>
        </div>
    </footer>
    
    <script>
    // Common JavaScript functions
    function showLoading() {
        document.body.insertAdjacentHTML('beforeend', `
            <div class="loading-overlay">
                <div class="spinner"></div>
            </div>
        `);
    }
    
    function hideLoading() {
        const overlay = document.querySelector('.loading-overlay');
        if(overlay) overlay.remove();
    }
    
    // Confirm before delete
    function confirmAction(message, url) {
        if(confirm(message)) {
            window.location.href = url;
        }
    }
    
    // Show modal
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) {
            modal.style.display = 'flex';
        }
    }
    
    // Hide modal
    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) {
            modal.style.display = 'none';
        }
    }
    
    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if(e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    });
    
    // Auto-hide alerts
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
    
    // Image preview for file inputs
    document.addEventListener('DOMContentLoaded', function() {
        const imageInputs = document.querySelectorAll('input[type="file"][accept="image/*"]');
        imageInputs.forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if(file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = input.closest('.form-group').querySelector('.image-preview');
                        if(preview) {
                            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px;">`;
                        } else {
                            // Create preview container
                            const previewDiv = document.createElement('div');
                            previewDiv.className = 'image-preview mt-2';
                            previewDiv.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px;">`;
                            input.parentNode.insertBefore(previewDiv, input.nextSibling);
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    });
    </script>
</body>
</html>