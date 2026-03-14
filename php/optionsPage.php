<?php 
    include_once 'db.php';
    header('Content-Type: application/json');
    error_reporting(0);

    session_start();
    if(isset($_SESSION['email']) && $_SESSION['email'] !== 'admin@gmail.com'){
        header("Location: ../index.html");
        exit();
    }
    
    $db = new database();
    $db->connect();
    $section = $_GET['section'] ?? '';
    switch($section){
        case 'products':
            renderProducts($db);
            break;
        case 'configurations':
            renderConfig($db);
            break;
        case 'wishlist':
            renderWishlist($db);
            break;
        case 'security':
            renderSecurity($db);
            break;
    }
    $db->close();

    function renderProducts($db){
        echo "<h2>Buyed Products</h2>";
        /*$sql = "SELECT * FROM products";
        $result = $db->query($sql);
        $products = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        echo json_encode($products);*/
    }

    function renderConfig($db){
        echo "<h2>Account Configurations</h2>";
    }

    function renderWishlist($db){
        echo "<h2>Wishlist</h2>";
    }

    function renderSecurity($db){
        echo "<h2>Security</h2>";
    }
?>
