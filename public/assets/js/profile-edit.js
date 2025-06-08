// Handle form validation
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profileForm');
    const photoUploadForm = document.getElementById('photoUploadForm');
    const videoUploadForm = document.getElementById('videoUploadForm');
    const horoscopeUploadForm = document.getElementById('horoscopeUploadForm');
    const healthUploadForm = document.getElementById('healthUploadForm');

    // Form validation
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const dateOfBirth = new Date(document.getElementById('date_of_birth').value);
            const today = new Date();
            const age = today.getFullYear() - dateOfBirth.getFullYear();
            
            if (age < 18) {
                e.preventDefault();
                alert('You must be at least 18 years old to register.');
                return false;
            }
            
            const height = parseInt(document.getElementById('height_cm').value);
            if (height < 100 || height > 250) {
                e.preventDefault();
                alert('Please enter a valid height between 100-250 cm.');
                return false;
            }
            
            const income = parseInt(document.getElementById('income_lkr').value);
            if (income < 0) {
                e.preventDefault();
                alert('Income cannot be negative.');
                return false;
            }
        });
    }

    // Preview uploaded photo
    const photoInput = document.querySelector('input[name="photo"]');
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.querySelector('.profile-photo-preview') || document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('img-fluid', 'rounded', 'mb-3', 'profile-photo-preview');
                    img.style.maxHeight = '200px';
                    
                    const container = document.querySelector('.card-body.text-center');
                    if (container) {
                        const existingPreview = container.querySelector('.profile-photo-preview');
                        if (existingPreview) {
                            container.replaceChild(img, existingPreview);
                        } else {
                            container.insertBefore(img, photoUploadForm);
                        }
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // File size validation
    function validateFileSize(file, maxSize, fileType) {
        if (file.size > maxSize) {
            const sizeMB = maxSize / (1024 * 1024);
            alert(`${fileType} file size must not exceed ${sizeMB}MB`);
            return false;
        }
        return true;
    }

    // File type validation
    function validateFileType(file, allowedTypes, fileType) {
        if (!allowedTypes.includes(file.type)) {
            alert(`Invalid ${fileType} file type. Allowed types: ${allowedTypes.join(', ')}`);
            return false;
        }
        return true;
    }

    // Photo upload validation
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const maxSize = 5 * 1024 * 1024; // 5MB
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                
                if (!validateFileSize(file, maxSize, 'Photo') || 
                    !validateFileType(file, allowedTypes, 'Photo')) {
                    this.value = '';
                }
            }
        });
    }

    // Video upload validation
    const videoInput = document.querySelector('input[name="video"]');
    if (videoInput) {
        videoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const maxSize = 50 * 1024 * 1024; // 50MB
                const allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
                
                if (!validateFileSize(file, maxSize, 'Video') || 
                    !validateFileType(file, allowedTypes, 'Video')) {
                    this.value = '';
                }
            }
        });
    }

    // Horoscope upload validation
    const horoscopeInput = document.querySelector('input[name="horoscope"]');
    if (horoscopeInput) {
        horoscopeInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const maxSize = 10 * 1024 * 1024; // 10MB
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                
                if (!validateFileSize(file, maxSize, 'Horoscope') || 
                    !validateFileType(file, allowedTypes, 'Horoscope')) {
                    this.value = '';
                }
            }
        });
    }

    // Health report upload validation
    const healthInput = document.querySelector('input[name="health_report"]');
    if (healthInput) {
        healthInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const maxSize = 10 * 1024 * 1024; // 10MB
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                
                if (!validateFileSize(file, maxSize, 'Health report') || 
                    !validateFileType(file, allowedTypes, 'Health report')) {
                    this.value = '';
                }
            }
        });
    }
}); 