<?php

declare(strict_types=1);

namespace App\Domain;

use Doctrine\ORM\EntityRepository;

class AssessmentAnswerRepository extends EntityRepository
{
    public function findAnswersByInstanceAndQuestion(AssessmentInstance $instance, AssessmentQuestion $question): array
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('assessmentAnswer')
            ->from(AssessmentAnswer::class, 'assessmentAnswer')
            ->andWhere('assessmentAnswer.assessmentInstance = :instance')
            ->andWhere('assessmentAnswer.assessmentQuestion = :question')
            ->setParameter('instance', $instance)
            ->setParameter('question', $question);

        return $qb->getQuery()->getResult();
    }
}
