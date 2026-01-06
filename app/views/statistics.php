<?php

    use function EcoDrive\Environment\appConfig;
    use function EcoDrive\Helpers\monthName;

    require_once appConfig()->APP_ROOT . "/helpers/DateTimeToLocal.php";

    function roundStatInfo(float $num) {
        return round($num, 2);
    }

    function echoDeviation($dev, bool $decreaseIsPositive = true) {
        $devPercent = roundStatInfo($dev * 100);
        $class = "";
        $prefix = "";
        $arrow = "";

        if ($devPercent < 100) {
            $class = $decreaseIsPositive ? "positive-text" : "negative-text";
            $devPercent = 100 - $devPercent;
            $prefix = "-";

            $arrow = "<i class='fa-solid fa-arrow-down'></i>";

        } else if ($devPercent == 100) {
            $class = "neutral-text";
            $devPercent = 0;

        } else {
            $class = $decreaseIsPositive ? "negative-text" : "positive-text";
            $devPercent -= 100;
            $prefix = "+";
        
            $arrow = "<i class='fa-solid fa-arrow-up'></i>";
        }

        echo "<span class='$class'>$prefix$devPercent%$arrow</span>";
    }
?>

<h1>Statisztika</h1>

<?php if (!isset($stats)): ?>

<div class="empty card-container">
    <span>
        <h1>Nincsenek adatok erre az évre</h1>
        <p>Mentsd el a megtett utaidat és az adatok itt fognak megjelenni.</p>
    </span>
</div>

<?php else: ?>
<div class="main-stat-container">
   
    <div class="card">
        <h2>Havi CO<sub>2</sub>-kibocsátás</h2>

        <div class="stat-columns">
            <div class="stat-grid">
                <p>Ebben a hónapban:</p>
                <p>
                    <?php if ($stats["previousMonthEmission"] == 0): ?>
                        <?= roundStatInfo($stats["monthlyEmission"] / 1000.0) ?> kg
                    <?php else: ?>
                        <?= roundStatInfo($stats["monthlyEmission"] / 1000.0) ?> kg (<?php echoDeviation($stats["monthlyEmission"] / $stats["previousMonthEmission"]) ?>)
                    <?php endif ?>
                </p>

                <?php if ($stats["previousYearMonthlyEmission"] != 0): ?>
                    <p>Tavaly ebben a hónapban:</p>
                    <p>
                        <?= roundStatInfo($stats["previousYearMonthlyEmission"] / 1000.0) ?> kg (<?php echoDeviation($stats["monthlyEmission"] / $stats["previousYearMonthlyEmission"]); ?>)
                    </p>
                <?php endif ?>

                <p>Eltérés az EU átlagtól:</p>
                <p>
                    <?php echoDeviation($stats["monthlyEmission"] / $stats["averageEUMonthlyCO2Emission"]); ?>
                </p>

                <p>Megtett távolság:</p>
                <p><?= roundStatInfo($stats["monthlyDistance"]) ?> km</p>
            </div>

            <canvas id="previousMonthConsumptionComparison" style="max-width:250px;max-height:200px"></canvas>
        </div>
    </div>

    <div class="card">
        <h2>Éves CO<sub>2</sub>-kibocsátás</h2>

        <div class="stat-columns">
            <div class="stat-grid">
                <p>Idén:</p>
                <p>
                    <?php if ($stats["previousYearlyEmission"] == 0): ?>
                        <?= roundStatInfo($stats["yearlyEmission"] / 1000.0) ?> kg
                    <?php else: ?>
                        <?= roundStatInfo($stats["yearlyEmission"] / 1000.0) ?> kg (<?php echoDeviation($stats["yearlyEmission"] / $stats["previousYearlyEmission"]); ?>)
                    <?php endif ?>
                </p>

                <?php if ($stats["previousYearlyEmission"] != 0): ?>
                    <p>Tavaly:</p>
                    <p>
                        <?= roundStatInfo($stats["previousYearlyEmission"] / 1000.0) ?> kg
                    </p>
                <?php endif ?>

                <p>Eltérés az EU átlagtól:</p>
                <p>
                    <?php echoDeviation($stats["yearlyEmission"] / ($stats["averageEUMonthlyCO2Emission"] * 12)); ?>
                </p>

                <p>Megtett távolság:</p>
                <p><?= roundStatInfo($stats["yearlyDistance"]) ?> km</p>
            </div>

            <canvas id="previousYearConsumptionComparison" style="max-width:250px;max-height:200px"></canvas>
        </div>
    </div>

    <div class="card">
        <h2>Kibocsátás megoszlás</h2>
        <div class="stat-columns dual-canvas">
            <canvas id="monthlyConsumptionShare" style="max-width:250px;max-height:200px"></canvas>
            <canvas id="yearlyConsumptionShare" style="max-width:250px;max-height:200px"></canvas>
        </div>
    </div>

    <div class="card">
        <h2>Becsült kibocsátás</h2>
        <div class="stat-columns dual-canvas">
            <canvas id="monthlyConsumptionPredictionCurve" style="max-width:250px;max-height:250px"></canvas>
            <canvas id="yearlyConsumptionPredictionCurve" style="max-width:250px;max-height:250px"></canvas>
        </div>
    </div>
</div>

<script>
    Chart.defaults.color = "#c8d6e5";
    Chart.defaults.font.family = "Lexend Deca, sans-serif";

    let barChartOptions = {
        barThickness: 20,
        borderRadius: 5,
        responsive: true,
        plugins: {
           legend: {
                display: false
            },

            tooltip: {
                enabled: true,
                callbacks: {
                    label: (item) => item.parsed.y + " kg"
                }            
            }
        },
        scales: {
            y: {
                ticks: {
                    callback: (value, idx, ticks) => value + " kg",
                }
            },
        }
    };

    let pieChartOptions = {
        responsive: true,
        elements: {
            arc: {
                borderWidth: 0
            }
        }
    };

    const previousMonthConsumptionComparison = new Chart("previousMonthConsumptionComparison", {
        type: 'bar',
        options: barChartOptions,
        data: {
            labels: [ 
                "<?= ucfirst(monthName(new DateTimeImmutable("previous month"))) ?>",
                "<?= ucfirst(monthName(new DateTimeImmutable("now"))) ?>"
            ],
            datasets: [{
                backgroundColor: [ "#1dd1a1", "#ffc300" ],
                data: [
                    <?= round($stats["previousMonthEmission"] / 1000, 3) ?>,
                    <?= round($stats["monthlyEmission"] / 1000, 3) ?>
                ]
            }]
        }
    });

    const previousYearConsumptionComparison = new Chart("previousYearConsumptionComparison", {
        type: 'bar',
        options: barChartOptions,
        data: {
            labels: [ 
                "<?= (new DateTimeImmutable("previous year"))->format('Y') ?>",
                "<?= (new DateTimeImmutable("now"))->format('Y') ?>"
            ],
            datasets: [{
                backgroundColor: [ "#1dd1a1", "#ffc300" ],
                data: [
                    <?= round($stats["previousYearlyEmission"] / 1000, 3) ?>,
                    <?= round($stats["yearlyEmission"] / 1000, 3) ?>
                ]
            }]
        }
    });

    const monthlyConsumptionShare = new Chart("monthlyConsumptionShare", {
        type: 'doughnut',
        options: { 
            ...pieChartOptions, 
            plugins: {
                title: {
                    display: true,
                    text: "Havi megoszlás",
                    position: "bottom"
                },

                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: (item) => item.parsed + " kg"
                    }            
                }
            }
        },
        data: {
            labels: [
                <?php foreach ($stats["perVehicleMonthlyEmissionData"] as $data): ?>
                    "<?= $data["vehicle"]->licensePlate ?>",
                <?php endforeach ?>
            ],

            datasets: [{
                data: [
                    <?php foreach ($stats["perVehicleMonthlyEmissionData"] as $data): ?>
                        <?= round($data["emission"] / 1000, 3) ?>,
                    <?php endforeach ?>
                ]
            }]
        }
    });

    const yearlyConsumptionShare = new Chart("yearlyConsumptionShare", {
        type: 'doughnut',
        options: { 
            ...pieChartOptions, 
            plugins: {
                title: {
                    display: true,
                    text: "Éves megoszlás",
                    position: "bottom"
                },

                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: (item) => item.parsed + " kg"
                    }            
                }
            }
        },

        data: {
            labels: [
                <?php foreach ($stats["perVehicleYearlyEmissionData"] as $data): ?>
                    "<?= $data["vehicle"]->licensePlate ?>",
                <?php endforeach ?>
            ],

            datasets: [{
                data: [
                    <?php foreach ($stats["perVehicleYearlyEmissionData"] as $data): ?>
                        <?= round($data["emission"] / 1000, 3) ?>,
                    <?php endforeach ?>
                ]
            }]
        }
    });

    <?php $lastDayOfMonth = (new DateTimeImmutable('last day of this month'))->format('z') + 1 ?>
    <?php $lastDayOfYear = (new DateTimeImmutable('last day of december this year'))->format('z') + 1 ?>

    const monthlyConsumptionPredictionCurve = new Chart("monthlyConsumptionPredictionCurve", {
        options: { 
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    position: "bottom",
                    text: "Becsült hó végi összkibocsátás: <?= roundStatInfo($stats["predictedMonthlyExtrapolator"]->accumulate(1, $lastDayOfMonth) / 1000) ?> kg"
                }
            },

            scales: {
                x: { min: 1, max: <?= $lastDayOfMonth ?> },
                y: { min: 0 }
        }},

        data: {
            datasets: [
                {
                    type: "scatter",
                    label: "Kibocsátási adatok",
                    pointBackgroundColor: "#ffc300",
                    data: [
                        <?php foreach (array_keys($stats["predictedMonthlyExtrapolator"]->data()) as $x): ?>
                            { 
                                x: <?= $x ?>,
                                y: <?= roundStatInfo($stats["predictedMonthlyExtrapolator"]->data()[$x] / 1000) ?>
                            },
                        <?php endforeach ?>
                    ]
                },

                {
                    type: "line",
                    label: "Interpolált kibocsátási görbe",
                    borderColor: "#1dd1a1",
                    data: [
                        {x: 1, y: <?= roundStatInfo($stats["predictedMonthlyExtrapolator"]->evaluate(1) / 1000) ?> },
                        {x: <?= $lastDayOfMonth ?>, y: <?= roundStatInfo($stats["predictedMonthlyExtrapolator"]->evaluate($lastDayOfMonth) / 1000) ?> },
                    ]
                }
            ]
        }
    });

    const yearlyConsumptionPredictionCurve = new Chart("yearlyConsumptionPredictionCurve", {
        options: { 
            plugins: {
                title: {
                    display: true,
                    position: "bottom",
                    text: "Becsült év végi összkibocsátás: <?= roundStatInfo($stats["predictedYearlyExtrapolator"]->accumulate(1, $lastDayOfYear) / 1000) ?> kg"
                }
            },

            scales: {
                x: { min: 1, max: <?= $lastDayOfYear ?> },
                y: { min: 0 }
        }},

        data: {
            datasets: [
                {
                    type: "scatter",
                    label: "Kibocsátási adatok",
                    pointBackgroundColor: "#ffc300",
                    data: [
                        <?php foreach (array_keys($stats["predictedYearlyExtrapolator"]->data()) as $x): ?>
                            { 
                                x: <?= $x ?>,
                                y: <?= round($stats["predictedYearlyExtrapolator"]->data()[$x] / 1000, 3) ?>
                            },
                        <?php endforeach ?>
                    ]
                },

                {
                    type: "line",
                    label: "Interpolált kibocsátási görbe",
                    borderColor: "#1dd1a1",
                    data: [
                        {x: 1, y: <?= roundStatInfo($stats["predictedYearlyExtrapolator"]->evaluate(1) / 1000) ?> },
                        {x: <?= $lastDayOfYear ?>, y: <?= roundStatInfo($stats["predictedYearlyExtrapolator"]->evaluate($lastDayOfYear) / 1000) ?> },
                    ]
                }
            ]
        }
    });

</script>
<?php endif ?>