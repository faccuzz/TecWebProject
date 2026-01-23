const searchBox = document.querySelector('.home-search-box');
const searchButton = document.getElementById('home-search-button');
const searchBar = document.querySelector('.home-search-bar');

searchButton.addEventListener('click', (e) => {
    e.preventDefault();

    searchBox.classList.toggle('active');

    if(searchBox.classList.contains('active')){
        searchBar.focus();
    }
});

document.addEventListener('click', (e) => {
    if(!searchBox.contains(e.target) && e.target != searchButton){
        searchBox.classList.remove('active');
        searchBar.value = '';
    }
})