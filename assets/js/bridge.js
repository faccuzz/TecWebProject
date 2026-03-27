async function fetchProducts(category = '', value = '') {
    try {
        let url = './php/getProducts.php';
        if(category !== '') url += `?category=${encodeURIComponent(category)}&value=${encodeURIComponent(value)}`;
        const risposta = await fetch(url);
        
        if (!risposta.ok) {
            throw new Error(`Errore HTTP: ${risposta.status}`);
        }

        const dati = await risposta.json();

        return dati;

    } catch (errore) {
        console.error("Something's wrong:", errore);
    }
}