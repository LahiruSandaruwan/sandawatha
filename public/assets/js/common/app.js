// Sandawatha.lk Global JavaScript Functions

// CSRF Token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Global AJAX setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': csrfToken
    }
});

// Dark Mode Toggle
function toggleDarkMode() {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-bs-theme', newTheme);
    updateDarkModeIcon();
    
    // Save preference via AJAX
    $.post('/toggle-dark-mode', { 
        dark_mode: newTheme === 'dark' ? 1 : 0 
    });
    
    // Save to localStorage for guests
    localStorage.setItem('darkMode', newTheme);
}

function updateDarkModeIcon() {
    const icon = document.getElementById('darkModeIcon');
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    
    if (icon) {
        icon.className = currentTheme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    }
}

// Initialize dark mode on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check for saved preference or default to light
    const savedTheme = localStorage.getItem('darkMode') || 'light';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
    updateDarkModeIcon();
});

// Password Toggle Function
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-toggle');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

// Cookie Consent Functions
function acceptCookies() {
    localStorage.setItem('cookieConsent', 'accepted');
    document.getElementById('cookieConsent').style.display = 'none';
}

function declineCookies() {
    localStorage.setItem('cookieConsent', 'declined');
    document.getElementById('cookieConsent').style.display = 'none';
}

// Newsletter Subscription
function subscribeNewsletter(event) {
    event.preventDefault();
    const form = event.target;
    const email = form.querySelector('input[type="email"]').value;
    
    if (!email) {
        showAlert('Please enter your email address', 'warning');
        return;
    }
    
    $.post('/newsletter/subscribe', { email: email })
        .done(function(response) {
            showAlert('Successfully subscribed to newsletter!', 'success');
            form.reset();
        })
        .fail(function() {
            showAlert('Subscription failed. Please try again.', 'error');
        });
}

// Alert System
function showAlert(message, type = 'info', duration = 5000) {
    const alertTypes = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    const alertClass = alertTypes[type] || 'alert-info';
    const alertId = 'alert-' + Date.now();
    
    const alertHTML = `
        <div id="${alertId}" class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 80px; right: 20px; z-index: 1060; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHTML);
    
    // Auto dismiss after duration
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, duration);
}

// Image Preview for File Uploads
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Profile Actions
function sendContactRequest(profileId) {
    if (!confirm('Send contact request to this profile?')) return;
    
    $.post('/contact-request/send', { profile_id: profileId })
        .done(function(response) {
            showAlert('Contact request sent successfully!', 'success');
            updateContactButton(profileId, 'sent');
        })
        .fail(function(xhr) {
            const message = xhr.responseJSON?.message || 'Failed to send contact request';
            showAlert(message, 'error');
        });
}

function toggleFavorite(profileId) {
    const btn = document.querySelector(`[data-profile-id="${profileId}"]`);
    const isFavorite = btn.classList.contains('btn-danger');
    
    const url = isFavorite ? '/favorites/remove' : '/favorites/add';
    
    $.post(url, { profile_id: profileId })
        .done(function(response) {
            if (isFavorite) {
                btn.classList.remove('btn-danger');
                btn.classList.add('btn-outline-danger');
                btn.innerHTML = '<i class="bi bi-heart"></i>';
                showAlert('Removed from favorites', 'info');
            } else {
                btn.classList.remove('btn-outline-danger');
                btn.classList.add('btn-danger');
                btn.innerHTML = '<i class="bi bi-heart-fill"></i>';
                showAlert('Added to favorites', 'success');
            }
        })
        .fail(function() {
            showAlert('Action failed. Please try again.', 'error');
        });
}

function updateContactButton(profileId, status) {
    const btn = document.querySelector(`[onclick="sendContactRequest(${profileId})"]`);
    if (btn) {
        switch (status) {
            case 'sent':
                btn.className = 'btn btn-warning btn-sm';
                btn.innerHTML = '<i class="bi bi-clock"></i> Pending';
                btn.onclick = null;
                break;
            case 'accepted':
                btn.className = 'btn btn-success btn-sm';
                btn.innerHTML = '<i class="bi bi-check-circle"></i> Connected';
                btn.onclick = null;
                break;
        }
    }
}

// Search and Filter Functions
function initializeSearch() {
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch();
        });
    }
    
    // Initialize filter chips
    document.querySelectorAll('.filter-chip').forEach(chip => {
        chip.addEventListener('click', function() {
            this.classList.toggle('active');
            performSearch();
        });
    });
    
    // Initialize range sliders
    initializeRangeSliders();
}

function performSearch() {
    const formData = new FormData(document.getElementById('searchForm'));
    const searchData = Object.fromEntries(formData);
    
    // Add active filter chips
    const activeFilters = [];
    document.querySelectorAll('.filter-chip.active').forEach(chip => {
        activeFilters.push(chip.dataset.filter);
    });
    searchData.filters = activeFilters;
    
    showLoadingSpinner();
    
    $.post('/search', searchData)
        .done(function(response) {
            updateSearchResults(response.profiles);
            updateResultsCount(response.count);
        })
        .fail(function() {
            showAlert('Search failed. Please try again.', 'error');
        })
        .always(function() {
            hideLoadingSpinner();
        });
}

function initializeRangeSliders() {
    const ageRange = document.getElementById('ageRange');
    const incomeRange = document.getElementById('incomeRange');
    
    if (ageRange) {
        ageRange.addEventListener('input', function() {
            document.getElementById('ageValue').textContent = this.value;
        });
    }
    
    if (incomeRange) {
        incomeRange.addEventListener('input', function() {
            const value = parseInt(this.value).toLocaleString();
            document.getElementById('incomeValue').textContent = 'LKR ' + value;
        });
    }
}

function updateSearchResults(profiles) {
    const container = document.getElementById('searchResults');
    if (!container) return;
    
    if (profiles.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-search" style="font-size: 3rem; color: #ccc;"></i>
                <h4 class="mt-3">No profiles found</h4>
                <p class="text-muted">Try adjusting your search criteria</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = profiles.map(profile => createProfileCard(profile)).join('');
}

function createProfileCard(profile) {
    return `
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card profile-card h-100">
                <div class="position-relative">
                    <img src="${profile.photo || '/assets/images/default-profile.jpg'}" 
                         class="card-img-top profile-image" alt="Profile">
                    ${profile.online ? '<div class="online-indicator"></div>' : ''}
                    ${profile.premium ? '<span class="badge premium-badge position-absolute top-0 start-0 m-2">Premium</span>' : ''}
                </div>
                <div class="card-body">
                    <h5 class="card-title">${profile.name}</h5>
                    <p class="card-text">
                        <small class="text-muted">
                            ${profile.age} years • ${profile.location}<br>
                            ${profile.profession}
                        </small>
                    </p>
                    ${profile.compatibility_score ? `
                        <div class="compatibility-score ${getScoreClass(profile.compatibility_score)} mx-auto mb-2">
                            ${profile.compatibility_score}%
                        </div>
                    ` : ''}
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group w-100">
                        <a href="/profile/${profile.id}" class="btn btn-primary btn-sm">View Profile</a>
                        <button class="btn btn-outline-danger btn-sm" onclick="toggleFavorite(${profile.id})" 
                                data-profile-id="${profile.id}">
                            <i class="bi bi-heart"></i>
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="sendContactRequest(${profile.id})">
                            <i class="bi bi-envelope"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function getScoreClass(score) {
    if (score >= 80) return 'high';
    if (score >= 60) return 'medium';
    return 'low';
}

function updateResultsCount(count) {
    const countElement = document.getElementById('resultsCount');
    if (countElement) {
        countElement.textContent = `${count} profiles found`;
    }
}

// Loading Spinner Functions
function showLoadingSpinner() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.style.display = 'block';
    } else {
        document.body.insertAdjacentHTML('beforeend', `
            <div id="loadingSpinner" class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
                <div class="loading-spinner"></div>
            </div>
        `);
    }
}

function hideLoadingSpinner() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.style.display = 'none';
    }
}

// File Upload Functions
function validateFile(input, maxSize, allowedTypes) {
    const file = input.files[0];
    if (!file) return false;
    
    if (file.size > maxSize) {
        showAlert(`File size must be less than ${Math.round(maxSize / 1024 / 1024)}MB`, 'error');
        input.value = '';
        return false;
    }
    
    if (!allowedTypes.includes(file.type)) {
        showAlert('Invalid file type', 'error');
        input.value = '';
        return false;
    }
    
    return true;
}

function uploadFile(input, endpoint, onSuccess) {
    if (!validateFile(input, 5 * 1024 * 1024, ['image/jpeg', 'image/png', 'image/webp'])) {
        return;
    }
    
    const formData = new FormData();
    formData.append('file', input.files[0]);
    
    showLoadingSpinner();
    
    $.ajax({
        url: endpoint,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            hideLoadingSpinner();
            showAlert('File uploaded successfully!', 'success');
            if (onSuccess) onSuccess(response);
        },
        error: function() {
            hideLoadingSpinner();
            showAlert('Upload failed. Please try again.', 'error');
        }
    });
}

// Message Functions
function markAsRead(messageId) {
    $.post('/messages/mark-read', { message_id: messageId });
}

function deleteMessage(messageId) {
    if (!confirm('Are you sure you want to delete this message?')) return;
    
    $.post('/messages/delete', { message_id: messageId })
        .done(function() {
            document.querySelector(`[data-message-id="${messageId}"]`).remove();
            showAlert('Message deleted', 'success');
        })
        .fail(function() {
            showAlert('Failed to delete message', 'error');
        });
}

// Compatibility Check
function checkCompatibility(profileId) {
    showLoadingSpinner();
    
    $.post('/api/check-compatibility', { profile_id: profileId })
        .done(function(response) {
            displayCompatibilityResult(response);
        })
        .fail(function() {
            showAlert('Failed to calculate compatibility', 'error');
        })
        .always(function() {
            hideLoadingSpinner();
        });
}

function displayCompatibilityResult(result) {
    const modal = new bootstrap.Modal(document.getElementById('compatibilityModal'));
    document.getElementById('compatibilityScore').textContent = result.score + '%';
    document.getElementById('compatibilityExplanation').textContent = result.explanation;
    modal.show();
}

// Form Validation
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^(\+94|0)[0-9]{9}$/;
    return re.test(phone);
}

// Initialize page-specific functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search if on search page
    if (document.getElementById('searchForm')) {
        initializeSearch();
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize modals
    const modalList = [].slice.call(document.querySelectorAll('.modal'));
    modalList.map(function(modalEl) {
        return new bootstrap.Modal(modalEl);
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});


// Performance monitoring
window.addEventListener('load', function() {
    if (window.performance && window.performance.timing) {
        const loadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
        console.log('Page load time:', loadTime + 'ms');
    }
});

// Error handling for uncaught errors
window.addEventListener('error', function(e) {
    console.error('Uncaught error:', e.error);
    // In production, you might want to send this to an error tracking service
});

// Export functions for use in other scripts
window.SandawathaApp = {
    toggleDarkMode,
    showAlert,
    previewImage,
    sendContactRequest,
    toggleFavorite,
    checkCompatibility,
    validateEmail,
    validatePhone,
    uploadFile,
    acceptCookies,
    declineCookies
};