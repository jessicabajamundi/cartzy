<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * One-time copy of the uploads on this computer (storage/app/public and storage/app/private)
 * into the Cloudflare R2 buckets, keeping the same paths so the database needs no changes.
 */
class PushStorageToR2 extends Command
{
    protected $signature = 'storage:push-to-r2 {--dry-run : List what would be uploaded without uploading}';

    protected $description = 'Upload existing local uploads to the Cloudflare R2 buckets';

    public function handle(): int
    {
        if (!config('filesystems.disks.public.bucket') || !config('filesystems.disks.local.bucket')) {
            $this->error('Set R2_ENDPOINT, R2_ACCESS_KEY_ID, R2_SECRET_ACCESS_KEY, R2_PUBLIC_BUCKET, R2_PUBLIC_URL and R2_PRIVATE_BUCKET first.');

            return self::FAILURE;
        }

        foreach (['local_public_source' => 'public', 'local_private_source' => 'local'] as $source => $target) {
            $from = Storage::disk($source);
            $to = Storage::disk($target);
            $files = array_values(array_filter($from->allFiles(), fn ($path) => basename($path) !== '.gitignore'));
            $this->info(count($files)." file(s) from storage/app/".($target === 'public' ? 'public' : 'private')." -> bucket ".config("filesystems.disks.$target.bucket"));

            $uploaded = $skipped = $failed = 0;
            $bar = $this->output->createProgressBar(count($files));
            foreach ($files as $path) {
                $bar->advance();
                if ($this->option('dry-run')) continue;
                if ($to->exists($path) && $to->size($path) === $from->size($path)) {
                    $skipped++;
                    continue;
                }
                $stream = $from->readStream($path);
                $to->writeStream($path, $stream) ? $uploaded++ : $failed++;
                if (is_resource($stream)) fclose($stream);
            }
            $bar->finish();
            $this->newLine();
            $this->line("  uploaded: $uploaded, already there: $skipped, failed: $failed");
            if ($failed) return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
