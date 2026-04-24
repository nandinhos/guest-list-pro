<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\Request;

class BackupDownloadController
{
    public function __invoke(Request $request, string $filename)
    {
        $user = auth()->user();

        if (!$user || $user->role !== UserRole::ADMIN) {
            abort(403, 'Unauthorized');
        }

        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);

        if (!str_ends_with($safeFilename, '.sql') && !str_ends_with($safeFilename, '.sqlite')) {
            abort(403, 'Invalid filename format');
        }

        $backupDir = storage_path('app/backups');
        $filepath = $backupDir . '/' . $safeFilename;

        if (!file_exists($filepath)) {
            abort(404, 'Backup not found');
        }

        activity()
            ->causedBy($user)
            ->log("Backup downloaded: {$safeFilename}");

        return response()->download($filepath, $safeFilename, [
            'Content-Type' => 'application/sql',
        ]);
    }
}
