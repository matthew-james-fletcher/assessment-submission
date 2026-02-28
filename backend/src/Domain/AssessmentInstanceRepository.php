<?php

declare(strict_types=1);

namespace App\Domain;

use Doctrine\ORM\EntityRepository;

class AssessmentInstanceRepository extends EntityRepository
{
    public function findInstanceById(string $id): ?AssessmentInstance
    {
        return $this->find($id);
    }
}
