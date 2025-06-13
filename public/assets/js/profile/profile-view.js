// Contact Request Functions are now handled in app.js

// Initialize event handlers
document.addEventListener('DOMContentLoaded', function() {
    // Handle favorite button clicks
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.toggle-favorite');
        if (btn) {
            e.preventDefault();
            const profileId = btn.dataset.profileId;
            toggleFavorite(profileId);
        }
    });
});

// Favorite Functions
function toggleFavorite(profileId) {
    const btn = document.querySelector(`button[data-profile-id="${profileId}"]`);
    if (!btn) {
        showAlert('Favorite button not found.', 'error');
        return;
    }

    const action = btn.classList.contains('btn-danger') ? 'remove' : 'add';
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
    
    if (!csrfToken) {
        showAlert('Security token not found. Please refresh the page.', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('profile_id', profileId);
    formData.append('csrf_token', csrfToken);
    
    fetch(BASE_URL + '/favorites/' + action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const icon = btn.querySelector('i');
            
            if (action === 'add') {
                btn.classList.remove('btn-outline-danger');
                btn.classList.add('btn-danger');
                icon.classList.add('bi-heart-fill');
                icon.classList.remove('bi-heart');
                btn.innerHTML = '<i class="bi bi-heart-fill"></i> Remove from Favorites';
            } else {
                btn.classList.add('btn-outline-danger');
                btn.classList.remove('btn-danger');
                icon.classList.remove('bi-heart-fill');
                icon.classList.add('bi-heart');
                btn.innerHTML = '<i class="bi bi-heart"></i> Add to Favorites';
            }
            
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message || 'Failed to update favorite status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to update favorite status. Please try again.', 'error');
    });
}

// Helper Functions
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
} 