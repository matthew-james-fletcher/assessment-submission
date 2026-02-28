<?php

declare(strict_types=1);

namespace App\Domain;

use Doctrine\ORM\EntityRepository;

class AssessmentAnswerRepository extends EntityRepository
{
    public function findAnswersByInstanceAndQuestion(string $instanceId, string $question): array
    {
        //todo implement
        return [];
    }
}
