<?php if (isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="contactRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Contact Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="contactRequestForm" onsubmit="event.preventDefault(); submitContactRequest();">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="profile_id" id="contactProfileId">
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message (Optional)</label>
                        <textarea class="form-control" id="message" name="message" rows="3" 
                                placeholder="Write a brief message to introduce yourself..."></textarea>
                        <div class="form-text">A personalized message increases your chances of acceptance.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-envelope"></i> Send Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?> 