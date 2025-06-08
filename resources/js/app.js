import './bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    const loadingOverlay = document.getElementById('loading-overlay');
    
    if (!loadingOverlay) return;

    // Show loading on page navigation
    window.addEventListener('beforeunload', function() {
        loadingOverlay.style.display = 'block';
    });

    // Show loading on form submit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            loadingOverlay.style.display = 'block';
        });
    });

    // Show loading on button clicks that trigger navigation
    document.querySelectorAll('a, button').forEach(element => {
        element.addEventListener('click', function(e) {
            if (this.href && !this.href.includes('#') && !this.href.includes('javascript:')) {
                loadingOverlay.style.display = 'block';
            }
        });
    });

    // Handle AJAX requests
    document.addEventListener('ajax:send', function() {
        loadingOverlay.style.display = 'block';
    });

    document.addEventListener('ajax:complete', function() {
        loadingOverlay.style.display = 'none';
    });
});