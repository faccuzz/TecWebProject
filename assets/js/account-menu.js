document.addEventListener('DOMContentLoaded', () => {
    const accountBtn = document.getElementById('account-button');
    const accountPopup = document.getElementById('account-popup');

    if (!accountBtn || !accountPopup) return;

    //Elementi clicabili all'interno del popup
    //firstElement === closePopupButton, distinzione: firstElement x posizione, closePopupButton x funzione
    const closePopupBtn = document.getElementById('close-popup-button');
    
    const focusableElements = accountPopup.querySelectorAll('a, button');
    const firstElement = focusableElements[0]; 
    const lastElement = focusableElements[focusableElements.length - 1];
    
    let isUserLoggedIn = false;

    async function verifyLogin() {
        try {
            const response = await fetch('./php/account-managing/loginCheck.php');
            const data = await response.json();

            if (data.logged_in) {
                isUserLoggedIn = true;
                
                accountBtn.removeAttribute('aria-haspopup');
                accountBtn.removeAttribute('aria-expanded');
            } else {
                isUserLoggedIn = false;
            }

        } catch (error) {
            console.error("Errore nel login check:", error);
        }
    }

    function toggleAccountMenu() {
        const isActive = accountPopup.classList.toggle('active');

        accountBtn.setAttribute('aria-expanded', isActive);

        if (isActive) {
            focusableElements[1].focus();
        } else {
            accountBtn.focus();
        }
    }

    accountBtn.addEventListener('click', (e) => {
        if (isUserLoggedIn) {
            //Se loggato apre impostazioni, altrimenti il popup
            window.location.href = './optionsPage.html'; 
        } else {
            //Permette di non propagare l'evento verso l'alto, evita che il click per aprire chiuda anche il popup stesso
            e.stopPropagation();
            toggleAccountMenu();
        }
    });
    

    closePopupBtn.addEventListener('click', toggleAccountMenu);

    //Chiudi cliccando fuori dal pop up
    document.addEventListener('click', (e) => {
        if (accountPopup.classList.contains('active') && !accountPopup.contains(e.target)) {
            toggleAccountMenu();
        }
    });

    //Focus Trap per accessibilità
    accountPopup.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            toggleAccountMenu();
            return;
        }

        if (e.key === 'Tab') {
            if (document.activeElement === lastElement) {
                e.preventDefault();
                firstElement.focus();
            }
        }
    });
    //Verifica se utente già loggato tramite sessione
    verifyLogin();
});