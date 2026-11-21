const privacyDialog = document.getElementById('registrar-privacy-policy');
const privacyTrigger = document.querySelector('[data-open-privacy-policy]');

if (typeof privacyDialog?.showModal === 'function' && privacyTrigger) {
    privacyTrigger.addEventListener('click', () => privacyDialog.showModal());

    const sessionKey = 'registrar-privacy-policy-shown';
    let showOnLoad = true;

    try {
        showOnLoad = sessionStorage.getItem(sessionKey) === null;
    } catch {
        // The dialog can still be used when browser storage is unavailable.
    }

    if (showOnLoad) {
        privacyDialog.showModal();

        try {
            sessionStorage.setItem(sessionKey, '1');
        } catch {
            // No session marker can be saved when browser storage is unavailable.
        }
    }
}
