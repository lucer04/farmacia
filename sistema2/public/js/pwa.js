const installButtons = document.querySelectorAll('[data-pwa-install]');
let installPrompt = null;

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    installPrompt = event;
    installButtons.forEach((button) => button.classList.remove('d-none'));
});

installButtons.forEach((button) => {
    button.addEventListener('click', async () => {
        if (!installPrompt) {
            return;
        }

        installPrompt.prompt();
        await installPrompt.userChoice;
        installPrompt = null;
        installButtons.forEach((installButton) => installButton.classList.add('d-none'));
    });
});

window.addEventListener('appinstalled', () => {
    installPrompt = null;
    installButtons.forEach((button) => button.classList.add('d-none'));
});

if ('serviceWorker' in navigator) {
    const serviceWorkerUrl = document.querySelector('meta[name="service-worker"]')?.content;

    if (serviceWorkerUrl) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register(serviceWorkerUrl).catch((error) => {
                console.error('No se pudo registrar el Service Worker de Farmacia San Juan.', error);
            });
        });
    }
}
