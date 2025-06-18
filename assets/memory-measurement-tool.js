// Cancel function - make it directly global
window.cancelMemoryTest = function() {
	if (window.memoryTestTimer) {
		clearTimeout(window.memoryTestTimer);
	}
	return confirm('Are you sure you want to cancel the memory test?');
};

// Auto-save memory settings - make it global
window.saveMemorySettings = function saveMemorySettings() {
	const nomeas = document.getElementById('nomeas').value;
	const secrel = document.getElementById('secrel').value;
	const status = document.getElementById('save-status');
	
	if (!nomeas || !secrel) return;
	
	status.textContent = 'Saving...';
	status.style.color = '#666';
	
	// Use WordPress AJAX
	if (typeof memoryToolAjax === 'undefined') {
		status.textContent = 'AJAX not ready';
		status.style.color = '#dc3545';
		return;
	}
	
	const ajaxurl = memoryToolAjax.ajaxurl;
	const nonce = memoryToolAjax.memorySettingsNonce;
	
	fetch(ajaxurl, {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: 'action=save_memory_settings&nomeas=' + nomeas + '&secrel=' + secrel + '&nonce=' + nonce
	})
	.then(response => response.json())
	.then(data => {
		if (data.success) {
			status.textContent = 'Saved!';
			status.style.color = '#28a745';
			setTimeout(() => { status.textContent = ''; }, 2000);
		} else {
			status.textContent = 'Save failed';
			status.style.color = '#dc3545';
		}
	})
	.catch(() => {
		status.textContent = 'Save error';
		status.style.color = '#dc3545';
	});
}

// Setup memory test timer
function setupMemoryTestTimer(intervalMs, nonce) {
	// Clear any existing timer
	if (window.memoryTestTimer) {
		clearTimeout(window.memoryTestTimer);
	}
	
	// Set up auto-continue with current URL parameters to maintain nonce
	window.memoryTestTimer = setTimeout(function() {
		// Use current page URL but change action to continue
		var currentUrl = new URL(window.location.href);
		currentUrl.searchParams.set('test', 'continue');
		// Keep existing nonce if present
		if (!currentUrl.searchParams.has('nonce')) {
			currentUrl.searchParams.set('nonce', nonce);
		}
		window.location.href = currentUrl.toString();
	}, intervalMs);
}

// Auto-detect test in progress and setup timer
document.addEventListener('DOMContentLoaded', function() {
	var testProgress = document.querySelector('.wp-system-info-test-progress:first-of-type');
	if (testProgress) {
		// Get data from attributes
		var intervalMs = parseInt(testProgress.getAttribute('data-interval')) || 2000;
		var nonce = testProgress.getAttribute('data-nonce') || '';
		if (nonce) {
			setupMemoryTestTimer(intervalMs, nonce);
		}
	}
});