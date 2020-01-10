<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/modern-normalize@0.4.0/modern-normalize.min.css">
        <link rel="stylesheet" href="style.css">

        <title>DS Mediatheken</title>
    </head>
    <body>
        <div class="navbar">
            <a href="index.php" class="navbar-title">
                DS Mediatheken
            </a>
            <form class="navbar-form" action="index.php" method="GET">
                <input
                    class="navbar-form__input"
                    type="text"
                    name="url"
                    placeholder="URL"
                    value="<?php echo $url; ?>"
                    required
                    autofocus
                />
                <button class="navbar-form__button" type="submit">Go</button>
            </form>
        </div>
