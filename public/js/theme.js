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

// Exponer función globalmente para usar en botones HTML
window.toggleTheme = toggleTheme;