<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    header('Location: index.php');
    exit;
}

// Function to get all images from the ./img/players directory
function getPlayerImages($dir) {
    $images = [];
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $images[] = $dir . '/' . $file;
                }
            }
            closedir($dh);
        }
    }
    return $images;
}

$playerImages = getPlayerImages('./img/players');
?>

<?php include 'templates/header.php'; ?>
<div class="container mt-5">
    <h1 class="text-center">User Dashboard</h1>
    <p class="text-center">Here you can view your stats and play games.</p>
    
    <!-- Bootstrap Carousel -->
    <div id="carouselExampleIndicators" class="carousel slide smaller-carousel" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($playerImages as $index => $image): ?>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($playerImages as $index => $image): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <img src="<?= $image ?>" class="d-block w-100" alt="Slide <?= $index + 1 ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <div class="row mt-5 justify-content-center">
        <div class="col-md-4">
            <a href="user_stats.php" class="btn btn-secondary btn-block">View Your Statistics</a>
        </div>
        <div class="col-md-4">
            <a href="play_game.php" class="btn btn-primary btn-block">Play a Game</a>
        </div>
    </div>
</div>
<?php include 'templates/footer.php'; ?>

<!-- Include Bootstrap 5 JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>

<style>
    .smaller-carousel {
        max-width: 600px; /* Adjust the width as needed */
        margin: 0 auto; /* Center the carousel */
    }

    .smaller-carousel .carousel-inner {
        height: 300px; /* Adjust the height as needed */
    }

    .smaller-carousel .carousel-inner img {
        width: 100%;
        height: 100%;
        object-fit: contain; /* Ensure the image fits within the area without being cut off */
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(0, 0, 0, 0.5); /* Add a background color to make the arrows visible */
        border-radius: 50%; /* Optional: make the arrows circular */
    }
</style>