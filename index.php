<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Platform</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/scripts.js" defer></script>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">Products</a></li>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
            <div>
                <input type="text" id="search" placeholder="Search products...">
                <button onclick="searchFunction()">Search</button>
            </div>
        </nav>
    </header>

    <main>
        <h1>Featured Products</h1>
        <section id="featured-products">
            <div class="product">
                <img src="images/product1.jpg" alt="Product 1">
                <h2>Product 1</h2>
                <p>$19.99</p>
            </div>
            <div class="product">
                <img src="images/product2.jpg" alt="Product 2">
                <h2>Product 2</h2>
                <p>$29.99</p>
            </div>
            <div class="product">
                <img src="images/product3.jpg" alt="Product 3">
                <h2>Product 3</h2>
                <p>$39.99</p>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 E-Commerce Platform. All rights reserved.</p>
    </footer>
</body>
</html>