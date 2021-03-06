<?php

/*
 * This file is part of Yrgo.
 *
 * (c) Yrgo, högre yrkesutbildning.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

require __DIR__.'/../autoload.php';

if (!userLoggedIn()) {
    $_SESSION['errors'][] = 'Please log in and try again';
    redirect('/');
}

if (isset($_POST['bio'],$_SESSION['user'])) {
    $updatedBio = filter_var($_POST['bio'], FILTER_SANITIZE_STRING);

    preg_match_all("/(\n)/", $updatedBio, $matches);
    $totalLines = count($matches[0]) + 1;

    if ($totalLines > 6 || strlen($updatedBio) - $totalLines > 255) {
        $_SESSION['errors'][] = 'Your text is tooooo long';
    }
    if (empty($_SESSION['errors'])) {
        $statement = $pdo->prepare('UPDATE users SET biography = :biography WHERE id = :id');
        $statement->execute([
            ':biography' => $updatedBio,
            ':id'        => $_SESSION['user']['id'],
        ]);
        $_SESSION['user']['biography'] = $updatedBio;
        $_SESSION['success'][] = 'Your bio has been updated';
        // if (!$hej) {
        //     die(var_dump($pdo->errorInfo()));
        // }
    }
    redirect('/settings.php');
}

if (isset($_FILES['avatar'],$_SESSION['user'])) {
    $avatar = $_FILES['avatar'];

    if ($avatar['type'] != 'image/png' && $avatar['type'] != 'image/jpg' && $avatar['type'] != 'image/jpeg') {
        $_SESSION['errors'][] = 'The image file type is not allowed.';
    }

    if ($avatar['size'] > 2097152) {
        $_SESSION['errors'][] = 'The uploaded file exceeded the file size limit.';
    }

    if (!isset($_SESSION['errors'])) {
        if ($avatar['type'] == 'image/jpg' || $avatar['type'] == 'image/jpeg') {
            $avatarPath = '/uploads/'.uniqid().'-avatar.jpg';
        } else {
            $avatarPath = '/uploads/'.uniqid().'-avatar.png';
        }
        move_uploaded_file($avatar['tmp_name'], '../..'.$avatarPath);
        $statement = $pdo->prepare('UPDATE users SET avatar_image = :avatar_image WHERE id = :id');
        $statement->execute([
            ':avatar_image' => $avatarPath,
            ':id'           => $_SESSION['user']['id'],
        ]);
        if ($_SESSION['user']['avatar_image'] != '/uploads/default-avatar.png') {
            unlink('../..'.$_SESSION['user']['avatar_image']);
        }
        $_SESSION['user']['avatar_image'] = $avatarPath;
        $_SESSION['success'][] = 'Your avatar has been changed';
        redirect('/profile.php?username='.$_SESSION['user']['username']);
    }
}

if (isset($_POST['oldPassword'], $_POST['newPassword'],$_POST['repeatNewPassword'],$_SESSION['user'])) {
    $userData = getUserData($pdo);

    if (strlen($_POST['newPassword']) < 5) {
        $_SESSION['errors'][] = 'Your password needs to be atleast 5 characters long';
    }
    if (!password_verify($oldPassword, $userData['password'])) {
        $_SESSION['errors'][] = 'You entered the wrong password';
    }
    if ($_POST['newPassword'] !== $_POST['repeatNewPassword']) {
        $_SESSION['errors'][] = 'Your new password didn\'t match';
    }
    if (password_verify($_POST['newPassword'], $userData['password'])) {
        $_SESSION['errors'][] = 'You can\'t pick the same password.';
    }
    if (empty($_SESSION['errors'])) {
        $statement = $pdo->prepare('UPDATE users SET password = :newPassword WHERE id = :id');
        $statement->execute([
            ':newPassword' => password_hash($_POST['newPassword'], PASSWORD_BCRYPT),
            ':id'          => $_SESSION['user']['id'],
        ]);
        $_SESSION['success'][] = 'Your password was successfully changed';
    }

    redirect('/settings.php');
}

if (isset($_POST['oldEmail'], $_POST['newEmail'], $_POST['password'], $_SESSION['user'])) {
    $userData = getUserData($pdo);
    $newEmail = filter_var($_POST['newEmail'], FILTER_SANITIZE_EMAIL);
    $oldEmail = filter_var($_POST['oldEmail'], FILTER_SANITIZE_EMAIL);

    if (!password_verify($_POST['password'], $userData['password'])) {
        $_SESSION['errors'][] = 'You entered the wrong password';
    }

    if ($newEmail === $userData['email']) {
        $_SESSION['errors'][] = 'No need to change to the same email';
    }

    if ($oldEmail !== $userData['email']) {
        $_SESSION['errors'][] = 'Old email is not a match';
    }

    if (empty($_SESSION['errors']) && password_verify($_POST['password'], $userData['password'])) {
        $statement = $pdo->prepare('UPDATE users SET email = :newEmail WHERE id = :id');
        $statement->execute([
            ':newEmail' => $newEmail,
            ':id'       => $_SESSION['user']['id'],
        ]);
        $_SESSION['success'][] = 'Your email has been updated';
    }

    redirect('/settings.php');
}

redirect('/');
