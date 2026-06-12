document.addEventListener('DOMContentLoaded', () => {
    const accountBtn = document.getElementById('account-button');
    const accountPopup = document.getElementById('account-popup');

    if (!accountBtn || !accountPopup) return;

    const closePopupBtn = document.getElementById('close-popup-button');

    let isUserLoggedIn = false;

    function getFocusable() {
        return accountPopup.querySelectorAll('a, button');
    }

    async function verifyLogin() {
        try {
            const response = await fetch('./php/account/loginCheck.php');
            const data = await response.json();

            if (data.logged_in) {
                isUserLoggedIn = true;
                accountBtn.setAttribute('aria-label', "Vai all'area personale");
                accountBtn.removeAttribute('aria-haspopup');
                accountBtn.removeAttribute('aria-expanded');
            } else {
                isUserLoggedIn = false;
            }
        } catch (error) {
            console.error("Errore nel login check:", error);
        }
    }

    function openMenu() {
        accountPopup.classList.add('active');
        accountBtn.setAttribute('aria-expanded', 'true');
        const firstLink = accountPopup.querySelector('a.popup-link');
        if (firstLink) firstLink.focus();
        else {
            const focusable = getFocusable();
            if (focusable[0]) focusable[0].focus();
        }
    }

    function closeMenu({ returnFocus = true } = {}) {
        accountPopup.classList.remove('active');
        accountBtn.setAttribute('aria-expanded', 'false');
        if (returnFocus) accountBtn.focus();
    }

    accountBtn.addEventListener('click', (e) => {
        if (isUserLoggedIn) {
            window.location.href = './optionsPage.html';
            return;
        }
        e.stopPropagation();
        if (accountPopup.classList.contains('active')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    if (closePopupBtn) {
        closePopupBtn.addEventListener('click', () => closeMenu());
    }

    document.addEventListener('click', (e) => {
        if (
            accountPopup.classList.contains('active') &&
            !accountPopup.contains(e.target) &&
            e.target !== accountBtn
        ) {
            closeMenu({ returnFocus: false });
        }
    });

    accountPopup.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMenu();
            return;
        }

        // Tengo il focus dentro al popup (Tab e Shift+Tab fanno il giro)
        if (e.key === 'Tab') {
            const focusable = Array.from(getFocusable());
            if (focusable.length === 0) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });

    verifyLogin();
});
