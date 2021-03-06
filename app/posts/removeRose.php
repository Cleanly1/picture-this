<?php

/*
 * This file is part of Yrgo.
 *
 * (c) Yrgo, högre yrkesutbildning.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__.'/../autoload.php';

if (!userLoggedIn()) {
    $_SESSION['errors'][] = 'Please log in and try again';
    redirect('/');
}
if (isset($_POST['rose'])) {
    $postId = filter_var($_POST['rose'], FILTER_SANITIZE_NUMBER_INT);

    $statement = $pdo->prepare('DELETE FROM roses WHERE post_id = :post_id AND user_id = :user_id');
    $statement->execute([
        ':user_id' => $_SESSION['user']['id'],
        ':post_id' => $postId,
    ]);

    if (!$statement) {
        die(var_dump($pdo->errorInfo()));
    }

    updateRose($pdo, $postId, countRoses($pdo, $postId));

    $roses = countRoses($pdo, $postId);
    $roses = json_encode($roses);
    header('Content-Type: application/json');
    echo $roses;
}
