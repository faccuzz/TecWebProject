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
        passwordInput.addEventListener('input', () => {
            const ps = checkPasswordStrength(passwordInput.value);
            if (ps) updateRequirementsUI(ps.checks);
        });
    }
}

document.addEventListener('DOMContentLoaded', initFormChecker);

function verifyEmail() {
    if (emailInput && emailInput.checkValidity()) return true;
    else return false;
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

function updateRequirementsUI(checks) {
    hideTextError(txtRequirement1);
    hideTextError(txtRequirement2);
    hideTextError(txtRequirement3);
    hideTextError(txtRequirement4);

    if (!checks.length) showTextError(txtRequirement1);
    if (!checks.number) showTextError(txtRequirement2);
    if (!checks.uppercase) showTextError(txtRequirement3);
    if (!checks.special) showTextError(txtRequirement4);
}

function verifyPhoneNumber() {
    if (phoneInputContainer) return phoneInputContainer.isValidNumber();
    else return false;

}

function verifyName(input) {
    if (input) {
        const name = input.value;
        return name.length > 0 && /^[a-zA-Z\s]+$/.test(name)
    }
}

async function grantAccess() {
    const credentials = {
        'email': emailInput.value.toLowerCase(),
        'password': passwordInput.value,
    }
    try {
        const response = await fetch('php/account/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(credentials)
        })
        const data = await response.json();
        return data.success;
    }
    catch (error) {
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
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(accountDetails)
        });

        const data = await response.json();

        return data;

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

    verifyEmail() ? validRegistration++ : showInputError(emailInput, 'Invalid Email');

    if (ps.score === 4) validRegistration++;

    verifyName(nameInput) ? validRegistration++ : showInputError(nameInput, 'Invalid Name');
    verifyName(surnameInput) ? validRegistration++ : showInputError(surnameInput, 'Invalid Surname');

    verifyPhoneNumber() ? validRegistration++ : showInputError(phoneInput, 'Invalid Phone Number');

    if (validRegistration === 5) {
        const isRegistered = await registerUser();

        if (isRegistered && (isRegistered.success === true || isRegistered.success === "true")) {
            if (isRegistered.stayOnPage === true) {
                alert('Admin account created successfully!');
                form.reset();
                if (typeof txtRequirement1 !== 'undefined') {
                    updateRequirementsUI({length: true, number: true, uppercase: true, special: true});
                }
                window.location.href = 'optionsPage.html?section=users';
            } else {
                redirect();
            }
        } else {
            const generalError = document.getElementById('general-register-error');
            if (generalError) {
                generalError.textContent = isRegistered?.message || 'Registration failed. Please try again.';
                generalError.classList.add('active');
            }
        }
    }
}

function showInputError(input, message) {
    input.classList.add('input-error');
    const inputForm = input.closest('.form-group');
    if (inputForm) {
        const errorMessage = inputForm.querySelector('.form-error-msg');
        if (errorMessage) {
            errorMessage.innerText = message;
            errorMessage.classList.add('active');
        }
    }
}
function showTextError(textReference) {
    textReference.style.color = 'var(--error-color)';
}
function hideInputError(input) {
    input.classList.remove('input-error');
    const inputForm = input.closest('.form-group');
    if (inputForm) {
        const errorMessage = inputForm.querySelector('.form-error-msg');
        if (errorMessage) errorMessage.classList.remove('active');
    }
}
function hideTextError(textReference) {
    textReference.style.color = 'var(--text-color)';
}

async function login() {
    hideInputError(emailInput);
    hideInputError(passwordInput);
    if (verifyEmail()) {
        if (await grantAccess()) redirect();
        else {
            showInputError(passwordInput, 'Invalid credentials');
            showInputError(emailInput, 'Invalid credentials');
        }
    }
    else showInputError(emailInput, 'Invalid email address');
}

function redirect() {
    window.location.replace('index.html');
}

