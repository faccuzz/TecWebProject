let emailInput, passwordInput, nameInput, surnameInput, phoneInput;
let form, txtRequirement1, txtRequirement2, txtRequirement3, txtRequirement4;
let phoneInputContainer;

function initFormChecker() {
    emailInput = document.getElementById('email-input');
    passwordInput = document.getElementById('password-input');
    nameInput = document.getElementById('name-input');
    surnameInput = document.getElementById('surname-input');
    phoneInput = document.getElementById('phone-input');
    form = document.getElementById('access-form');

    txtRequirement1 = document.getElementById('requirement-1');
    txtRequirement2 = document.getElementById('requirement-2');
    txtRequirement3 = document.getElementById('requirement-3');
    txtRequirement4 = document.getElementById('requirement-4');

    if (phoneInput && window.intlTelInput) {
        try {
            phoneInputContainer = window.intlTelInput(phoneInput, {
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
                preferredCountries: ["it", "fr", "de", "gb"],
                separateDialCode: true,
                dropdownContainer: document.body,
                initialCountry: "it",
            });

            // Patch a11y: la libreria mette aria-activedescendant verso un id
            // che non esiste finché il dropdown è chiuso → violazione 4.1.2.
            // Lo rimuoviamo finché il dropdown non è aperto.
            const selectedFlag = phoneInput.closest('.iti')?.querySelector('.iti__selected-flag');
            if (selectedFlag) {
                selectedFlag.removeAttribute('aria-activedescendant');
                selectedFlag.removeAttribute('aria-owns');
                selectedFlag.setAttribute('role', 'button');
                // Niente aria-label: lasciamo che il nome accessibile derivi
                // dal title (es. "Italy (Italia): +39") che corrisponde al testo visibile.
                // Quando l'utente apre la lista, la libreria ri-assegna aria-activedescendant
                // verso l'elemento corrente; va ripulito quando si richiude.
                const observer = new MutationObserver(() => {
                    const isOpen = selectedFlag.getAttribute('aria-expanded') === 'true';
                    if (!isOpen) {
                        selectedFlag.removeAttribute('aria-activedescendant');
                    }
                });
                observer.observe(selectedFlag, { attributes: true, attributeFilter: ['aria-expanded'] });
            }
        } catch (err) {
            console.error("Errore caricamento intlTelInput:", err);
        }
    }

    if (form) {
        form.removeAttribute('action');
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            e.stopPropagation();
            nameInput ? register() : login();
        });
    }

    if (passwordInput && txtRequirement1) {
        // Stato iniziale: tutti i requisiti non soddisfatti
        updateRequirementsUI({ length: false, number: false, uppercase: false, special: false });
        passwordInput.addEventListener('input', () => {
            const ps = checkPasswordStrength(passwordInput.value);
            if (ps) updateRequirementsUI(ps.checks);
        });
    }
}

document.addEventListener('DOMContentLoaded', initFormChecker);

function verifyEmail() {
    return !!(emailInput && emailInput.checkValidity());
}

function checkPasswordStrength(password) {
    const checks = {
        length: password.length >= 8,
        number: /[0-9]/.test(password),
        uppercase: /[A-Z]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    let score = 0;
    if (checks.length) score++;
    if (checks.number) score++;
    if (checks.uppercase) score++;
    if (checks.special) score++;
    return { score, checks };
}

function setRequirementState(el, met) {
    if (!el) return;
    el.classList.toggle('requirement-met', met);
    el.classList.toggle('requirement-unmet', !met);
    // Annuncio per screen reader: testo originale + stato
    const baseText = el.dataset.baseText || el.textContent.replace(/^[✓✗]\s*/, '');
    el.dataset.baseText = baseText;
    el.setAttribute('aria-label', `${baseText}: ${met ? 'soddisfatto' : 'non soddisfatto'}`);
}

function updateRequirementsUI(checks) {
    setRequirementState(txtRequirement1, checks.length);
    setRequirementState(txtRequirement2, checks.number);
    setRequirementState(txtRequirement3, checks.uppercase);
    setRequirementState(txtRequirement4, checks.special);
}

function verifyPhoneNumber() {
    if (phoneInputContainer) return phoneInputContainer.isValidNumber();
    return false;
}

function verifyName(input) {
    if (input) {
        const name = input.value;
        return name.length > 0 && /^[a-zA-ZàèéìòùÀÈÉÌÒÙ'\s\-]+$/.test(name);
    }
    return false;
}

async function grantAccess() {
    const credentials = {
        'email': emailInput.value.toLowerCase(),
        'password': passwordInput.value,
    };
    try {
        const response = await fetch('php/account/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(credentials)
        });
        const data = await response.json();
        return data.success;
    } catch (error) {
        console.log('Errore durante il login', error);
        return false;
    }
}

async function registerUser() {
    const isAdminField = form.querySelector('input[name="isAdmin"]');
    const isAdminValue = isAdminField ? parseInt(isAdminField.value) : 0;

    const accountDetails = {
        'username': emailInput.value.split('@')[0],
        'email': emailInput.value,
        'password': passwordInput.value,
        'name': nameInput.value,
        'surname': surnameInput.value,
        'phone': phoneInput.value,
        'isAdmin': isAdminValue,
    };

    try {
        const response = await fetch('php/account/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(accountDetails)
        });
        return await response.json();
    } catch (error) {
        console.error("Errore durante la registrazione:", error);
        return false;
    }
}

async function register() {
    hideInputError(emailInput);
    hideInputError(passwordInput);
    hideInputError(nameInput);
    hideInputError(surnameInput);
    hideInputError(phoneInput);

    let validRegistration = 0;
    const ps = checkPasswordStrength(passwordInput.value);

    verifyEmail() ? validRegistration++ : showInputError(emailInput, 'Email non valida');

    if (ps.score === 4) validRegistration++;
    else showInputError(passwordInput, 'La password non soddisfa tutti i requisiti');

    verifyName(nameInput) ? validRegistration++ : showInputError(nameInput, 'Nome non valido');
    verifyName(surnameInput) ? validRegistration++ : showInputError(surnameInput, 'Cognome non valido');

    verifyPhoneNumber() ? validRegistration++ : showInputError(phoneInput, 'Numero di telefono non valido');

    if (validRegistration === 5) {
        const isRegistered = await registerUser();

        if (isRegistered && (isRegistered.success === true || isRegistered.success === "true")) {
            if (isRegistered.stayOnPage === true) {
                showGeneralStatus('Account amministratore creato con successo.', 'success');
                form.reset();
                updateRequirementsUI({ length: false, number: false, uppercase: false, special: false });
                setTimeout(() => { window.location.href = 'optionsPage.html?section=users'; }, 1500);
            } else {
                redirect();
            }
        } else {
            showGeneralStatus(
                (isRegistered && isRegistered.message) || 'Registrazione fallita. Riprova.',
                'error'
            );
        }
    } else {
        // Sposto il focus sul primo campo in errore per non lasciare l'utente "perso"
        const firstError = form.querySelector('.input-error');
        if (firstError) firstError.focus();
    }
}

function showGeneralStatus(message, kind = 'info') {
    const el = document.getElementById('general-register-error');
    if (!el) return;
    el.textContent = message;
    el.classList.add('active');
    el.style.color = kind === 'success' ? 'var(--success-color)' : '';
}

function showInputError(input, message) {
    if (!input) return;
    input.classList.add('input-error');
    input.setAttribute('aria-invalid', 'true');
    const inputForm = input.closest('.form-group');
    if (inputForm) {
        const errorMessage = inputForm.querySelector('.form-error-msg');
        if (errorMessage) {
            errorMessage.innerText = message;
            errorMessage.classList.add('active');
        }
    }
}

function hideInputError(input) {
    if (!input) return;
    input.classList.remove('input-error');
    input.removeAttribute('aria-invalid');
    const inputForm = input.closest('.form-group');
    if (inputForm) {
        const errorMessage = inputForm.querySelector('.form-error-msg');
        if (errorMessage) {
            errorMessage.classList.remove('active');
            errorMessage.textContent = '';
        }
    }
}

async function login() {
    hideInputError(emailInput);
    hideInputError(passwordInput);
    if (verifyEmail()) {
        if (await grantAccess()) redirect();
        else {
            showInputError(passwordInput, 'Credenziali non valide');
            showInputError(emailInput, 'Credenziali non valide');
            emailInput.focus();
        }
    } else {
        showInputError(emailInput, 'Indirizzo email non valido');
        emailInput.focus();
    }
}

function redirect() {
    window.location.replace('index.html');
}
