/**
 * Sistema de Tema Claro/Oscuro
 * Hersil Shop
 */

// Inicializar tema al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Obtener tema guardado o usar 'dark' por defecto
    const savedTheme = localStorage.getItem('theme') || 'dark';
    
    // Aplicar tema
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    
    // Actualizar estado del toggle si existe
    updateToggleButton(savedTheme);
});

// Función para cambiar el tema
function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.classList.contains('dark') ? 'dark' : 'light';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    // Aplicar nuevo tema
    if (newTheme === 'dark') {
        html.classList.add('dark');
    } else {
        html.classList.remove('dark');
    }
    
    // Guardar en localStorage
    localStorage.setItem('theme', newTheme);
    
    // Actualizar botón
    updateToggleButton(newTheme);
}

// Actualizar el estado visual del botón toggle
function updateToggleButton(theme) {
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    
    if (themeToggle && themeIcon) {
        if (theme === 'dark') {
            themeIcon.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            `;
        } else {
            themeIcon.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            `;
        }
    }
}

// Compartir producto - copiar enlace al clipboard
function shareProduct(url, nombre) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function() {
            showShareToast('Enlace copiado al portapapeles');
        }).catch(function() {
            fallbackCopy(url);
        });
    } else {
        fallbackCopy(url);
    }
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        showShareToast('Enlace copiado al portapapeles');
    } catch (e) {
        showShareToast('No se pudo copiar el enlace', true);
    }
    document.body.removeChild(textarea);
}

// Compartir por WhatsApp
function shareWhatsApp(url, nombre) {
    const message = 'Mira este producto: ' + nombre + ' ' + url;
    const waUrl = 'https://wa.me/?text=' + encodeURIComponent(message);
    window.open(waUrl, '_blank');
}

// Mostrar toast de confirmación
function showShareToast(message, isError) {
    const existing = document.getElementById('share-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'share-toast';
    const bgColor = isError
        ? 'bg-red-600'
        : 'bg-green-600';
    toast.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 ' + bgColor + ' text-white px-6 py-3 rounded-lg shadow-lg z-[9999] text-sm font-semibold transition-opacity duration-300';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(function() {
        toast.style.opacity = '0';
        setTimeout(function() { toast.remove(); }, 300);
    }, 2000);
}

// Exponer funciones globalmente para usar en botones HTML
window.toggleTheme = toggleTheme;
window.shareProduct = shareProduct;
window.shareWhatsApp = shareWhatsApp;