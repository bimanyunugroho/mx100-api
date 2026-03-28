<?php

namespace App\Services\Shared;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Exceptions\UnprocessableException;

class FileUploadService
{
    private const BASE_PATH = 'cv';
    private const ALLOWED_EXTENSIONS = ['pdf'];
    private const MAX_SIZE_BYTES = 5_242_880; // 5MB

    private string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.default', 'local');
    }

    /**
     * Simpan CV user
     */
    public function storeCv(UploadedFile $file, string $freelancerId): string
    {
        $this->validateFile($file);

        $filename  = Str::ulid() . '_' . $this->sanitizeFilename($file->getClientOriginalName());
        $directory = self::BASE_PATH . '/' . $freelancerId;

        return Storage::disk($this->disk)
            ->putFileAs($directory, $file, $filename);
    }

    /**
     * Ambil stream file CV
     */
    public function getCvStream(string $path): resource|false
    {
        $disk = Storage::disk($this->disk);

        if (! $disk->exists($path)) {
            return false;
        }

        return $disk->readStream($path);
    }

    /**
     * Ambil path file CV lokal
     */
    public function getCvPath(string $path): ?string
    {
        $disk = Storage::disk($this->disk);

        if (! $disk->exists($path)) {
            return null;
        }

        return $disk->path($path);
    }

    /**
     * Hapus CV
     */
    public function deleteCv(string $path): bool
    {
        $disk = Storage::disk($this->disk);

        if (! $disk->exists($path)) {
            return false;
        }

        return $disk->delete($path);
    }

    /**
     * Validasi file
     */
    private function validateFile(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new UnprocessableException('File upload gagal atau korup.');
        }

        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            throw new UnprocessableException(
                'Ukuran file CV maksimal 5MB. Ukuran file kamu: '
                . round($file->getSize() / 1_048_576, 2) . 'MB.'
            );
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new UnprocessableException(
                'Format file tidak didukung. Gunakan PDF.'
            );
        }
    }

    /**
     * Sanitasi nama file
     */
    private function sanitizeFilename(string $filename): string
    {
        $name      = pathinfo($filename, PATHINFO_FILENAME);
        $safeName  = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
        $safeName  = substr($safeName, 0, 50);

        return $safeName . '.pdf';
    }
}
