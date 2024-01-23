<?php
require 'vendor/autoload.php';

use Carbon\Carbon;

class RentalRequest
{
    public $id;
    public $startDate;
    public $endDate;

    public function __construct(string $id, string $startDate, string $endDate)
    {
        $this->id = $id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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


function procedeAttribution(array $rentalRequests, $iteration = 0)
{
    foreach ($rentalRequests as $r) {
        $round[] = new RentalRequest($r->id, $r->startDate, $r->endDate);
    }

    $optimized = optimizeRentalSchedule($round);

    // Output the optimized schedule
    echo '<br /><br />Rental Schedule '. ++$iteration . ':<br />';
    foreach ($optimized['optimize'] as $m) {
        echo "{$m->id} (Start Date: {$m->startDate}, End Date: {$m->endDate})<br />";
    }

    if (count($optimized['not-satisfied']) > 0 ) {
        procedeAttribution($optimized['not-satisfied'], $iteration);
    }    

}

// Example usage:
$rentalRequests = [
    new RentalRequest('A', '2024-02-01', '2024-02-05'),
    new RentalRequest('B', '2024-02-03', '2024-02-07'),
    new RentalRequest('C', '2024-02-08', '2024-02-12'),
    new RentalRequest('D', '2024-03-05', '2024-03-12'),
    new RentalRequest('E', '2024-02-26', '2024-03-04'),
    new RentalRequest('F', '2024-02-14', '2024-02-21'),
    new RentalRequest('G', '2024-02-10', '2024-02-17'),
    new RentalRequest('H', '2024-02-22', '2024-02-26'),
    new RentalRequest('I', '2024-02-19', '2024-03-01'),
    new RentalRequest('K', '2024-03-05', '2024-03-17'),
    new RentalRequest('L', '2024-03-15', '2024-03-20'),
    new RentalRequest('M', '2024-02-22', '2024-02-24'),
];

procedeAttribution($rentalRequests);
