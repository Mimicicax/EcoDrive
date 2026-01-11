<?php

namespace EcoDrive\Helpers;
use EcoDrive\Models\User;

use function EcoDrive\Environment\appConfig;

require_once "config.php";
require_once appConfig()->APP_ROOT . "/models/User.php";

class UserDataValidationError {
    const USERNAME_LENGTH_ERROR = "A felhasználónév minimum 1, maximum 50 karakterből állhat";
    const USERNAME_CODE_POINT_ERROR = "Érvénytelen karakter a felhasználónévben";
    const USERNAME_TAKEN_ERROR = "A felhasználónév már foglalt";
    const INVALID_EMAIL_ERROR = "Az email cím formátuma helytelen";
    const EMAIL_TAKEN_ERROR = "Az email cím már foglalt";
    const PASSWORD_ERROR = "A jelszónak legalább 8 karakterből kell állnia és tartalmaznia kell legalább egy nagybetűt és számot";
    const PASSWORD_MISMATCH_ERROR = "A jelszavak nem egyeznek";
}

function validateUsername(string $username): bool|string {
    // Minimum 1, maximum 50 karakter
    $username = trim($username);

    if (strlen($username) < 1 || strlen($username) > 50)
        return UserDataValidationError::USERNAME_LENGTH_ERROR;  

    // Látható Unicode, egyszerűbb emojik és zászlók
    if (preg_match("/^(\p{L}|\p{M}|\p{N}|\p{P}|\p{S}|\p{Zs}|\x{1F320}-\x{1FAFF}|(\x{1F1E6}-\x{1F1FF}){2})*$/u", $username) !== 1)
        return UserDataValidationError::USERNAME_CODE_POINT_ERROR;   

    // Nem tartalmazhat '@'-ot
    if (str_contains($username, '@'))
        return UserDataValidationError::USERNAME_CODE_POINT_ERROR;   

    // Szabadnak kell lennie
    if (User::exists($username, User::FIND_BY_USERNAME))
        return UserDataValidationError::USERNAME_TAKEN_ERROR;   

    return false;
}

function validateEmail(string $email, bool $performExistenceCheck = true): bool|string {    
    // Helyes formátum
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        return UserDataValidationError::INVALID_EMAIL_ERROR;    

    // Nem használhatja más
    if ($performExistenceCheck && User::exists($email, User::FIND_BY_EMAIL))
        return UserDataValidationError::EMAIL_TAKEN_ERROR;  

    return false;
}

function validatePassword(string $pass, string $confirmPass): bool|string {
    // Minimum 8 bájt, egy nagybetű és szám. Túl hosszú jelszó nem okoz problémát
    if (strlen($pass) < 8 || preg_match("/\p{Lu}|\p{N}/u", $pass) !== 1)
        return UserDataValidationError::PASSWORD_ERROR;  
    
    // Nem egyeznek a jelszavak
    if ($pass != $confirmPass)
        return UserDataValidationError::PASSWORD_MISMATCH_ERROR;    

    return false;
}