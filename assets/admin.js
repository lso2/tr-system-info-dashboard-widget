// Live memory monitoring functions
if (!window.memoryChart) {
	window.memoryChart = null;
}
if (!window.memoryData) {
	window.memoryData = [];
}
var memoryChart = window.memoryChart;
var memoryData = window.memoryData;
var maxDataPoints = 30;

function initMemoryChart() {
	const canvas = document.getElementById('wp-system-info-memory-chart');
	if (!canvas) return;
	const ctx = canvas.getContext('2d');
	canvas.width = canvas.offsetWidth;
	canvas.height = 80;
	memoryChart = { canvas, ctx };
	window.memoryChart = memoryChart;
	updateMemoryChart();
}

function updateMemoryChart() {
	if (!memoryChart) return;
	const { ctx, canvas } = memoryChart;
	ctx.clearRect(0, 0, canvas.width, canvas.height);
	if (memoryData.length < 2) return;
	// Draw grid
	ctx.strokeStyle = '#e0e0e0';
	ctx.lineWidth = 1;
	for (let i = 0; i < 5; i++) {
		const y = (canvas.height / 4) * i;
		ctx.beginPath();
		ctx.moveTo(0, y);
		ctx.lineTo(canvas.width, y);
		ctx.stroke();
	}
	// Draw memory line
	const maxMemory = Math.max(...memoryData.map(d => d.current));
	const minMemory = Math.min(...memoryData.map(d => d.current));
	const range = maxMemory - minMemory || 1;
	ctx.strokeStyle = '#0073aa';
	ctx.lineWidth = 2;
	ctx.beginPath();
	memoryData.forEach((data, index) => {
		const x = (canvas.width / (memoryData.length - 1)) * index;
		const y = canvas.height - ((data.current - minMemory) / range) * canvas.height;
		if (index === 0) { ctx.moveTo(x, y); } else { ctx.lineTo(x, y); }
	});
	ctx.stroke();
}

function fetchMemoryData() {
	if (!wpSystemInfoAjax) return;
	fetch(wpSystemInfoAjax.ajaxurl, {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: 'action=get_live_memory&nonce=' + wpSystemInfoAjax.liveMemoryNonce
	})
	.then(response => response.json())
	.then(data => {
		if (data.success) {
			memoryData.push(data.data);
			window.memoryData = memoryData;
			if (memoryData.length > maxDataPoints) memoryData.shift();
			if (document.getElementById('current-memory')) document.getElementById('current-memory').textContent = data.data.current;
			if (document.getElementById('peak-memory')) document.getElementById('peak-memory').textContent = data.data.peak;
			updateMemoryChart();
		}
	}).catch(() => {});
}

if (typeof jQuery !== 'undefined') {
	jQuery(document).ready(function() {
		setTimeout(() => {
			initMemoryChart();
			fetchMemoryData();
			setInterval(fetchMemoryData, 3000);
		}, 100);
	});
}

// Toggle functions for collapsible sections
function toggleSection(id) {
	const element = document.getElementById(id);
	if (element) {
		element.style.display = element.style.display === 'block' ? 'none' : 'block';
	}
}

function toggleSectionWithText(sectionId, toggleId) {
	const element = document.getElementById(sectionId);
	const toggle = document.getElementById(toggleId);
	if (!element || !toggle) return;
	
	// Hide the other section if it's a table/plugin toggle
	if (sectionId === 'db-tables' || sectionId === 'active-plugins') {
		const otherSection = sectionId === 'db-tables' ? 'active-plugins' : 'db-tables';
		const otherToggle = sectionId === 'db-tables' ? 'plugins-toggle' : 'tables-toggle';
		const otherElement = document.getElementById(otherSection);
		const otherToggleElement = document.getElementById(otherToggle);
		
		if (otherElement && otherElement.style.display === 'block') {
			otherElement.style.display = 'none';
			if (otherToggleElement && otherToggleElement.textContent.includes('Hide')) {
				otherToggleElement.textContent = otherToggleElement.textContent.replace('Hide', 'Show');
			}
		}
	}
	
	if (element.style.display === 'none' || element.style.display === '') {
		element.style.display = 'block';
		if (toggle.textContent.includes('Show')) {
			toggle.textContent = toggle.textContent.replace('Show', 'Hide');
		}
	} else {
		element.style.display = 'none';
		if (toggle.textContent.includes('Hide')) {
			toggle.textContent = toggle.textContent.replace('Hide', 'Show');
		}
	}
}

function initializePluginMemory() {
	const button = document.getElementById('initialize-plugins');
	const status = document.getElementById('initialize-status');
	if (!button || !status || !wpSystemInfoAjax) return;
	
	button.disabled = true;
	button.textContent = 'Initializing...';
	status.style.display = 'block';
	status.textContent = 'Recording memory usage for all active plugins...';
	
	fetch(wpSystemInfoAjax.ajaxurl, {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: 'action=initialize_plugin_memory&nonce=' + wpSystemInfoAjax.initPluginsNonce
	})
	.then(response => response.json())
	.then(data => {
		if (data.success) {
			status.textContent = data.data.message + ' Refresh the page to see updated data.';
			status.style.color = '#28a745';
			button.textContent = 'Initialized';
			setTimeout(() => location.reload(), 2000);
		} else {
			status.textContent = 'Error: ' + (data.data || 'Unknown error');
			status.style.color = '#dc3545';
			button.disabled = false;
			button.textContent = 'Initialize Plugin Memory Tracking';
		}
	})
	.catch(error => {
		status.textContent = 'Error initializing plugins.';
		status.style.color = '#dc3545';
		button.disabled = false;
		button.textContent = 'Initialize Plugin Memory Tracking';
	});
}

function cancelMemoryTest(event) {
	// Always prevent default link behavior first
	if (event) {
		event.preventDefault();
	}
	
	// Clear the timer immediately to stop auto-reload
	if (window.memoryTestTimer) {
		clearTimeout(window.memoryTestTimer);
		window.memoryTestTimer = null;
	}
	
	// Show confirmation dialog
	if (confirm('Are you sure you want to cancel the memory test?')) {
		// User confirmed - proceed with cancellation
		// Get the cancel URL from the link that was clicked
		const cancelUrl = event && event.target ? event.target.href : null;
		
		if (cancelUrl) {
			// Navigate to the cancel URL
			window.location.href = cancelUrl;
		} else {
			// Fallback: construct cancel URL manually
			const currentUrl = new URL(window.location.href);
			currentUrl.searchParams.set('test', 'cancel');
			// Use existing nonce if available
			const testProgress = document.querySelector('.wp-system-info-test-progress');
			if (testProgress && testProgress.getAttribute('data-nonce')) {
				currentUrl.searchParams.set('nonce', testProgress.getAttribute('data-nonce'));
			}
			window.location.href = currentUrl.toString();
		}
	} else {
		// User cancelled the confirmation - restart the timer if it was running
		const testProgress = document.querySelector('.wp-system-info-test-progress');
		if (testProgress) {
			const intervalMs = parseInt(testProgress.getAttribute('data-interval')) || 2000;
			const nonce = testProgress.getAttribute('data-nonce') || '';
			if (nonce) {
				// Re-setup the timer
				window.memoryTestTimer = setTimeout(function() {
					const currentUrl = new URL(window.location.href);
					currentUrl.searchParams.set('test', 'continue');
					if (!currentUrl.searchParams.has('nonce')) {
						currentUrl.searchParams.set('nonce', nonce);
					}
					window.location.href = currentUrl.toString();
				}, intervalMs);
			}
		}
	}
	
	return false; // Always prevent default link behavior
}
