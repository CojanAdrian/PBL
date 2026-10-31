import Alpine from 'alpinejs';

const COOKIE_CONSENT_KEY = 'cookie-consent';

Alpine.data('cookieConsent', () => ({
    open: false,

    init() {
        if (!localStorage.getItem(COOKIE_CONSENT_KEY)) {
            setTimeout(() => (this.open = true), 1500);
        }
    },

    accept() {
        localStorage.setItem(COOKIE_CONSENT_KEY, new Date().toISOString());
        this.open = false;
    },

    dismiss() {
        this.open = false;
    },
}));

window.Alpine = Alpine;
Alpine.start();
