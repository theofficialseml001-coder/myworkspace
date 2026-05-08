/**
 * Main JavaScript for PDF to Video Converter
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // File input preview
    const fileInput = document.getElementById('pdf_file');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Validate file type
                if (file.type !== 'application/pdf') {
                    alert('Please select a valid PDF file.');
                    fileInput.value = '';
                    return;
                }
                
                // Show file info
                const fileInfo = document.createElement('div');
                fileInfo.className = 'alert alert-info mt-3';
                fileInfo.innerHTML = `
                    <i class="bi bi-file-earmark-pdf me-2"></i>
                    <strong>Selected:</strong> ${file.name}<br>
                    <small>Size: ${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                `;
                
                // Remove existing info
                const existingInfo = form.querySelector('.alert-info');
                if (existingInfo) {
                    existingInfo.remove();
                }
                
                form.appendChild(fileInfo);
            }
        });
    }
    
    // Drag and drop functionality
    const uploadZone = document.querySelector('.upload-zone');
    
    if (uploadZone && fileInput) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.classList.add('dragover');
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.classList.remove('dragover');
            }, false);
        });
        
        uploadZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                // Trigger change event
                const event = new Event('change');
                fileInput.dispatchEvent(event);
            }
        }, false);
        
        // Click to upload
        uploadZone.addEventListener('click', () => {
            fileInput.click();
        });
    }
    
    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert-dismissible');
    
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 10000); // Close after 10 seconds
    });
    
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Lazy loading images
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for browsers without IntersectionObserver
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    }
    
    // Video player enhancements
    const videos = document.querySelectorAll('video');
    
    videos.forEach(video => {
        // Show controls on hover
        video.parentElement.addEventListener('mouseenter', () => {
            video.controls = true;
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (document.activeElement === video) {
                switch(e.key) {
                    case ' ':
                    case 'k':
                        e.preventDefault();
                        if (video.paused) {
                            video.play();
                        } else {
                            video.pause();
                        }
                        break;
                    case 'f':
                        e.preventDefault();
                        if (video.requestFullscreen) {
                            video.requestFullscreen();
                        }
                        break;
                    case 'm':
                        e.preventDefault();
                        video.muted = !video.muted;
                        break;
                }
            }
        });
    });
    
    // Search functionality enhancement
    const searchInput = document.querySelector('input[name="search"]');
    
    if (searchInput) {
        let timeout = null;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            
            timeout = setTimeout(() => {
                // Could implement AJAX search here
                console.log('Searching for:', this.value);
            }, 500);
        });
    }
    
    // Confirmation dialogs for destructive actions
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure?';
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Copy to clipboard functionality
    const copyButtons = document.querySelectorAll('[data-copy]');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const text = this.dataset.copy;
            
            try {
                await navigator.clipboard.writeText(text);
                
                // Show success feedback
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check"></i> Copied!';
                this.classList.add('btn-success');
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('btn-success');
                }, 2000);
            } catch (err) {
                console.error('Failed to copy:', err);
            }
        });
    });
    
    // Progress bar animation
    const progressBars = document.querySelectorAll('.progress-bar');
    
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        
        setTimeout(() => {
            bar.style.transition = 'width 1s ease-in-out';
            bar.style.width = width;
        }, 100);
    });
    
    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Popover initialization
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
});

// Utility functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatDuration(seconds) {
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    
    return `${minutes}:${secs.toString().padStart(2, '0')}`;
}

// Export functions for global use
window.PDFToVideo = {
    formatFileSize,
    formatDuration
};
