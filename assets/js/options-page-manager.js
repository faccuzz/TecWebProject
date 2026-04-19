document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.includes('optionsPage.html')) {
        const menu = document.getElementById('menu-nav');
        const areaContenuto = document.getElementById('contentZone');
        const loader = document.getElementById('page-loader');
        const mainContent = document.getElementById('main-content');
        const adminItems = document.querySelectorAll('.admin-only');

        async function verifyAdmin() {
            try {
                //Fa durare l'animazione di caricamento almeno un secondo e mezzo
                await new Promise(resolve => setTimeout(resolve, 1500));

                const response = await fetch('./php/account-managing/loginCheck.php');
                const data = await response.json();

                if (data.is_admin) {
                    adminItems.forEach(item => {
                        item.style.display = 'block';
                    });
                }
                else adminItems.forEach(item => item.remove()); //Rimuovo dall'HTML gli elementi admin
            }
            catch (error) {
                console.log('Errore di connessione al server')
            }
            finally {
                mainContent.classList.remove('hidden-content');
                mainContent.classList.add('visible-content');

                loader.classList.add('fade-out');
            }
        }

        menu.addEventListener('click', (event) => {
            const elemento = event.target.closest('li');
            const sezione = elemento.getAttribute('sessionOp');
            document.querySelectorAll('#menu-nav li').forEach(li => li.classList.remove('active'));
            elemento.classList.add('active');

            caricaDati(sezione);
        })

        async function caricaDati(sezione) {
            const risposta = await fetch(`./php/optionsPage.php?section=${sezione}`);
            const html = await risposta.text();
            areaContenuto.innerHTML = html;

            switch (sezione) {
                case 'products': activateProductForm(); break;
            }
        }

        function activateProductForm() {
            const fileInput = document.getElementById('image-upload');
            const fileNameDisplay = document.getElementById('file-name-display');

            const productForm = document.getElementById('product-upload');

            //Form di aggiunta nuovo prodotto
            if (productForm) {
                //Cambia solo il testo, quando viene selezionato un file, con il suo nome
                fileInput.addEventListener('change', async (event) => {
                    const file = event.target.files[0];

                    if (file)
                        fileNameDisplay.textContent = "File selezionato: " + file.name;

                });

                productForm.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const formData = new FormData(productForm);

                    //TODO: segnalare nel form la mancanza del dato
                    if (!formData.get('name')) return;
                    if (!formData.get('description')) return;
                    if (!formData.get('price')) return;
                    if (!formData.get('image')) return;
                    if (!formData.get('inStock')) return;

                    try {
                        const response = await fetch('./php/saveProduct.php', {
                            method: 'POST',
                            body: formData,
                        })

                        const result = await response.json();
                        if (result.success) {
                            console.log('Immagine salvata con successo');
                        }
                    }
                    catch (error) {
                        console.error('Errore di caricamento: ', error);
                    }
                })
            }
        }

        verifyAdmin();

    }
})