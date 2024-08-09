<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    header('Location: index.php');
    exit;
}

require_once 'classes/Database.php';
require_once 'classes/UserStats.php';

$userStats = UserStats::getStats($_SESSION['user_id']);

// Initialize default values if no stats are found
if ($userStats === false) {
    $userStats = [
        'games_against_stockfish' => 0,
        'wins_against_stockfish' => 0,
        'losses_against_stockfish' => 0,
        'draws_against_stockfish' => 0,
        'games_against_players' => 0,
        'wins_against_players' => 0,
        'losses_against_players' => 0,
        'draws_against_players' => 0,
        'rating' => 0
    ];
}
?>

<?php include 'templates/header.php'; ?>
<div class="container mt-5">
    <h1 class="text-center">Your Statistics</h1>
    <table class="table table-bordered">
        <tr>
            <th colspan="2" class="text-center">Against Stockfish</th>
        </tr>
        <tr>
            <th>Games Played</th>
            <td><?php echo $userStats['games_against_stockfish']; ?></td>
        </tr>
        <tr>
            <th>Games Won</th>
            <td><?php echo $userStats['wins_against_stockfish']; ?></td>
        </tr>
        <tr>
            <th>Games Lost</th>
            <td><?php echo $userStats['losses_against_stockfish']; ?></td>
        </tr>
        <tr>
            <th>Games Drawn</th>
            <td><?php echo $userStats['draws_against_stockfish']; ?></td>
        </tr>
        <tr>
            <th colspan="2" class="text-center">Against Online Players</th>
        </tr>
        <tr>
            <th>Games Played</th>
            <td><?php echo $userStats['games_against_players']; ?></td>
        </tr>
        <tr>
            <th>Games Won</th>
            <td><?php echo $userStats['wins_against_players']; ?></td>
        </tr>
        <tr>
            <th>Games Lost</th>
            <td><?php echo $userStats['losses_against_players']; ?></td>
        </tr>
        <tr>
            <th>Games Drawn</th>
            <td><?php echo $userStats['draws_against_players']; ?></td>
        </tr>
        <tr>
            <th>Rating</th>
            <td><?php echo $userStats['rating']; ?></td>
        </tr>
    </table>
</div>
<?php include 'templates/footer.php'; ?>