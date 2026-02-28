<?php

declare(strict_types=1);

namespace App\Domain;

use Doctrine\ORM\EntityRepository;

class AssessmentAnswerRepository extends EntityRepository
{
    public function findAnswerById(string $id): ?AssessmentAnswer
    {
        return $this->find($id);
    }

    public function findAnswersByInstanceAndQuestion(AssessmentInstance $instance, AssessmentQuestion $question): array
    {
        $qb = $this->getEntityManager()
          ->createQueryBuilder()
          ->select('aa')
          ->from(AssessmentAnswer::class, 'aa')
          ->join('aa.assessmentAnswerOption', 'option')
          ->join('option.assessmentQuestion', 'q')
          ->where('aa.assessmentInstance = :instance')
          ->andWhere('q = :question')
          ->setParameter('instance', $instance)
          ->setParameter('question', $question);

        return $qb->getQuery()->getResult();
    }
}
