document.addEventListener('DOMContentLoaded', function() {
    if (window.toastType && window.toastTitle) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: window.toastType,
            title: window.toastTitle,
            iconColor: window.toastIconColor || '#fff',
            showConfirmButton: false,
            timer: window.toastTimer || 4000,
            timerProgressBar: true,
            customClass: {
                popup: window.toastPopupClass || 'bg-success text-white'
            }
        });
    }
});