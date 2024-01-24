<?php
require 'vendor/autoload.php';
use Carbon\Carbon;

class RentalRequest
{
    public $id;
    public $startDate;
    public $endDate;
    public $nbItems;
    public $nbDays;
    public $value;

    public function __construct(
        string $id,
        string $startDate,
        string $endDate,
        int $nbItems,
        int $value
    ) {
        $this->id = $id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->nbItems = $nbItems;
        $this->nbDays = (strtotime($endDate) - strtotime($startDate)) / 86400;
        $this->value = $value;
    }
}


function optimizeRentalSchedule($rentalMaterials)
{
    // Sort rental materials by end date
    usort($rentalMaterials, function ($a, $b) {
        return strtotime($a->endDate) - strtotime($b->endDate);
    });

    $optimizedSchedule = [];
    $rentalRequestNotSatisfied = [];

    foreach ($rentalMaterials as $material) {

        // Check if the material's schedule overlaps with any existing schedules
        $overlap = false;
        foreach ($optimizedSchedule as $existingMaterial) {
            if (
                strtotime($material->startDate) <= strtotime($existingMaterial->endDate) &&
                strtotime($material->endDate) >= strtotime($existingMaterial->startDate)
            ) {
                $overlap = true;
                break;
            }
        }

        // If no overlap, add the material to the optimized schedule
        if (!$overlap) {
            $optimizedSchedule[] = $material;
        } else {
            $rentalRequestNotSatisfied[] = $material;
        }
    }

    return [
        'optimize' => $optimizedSchedule,
        'not-satisfied' => $rentalRequestNotSatisfied
    ];
}


function procedeAttribution(array $rentalRequests, $iteration = 0, $max = 3)
{
    foreach ($rentalRequests as $r) {
        $round[] = new RentalRequest(
            $r->id,
            $r->startDate,
            $r->endDate,
            $r->nbItems,
            $r->value,
        );
    }

    $optimized = optimizeRentalSchedule($round);

    $totalDays = 0;
    $totalDaysLost = 0;
    $totalValue = 0;
    $totalValueLost = 0;
    // Output the optimized schedule
    echo '<br /><br />Rental Schedule REF '. ++$iteration . ':<br />';
    foreach ($optimized['optimize'] as $m) {
        echo "{$m->id} (
            Start Date: {$m->startDate},
            End Date: {$m->endDate},
            Nb Items : {$m->nbItems},
            Nb Days : {$m->nbDays},
            Value : {$m->value}
        )<br />";
        $totalDays += $m->nbDays;
        $totalValue += $m->nbDays * $m->value;
    }
    echo 'TOTAL days : ' . $totalDays . '<br />';
    echo 'TOTAL value : ' . $totalValue;

    if ($iteration < $max)
    {
        if (count($optimized['not-satisfied']) > 0 ) {
            procedeAttribution($optimized['not-satisfied'], $iteration);
        }

    } else {

        // surbooking
        echo '<br /><br />Surbooking :<br />';
        foreach ($optimized['not-satisfied'] as $m) {
            echo "{$m->id} (
                Start Date: {$m->startDate},
                End Date: {$m->endDate},
                Nb Items : {$m->nbItems},
                Nb Days : {$m->nbDays},
                Value : {$m->value}
            )<br />";
            $totalDaysLost += $m->nbDays;
            $totalValueLost += $m->nbDays * $m->value;
        }
        echo 'TOTAL Days lost: ' . $totalDaysLost . '<br />';
        echo 'TOTAL Value lost: ' . $totalValueLost;
    }

}

// Example usage:
$rentalRequests = [
    new RentalRequest('B', '2024-02-03', '2024-02-07', '1', '10.00'),
    new RentalRequest('C', '2024-02-08', '2024-02-12', '2', '20.00'),
    new RentalRequest('D', '2024-03-05', '2024-03-12', '1', '10.00'),
    new RentalRequest('F', '2024-02-14', '2024-02-21', '1', '10.00'),
    new RentalRequest('G', '2024-02-10', '2024-02-17', '1', '10.00'),
    new RentalRequest('H', '2024-02-22', '2024-02-26', '1', '10.00'),
    new RentalRequest('I', '2024-02-19', '2024-03-01', '1', '10.00'),
    new RentalRequest('K', '2024-03-05', '2024-03-17', '3', '30.00'),
    new RentalRequest('L', '2024-03-15', '2024-03-20', '1', '10.00'),
    new RentalRequest('M', '2024-02-22', '2024-02-24', '1', '10.00'),
    new RentalRequest('N', '2024-02-10', '2024-02-17', '1', '10.00'),
    new RentalRequest('O', '2024-02-14', '2024-02-20', '1', '10.00'),
    new RentalRequest('P', '2024-03-07', '2024-03-17', '1', '10.00'),
    new RentalRequest('Q', '2024-02-20', '2024-03-10', '1', '10.00'),
];


procedeAttribution($rentalRequests);
