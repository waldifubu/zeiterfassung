if ((sessionStorage.getItem('darkMode') !== null && sessionStorage.getItem('darkMode') === 'true')
|| (sessionStorage.getItem('darkMode') === null && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    toggle('light')
}

document.querySelector('.toggle-mode').addEventListener('click', () => {
    const mode = document.querySelector('html').getAttribute('data-bs-theme');
    toggle(mode)
})

function toggle(mode) {
    if (mode === 'light') {
        document.querySelector('html').setAttribute('data-bs-theme', 'dark');
        sessionStorage.setItem('darkMode', 'true');

    } else {
        document.querySelector('html').setAttribute('data-bs-theme', 'light');
        sessionStorage.setItem('darkMode', 'false');
    }

    document.querySelector('.sun-logo').classList.toggle('animate-sun');
    document.querySelector('.moon-logo').classList.toggle('animate-moon');
    if (document.querySelector('.head-logo') !== null) {
        document.querySelector('.head-logo').classList.toggle('invert');
    }

    document.querySelectorAll('[data-bs-theme-value]')
        .forEach(toggle => {
            toggle.setAttribute('data-bs-theme-value', mode);
        })
}