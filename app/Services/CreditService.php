<?php

namespace App\Services;

use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditBalance;
use App\Models\CreditTransaction;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Zentrale, transaktionssichere Credit-Logik.
 *
 * - Jede Bewegung schreibt eine credit_transactions-Zeile.
 * - Saldo wird zeilenweise gesperrt (lockForUpdate) → keine Race Conditions / Doppelbuchungen.
 * - Keine negativen Salden, ausser der Aufrufer erzwingt es bewusst (['force' => true]).
 * - Einmal-Gutschriften (Launch-Bonus etc.) sind idempotent (['once' => true]).
 */
class CreditService
{
    /**
     * Kernbuchung. $amount > 0 = Gutschrift, $amount < 0 = Abbuchung.
     *
     * @param  array{profile_id?:int|null, description?:string|null, reference_type?:string|null,
     *               reference_id?:int|null, admin_id?:int|null, metadata?:array|null,
     *               force?:bool, once?:bool}  $opts
     */
    public function record(User $user, int $amount, string $type, array $opts = []): CreditTransaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $opts) {
            // Saldo-Zeile sicherstellen und exklusiv sperren.
            CreditBalance::firstOrCreate(['user_id' => $user->id]);
            $balance = CreditBalance::where('user_id', $user->id)->lockForUpdate()->first();

            // Idempotenz: Einmal-Gutschrift überspringen, wenn bereits vorhanden.
            if (! empty($opts['once'])) {
                $q = CreditTransaction::where('user_id', $user->id)->where('type', $type);
                if (! empty($opts['profile_id'])) {
                    $q->where('profile_id', $opts['profile_id']);
                }
                if ($existing = $q->first()) {
                    return $existing;
                }
            }

            $force      = $opts['force'] ?? false;
            $newBalance = $balance->balance + $amount;

            if ($newBalance < 0 && ! $force) {
                throw new InsufficientCreditsException();
            }

            $balance->balance = $newBalance;
            if ($amount > 0) {
                $balance->total_granted += $amount;
            } elseif ($amount < 0) {
                $balance->total_spent += abs($amount);
            }
            if ($type === 'purchase' && $amount > 0) {
                $balance->total_purchased += $amount;
            }
            if (in_array($type, ['initial_launch_bonus', 'bonus_private_gallery_launch'], true) && $amount > 0) {
                $balance->total_bonus += $amount;
            }
            $balance->save();

            return CreditTransaction::create([
                'user_id'             => $user->id,
                'profile_id'          => $opts['profile_id'] ?? null,
                'amount'              => $amount,
                'type'                => $type,
                'description'         => $opts['description'] ?? null,
                'reference_type'      => $opts['reference_type'] ?? null,
                'reference_id'        => $opts['reference_id'] ?? null,
                'created_by_admin_id' => $opts['admin_id'] ?? null,
                'metadata'            => $opts['metadata'] ?? null,
            ]);
        });
    }

    /** Gutschrift (immer positiv gebucht). */
    public function grant(User $user, int $credits, string $type, array $opts = []): CreditTransaction
    {
        return $this->record($user, abs($credits), $type, $opts);
    }

    /** Abbuchung (immer negativ gebucht). Wirft InsufficientCreditsException bei zu wenig Guthaben. */
    public function spend(User $user, int $credits, string $type, ?Profile $profile = null, array $opts = []): CreditTransaction
    {
        if ($profile) {
            $opts['profile_id'] = $profile->id;
        }
        return $this->record($user, -abs($credits), $type, $opts);
    }

    public function balanceOf(User $user): int
    {
        return (int) (CreditBalance::where('user_id', $user->id)->value('balance') ?? 0);
    }

    public function hasEnough(User $user, int $credits): bool
    {
        return $this->balanceOf($user) >= $credits;
    }

    public function hasTransactionType(User $user, string $type, ?int $profileId = null): bool
    {
        $q = CreditTransaction::where('user_id', $user->id)->where('type', $type);
        if ($profileId) {
            $q->where('profile_id', $profileId);
        }
        return $q->exists();
    }

    /**
     * Einmaliger Launch-Bonus für eine Inserentin. Nur im Launch-Modus, nur einmal pro User.
     * Gibt null zurück, wenn nicht anwendbar (kein Launch-Modus oder bereits gutgeschrieben).
     */
    public function grantLaunchBonus(User $user): ?CreditTransaction
    {
        if (! config('features.launch_mode')) {
            return null;
        }

        $amount = (int) config('features.launch_credits_initial', 10);
        if ($amount <= 0) {
            return null;
        }

        $tx = $this->grant($user, $amount, 'initial_launch_bonus', [
            'once'        => true,
            'description' => 'Launch-Bonus für neue Inserentin',
        ]);

        // once => wenn bereits vorhanden, kommt die alte Transaktion zurück (kein Doppel-Bonus).
        return $tx;
    }

    /**
     * Einmaliger Bonus, wenn eine Inserentin ihre private Galerie im Launch freigibt
     * und private Medien vorhanden sind. Einmal pro Profil.
     */
    public function grantGalleryBonus(User $user, Profile $profile): ?CreditTransaction
    {
        if (! config('features.launch_mode')) {
            return null;
        }

        $amount = (int) config('features.private_gallery_bonus', 3);
        if ($amount <= 0) {
            return null;
        }

        return $this->grant($user, $amount, 'bonus_private_gallery_launch', [
            'once'        => true,
            'profile_id'  => $profile->id,
            'description' => 'Bonus: private Galerie im Launch freigegeben',
        ]);
    }
}
