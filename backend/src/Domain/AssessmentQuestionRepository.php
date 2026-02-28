<?php

declare(strict_types=1);

namespace App\Domain;

use Doctrine\ORM\EntityRepository;

class AssessmentQuestionRepository extends EntityRepository
{
    public function findQuestionById(string $id): ?AssessmentQuestion
    {
        return $this->find($id);
    }
}
