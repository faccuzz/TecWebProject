document.addEventListener('DOMContentLoaded', () => {
    const path = window.location.pathname;

    if (path.includes('optionsPage.html')) {
        initOptionsPage();
    } else if (path.includes('modifyProduct.html')) {
        getProdotto();
        initModifyProductForm();
    }
});

function initOptionsPage() {
    const menu = document.getElementById('menu-nav');
    const areaContenuto = document.getElementById('contentZone');
    const loader = document.getElementById('page-loader');
    const mainContent = document.getElementById('main-content');
    const adminItems = document.querySelectorAll('.admin-only');

    async function verifyAdmin() {
        try {
            await new Promise(resolve => setTimeout(resolve, 1500));

            const response = await fetch('./php/account/loginCheck.php');
            const data = await response.json();

            if (data.is_admin) {
                adminItems.forEach(item => {
                    item.style.display = 'block';
                });
            } else {
                adminItems.forEach(item => item.remove());
            }
        } catch (error) {
            console.log('Errore di connessione al server');
        } finally {
            mainContent.classList.remove('hidden-content');
            mainContent.classList.add('visible-content');

            loader.classList.add('fade-out');
            loader.setAttribute('aria-hidden', 'true');
        }
    }

    menu.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-section]');
        if (!button) return;
        const sezione = button.getAttribute('data-section');
        const elemento = button.closest('li');
        document.querySelectorAll('#menu-nav li').forEach(li => li.classList.remove('active'));
        elemento.classList.add('active');
        caricaDati(sezione);
    });

    async function caricaDati(sezione) {
        const risposta = await fetch(`./php/optionsPage.php?section=${sezione}`);
        const html = await risposta.text();
        areaContenuto.innerHTML = html;

        switch (sezione) {
            case 'products': activateProductForm(); break;
            case 'users':
                if (typeof initFormChecker === 'function') initFormChecker();
                break;
            case 'security': initSecurityPasswordChecker(); break;
        }
    }

    function activateProductForm() {
        const fileInput = document.getElementById('image-upload');
        const fileNameDisplay = document.getElementById('file-name-display');
        const productForm = document.getElementById('product-upload');

        if (productForm) {
            fileInput.addEventListener('change', async (event) => {
                const file = event.target.files[0];
                if (file) fileNameDisplay.textContent = "File selezionato: " + file.name;
            });

            productForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const formData = new FormData(productForm);

                if (!formData.get('name')) return;
                if (!formData.get('description')) return;
                if (!formData.get('price')) return;
                if (!formData.get('image')) return;
                if (!formData.get('inStock')) return;

                try {
                    const response = await fetch('./php/product/saveProduct.php', {
                        method: 'POST',
                        body: formData,
                    });

                    const result = await response.json();
                    if (result.success) {
                        window.location.href = 'optionsPage.html?section=products';
                    }
                } catch (error) {
                    console.error('Errore di caricamento: ', error);
                }
            });
        }
    }

    verifyAdmin();
}

function getProdotto() {
    const id = new URLSearchParams(window.location.search).get('id');
    document.getElementById('id').value = id;

    fetch(`./php/product/singleInfo.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('name').value = data.productName;
            document.getElementById('price').value = data.price;
            document.getElementById('description').value = data.description;
            document.getElementById('inStock').checked = data.inStock == 1 ? true : false;

            if (data.imageUrl) {
                document.getElementById('file-name-display').textContent = "File selezionato: " + data.imageUrl;
            }
        });
}

function initModifyProductForm() {
    const modifyForm = document.getElementById('product-upload');
    const fileInput = document.getElementById('image-upload');
    const fileNameDisplay = document.getElementById('file-name-display');

    fileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) fileNameDisplay.textContent = file.name;
    });

    modifyForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(modifyForm);
        formData.append('submit', 'true');

        try {
            const response = await fetch('php/product/modifyProduct.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                alert('Prodotto modificato con successo!');
                window.location.href = 'optionsPage.html?section=products';
            } else {
                alert('Errore durante la modifica: ' + result.error);
            }
        } catch (error) {
            console.error('Errore di connessione:', error);
            alert('Impossibile connettersi al server per salvare le modifiche.');
        }
    });
}

function initSecurityPasswordChecker() {
    const form = document.getElementById('change-password-form');
    const newPass = document.getElementById('newPass');
    const confPass = document.getElementById('confPass');
    const errorMsg = document.getElementById('password-error');

    const req1 = document.getElementById('requirement-1');
    const req2 = document.getElementById('requirement-2');
    const req3 = document.getElementById('requirement-3');
    const req4 = document.getElementById('requirement-4');

    if (!newPass || !form) return;

    newPass.addEventListener('input', () => {
        const checks = checkPasswordStrength(newPass.value).checks;
        req1.style.color = checks.length ? 'green' : 'red';
        req2.style.color = checks.number ? 'green' : 'red';
        req3.style.color = checks.uppercase ? 'green' : 'red';
        req4.style.color = checks.special ? 'green' : 'red';
    });

    form.addEventListener('submit', (event) => {
        const result = checkPasswordStrength(newPass.value);
        const isSecure = result.score === 4;

        if (!isSecure) {
            event.preventDefault();
            errorMsg.innerText = "Password does not meet the requirements.";
            errorMsg.style.display = "block";
            return false;
        }

        if (newPass.value !== confPass.value) {
            event.preventDefault();
            errorMsg.innerText = "Passwords do not match.";
            errorMsg.style.display = "block";
            return false;
        }

        errorMsg.style.display = "none";
        return true;
    });
}
