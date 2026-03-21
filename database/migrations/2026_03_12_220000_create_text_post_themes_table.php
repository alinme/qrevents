<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('text_post_themes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('image_path');
            $table->string('background_color', 7)->nullable();
            $table->string('text_color', 7)->default('#FFFFFF');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->seedThemes();
    }

    public function down(): void
    {
        Schema::dropIfExists('text_post_themes');
    }

    private function seedThemes(): void
    {
        $directory = public_path('text-backgrounds');
        if (! is_dir($directory)) {
            return;
        }

        $files = collect(File::files($directory))
            ->filter(function (\SplFileInfo $file): bool {
                return in_array(
                    Str::lower($file->getExtension()),
                    ['jpg', 'jpeg', 'png', 'webp', 'avif'],
                    true,
                );
            })
            ->sortBy(fn (\SplFileInfo $file): string => Str::lower($file->getFilename()))
            ->values();

        $now = now();

        foreach ($files as $index => $file) {
            $filename = $file->getFilename();
            $stem = pathinfo($filename, PATHINFO_FILENAME);
            $slug = Str::slug($stem);
            $backgroundColor = $this->averageCenterColor($file->getPathname()) ?? '#334155';
            $textColor = $this->preferredTextColor($backgroundColor);

            DB::table('text_post_themes')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => Str::of($stem)
                        ->replace(['-', '_'], ' ')
                        ->title()
                        ->toString(),
                    'image_path' => 'text-backgrounds/'.$filename,
                    'background_color' => $backgroundColor,
                    'text_color' => $textColor,
                    'sort_order' => $index,
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function averageCenterColor(string $path): ?string
    {
        if (extension_loaded('imagick')) {
            try {
                $imagick = new \Imagick($path);
                $width = $imagick->getImageWidth();
                $height = $imagick->getImageHeight();

                if ($width > 0 && $height > 0) {
                    $cropWidth = max(1, (int) floor($width * 0.45));
                    $cropHeight = max(1, (int) floor($height * 0.45));
                    $x = max(0, (int) floor(($width - $cropWidth) / 2));
                    $y = max(0, (int) floor(($height - $cropHeight) / 2));

                    $imagick->cropImage($cropWidth, $cropHeight, $x, $y);
                    $imagick->resizeImage(1, 1, \Imagick::FILTER_BOX, 1, true);
                    $pixel = $imagick->getImagePixelColor(0, 0);
                    $color = $pixel->getColor();
                    $imagick->clear();
                    $imagick->destroy();

                    return sprintf(
                        '#%02X%02X%02X',
                        (int) ($color['r'] ?? 0),
                        (int) ($color['g'] ?? 0),
                        (int) ($color['b'] ?? 0),
                    );
                }
            } catch (\Throwable) {
                // Fall back to GD if Imagick cannot process the image.
            }
        }

        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $imageData = @file_get_contents($path);
        if ($imageData === false) {
            return null;
        }

        $image = @imagecreatefromstring($imageData);
        if (! $image instanceof \GdImage) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        if ($width <= 0 || $height <= 0) {
            imagedestroy($image);

            return null;
        }

        $startX = max(0, (int) floor($width * 0.275));
        $endX = min($width - 1, (int) floor($width * 0.725));
        $startY = max(0, (int) floor($height * 0.275));
        $endY = min($height - 1, (int) floor($height * 0.725));

        $steps = 6;
        $red = 0;
        $green = 0;
        $blue = 0;
        $samples = 0;

        for ($xIndex = 0; $xIndex <= $steps; $xIndex++) {
            for ($yIndex = 0; $yIndex <= $steps; $yIndex++) {
                $x = (int) round($startX + (($endX - $startX) * $xIndex / $steps));
                $y = (int) round($startY + (($endY - $startY) * $yIndex / $steps));
                $rgb = imagecolorat($image, $x, $y);
                $red += ($rgb >> 16) & 0xFF;
                $green += ($rgb >> 8) & 0xFF;
                $blue += $rgb & 0xFF;
                $samples++;
            }
        }

        imagedestroy($image);

        if ($samples === 0) {
            return null;
        }

        return sprintf(
            '#%02X%02X%02X',
            (int) round($red / $samples),
            (int) round($green / $samples),
            (int) round($blue / $samples),
        );
    }

    private function preferredTextColor(string $hexColor): string
    {
        $normalized = ltrim($hexColor, '#');
        if (strlen($normalized) !== 6) {
            return '#FFFFFF';
        }

        $red = hexdec(substr($normalized, 0, 2));
        $green = hexdec(substr($normalized, 2, 2));
        $blue = hexdec(substr($normalized, 4, 2));
        $luminance = (0.299 * $red + 0.587 * $green + 0.114 * $blue) / 255;

        return $luminance > 0.7 ? '#111827' : '#FFFFFF';
    }
};
