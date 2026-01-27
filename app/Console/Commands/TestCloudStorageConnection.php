<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestCloudStorageConnection extends Command
{
    protected $signature = 'test:cloud-storage';
    protected $description = 'Test Cloud Storage connection (Digital Ocean Spaces, AWS S3, etc.)';

    public function handle()
    {
        $this->info('Testing Cloud Storage connection...');

        try {
            // Test connection
            $disk = Storage::disk('media_cloud');
            
            // Try to put a test file
            $testContent = 'Hello Cloud Storage from UsPage! ' . now();
            $testPath = 'test/connection-test-' . time() . '.txt';
            
            $disk->put($testPath, $testContent);
            $this->info("✅ Upload successful: {$testPath}");

            // Try to get the URL
            $url = $disk->url($testPath);
            $this->info("✅ URL generated: {$url}");

            // Try to read the file back
            $content = $disk->get($testPath);
            if ($content === $testContent) {
                $this->info("✅ File read back successfully");
            }

            // Clean up test file
            $disk->delete($testPath);
            $this->info("✅ Test file cleaned up");

            $this->info("\n🎉 Cloud Storage connection successful!");
            
        } catch (\Exception $e) {
            $this->error("❌ Connection failed: " . $e->getMessage());
            $this->error("\n💡 Check your .env configuration:");
            $this->error("   - CLOUD_ACCESS_KEY_ID");
            $this->error("   - CLOUD_SECRET_ACCESS_KEY");
            $this->error("   - CLOUD_BUCKET");
            $this->error("   - CLOUD_ENDPOINT");
        }
    }
}