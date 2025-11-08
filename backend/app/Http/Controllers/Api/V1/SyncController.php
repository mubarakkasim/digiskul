<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    public function bulk(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'operations' => 'required|array',
            'operations.*.type' => 'required|in:INSERT,UPDATE,DELETE',
            'operations.*.table' => 'required|string',
            'operations.*.payload' => 'required|array',
            'operations.*.device_tx_id' => 'required|string',
            'operations.*.local_ts' => 'required|string',
        ]);

        $schoolId = $request->user()->school_id;
        $syncedOperations = [];

        DB::transaction(function () use ($request, $schoolId, &$syncedOperations) {
            foreach ($request->operations as $operation) {
                try {
                    $result = $this->processOperation($operation, $schoolId);
                    
                    SyncLog::create([
                        'school_id' => $schoolId,
                        'device_id' => $request->device_id,
                        'operation_type' => $operation['type'],
                        'payload' => $operation['payload'],
                        'status' => 'success',
                        'server_ts' => now(),
                    ]);

                    $syncedOperations[] = [
                        'device_tx_id' => $operation['device_tx_id'] ?? null,
                        'server_id' => $result['id'] ?? null,
                        'server_ts' => now()->toISOString(),
                    ];
                } catch (\Exception $e) {
                    SyncLog::create([
                        'school_id' => $schoolId,
                        'device_id' => $request->device_id,
                        'operation_type' => $operation['type'],
                        'payload' => $operation['payload'],
                        'status' => 'failed',
                        'server_ts' => now(),
                    ]);

                    // Continue with other operations
                }
            }
        });

        return response()->json([
            'data' => [
                'synced_operations' => $syncedOperations,
            ],
        ]);
    }

    private function processOperation($operation, $schoolId)
    {
        $table = $operation['table'];
        $payload = $operation['payload'];
        $payload['school_id'] = $schoolId;

        switch ($operation['type']) {
            case 'INSERT':
                return DB::table($table)->insertGetId($payload);
            
            case 'UPDATE':
                $id = $payload['id'] ?? null;
                unset($payload['id']);
                DB::table($table)->where('id', $id)->update($payload);
                return ['id' => $id];
            
            case 'DELETE':
                $id = $payload['id'] ?? null;
                DB::table($table)->where('id', $id)->delete();
                return ['id' => $id];
        }
    }

    public function status(Request $request, $deviceId)
    {
        $schoolId = $request->user()->school_id;
        
        $pending = SyncLog::forSchool($schoolId)
            ->where('device_id', $deviceId)
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'data' => [
                'pending' => $pending,
            ],
        ]);
    }
}

