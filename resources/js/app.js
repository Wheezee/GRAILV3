import './bootstrap';
import { createIcons, icons } from 'lucide';
import Chart from 'chart.js/auto';

function renderLucideIcons() {
	try {
		createIcons({ icons });
	} catch (error) {
		console.error('Failed to render Lucide icons:', error);
	}
}

// Expose a global helper so inline scripts can refresh icons after DOM changes
// or attribute updates (e.g., switching sun/moon icon on theme toggle).
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
window.applyLucideIcons = renderLucideIcons;

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', renderLucideIcons);
} else {
	renderLucideIcons();
}

// Backward compatibility for existing inline calls in Blade views
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
window.lucide = window.lucide || {};
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
window.lucide.createIcons = renderLucideIcons;

// Expose Chart globally for inline scripts
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
window.Chart = Chart;
// Signal that charts are available for inline scripts that wait for readiness
window.dispatchEvent(new Event('grail:charts-ready'));
