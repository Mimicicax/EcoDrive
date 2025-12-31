<?php

namespace EcoDrive\Helpers\Internal {
    function monthNames() {
        static $names  = [
            "január",
            "február",
            "március",
            "április",
            "május",
            "június",
            "július",
            "augusztus",
            "szeptember",
            "október",
            "november",
            "december"
        ];

        return $names;
    }
}

namespace EcoDrive\Helpers {

function monthName(\DateTimeImmutable $datetime) {
    return \EcoDrive\Helpers\Internal\monthNames()[$datetime->format("n") - 1];
}

function dateTimeToLocalString(\DateTimeImmutable $datetime) {
    return $datetime->format('Y') .  " " . monthName($datetime) . " " . $datetime->format("d") . ", " . $datetime->format("H:i");
}

}