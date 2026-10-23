// Fakizon Client Interactions
document.addEventListener('DOMContentLoaded', () => {
    // Console Welcome Notice
    console.log('%c[Fakizon Security Lab Active]', 'color: #f08804; font-size: 16px; font-weight: bold;');
    console.log('Fakizon mock e-commerce store is ready for vulnerability assessment testing.');

    // Auto-dismiss alert banners if needed
    const alerts = document.querySelectorAll('.alert-auto-dismiss');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });
});
