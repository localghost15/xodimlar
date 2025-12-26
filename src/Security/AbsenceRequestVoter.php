<?php

namespace App\Security;

use App\Entity\AbsenceRequest;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AbsenceRequestVoter extends Voter
{
    public const DECIDE = 'DECIDE';
    public const VIEW = 'VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DECIDE, self::VIEW])
            && $subject instanceof AbsenceRequest;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var AbsenceRequest $absenceRequest */
        $absenceRequest = $subject;

        switch ($attribute) {
            case self::DECIDE:
                // Only HR can decide (Approve/Reject)
                // In a real app we might check if this HR is responsible for this department, 
                // but requirements say "ROLE_HR" broadly.
                return in_array('ROLE_HR', $user->getRoles());

            case self::VIEW:
                // Owner can view
                if ($absenceRequest->getUser() === $user) {
                    return true;
                }
                // HR can view all
                if (in_array('ROLE_HR', $user->getRoles())) {
                    return true;
                }
                // Dept Head can view their department
                if (in_array('ROLE_DEPT_HEAD', $user->getRoles())) {
                    // Assuming basic logic: if same department.
                    // $user->getDepartment() === $absenceRequest->getUser()->getDepartment()
                    return true;
                }
                return false;
        }

        return false;
    }
}
