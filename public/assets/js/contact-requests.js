// Contact Request Response Functions
function respondToRequest(requestId, status) {
    console.log('Responding to request:', { requestId, status });
    
    if (!confirm(`Are you sure you want to ${status} this contact request?`)) return;
    
    const formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('status', status);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    formData.append('csrf_token', csrfToken);
    
    console.log('CSRF Token:', csrfToken);
    console.log('Request URL:', `${BASE_URL}/contact-request/respond`);
    
    fetch(`${BASE_URL}/contact-request/respond`, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showAlert(data.message, 'success');
            // Update UI
            const requestCard = document.querySelector(`[data-request-id="${requestId}"]`);
            if (requestCard) {
                if (status === 'accepted') {
                    requestCard.classList.add('bg-success-subtle');
                    requestCard.querySelector('.btn-group-vertical').innerHTML = `
                        <button class="btn btn-success btn-sm" disabled>
                            <i class="bi bi-check-circle"></i> Accepted
                        </button>`;
                } else {
                    requestCard.classList.add('bg-danger-subtle');
                    requestCard.querySelector('.btn-group-vertical').innerHTML = `
                        <button class="btn btn-danger btn-sm" disabled>
                            <i class="bi bi-x-circle"></i> Rejected
                        </button>`;
                }
                
                // Update status badge
                const statusBadge = requestCard.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.className = `badge bg-${status === 'accepted' ? 'success' : 'danger'} status-badge`;
                    statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                }
                
                // Refresh the page after a short delay to update all UI elements
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } else {
            showAlert(data.message || 'Failed to respond to request', 'error');
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        showAlert('Failed to respond to request. Please try again.', 'error');
    });
}

// Update the contact request card status
function updateRequestStatus(requestId, status) {
    const requestCard = document.querySelector(`[data-request-id="${requestId}"]`);
    if (!requestCard) return;
    
    const statusBadge = requestCard.querySelector('.status-badge');
    if (statusBadge) {
        statusBadge.className = `badge bg-${status === 'accepted' ? 'success' : 'danger'} status-badge`;
        statusBadge.innerHTML = status.charAt(0).toUpperCase() + status.slice(1);
    }
    
    const actionButtons = requestCard.querySelector('.btn-group-vertical');
    if (actionButtons) {
        actionButtons.remove();
    }
} 