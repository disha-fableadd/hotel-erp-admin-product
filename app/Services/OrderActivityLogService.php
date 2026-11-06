<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OrderActivityLogService
{
    public function ensureTable(): bool
    {
        try {
            if (Schema::hasTable('order_activity_logs')) {
                return true;
            }

            Schema::create('order_activity_logs', function ($table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->string('order_number', 64)->nullable()->index();
                $table->string('action', 50)->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('product_name')->nullable();
                $table->decimal('quantity', 12, 2)->nullable();
                $table->decimal('old_quantity', 12, 2)->nullable();
                $table->decimal('new_quantity', 12, 2)->nullable();
                $table->string('table_no', 50)->nullable();
                $table->text('details')->nullable();
                $table->json('meta')->nullable();
                $table->unsignedBigInteger('actor_id')->nullable()->index();
                $table->string('actor_name')->nullable();
                $table->string('actor_role', 50)->nullable();
                $table->timestamps();
                $table->index(['branch_id', 'created_at']);
                $table->index(['action', 'created_at']);
            });

            return true;
        } catch (\Throwable $e) {
            Log::warning('OrderActivityLogService ensureTable: ' . $e->getMessage());

            return Schema::hasTable('order_activity_logs');
        }
    }

    public function log(array $data): ?OrderActivityLog
    {
        try {
            if (! $this->ensureTable()) {
                return null;
            }

            $now = Carbon::now('Asia/Kolkata');

            return OrderActivityLog::create([
                'branch_id' => $data['branch_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'order_number' => $data['order_number'] ?? null,
                'action' => $data['action'] ?? 'unknown',
                'product_id' => $data['product_id'] ?? null,
                'product_name' => $data['product_name'] ?? null,
                'quantity' => $data['quantity'] ?? null,
                'old_quantity' => $data['old_quantity'] ?? null,
                'new_quantity' => $data['new_quantity'] ?? null,
                'table_no' => isset($data['table_no']) && $data['table_no'] !== '' && $data['table_no'] !== null
                    ? (string) $data['table_no']
                    : null,
                'details' => $data['details'] ?? null,
                'meta' => $data['meta'] ?? null,
                'actor_id' => $data['actor_id'] ?? null,
                'actor_name' => $data['actor_name'] ?? null,
                'actor_role' => $data['actor_role'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } catch (\Throwable $e) {
            Log::warning('OrderActivityLogService log failed: ' . $e->getMessage());

            return null;
        }
    }

    public function actorPayload($user): array
    {
        if (! $user) {
            return [
                'actor_id' => null,
                'actor_name' => 'System',
                'actor_role' => null,
            ];
        }

        return [
            'actor_id' => $user->id ?? null,
            'actor_name' => $user->name ?? ('User #' . ($user->id ?? '')),
            'actor_role' => $user->role ?? null,
        ];
    }

    public function logBillCreated(Order $order, $actor, array $itemLines = []): void
    {
        $names = [];
        foreach ($itemLines as $line) {
            $name = $line['name'] ?? $line['product_name'] ?? 'Item';
            $qty = (float) ($line['qty'] ?? $line['quantity'] ?? 0);
            $names[] = $name . ' × ' . (fmod($qty, 1.0) === 0.0 ? (int) $qty : $qty);
        }

        $details = $names
            ? ('Bill created with: ' . implode(', ', $names))
            : 'Bill created';

        $this->log(array_merge($this->actorPayload($actor), [
            'branch_id' => $order->branch_id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'action' => 'bill_created',
            'table_no' => $order->table_no,
            'details' => $details,
            'meta' => [
                'items' => $itemLines,
                'total_amount' => $order->total_amount,
                'order_type' => $order->order_type ?? $order->type,
            ],
        ]));
    }

    /**
     * Log item add / qty increase / qty reduce / full remove from a fire-ticket style change list.
     *
     * @param  array<int, array{action:string,name:string,qty:int|float,product_id?:int|null,variant_name?:string|null}>  $changes
     */
    public function logItemChanges(Order $order, $actor, array $changes): void
    {
        foreach ($changes as $change) {
            $action = strtolower((string) ($change['action'] ?? ''));
            $qty = (float) ($change['qty'] ?? $change['quantity'] ?? 0);
            if ($qty <= 0 && ! in_array($action, ['cancel', 'remove', 'item_removed'], true)) {
                continue;
            }

            $name = (string) ($change['name'] ?? 'Item');
            if (! empty($change['variant_name'])) {
                $name .= ' (' . $change['variant_name'] . ')';
            }

            if ($action === 'new' || $action === 'add' || $action === 'item_added') {
                $logAction = 'item_added';
                $details = "Added {$name} × " . $this->fmtQty($qty);
                $oldQty = $change['old_quantity'] ?? null;
                $newQty = $change['new_quantity'] ?? $qty;
            } elseif ($action === 'increase' || $action === 'item_qty_increased') {
                $logAction = 'item_qty_increased';
                $oldQty = $change['old_quantity'] ?? null;
                $newQty = $change['new_quantity'] ?? null;
                $details = "Increased {$name} qty" . ($oldQty !== null && $newQty !== null
                    ? (" from {$this->fmtQty($oldQty)} to {$this->fmtQty($newQty)}")
                    : (" by {$this->fmtQty($qty)}"));
            } elseif ($action === 'reduce' || $action === 'item_qty_reduced') {
                $logAction = 'item_qty_reduced';
                $oldQty = $change['old_quantity'] ?? null;
                $newQty = $change['new_quantity'] ?? null;
                $details = "Reduced {$name} qty" . ($oldQty !== null && $newQty !== null
                    ? (" from {$this->fmtQty($oldQty)} to {$this->fmtQty($newQty)}")
                    : (" by {$this->fmtQty($qty)}"));
            } else {
                // cancel / remove
                $logAction = 'item_removed';
                $oldQty = $change['old_quantity'] ?? $qty;
                $newQty = $change['new_quantity'] ?? 0;
                $details = "Removed {$name} × " . $this->fmtQty($qty);
            }

            $this->log(array_merge($this->actorPayload($actor), [
                'branch_id' => $order->branch_id,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'action' => $logAction,
                'product_id' => $change['product_id'] ?? null,
                'product_name' => $name,
                'quantity' => $qty,
                'old_quantity' => $oldQty,
                'new_quantity' => $newQty,
                'table_no' => $order->table_no,
                'details' => $details,
                'meta' => $change,
            ]));
        }
    }

    public function logBillAction(Order $order, $actor, string $action, ?string $details = null, array $meta = []): void
    {
        $defaultDetails = [
            'bill_deleted' => 'Bill deleted',
            'bill_restored' => 'Bill recovered from deleted list',
            'bill_cleared' => 'Dine-in bill cleared / closed',
        ];

        $this->log(array_merge($this->actorPayload($actor), [
            'branch_id' => $order->branch_id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'action' => $action,
            'table_no' => $order->table_no,
            'details' => $details ?: ($defaultDetails[$action] ?? ucfirst(str_replace('_', ' ', $action))),
            'meta' => $meta ?: null,
        ]));
    }

    protected function fmtQty($qty): string
    {
        $n = (float) $qty;
        if (! is_finite($n)) {
            return '0';
        }

        return fmod($n, 1.0) === 0.0 ? (string) (int) $n : rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }
}
