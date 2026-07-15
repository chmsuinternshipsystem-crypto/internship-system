<?php

namespace App\Support;

use App\Models\StudentAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves who is acting in messaging: staff (users) or student portal (student_accounts).
 */
final class MessageActor
{
    /**
     * @return array{user_id: int|null, student_account_id: int|null, user: ?User, studentAccount: ?StudentAccount}
     */
    public static function fromRequest(Request $request): array
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            return [
                'user_id' => (int) $user->id,
                'student_account_id' => null,
                'user' => $user,
                'studentAccount' => null,
            ];
        }

        /** @var StudentAccount|null $acc */
        $acc = $request->attributes->get('studentAccount');
        if ($acc instanceof StudentAccount) {
            return [
                'user_id' => null,
                'student_account_id' => (int) $acc->id,
                'user' => null,
                'studentAccount' => $acc,
            ];
        }

        abort(403);
    }

    public static function isStudentPortal(Request $request): bool
    {
        return $request->attributes->get('studentAccount') instanceof StudentAccount;
    }
}
