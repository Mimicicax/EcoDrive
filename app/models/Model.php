<?php

namespace EcoDrive\Models;

abstract class Model {
    public abstract function modelEscaped(): Model;
}