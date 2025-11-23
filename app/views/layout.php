<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href=<?= asset("style.css") ?> rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@100..900&display=swap" rel="stylesheet">
    <title>
        <?= isset($title) ? $title . " | EcoDrive" : "EcoDrive" ?>
    </title>
</head>
<body>
    <main>
        <?php include $_pageContent ?>
    </main>
</body>
</html>