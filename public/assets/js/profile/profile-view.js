// Get BASE_URL from meta tag
const BASE_URL = document.querySelector('meta[name="base-url"]')?.content || '';

// Contact Request Functions are now handled in app.js

// Favorite Functions
function toggleFavorite(profileId) {
    const action = document.querySelector(`button[onclick="toggleFavorite(${profileId})"]`).classList.contains('btn-danger') 
        ? 'remove' 
        : 'add';
    
    fetch(BASE_URL + '/favorites/' + action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'profile_id=' + profileId + '&csrf_token=' + document.querySelector('input[name="csrf_token"]').value
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const btn = document.querySelector(`button[onclick="toggleFavorite(${profileId})"]`);
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
            showAlert(data.message, 'error');
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