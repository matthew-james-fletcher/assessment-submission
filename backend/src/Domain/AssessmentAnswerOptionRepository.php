<?php

declare(strict_types=1);

namespace App\Domain;

use Doctrine\ORM\EntityRepository;

class AssessmentAnswerOptionRepository extends EntityRepository
{
    public function findAnswerOptionById(string $id): ?AssessmentAnswerOption
    {
        return $this->find($id);
    }
}
