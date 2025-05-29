/**
 * TechReader System Info Dashboard Widget - Admin JavaScript
 */

// Live memory monitoring
let memoryChart = null;
let memoryData = [];
let maxDataPoints = 30;

function initMemoryChart() {
	const canvas = document.getElementById('wp-system-info-memory-chart');
	if (!canvas) {
		return;
	}
	
	const ctx = canvas.getContext('2d');
	canvas.width = canvas.offsetWidth || 300;
	canvas.height = 80;
	
	memoryChart = { canvas, ctx };
}

function updateMemoryChart() {
if (!memoryChart) return;

const { ctx, canvas } = memoryChart;
ctx.clearRect(0, 0, canvas.width, canvas.height);

// Always draw basic grid even without data
ctx.strokeStyle = '#e0e0e0';
ctx.lineWidth = 1;
for (let i = 0; i < 5; i++) {
 const y = (canvas.height / 4) * i;
 ctx.beginPath();
 ctx.moveTo(0, y);
 ctx.lineTo(canvas.width, y);
 ctx.stroke();
}

if (memoryData.length < 2) {
return;
}

// Calculate memory range and create dynamic scale
const maxMemory = Math.max(...memoryData.map(d => d.current));
const minMemory = Math.min(...memoryData.map(d => d.current));
const range = maxMemory - minMemory || 1;

// Draw memory line
ctx.strokeStyle = '#0073aa';
ctx.lineWidth = 2;
ctx.beginPath();

memoryData.forEach((data, index) => {
const x = (canvas.width / (memoryData.length - 1)) * index;
const y = canvas.height - ((data.current - minMemory) / range) * canvas.height;

 if (index === 0) {
 ctx.moveTo(x, y);
 } else {
			ctx.lineTo(x, y);
		}
	});
	
	ctx.stroke();
}

function fetchMemoryData() {
	if (!wpSystemInfoAjax) {
		return;
	}
	
	fetch(wpSystemInfoAjax.ajaxurl, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
		},
		body: 'action=get_live_memory&nonce=' + wpSystemInfoAjax.liveMemoryNonce
	})
	.then(response => response.json())
	.then(data => {
		if (data.success) {
			memoryData.push(data.data);
			if (memoryData.length > maxDataPoints) {
				memoryData.shift();
			}
			
			if (document.getElementById('current-memory')) {
				document.getElementById('current-memory').textContent = data.data.current;
			}
			if (document.getElementById('peak-memory')) {
				document.getElementById('peak-memory').textContent = data.data.peak;
			}
			
			updateMemoryChart();
		}
	})
	.catch(error => {});
}

function toggleSection(id) {
	const element = document.getElementById(id);
	element.style.display = element.style.display === 'block' ? 'none' : 'block';
}

function toggleSectionWithText(sectionId, toggleId) {
	const element = document.getElementById(sectionId);
	const toggle = document.getElementById(toggleId);
	
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
		// Update text to show Hide
		if (toggle.textContent.includes('Show')) {
			toggle.textContent = toggle.textContent.replace('Show', 'Hide');
		}
	} else {
		element.style.display = 'none';
		// Update text to show Show
		if (toggle.textContent.includes('Hide')) {
			toggle.textContent = toggle.textContent.replace('Hide', 'Show');
		}
	}
}

function initializePluginMemory() {
	const button = document.getElementById('initialize-plugins');
	const status = document.getElementById('initialize-status');
	
	button.disabled = true;
	button.textContent = 'Initializing...';
	status.style.display = 'block';
	status.textContent = 'Recording memory usage for all active plugins...';
	
	fetch(wpSystemInfoAjax.ajaxurl, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
		},
		body: 'action=initialize_plugin_memory&nonce=' + wpSystemInfoAjax.initPluginsNonce
	})
	.then(response => response.json())
	.then(data => {
		if (data.success) {
			status.textContent = data.data.message + ' Refresh the page to see updated data.';
			status.style.color = '#28a745';
			button.textContent = 'Initialized';
			setTimeout(() => {
				location.reload();
			}, 2000);
		} else {
			status.textContent = 'Error: ' + (data.data || 'Unknown error');
			status.style.color = '#dc3545';
			button.disabled = false;
			button.textContent = 'Initialize Plugin Memory Tracking';
		}
	})
	.catch(error => {
		console.log('Initialize error:', error);
		status.textContent = 'Error initializing plugins.';
		status.style.color = '#dc3545';
		button.disabled = false;
		button.textContent = 'Initialize Plugin Memory Tracking';
	});
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
	setTimeout(() => {
		initMemoryChart();
		updateMemoryChart();
		fetchMemoryData();
		setInterval(fetchMemoryData, 3000);
	}, 100);
});
