<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Durable idempotency for database writes on the default connection. */
class SubmissionGuard
{
    /** @return array{0: Model, 1: bool} Resource and whether this request created it. */
    public function run(Request $request, string $scope, string $model, Closure $create): array
    {
        $token = $request->validate(['submission_token' => ['required', 'uuid']])['submission_token'];
        $owner = $request->user() ? 'user:'.$request->user()->id : 'session:'.$request->session()->getId();
        $key = hash('sha256', $scope.':'.$owner.':'.$token);
        $find = fn () => DB::table('submission_receipts')->where('key', $key)->where('resource_type', $model)->first();
        if ($receipt = $find()) {
            return [$model::findOrFail($receipt->resource_id), false];
        }
        try {
            return DB::transaction(function () use ($key, $model, $create) {
                // Inserting the unique claim first serializes competing submissions.
                DB::table('submission_receipts')->insert(['key' => $key, 'resource_type' => $model, 'created_at' => now()]);
                $resource = $create();
                if (! $resource instanceof $model || ! $resource->exists) {
                    throw new \LogicException('The submission callback must return a persisted resource.');
                }
                DB::table('submission_receipts')->where('key', $key)->update(['resource_id' => $resource->getKey()]);
                return [$resource, true];
            });
        } catch (QueryException $exception) {
            // A unique-claim conflict is visible only after the first transaction commits.
            if ($receipt = $find()) return [$model::findOrFail($receipt->resource_id), false];
            throw $exception;
        }
    }
}
