<?php

namespace App\Console\Commands;

use App\Models\ConversationMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneOldChatMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:prune {--days=90 : Days of messages to keep} {--attachments-only : Delete only attachment files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up and prune old chat messages and voice/image attachments to optimize storage space.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $attachmentsOnly = $this->option('attachments-only');

        $cutoff = now()->subDays($days);
        $this->info("Scanning for chat messages and attachments older than {$days} days (before {$cutoff->toDateTimeString()})...");

        // Find messages with attachments older than cutoff
        $messagesWithFiles = ConversationMessage::where('created_at', '<', $cutoff)
            ->whereNotNull('attachment_url')
            ->get();

        $freedBytes = 0;
        $deletedFiles = 0;

        foreach ($messagesWithFiles as $msg) {
            $path = parse_url($msg->attachment_url, PHP_URL_PATH);
            if ($path) {
                // Storage path mapping e.g. /storage/chat_attachments/xyz.png -> chat_attachments/xyz.png
                $relative = ltrim(str_replace('/storage/', '', $path), '/');
                if (Storage::disk('public')->exists($relative)) {
                    $size = Storage::disk('public')->size($relative);
                    if (Storage::disk('public')->delete($relative)) {
                        $freedBytes += $size;
                        $deletedFiles++;
                    }
                }
            }

            if (! $attachmentsOnly) {
                $msg->update(['attachment_url' => null, 'message' => '[Attachment deleted due to storage cleanup]']);
            }
        }

        $formattedFreed = number_format($freedBytes / (1024 * 1024), 2);
        $this->info("Cleaned up {$deletedFiles} media attachments. Freed ~{$formattedFreed} MB of disk space.");

        if (! $attachmentsOnly) {
            // Prune plain text messages older than cutoff
            $deletedCount = ConversationMessage::where('created_at', '<', $cutoff)->delete();
            $this->info("Pruned {$deletedCount} old text messages from database.");
        }

        $this->info('Chat storage space optimization complete!');

        return Command::SUCCESS;
    }
}
