<?php use function EcoDrive\Routing\route ?>

<?php
    $usernameError = $errors["usernameError"] ?? null;
    $emailError = $errors["emailError"] ?? null;
    $passwordError = $errors["passwordError"] ?? null;

    function generateInputGroup($type, $label, $name, $error, $default = "") {
        if (isset($error))
            echo '<div class="input-group error">';
        else
            echo '<div class="input-group">';

        echo "<label for=\"$name\">$label</label>";
        echo "<input type=\"$type\" name=\"$name\" id=\"$name\" placeholder=\"$label\" value=\"$default\" required>";
        echo "</div>";

        if (isset($error))
            echo "<span class=\"error\">$error</span>";
    }
?>

<div class="hero card">
    <h1>Regisztráció</h1>

    <form action="<?= route("register") ?>" method="POST">
    <?= generateInputGroup("text", "Felhasználónév", "username", $usernameError, $providedUsername ?? "") ?>

    <?= generateInputGroup("email", "Email cím", "email", $emailError, $providedEmail ?? "") ?>

    <?= generateInputGroup("password", "Jelszó", "password", $passwordError) ?>

    <?= generateInputGroup("password", "Jelszó megerősítése", "confirmPassword", $passwordError) ?>

    <input type="submit" class="button primary action-section" value="Regisztráció">
</form>
</div>