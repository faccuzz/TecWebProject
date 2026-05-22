document.addEventListener('DOMContentLoaded', () => {
    const addToWishlistButton = document.getElementById('addToWishlistButton');

    addToWishlistButton.addEventListener('click', async (e) => {
        e.preventDefault();

        const parameters = new URLSearchParams(window.location.search);
        const productId = parameters.get('id'); 

        const data = await fetch('php/wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: productId })

        });
        addToWishlistButton.innerHTML = '<i class="fa-solid fa-heart" aria-hidden="true"></i>';
    });
});