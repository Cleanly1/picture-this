<?php

require __DIR__.'/../autoload.php';

if (!userLoggedIn()){
    $_SESSION['errors'][] = 'Please log in and try again';
    redirect('/');
};
if (isset($_GET['search'])) {

    $searchedUsername = filter_var($_GET['search'], FILTER_SANITIZE_STRING);

    $statement = $pdo->prepare('SELECT id, username, email, avatar_image FROM users WHERE username LIKE :searchedUsername');
    $statement->execute([
        ':searchedUsername' => $searchedUsername . '%',
    ]);

    $users = $statement->fetchAll(PDO::FETCH_ASSOC);
    // Could make proper error and results array that "always" is sent with the request
    if ($users === []) {
        $users = json_encode([
            [
                'error' => 404,
            ]
        ]);
        header('Content-Type: application/json');
        echo $users;
    }else {

        $users = json_encode($users);
        header('Content-Type: application/json');
        echo $users;
    }

}
