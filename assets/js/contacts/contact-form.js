const contactForm = document.getElementById('contact-form');
const contactNameInput = document.getElementById('contact-name');
const contactSurnameInput = document.getElementById('contact-surname');
const contactEmailInput = document.getElementById('contact-email');
const contactSubjectSelect = document.getElementById('contact-subject');
const contactMessageInput = document.getElementById('contact-message');
const contactStatus = document.getElementById('contact-form-status');

const allowedSubjects = ['order', 'damage', 'custom', 'general'];

function setFieldError(input, message) {
    if (!input) return;
    const errorEl = document.getElementById(input.getAttribute('aria-describedby'));
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.add('active');
    }
    input.classList.add('input-error');
    input.setAttribute('aria-invalid', 'true');
}

function clearFieldError(input) {
    if (!input) return;
    const errorEl = document.getElementById(input.getAttribute('aria-describedby'));
    if (errorEl) {
        errorEl.textContent = '';
        errorEl.classList.remove('active');
    }
    input.classList.remove('input-error');
    input.removeAttribute('aria-invalid');
}

function clearAllErrors() {
    [contactNameInput, contactSurnameInput, contactEmailInput, contactSubjectSelect, contactMessageInput]
        .forEach(clearFieldError);
}

function validateName(value) {
    return value.length > 0 && value.length <= 64 && /^[a-zA-ZàèéìòùÀÈÉÌÒÙ'\s\-]+$/.test(value);
}

function validateEmail(value) {
    if (!value || value.length > 254) return false;
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

function validateContactForm() {
    clearAllErrors();
    let valid = true;

    const name = contactNameInput.value.trim();
    const surname = contactSurnameInput.value.trim();
    const email = contactEmailInput.value.trim();
    const subject = contactSubjectSelect.value;
    const message = contactMessageInput.value.trim();

    if (!validateName(name)) {
        setFieldError(contactNameInput, 'Nome non valido');
        valid = false;
    }
    if (!validateName(surname)) {
        setFieldError(contactSurnameInput, 'Cognome non valido');
        valid = false;
    }
    if (!validateEmail(email)) {
        setFieldError(contactEmailInput, 'Email non valida');
        valid = false;
    }
    if (!allowedSubjects.includes(subject)) {
        setFieldError(contactSubjectSelect, 'Seleziona un argomento');
        valid = false;
    }
    if (message.length < 10 || message.length > 2000) {
        setFieldError(contactMessageInput, 'Il messaggio deve essere lungo tra 10 e 2000 caratteri');
        valid = false;
    }

    return valid;
}

function showStatus(text, kind) {
    if (!contactStatus) return;
    contactStatus.textContent = text;
    contactStatus.classList.remove('status-success', 'status-error');
    if (kind === 'success') contactStatus.classList.add('status-success');
    else if (kind === 'error') contactStatus.classList.add('status-error');
}

async function sendContactMessage(payload) {
    try {
        const response = await fetch('php/contacts/sendMessage.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        return await response.json();
    } catch (error) {
        console.error('Errore durante l\'invio del messaggio:', error);
        return { success: false, message: 'Errore di rete. Riprova più tardi.' };
    }
}

function initContactForm() {
    if (!contactForm) return;
    contactForm.removeAttribute('action');

    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (!validateContactForm()) {
            showStatus('Controlla i campi evidenziati.', 'error');
            return;
        }

        const submitButton = contactForm.querySelector('button[type="submit"]');
        if (submitButton) submitButton.disabled = true;
        showStatus('Invio in corso…', '');

        const payload = {
            name: contactNameInput.value.trim(),
            surname: contactSurnameInput.value.trim(),
            email: contactEmailInput.value.trim(),
            subject: contactSubjectSelect.value,
            message: contactMessageInput.value.trim()
        };

        const result = await sendContactMessage(payload);

        if (submitButton) submitButton.disabled = false;

        if (result && result.success) {
            showStatus(result.message || 'Messaggio inviato.', 'success');
            contactForm.reset();
        } else {
            //campi già compilati non spariscono
            showStatus((result && result.message) || 'Invio non riuscito.', 'error');
        }
    });
}

document.addEventListener('DOMContentLoaded', initContactForm);
